<?php
class PC_plugin_pc_shop_mini_basket_widget extends PC_plugin_pc_shop_widget {
	
	protected $_template_group = 'mini_basket';
	
	protected function _get_default_config() {
		return array(
			'url' => '',
			'page_id' => 0,
			'page_ref' => '',
			'default_order_data' => array()
		);
	}
	
	protected function _get_cart_url() {
		$shop_page_url = $this->_get_url(true);	
		$cart_url = pc_append_route($shop_page_url, 'cart');
		return $cart_url;
	}
	
	public function get_data() {
		$this->click('get_data()');
		$shop = $this->core->Get_object('PC_shop_site');
		$data = array(
		);
		$shop->cart->absorb_debug_settings($this);
		$data['cart_data'] = $shop->cart->Get($this->_config['default_order_data']);
		$this->debug($shop->cart->get_exec_times_summary());
		$this->debug($shop->cart->get_debug_string());
		$this->click('after cart_data');
		$data['highlight_cart'] = $this->site->Get_data('pc_shop_highlight_cart');
		$this->click('after highlight_cart');
		$data['cart_url'] = $this->_get_cart_url();
		$this->click('after _get_cart_url');
		
		//print_pre($data);
		return $data;
	}
}