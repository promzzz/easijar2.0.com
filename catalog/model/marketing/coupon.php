<?php
class ModelMarketingCoupon extends Model {
	public function addCoupon($seller_id,$data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "coupon SET name = '" . $this->db->escape((string)$data['name']) . "', code = '" . $this->db->escape((string)$data['code']) . "', discount = '" . (float)$data['discount'] . "', type = '" . $this->db->escape((string)$data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['logged'] . "', shipping = '" . (int)$data['shipping'] . "', date_start = '" . $this->db->escape((string)$data['date_start']) . "', date_end = '" . $this->db->escape((string)$data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . (int)$data['uses_customer'] . "', status = '" . (int)$data['status'] . "', seller_id = '" . (int)$seller_id ."', date_added = NOW()");

		$coupon_id = $this->db->getLastId();

		return $coupon_id;
	}

	public function editCoupon($coupon_id,$seller_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "coupon SET name = '" . $this->db->escape((string)$data['name']) . "', discount = '" . (float)$data['discount'] . "', total = '" . (float)$data['total'] . "', date_start = '" . $this->db->escape((string)$data['date_start']) . "', date_end = '" . $this->db->escape((string)$data['date_end']) . "', uses_customer = '" . (int)$data['uses_customer'] . "', uses_total = '" . (int)$data['uses_total'] . "', status = '" . (int)$data['status'] . "' WHERE coupon_id = '" . (int)$coupon_id . "' AND seller_id = '" . (int)$seller_id . "'");
	}

	public function deleteCoupon($coupon_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int)$coupon_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_history WHERE coupon_id = '" . (int)$coupon_id . "'");
	}

	public function getCoupon($coupon_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "'");

		return $query->row;
	}

	public function getCouponByCode($code) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE code = '" . $this->db->escape($code) . "'");

		return $query->row;
	}

	public function getCoupons($data = array(),$seller_id=0) {

		$sql = "SELECT coupon_id, name, code, discount, date_start, date_end, status FROM " . DB_PREFIX . "coupon WHERE seller_id = '" . $seller_id . "'";

		if(isset($data['date']) && $data['date'] == 1){
			$sql .= " AND date_start <= NOW() AND date_end >= NOW() AND status = 1";
		}

		$sort_data = array(
			'name',
			'code',
			'discount',
			'date_start',
			'date_end',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getCouponProducts($coupon_id) {
		$coupon_product_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");

		foreach ($query->rows as $result) {
			$coupon_product_data[] = $result['product_id'];
		}

		return $coupon_product_data;
	}

	public function getCouponCategories($coupon_id) {
		$coupon_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int)$coupon_id . "'");

		foreach ($query->rows as $result) {
			$coupon_category_data[] = $result['category_id'];
		}

		return $coupon_category_data;
	}

	public function getTotalCoupons($seller_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "coupon WHERE seller_id = '" . (int)$seller_id . "'");

		return $query->row['total'];
	}

	public function getCouponHistories($coupon_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT ch.order_id, c.fullname AS customer, ch.amount, ch.date_added FROM " . DB_PREFIX . "coupon_history ch LEFT JOIN " . DB_PREFIX . "customer c ON (ch.customer_id = c.customer_id) WHERE ch.coupon_id = '" . (int)$coupon_id . "' ORDER BY ch.date_added ASC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalCouponHistories($coupon_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "coupon_history WHERE coupon_id = '" . (int)$coupon_id . "'");

		return $query->row['total'];
	}

	public function insertCoupon($coupon_id = 0, $customer_id = 0){
		$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_customer SET coupon_id = '" . (int)$coupon_id . "', customer_id = '" . (int)$customer_id . "'");
		return $this->db->getLastId();
	}

	public function isGetCoupon($coupon_id = 0, $customer_id = 0){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "coupon_customer` WHERE `coupon_id` = '" . (int)$coupon_id . "' AND customer_id = '" . (int)$customer_id . "'");
		return $query->row['total'];
	}

	public function getCustomerCoupons()
	{
		$query = $this->db->query("SELECT `coupon_id` FROM " . get_tabname('coupon_customer') . " WHERE customer_id = '" . (int)$this->customer->getId() . "'");
		return $query->rows;
	}
}