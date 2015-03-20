<?php

class PC_shop_manufacturers_admin_api extends PC_shop_admin_api {
	protected function _get_model() {
		return $this->core->Get_object('PC_shop_manufacturer_model');
	}
	
	
	protected function _get_available_order_columns() {
		return array();
	}
	
}

?>
