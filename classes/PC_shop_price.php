<?php

class PC_shop_price extends PC_base {
	
	/**
	 *
	 * @var array
	 */
	public $currency_rates;
	
	
	public $base_currency;
	
	/**
	 * 
	 * @param boolean $all_ln Load currency rates for all languages
	 */
	public function Init($all_ln = false) {
		$this->base_currency = $this->cfg['pc_shop']['currency'];
		$this->load_prices();
		
	}
	
	public function load_prices($all_ln = false) {
		$currency_rate_model = $this->core->Get_object('PC_shop_currency_rate_model');
		$this->currency_rates = $currency_rate_model->get_all(array(
			'join' => "LEFT JOIN {$this->db_prefix}shop_currencies sc ON sc.id = t.c_id",
			'select' => 't.rate, sc.code',
			'key' => 'code',
			'value' => 'rate'
		));
	}
	
	public function get_converted_price_in_currency($price, $currency_code, $do_not_round = false) {
		$converted_price = 0;
		if ($currency_code == $this->base_currency) {
			return $price;
		}
		$round_to = 2;
		if ($do_not_round) {
			$round_to = 4;
		}
		if (isset($this->currency_rates[$currency_code])) {
			return round($this->currency_rates[$currency_code] * $price, $round_to);
		}
		return $converted_price;
	}
		
}
