<?php
class ControllerExtensionPaymentPayssion extends Controller {
	private $error = array();
	public function index() {
		$this->load->language('extension/payment/payssion');
		
		$title = $this->language->get('heading_title');
		$class_name = get_class($this);
		$index = strrpos($class_name, 'Payssion');
		$id_raw = strtolower(substr($class_name, $index));
		$id = 'payment_' . $id_raw;
		$channel = false;
		if (strlen($class_name) - $index > 8) {
			$channel = true;
			$title = substr($class_name, $index + 8) . ' (via Payssion)';
		}
		
		$data['pm'] = $id;
		
		$this->document->setTitle($title);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting($id, $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$data['heading_title'] = $title;
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_testmode_on'] = $this->language->get('text_testmode_on');
		$data['text_testmode_off'] = $this->language->get('text_testmode_off');

		$data['entry_apikey'] = $this->language->get('entry_apikey');
		$data['entry_secretkey'] = $this->language->get('entry_secretkey');
		$data['entry_test'] = $this->language->get('entry_test');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_expired_status'] = $this->language->get('entry_expired_status');
		$data['entry_pending_status'] = $this->language->get('entry_pending_status');
		$data['entry_canceled_status'] = $this->language->get('entry_canceled_status');
		$data['entry_failed_status'] = $this->language->get('entry_failed_status');
		$data['entry_chargeback_status'] = $this->language->get('entry_chargeback_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');


		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['apikey'])) {
			$data['error_apikey'] = $this->error['apikey'];
		} else {
			$data['error_apikey'] = '';
		}

		if (isset($this->error['secretkey'])) {
			$data['error_secretkey'] = $this->error['secretkey'];
		} else {
			$data['error_secretkey'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $title,
			'href' => $this->url->link('extension/payment/' . $id_raw, 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['action'] = $this->url->link('extension/payment/' . $id_raw, 'user_token=' . $this->session->data['user_token'], 'SSL');

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL');

		if (isset($this->request->post['payment_payssion_apikey'])) {
			$data['payment_payssion_apikey'] = $this->request->post['payment_payssion_apikey'];
		} else {
			$data['payment_payssion_apikey'] = $this->config->get('payment_payssion_apikey');
		}

		if (isset($this->request->post['payssion_secretkey'])) {
			$data['payment_payssion_secretkey'] = $this->request->post['payment_payssion_secretkey'];
		} else {
			$data['payment_payssion_secretkey'] = $this->config->get('payment_payssion_secretkey');
		}

		if (isset($this->request->post['payment_payssion_test'])) {
			$data['payment_payssion_test'] = $this->request->post['payment_payssion_test'];
		} else {
			$data['payment_payssion_test'] = $this->config->get('payment_payssion_test');
		}

		if (isset($this->request->post['payment_payssion_total'])) {
			$data['payment_payssion_total'] = $this->request->post['payment_payssion_total'];
		} else {
			$data['payment_payssion_total'] = $this->config->get('payment_payssion_total');
		}

		if (isset($this->request->post['payment_payssion_order_status_id'])) {
			$data['payment_payssion_order_status_id'] = $this->request->post['payment_payssion_order_status_id'];
		} else {
			$data['payment_payssion_order_status_id'] = $this->config->get('payment_payssion_order_status_id');
		}
		
		if (isset($this->request->post['payment_payssion_pending_status_id'])) {
			$data['payment_payssion_pending_status_id'] = $this->request->post['payment_payssion_pending_status_id'];
		} else {
			$data['payment_payssion_pending_status_id'] = $this->config->get('payment_payssion_pending_status_id');
		}
		
		if (isset($this->request->post['payment_payssion_canceled_status_id'])) {
			$data['payment_payssion_canceled_status_id'] = $this->request->post['payment_payssion_canceled_status_id'];
		} else {
			$data['payment_payssion_canceled_status_id'] = $this->config->get('payment_payssion_canceled_status_id');
		}
		
		if (isset($this->request->post['payment_payssion_expired_status_id'])) {
			$data['payment_payssion_expired_status_id'] = $this->request->post['payment_payssion_expired_status_id'];
		} else {
			$data['payment_payssion_expired_status_id'] = $this->config->get('payment_payssion_expired_status_id');
		}
		
		if (isset($this->request->post['payment_payssion_failed_status_id'])) {
			$data['payment_payssion_failed_status_id'] = $this->request->post['payment_payssion_failed_status_id'];
		} else {
			$data['payment_payssion_failed_status_id'] = $this->config->get('payment_payssion_failed_status_id');
		}
		
		if (isset($this->request->post['payment_payssion_chargeback_status_id'])) {
			$data['payment_payssion_chargeback_status_id'] = $this->request->post['payment_payssion_chargeback_status_id'];
		} else {
			$data['payment_payssion_chargeback_status_id'] = $this->config->get('payment_payssion_chargeback_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$geo_zone_id = $id . '_geo_zone_id';
		if (isset($this->request->post[$geo_zone_id])) {
			$data[$geo_zone_id] = $this->request->post[$geo_zone_id];
		} else {
			$data[$geo_zone_id] = $this->config->get($geo_zone_id);
		}
		$data['current_geo_zone_id'] = $data[$geo_zone_id];

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$pm_status = $id . '_status';
		if (isset($this->request->post[$pm_status])) {
			$data[$pm_status] = $this->request->post[$pm_status];
		} else {
			$data[$pm_status] = $this->config->get($pm_status);
		}
		$data['pm_status_name'] = $pm_status;
		$data['current_pm_status'] = $data[$pm_status];

		$pm_sortorder = $id . '_sort_order';
		if (isset($this->request->post[$pm_sortorder])) {
			$data[$pm_sortorder] = $this->request->post[$pm_sortorder];
		} else {
			$data[$pm_sortorder] = $this->config->get($pm_sortorder);
		}
		$data['current_sort_order'] = $data[$pm_sortorder];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput(
				$this->load->view($channel ? 'extension/payment/payssion_channel' : 'extension/payment/payssion', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/payssion')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['payment_payssion_apikey']) && !$this->request->post['payment_payssion_apikey']) {
			$this->error['apikey'] = $this->language->get('error_apikey');
		}

		if (isset($this->request->post['payment_payssion_secretkey']) && !$this->request->post['payment_payssion_secretkey']) {
			$this->error['secretkey'] = $this->language->get('error_secretkey');
		}

		return !$this->error;
	}
}