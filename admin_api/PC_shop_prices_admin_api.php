<?php

class PC_shop_prices_admin_api extends PC_shop_admin_api {

	/**
	 *
	 * @var int
	 */
	public $pkey;
	
	/**
	 *
	 * @var array
	 */
	public $pkeys;
	
	public $price_in_base_currency;
	
	protected function _get_model() {
		return $this->core->Get_object('PC_shop_price_model');
	}
	
	protected function _get_sync_fields() {
		return array('price', 'c_id', 'pkey');
	}
	
	/**
	 * Plugin access is being checked
	 */
	protected function _before_action() {
		$this->pkey = v($_POST['pkey'], v($this->route[3]));
		$this->_check_plugin_access();
	}
	
	protected function _adjust_search(&$params) {
		$currency_model = new PC_shop_currency_model();
		$base_currency_id = $currency_model->get_one(array(
			'where' => array(
				'code' => $this->cfg['pc_shop']['currency'],
			),
			'value' => 'id'
		));
		/*
		$params['where'][] = array(
			'field' => 'c_id',
			'op' => '!=',
			'value' => $base_currency_id
		);
		*/
		$params['where']['pkey'] = v($_POST['pkey'], v($this->route[3]));
		vv($params['join'], array());
		$params['join'][] = "LEFT JOIN {$this->db_prefix}shop_currencies sc ON sc.id = t.c_id";
		$params['select'] = 't.*, sc.code';
	}
	
	protected function _after_get() {
		$product_price_in_base_currency = 0;
		
		$currency_ids_with_custom_prices = array();
		foreach ($this->_out['list'] as $key => $value) {
			$currency_ids_with_custom_prices[] = $value['c_id'];
			if ($value['code'] == $this->cfg['pc_shop']['currency']) {
				$product_price_in_base_currency = $value['price'];
			}
		}
		if (!v($this->selected_currencies)) {
			$ln_currency_model = new PC_shop_ln_currency_model();
			$this->selected_currencies = $ln_currency_model->get_all(array(
				'select' => 'distinct(t.c_id), t.*, sc.code',
				'join' => "LEFT JOIN {$this->db_prefix}shop_currencies sc ON sc.id = t.c_id",
				'key' => 'c_id',
				'where' => array(
					/*
					array(
						'field' => 'sc.code',
						'op' => '!=',
						'value' => $this->cfg['pc_shop']['currency']
					)
					*/
				)
			));
		}
		
		
			
		$all_currency_ids = array_keys($this->selected_currencies);
		
		foreach ($all_currency_ids as $c_id) {
			if (in_array($c_id, $currency_ids_with_custom_prices)) {
				continue;
			}
			$this->_out['list'][] = array(
				//'id' => 0,
				//'phantom' => true,
				'pkey' => $this->pkey,
				'price' => 0,
				'c_id' => $c_id,
				'code' => $this->selected_currencies[$c_id]['code']
			);
		}
		
		$price_manager = new PC_shop_price(true);
		//print_pre($price_manager->currency_rates);
		
		$base_key = -1;
		foreach ($this->_out['list'] as $key => $value) {
			if ($value['code'] == $this->cfg['pc_shop']['currency']) {
				$base_key = $key;
			}
			$currency_ids_with_custom_prices[] = $value['c_id'];
			$this->_out['list'][$key]['converted_price'] = $price_manager->get_converted_price_in_currency($product_price_in_base_currency, $value['code']);
		}
		
		if ($base_key > -1) {
			$base_currency_price_data = $this->_out['list'][$base_key];
			unset($this->_out['list'][$base_key]);
			$this->_out['list'] = array_merge(array($base_currency_price_data), $this->_out['list']);
		}
		
		//print_pre($this->_out['list']);
		
		
		//$price_manager = $this->core->Get_object('PC_shop_price');
	}
	
	protected function _before_insert(&$data, &$content) {
		$data['pkey'] = $this->pkey;
	}
	
	protected function _before_update(&$data, &$content) {
		unset($data['pkey']);
	}
	
}

?>
