<?php 
class ModelExtensionPaymentPayssion extends Model {
  	public function getMethod($address) {
		$this->load->language('extension/payment/payssion');
		$title = $this->language->get('text_title');
		$class_name = get_class($this);
		$index = strrpos($class_name, 'Payssion');
		$id_raw = strtolower(substr($class_name, $index));
		$id = 'payment_' . $id_raw;
		
		$title = $this->language->get("text_title_");
		$channel = false;
		if (strlen($class_name) - $index > 8) {
			$channel = true;
			$pm = substr($class_name, $index + 8);
			$key = "text_title_" . strtolower($pm);
			$title = $this->language->get($key);
			$title = ($title && $title != $key ? $title : $pm) . ' (Payssion)';
		}
		
		if ($channel && $this->config->get($id . '_status')) {
			$geo_zone_id = $id . '_geo_zone_id';
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get($geo_zone_id) . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
			if (!$this->config->get($geo_zone_id)) {
				$status = TRUE;
			} elseif ($query->num_rows) {
				$status = TRUE;
			} else {
				$status = FALSE;
			}
		} else {
			$status = FALSE;
		}
		$method_data = array();
		
		if ($status) {  
			$method_data = array( 
				'code'		 => $id_raw,
				'title'		 => $title,
				'terms'      => '',
				'sort_order' => $this->config->get($id . '_sort_order')
			);
		}
		return $method_data;
	}
}

