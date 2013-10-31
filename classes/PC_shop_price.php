<?php

class PC_shop_price extends PC_base {
	
	/**
	 *
	 * @var array
	 */
	public $currency_rates;
	
	/**
	 *
	 * @var array
	 */
	public $currencies;
	
	public $base_currency;
	
	/**
	 * 
	 * @param boolean $all_ln Load currency rates for all languages
	 */
	public function Init($all_ln = false) {
		$this->base_currency = $this->cfg['pc_shop']['currency'];
		$this->load_currencies();
		$this->load_prices();
	}
	
	public function load_currencies() {
		$ln_currency_model = $this->core->Get_object('PC_shop_ln_currency_model');
		$this->currencies = $ln_currency_model->get_all(array(
			'where' => array(
				'ln' => $this->site->ln
			),
			'join' => "LEFT JOIN {$this->db_prefix}shop_currencies sc ON sc.id = t.c_id",
			'select' => 'sc.code, sc.id',
			'key' => 'code',
			'value' => 'id',
			'order' => 't.position'
		));
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
		
	public function get_price_in_user_currency($base_price) {
		return 10;
	}
	
	public function get_base_currency() {
		return $this->base_currency;
	}
	
	public function get_user_currency() {
		$currency = '';
		if(isset($_COOKIE['pc_currency'])) {
			$currency = $_COOKIE['pc_currency'];
		}
		elseif (isset($_SESSION['pc_currency'])) {
			$currency = $_SESSION['pc_currency'];
		}
		if (empty($currency) or !isset($this->currencies[$currency])) {
			$currency = $this->base_currency;
			if (!isset($this->currencies[$currency])) {
				$arr_keys = array_keys($this->currencies);
				if (isset($arr_keys[0])) {
					$currency = $arr_keys[0];
				}
			}
		}
		return $currency;
	}
	
	public function get_user_currency_id() {
		$currency = $this->get_user_currency();
		if (!isset($this->currencies[$currency])) {
			return false;
		}
		return $this->currencies[$currency];
	}
	
	public function set_user_currency($currency_code) {
		if (isset($this->currencies[$currency_code])) {
			$_SESSION['pc_currency'] = $currency_code;
			setcookie("pc_currency", $currency_code);
		}
		
	}
	
}
