<?php

class PC_shop_delivery_options_admin_api extends PC_shop_admin_api {

	/**
	 *
	 * @var PC_shop_manager 
	 */
	protected $shop;
	
	protected $_default_order = 'position';
	
	protected $_content_fields = array(
		'name'
	);
	
	protected function _get_model() {
		return $this->core->Get_object('PC_shop_delivery_option_model');
	}
	
	
	protected function _get_available_order_columns() {
		return array();
	}
	
	protected function _after_get() {
		require_once PLUGINS_ROOT . DS .  'pc_shop/admin_api/PC_shop_prices_admin_api.php';
		$prices_api = new PC_shop_prices_admin_api();
		$prices_api->absorb_debug_settings($this);
		
		foreach ($this->_out['list'] as $key => $delivery_option) {
			//print_pre($delivery_option);
			$_POST['pkey'] = $prices_api->pkey = 'delivery_option_' . $delivery_option['code'] . '_delivery_price';
			$prices_api->price_in_base_currency = $delivery_option['delivery_price'];
			$prices_api->get();
			$output = $prices_api->get_output();
			$this->_out['list'][$key]['delivery_prices'] = $output['list'];
			
			$_POST['pkey'] = $prices_api->pkey = 'delivery_option_' . $delivery_option['code'] . '_no_delivery_price_from';
			$prices_api->price_in_base_currency = $delivery_option['no_delivery_price_from'];
			$prices_api->get();
			$output = $prices_api->get_output();
			$this->_out['list'][$key]['no_delivery_prices_from'] = $output['list'];
			
			$_POST['pkey'] = $prices_api->pkey = 'delivery_option_' . $delivery_option['code'] . '_cod_price';
			$prices_api->price_in_base_currency = $delivery_option['cod_price'];
			$prices_api->get();
			$output = $prices_api->get_output();
			$this->_out['list'][$key]['cod_prices'] = $output['list'];
			
			$_POST['pkey'] = $prices_api->pkey = 'delivery_option_' . $delivery_option['code'] . '_no_cod_price_from';
			$prices_api->price_in_base_currency = $delivery_option['no_cod_price_from'];
			$prices_api->get();
			$output = $prices_api->get_output();
			$this->_out['list'][$key]['no_cod_prices_from'] = $output['list'];
		}
		//$_POST['pkey']
		//print_pre($this->_out['list']);
	}
	
}

?>
