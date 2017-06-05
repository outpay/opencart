<?php
class ControllerExtensionPaymentOutpay extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/outpay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('outpay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
		}

		/* Load Models */
		$this->load->model('localisation/order_status');
		$this->load->model('localisation/geo_zone');
		$this->load->model('customer/custom_field');

		/* Outpay Status */
		$data['outpay_statuses'] = ['generating', 'opened', 'canceled', 'paid', 'overdue', 'blocked', 'chargeback'];

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] 		= $this->language->get('text_edit');
		$data['text_enabled']   = $this->language->get('text_enabled');
		$data['text_disabled']  = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] 		= $this->language->get('text_yes');
		$data['text_no'] 		= $this->language->get('text_no');
		$data['text_custom_field'] = $this->language->get('text_custom_field');

		$data['entry_token'] = $this->language->get('entry_token');
		$data['entry_total'] = $this->language->get('entry_total');
		foreach($data['outpay_statuses'] as $outpay_order_status){
			$data['entry_order_status_'.$outpay_order_status] = $this->language->get('entry_order_status_'.$outpay_order_status);
		}
		$data['entry_notify']     = $this->language->get('entry_notify');
		$data['entry_address_number'] = $this->language->get('entry_address_number');
		$data['entry_cpf'] 		  = $this->language->get('entry_cpf');
		$data['entry_callback']	  = $this->language->get('entry_callback');
		$data['entry_geo_zone']   = $this->language->get('entry_geo_zone');
		$data['entry_status']     = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['help_token']  = $this->language->get('help_token');
		$data['help_total']  = $this->language->get('help_total');
		$data['help_notify'] = $this->language->get('help_notify');
		$data['help_address_number'] = $this->language->get('help_address_number');
		$data['help_cpf'] 	   = $this->language->get('help_cpf');
		$data['help_callback'] = $this->language->get('help_callback');

		$data['button_save']   = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/outpay', 'token=' . $this->session->data['token'], true)
		);

		/* Token */
		if (isset($this->request->post['outpay_token'])) {
			$data['outpay_token'] = $this->request->post['outpay_token'];
		} else {
			$data['outpay_token'] = $this->config->get('outpay_token');
		}

		/* Total */
		if (isset($this->request->post['outpay_total'])) {
			$data['outpay_total'] = $this->request->post['outpay_total'];
		} else {
			$data['outpay_total'] = $this->config->get('outpay_total');
		}

		/* Statuses */
		foreach($data['outpay_statuses'] as $outpay_order_status){
			if (isset($this->request->post['outpay_order_status_'.$outpay_order_status])) {
				$data['outpay_order_status_'.$outpay_order_status] = $this->request->post['outpay_order_status_'.$outpay_order_status];
			} else {
				$data['outpay_order_status_'.$outpay_order_status] = $this->config->get('outpay_order_status_'.$outpay_order_status);
			}
		}

		/* Notify */
		if (isset($this->request->post['outpay_notify'])) {
			$data['outpay_notify'] = $this->request->post['outpay_notify'];
		} else {
			$data['outpay_notify'] = $this->config->get('outpay_notify');
		}

		/* Custom Field (Number) */
		if (isset($this->request->post['outpay_address_number'])) {
			$data['outpay_address_number'] = $this->request->post['outpay_address_number'];
		} else {
			$data['outpay_address_number'] = $this->config->get('outpay_address_number');
		}

		/* Custom Field (CPF | CPNJ) */
		if (isset($this->request->post['outpay_cpf'])) {
			$data['outpay_cpf'] = $this->request->post['outpay_cpf'];
		} else {
			$data['outpay_cpf'] = $this->config->get('outpay_cpf');
		}

		/* Callback */
		if (isset($this->request->post['outpay_callback'])) {
			$data['outpay_callback'] = $this->request->post['outpay_callback'];
		} else {
			$data['outpay_callback'] = $this->config->get('outpay_callback');
		}

		/* Geo Zone */
		if (isset($this->request->post['outpay_geo_zone_id'])) {
			$data['outpay_geo_zone_id'] = $this->request->post['outpay_geo_zone_id'];
		} else {
			$data['outpay_geo_zone_id'] = $this->config->get('outpay_geo_zone_id');
		}

		/* Status */
		if (isset($this->request->post['outpay_status'])) {
			$data['outpay_status'] = $this->request->post['outpay_status'];
		} else {
			$data['outpay_status'] = $this->config->get('outpay_status');
		}

		/* Sort Order */
		if (isset($this->request->post['outpay_sort_order'])) {
			$data['outpay_sort_order'] = $this->request->post['outpay_sort_order'];
		} else {
			$data['outpay_sort_order'] = $this->config->get('outpay_sort_order');
		}

		/* Order Statuses */
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		/* Geo Zones */
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		/* Custom Fields */
		$data['custom_fields'] = $this->model_customer_custom_field->getCustomFields();

		/* Links */
		$data['action'] = $this->url->link('extension/payment/outpay', 'token=' . $this->session->data['token'], true);
		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

		$data['link_custom_field'] = $this->url->link('customer/custom_field', 'token=' . $this->session->data['token'], true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/outpay', $data));
	}

	public function validate() {
		/* Error Permission */
		if (!$this->user->hasPermission('modify', 'extension/payment/outpay')) {
			$this->error['warning'] = $this->language->get('warning');
		}

		/* Error Token */
		if (strlen($this->request->post['outpay_token']) <= 0) {
			$this->error['token'] = $this->language->get('error_token');
		}

		/* Error Total */
		if ($this->request->post['outpay_total']) {
			if (!filter_var($this->request->post['outpay_total'], FILTER_VALIDATE_FLOAT)) {
				$this->request->post['outpay_total'] = 0.00;
			}
		}

		/* Error Callback */
		if (strlen($this->request->post['outpay_callback']) <= 0) {
			$this->error['callback'] = $this->language->get('error_callback');
		}

		return !$this->error;
	}

	public function install() {
		// Create Table Outpay
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "outpay` (
			  `order_id` int(11) NOT NULL,
			  `token` VARCHAR(256) NOT NULL,
			  `expire_at` DATE NOT NULL,
			  `status_billet` VARCHAR(256) NOT NULL,
			  `date_added` DATETIME NOT NULL,
			  `date_modified` DATETIME NOT NULL,
			  PRIMARY KEY (`order_id`),
			  UNIQUE INDEX `token` (`token`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
	}
}
