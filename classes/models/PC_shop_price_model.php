<?php

class PC_shop_price_model extends PC_model {
	
	protected $_table = 'shop_prices';
	
	protected function _set_tables() {
		$this->_table = 'shop_prices';
	}
	
	public function get_price($key) {
		$prices = $this->get_all(array(
			'where' => array(
				'pkey' => $key
			),
			'key' => 'c_id',
			'value' => 'price'
		));

		$shop_price = $this->core->Get_object('PC_shop_price');
		$user_currency_id = $shop_price->get_user_currency_id();
		$base_currency_id = $shop_price->get_base_currency_id();
		if (isset($prices[$user_currency_id])) {
			$price = $prices[$user_currency_id];
		}
		elseif ($base_currency_id and isset($prices[$base_currency_id])) {
			$price = $shop_price->get_converted_price_in_currency_id($prices[$base_currency_id], $user_currency_id);

		}
		else {
			$price = 0;
		}
		return $price;
	}
	
}
