<?php
class PC_plugin_pc_shop_cart_widget extends PC_plugin_pc_shop_widget {
	
	protected $_template_group = 'cart';
	
	public function Init($config = array(), $product = null, $shop_products_site = null) {
		parent::Init($config);
		$this->_template_group = ':_plugin/' . $this->plugin_name . '/' . $this->_template_group;
	}
	
	protected function _get_default_config() {
		return array(
			'default_order_data' => array()
		);
	}

	
	public function get_data() {
		$shop = $this->core->Get_object('PC_shop_site');
		$data = array(
			'cart_data' => $shop->cart->Get($this->_config['default_order_data']),
			'order_fast_url' => pc_append_route(pc_append_route($this->page->Get_current_page_link(), 'order'), 'fast')
		);
		return $data;
	}
}