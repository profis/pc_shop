<?php

class PC_shop_coupon_model extends PC_model {
	
	
	protected $_table = 'shop_coupons';
	
	protected function _set_tables() {
		$this->_table = 'shop_coupons';
	}
	
	protected function _set_rules() {
		$this->_rules = array(
			array(
				'field' => 'code',
				'rule' => 'unique'
			),
			
			
		);
	}
	
}
