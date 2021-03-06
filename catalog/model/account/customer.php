<?php
class ModelAccountCustomer extends Model {
	public function addCustomer($data) {
		if (isset($data['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($data['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $data['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$this->load->model('account/customer_group');

		$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);
		$from = array_get($data, 'from', 'website');

		$this->db->query("INSERT INTO " . DB_PREFIX . "customer SET customer_group_id = '" . (int)$customer_group_id . "', store_id = '" . (int)$this->config->get('config_store_id') . "', language_id = '" . (int)$this->config->get('config_language_id') . "', fullname = '" . (isset($data['fullname']) ? $this->db->escape((string)$data['fullname']) : '') . "', email = '" . $this->db->escape((string)$data['email']) . "', telephone = '" . $this->db->escape((string)$data['telephone']) . "', custom_field = '" . $this->db->escape(isset($data['custom_field']['account']) ? json_encode($data['custom_field']['account']) : '') . "', salt = '', password = '" . $this->db->escape(password_hash($data['password'], PASSWORD_DEFAULT)) . "', newsletter = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', status = '" . (int)!$customer_group_info['approval'] . "', `from` = '" . $from . "', date_added = NOW()");

		$customer_id = $this->db->getLastId();

		if ($customer_group_info['approval']) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_approval` SET customer_id = '" . (int)$customer_id . "', type = 'customer', date_added = NOW()");
		}

		return $customer_id;
	}

	public function editCustomer($customer_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET fullname = '" . $this->db->escape((string)$data['fullname']) . "', email = '" . $this->db->escape((string)$data['email']) . "', telephone = '" . $this->db->escape((string)$data['telephone']) . "', custom_field = '" . $this->db->escape(isset($data['custom_field']['account']) ? json_encode($data['custom_field']['account']) : '') . "' WHERE customer_id = '" . (int)$customer_id . "'");
	}

	public function editTelephone($telephone) {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET telephone = '" . $this->db->escape((string)$telephone) . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function editPassword($customer_id, $password) {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET salt = '', password = '" . $this->db->escape(password_hash($password, PASSWORD_DEFAULT)) . "', code = '' WHERE customer_id = '" . (int)$customer_id . "'");
	}

	public function editAddressId($customer_id, $address_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
	}

	public function editCode($customer_id, $code) {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET code = '" . $this->db->escape($code) . "' WHERE customer_id = '" . (int)$customer_id . "'");
	}

	public function editToken($customer_id, $token) {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET token = '" . $this->db->escape($token) . "' WHERE customer_id = '" . (int)$customer_id . "'");
	}

	public function editFullName($fullname) {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET fullname = '" . $this->db->escape((string)$fullname) . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function editBrithday($brithday) {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET brithday = '" . $this->db->escape((string)$brithday) . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function editGender($gender) {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET gender = '" . (int)$gender . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function editNewsletter($newsletter) {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET newsletter = '" . (int)$newsletter . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function getCustomer($customer_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row;
	}

	public function getCustomerByEmail($email) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row;
	}

	public function getCustomerByTelephone($telephone) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(telephone) = '" . $this->db->escape(utf8_strtolower($telephone)) . "'");

		return $query->row;
	}

	public function getCustomerByCode($code) {
		$query = $this->db->query("SELECT customer_id, fullname, email FROM `" . DB_PREFIX . "customer` WHERE code = '" . $this->db->escape($code) . "' AND code != ''");

		return $query->row;
	}

	public function getCustomerByToken($token) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE token = '" . $this->db->escape($token) . "' AND token != ''");

		$this->db->query("UPDATE " . DB_PREFIX . "customer SET token = ''");

		return $query->row;
	}

	public function getTotalCustomersByEmail($email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row['total'];
	}

	public function getTotalCustomersByTelephone($telephone) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE telephone = '" . $this->db->escape($telephone) . "'");

		return $query->row['total'];
	}

	public function addTransaction($customer_id, $description, $amount = '', $order_id = 0, $order_recharge_id = 0, $withdraw_id = 0) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_transaction SET customer_id = '" . (int)$customer_id . "', order_id = '" . (float)$order_id . "', order_recharge_id = '" . (int)$order_recharge_id . "', withdraw_id = '" . (int)$withdraw_id . "', description = '" . $this->db->escape($description) . "', amount = '" . (float)$amount . "', date_added = NOW()");
	}

	public function deleteTransactionByOrderId($order_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_transaction WHERE order_id = '" . (int)$order_id . "'");
	}

	public function deleteTransactionByOrderRechargeId($order_recharge_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_transaction WHERE order_recharge_id = '" . (int)$order_recharge_id . "'");
	}

	public function getTotalTransactionsByOrderRechargeId($order_recharge_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_transaction WHERE order_recharge_id = '" . (int)$order_recharge_id . "'");

		return $query->row['total'];
	}

	public function getTransactionTotal($customer_id) {
		$query = $this->db->query("SELECT SUM(amount) AS total FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row['total'];
	}

	public function getTotalTransactionsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_transaction WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

	public function getRewardTotal($customer_id) {
		$query = $this->db->query("SELECT SUM(points) AS total FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row['total'];
	}

	public function getIps($customer_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_ip` WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->rows;
	}

	public function addLogin($customer_id, $ip, $country = '') {
		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_ip SET customer_id = '" . (int)$customer_id . "', store_id = '" . (int)$this->config->get('config_store_id') . "', ip = '" . $this->db->escape($ip) . "', country = '" . $this->db->escape($country) . "', date_added = NOW()");
	}

	public function addLoginAttempt($customer_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_login WHERE customer_id = '" . (int)$customer_id . "' AND ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_login SET customer_id = '" . (int)$customer_id . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', total = 1, date_added = '" . $this->db->escape(date('Y-m-d H:i:s')) . "', date_modified = '" . $this->db->escape(date('Y-m-d H:i:s')) . "'");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "customer_login SET total = (total + 1), date_modified = '" . $this->db->escape(date('Y-m-d H:i:s')) . "' WHERE customer_login_id = '" . (int)$query->row['customer_login_id'] . "'");
		}
	}

	public function getLoginAttempts($customer_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_login` WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row;
	}

	public function deleteLoginAttempts($customer_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_login` WHERE customer_id = '" . (int)$customer_id . "'");
	}
}
