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
	
	protected function _format(&$data) {
		$this->_format_dates($data);
		$data['prices_key'] = 'coupon_' . $data['id'];
	}
	
	protected function _format_dates(&$data) {
		$data['time_from'] = date('Y-m-d H:i', strtotime($data['time_from']));
		$data['time_to'] = date('Y-m-d H:i', strtotime($data['time_to']));
	}
	
	public function get_valid_coupon($code) {
		$current_time_date = date('Y-m-d H:i:s');
		$coupon_data = $this->get_one(array(
			'where' => array(
				't.code = ?',
				't.use_limit > t.used',
				'time_from <= ?',
				'time_to >= ?'
			),
			'query_params' => array(
				$code,
				$current_time_date,
				$current_time_date
			),
			//'query_only' => true
		));
		
		if ($coupon_data) {
			$price_model = $this->core->Get_object('PC_shop_price_model');
			$coupon_data['currency_discounts'] = $price_model->get_all(array(
				'where' => array(
					'pkey' => 'coupon_' . $coupon_data['id']
				),
				'key' => 'c_id',
				'value' => 'price'
			));
			
			$shop_price = $this->core->Get_object('PC_shop_price');
			$user_currency_id = $shop_price->get_user_currency_id();
			$base_currency_id = $shop_price->get_base_currency_id();
			if (isset($coupon_data['currency_discounts'][$user_currency_id])) {
				$coupon_data['discount'] = $coupon_data['currency_discounts'][$user_currency_id];
			}
			elseif ($base_currency_id and isset($coupon_data['currency_discounts'][$base_currency_id])) {
				$coupon_data['discount'] = $shop_price->get_converted_price_in_currency_id($coupon_data['currency_discounts'][$base_currency_id], $user_currency_id);
				
			}
			else {
				$coupon_data['discount'] = 0;
			}
			$coupon_data['_currency'] = $user_currency_id;
		}
		
		return $coupon_data;
	}
	
	public function use_coupon($id) {
		$this->update(array(
			'used = used + 1'
		), $id);
	}
	
}
