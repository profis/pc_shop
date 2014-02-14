<?php

class PC_shop_attribute_values_admin_api extends PC_shop_admin_api {

	
	protected $_default_order = 'position';
	
	protected function _get_model() {
		return $this->core->Get_object('PC_shop_attribute_value_model');
	}
	
	
	protected function _get_available_order_columns() {
		return array();
	}
	
}

?>
