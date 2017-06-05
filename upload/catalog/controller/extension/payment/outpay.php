<?php
class ControllerExtensionPaymentOutpay extends Controller {
	public function index() {
		$this->load->language('extension/payment/outpay');

		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['text_loading']   = $this->language->get('text_loading');

		$data['continue'] = $this->url->link('checkout/success');

		/* Load Model */
		$this->load->model('extension/payment/outpay');

		$payment = $this->model_extension_payment_outpay->getPayment($this->session->data['order_id']);
		$data['checkout_url'] = $payment ? $payment['checkout_url'] : '';

		return $this->load->view('extension/payment/outpay', $data);
	}

	public function confirm() {
		if ($this->session->data['payment_method']['code'] == 'outpay') {
			$this->load->language('extension/payment/outpay');

			/* Load Model */
			$this->load->model('extension/payment/outpay');
			$this->load->model('checkout/order');
			
			/* Informações do Pedido */
			$order_id   = $this->session->data['order_id'];
			$payment = $this->model_extension_payment_outpay->getPayment($order_id);
			if($payment){
				$comment = 'Boleto: <a href="'.$payment['checkout_url'].'" target="_blank">Clique aqui para abrir 2ª Via</a>';
				$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('outpay_order_status_'.$payment['status_billet']), $comment, true);
			}
		}
	}

	public function callback() {
		$this->load->model('extension/payment/outpay');

		$token = $this->request->post['billet_token'];
		if($token){
			$this->model_extension_payment_outpay->updatePayment($token);
		}
	}
}