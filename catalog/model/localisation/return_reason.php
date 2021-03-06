<?php
class ModelLocalisationReturnReason extends Model {
	public function getReturnReasons($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "return_reason WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'";

			if (isset($data['type'])) {
				$sql 	.= " AND type = '" . (int)$data['type'] . "'";
			}

			$sql .= " ORDER BY name";

			if (isset($data['return']) && ($data['return'] == 'DESC')) {
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
		} else {
			$return_reason_data = $this->cache->get('return_reason.' . (int)$this->config->get('config_language_id'));

			if (!$return_reason_data) {
				$query = $this->db->query("SELECT return_reason_id, name FROM " . DB_PREFIX . "return_reason WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY name");

				$return_reason_data = $query->rows;

				$this->cache->set('return_reason.' . (int)$this->config->get('config_language_id'), $return_reason_data);
			}

			return $return_reason_data;
		}
	}

	public function getRsasonNameByType($reason_id = 0,$reason_type = 0)
	{
		$query = $this->db->query("SELECT `name` FROM " . DB_PREFIX . "return_reason WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' AND return_reason_id = '".(int)$reason_id."' AND type = '".(int)$reason_type."'");
		return $query->row;
	}
}
