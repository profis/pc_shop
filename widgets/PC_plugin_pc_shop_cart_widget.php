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
		global $site_users;
		/** @var PC_shop_site $shop */
		$shop = $this->core->Get_object('PC_shop_site');
		$order_url = pc_append_route($this->page->Get_current_page_link(), 'order');
		$order_fast_url = pc_append_route($order_url, 'fast');
		if ($site_users and $site_users->Is_logged_in() or !v($this->cfg['pc_shop']['checkout_offer_to_register'])) {
			$order_url = $order_fast_url;
		}
		$data = array(
			'cart_data' => $shop->cart->Get($this->_config['default_order_data']),
			'order_data' => $shop->orders->Get_preserved_order_data(),
			'coupon_data' => $shop->cart->get_preserved_coupon_data(),
			'order_url' => $order_url,
			'order_fast_url' => $order_fast_url,
		);
		return $data;
	}
}