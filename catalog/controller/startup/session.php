<?php
class ControllerStartupSession extends Controller {
	public function index() {
		if (isset($this->request->get['route']) && substr((string)$this->request->get['route'], 0, 4) == 'api/') {

			$this->config->set('data_sign_key','a2a59c3e76604f1093f701ca670acf61');
			$this->config->set('data_api_id','f00344bad92dc5ab7b9a4c4d088f6485');

			$this->db->query("DELETE FROM `" . DB_PREFIX . "api_session` WHERE TIMESTAMPADD(HOUR, 2160, date_modified) < NOW()");

			$req_data 			= array_merge($this->request->get,$this->request->post);

			if(isset($req_data['api_token'])){
				// Make sure the IP is allowed
				$api_query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "api` `a` LEFT JOIN `" . DB_PREFIX . "api_session` `as` ON (a.api_id = as.api_id) LEFT JOIN " . DB_PREFIX . "api_ip `ai` ON (a.api_id = ai.api_id) WHERE a.status = '1' AND `as`.`session_id` = '" . $this->db->escape((string)$req_data['api_token']) . /*"' AND ai.ip = '" . $this->db->escape((string)$this->request->server['REMOTE_ADDR']) .*/ "'");
			 
				if ($api_query->num_rows) {
					$this->session->start($req_data['api_token']);
					
					// keep the session alive
					$this->db->query("UPDATE `" . DB_PREFIX . "api_session` SET `date_modified` = NOW() WHERE `api_session_id` = '" . (int)$api_query->row['api_session_id'] . "'");
				}
			}
		} else {
			if (isset($this->request->cookie[$this->config->get('session_name')])) {
				$session_id = $this->request->cookie[$this->config->get('session_name')];
			} else {
				$session_id = '';
			}
			
			$this->session->start($session_id);
			
			setcookie($this->config->get('session_name'), $this->session->getId(), (ini_get('session.cookie_lifetime') ? (time() + ini_get('session.cookie_lifetime')) : 0), ini_get('session.cookie_path'), ini_get('session.cookie_domain'));
		}
	}
}