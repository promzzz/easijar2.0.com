<?php

require_once(realpath(dirname(__FILE__)) . "/payssion.php");
class ControllerExtensionPaymentPayssionDocomoJP extends ControllerExtensionPaymentPayssion {
	protected $pm_id = 'docomo_jp';
}