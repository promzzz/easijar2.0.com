<?php

require_once(realpath(dirname(__FILE__)) . "/payssion.php");
class ControllerExtensionPaymentPayssionBoleto extends ControllerExtensionPaymentPayssion {
	protected $pm_id = 'boleto_br';
}