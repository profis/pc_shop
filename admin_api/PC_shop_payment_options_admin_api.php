<?php

class PC_shop_payment_options_admin_api extends PC_shop_admin_api {

	/**
	 *
	 * @var PC_shop_manager 
	 */
	protected $shop;
	
	protected $_default_order = 'position';
	
	protected $_content_fields = array(
		'name'
	);
	
	protected function _get_model() {
		return $this->core->Get_object('PC_shop_payment_option_model');
	}
	
	
	protected function _get_available_order_columns() {
		return array();
	}
	
}

?>
