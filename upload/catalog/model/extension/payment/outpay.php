<?php
class ModelExtensionPaymentOutpay extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/outpay');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('outpay_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('outpay_total') > 0 && $this->config->get('outpay_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('outpay_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'outpay',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('outpay_sort_order')
			);
		}

		return $method_data;
	}

	public function getPayment($order_id)
	{
		$query = $this->db->query("SELECT * FROM `".DB_PREFIX."outpay` WHERE order_id = '".$order_id."'");
		if(isset($query->row['order_id'])){
			$query->row['checkout_url'] = 'https://outpay.co/checkout/billet/'.$query->row['token'];
			return $query->row;
		}else{
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($order_id);

			$document = '';
			$cpf = $this->config->get('outpay_cpf');
			$cnpj = $this->config->get('outpay_cpf');
			if(isset($order_info['custom_field'][$cpf])){
				$document = preg_replace('/\D/', '', $order_info['custom_field'][$cpf]);	
			}elseif(isset($order_info['custom_field'][$cnpj])){
				$document = preg_replace('/\D/', '', $order_info['custom_field'][$cnpj]);	
			}

			$data = array(
				'order_amount' => number_format($order_info['total'], 2, '.', ''),
				'order_description' => $this->language->get('order_description').$order_id,
				'customer_fullname' => $order_info['payment_firstname']." ".$order_info['payment_lastname'],
				'customer_document' => $document,
				'customer_email' 	=> $order_info['email'],
				'customer_phone' 	=> preg_replace('/[^0-9]/', '', $order_info['telephone']),
			);

			if (isset($this->session->data['shipping_address'])) {
				$data['customer_address_street'] 	   = $order_info['shipping_address_1'];
	            $data['customer_address_number'] 	   = preg_replace("/[^0-9]/", "", $this->model_extension_payment_outpay->getAddressNumber($order_info['shipping_custom_field']));
	            $data['customer_address_complement']   = $this->model_extension_payment_outpay->getAddressNumber($order_info['shipping_custom_field']);
	            $data['customer_address_neighborhood'] = $order_info['shipping_address_2']; 
	            $data['customer_address_zipcode'] 	   = preg_replace('/[^\d]/', '', $order_info['shipping_postcode']);
	            $data['customer_address_city'] 		   = $order_info['shipping_city'];
	            $data['customer_address_state'] 	   = $order_info['shipping_zone_code'];
	        } else {
	            $data['customer_address_street'] 	   = $order_info['payment_address_1'];
	            $data['customer_address_number'] 	   = preg_replace("/[^0-9]/", "", $this->model_extension_payment_outpay->getAddressNumber($order_info['payment_custom_field']));
	            $data['customer_address_complement']   = $this->model_extension_payment_outpay->getAddressNumber($order_info['payment_custom_field']);
	            $data['customer_address_neighborhood'] = $order_info['payment_address_2'];
	            $data['customer_address_zipcode'] 	   = preg_replace('/[^\d]/', '', $order_info['payment_postcode']);
	            $data['customer_address_city'] 		   = $order_info['payment_city'];
	            $data['customer_address_state'] 	   = $order_info['payment_zone_code'];
			}

			$data['user_token']   = $this->config->get('outpay_token');
			$data['postback_url'] = $this->config->get('outpay_callback');

			$curl = curl_init('https://outpay.co/api/billet/create');
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json; charset=utf-8"));
			curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0');
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
			$response = curl_exec($curl);
			curl_close($curl);
			$response = json_decode($response);
			
			if($response){
				$this->db->query("REPLACE INTO `".DB_PREFIX."outpay` (order_id,token,expire_at,status_billet,date_added,date_modified) VALUES ('".$order_id."', '".$response->token."', '".$response->expire_at."', '".$response->status_billet."','".date('Y-m-d h:i:s')."','".date('Y-m-d h:i:s')."');");

				return $this->getPayment($order_id);
			}
		}
	}

	public function updatePayment($token)
	{
		$status = $this->getStatus($token);
		if($status){
			$query = $this->db->query("SELECT * FROM `".DB_PREFIX."outpay` WHERE token = '".$token."' AND status != '".$status."'");
			
			if(isset($query->row['order_id'])){
				$order_id = $query->row['order_id'];
				$order_status_id = $this->config->get('outpay_order_status_'.$status);
				if($order_status_id){
					$this->load->model('checkout/order');
					$notify = $this->config->get('outpay_notify');
					$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, '', $notify);
					$this->db->query("UPDATE `".DB_PREFIX."outpay` SET status = '".$status."' WHERE token = '".$token."';");
				}
			}
		}
	}

	public function getStatus($token)
	{
		$curl = curl_init('https://outpay.co/api/billet/status');
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json; charset=utf-8"));
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0');
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array('user_token' => $this->config->get('outpay_token'), 'billet_token' => $token)));
		$response = curl_exec($curl);
		curl_close($curl);
		$response = json_decode($response);
		
		if($response)
			return $response->billet_status;
	}

	public function getAddressNumber($custom_field) {
		if (array_key_exists($this->config->get('outpay_address_number'), $custom_field)) {
			return $custom_field[$this->config->get('outpay_address_number')];
		} else {
			return 0;
		}
	}
}