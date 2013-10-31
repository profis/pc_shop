<?php

class PC_shop_coupons_admin_api extends PC_shop_admin_api {

	
	protected $_content_fields = array(
		//'name'
	);
	
	protected function _get_model() {
		return $this->core->Get_object('PC_shop_coupon_model');
	}
	
}

?>
