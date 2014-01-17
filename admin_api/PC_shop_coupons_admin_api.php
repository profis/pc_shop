<?php

class PC_shop_coupons_admin_api extends PC_shop_admin_api {

	
	protected $_content_fields = array(
		//'name'
	);
	
	protected function _get_model() {
		return $this->core->Get_object('PC_shop_coupon_model');
	}
	
	protected function _adjust_search(&$params) {
		vv($params['select'], 't.*');
		$params['select'] .= ',cc.name as category_name';
		vv($params['join_params'], array());
		$params['join'][] = "LEFT JOIN {$this->db_prefix}shop_category_contents cc ON cc.category_id = t.category_id and cc.ln=?";
		$params['join_params'][] = $this->site->ln;
		$params['formatter'] = '_format';
	}
	
}

?>
