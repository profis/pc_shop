<?php
class PC_plugin_pc_shop_order_widget extends PC_plugin_pc_shop_widget {
	
	protected $_template_group = 'order';
	
	public function Init($config = array(), $product = null, $shop_products_site = null) {
		parent::Init($config);
		$this->_template_group = ':_plugin/' . $this->plugin_name . '/' . $this->_template_group;
	}
	
	protected function _get_default_config() {
		global $site_users;
		$default_order_data = array();
		if ($site_users and $site_users->Is_logged_in()) {
			$user_data = $site_users->Get_data(0, true);
			if ($user_data) {
				$default_order_data['name'] = $user_data['name'];
				$default_order_data['email'] = $user_data['email'];
			}
		}
		return array(
			'default_order_data' => $default_order_data
		);
	}

	/** @deprecated */
	public function get_delivery_options() {
		//return PC_shop_delivery_option_model::get_select_options();
		$m = new PC_shop_delivery_option_model();
		return $m->get_all(array(
			'content' => true,
			'where' => array(
				'enabled' => 1
			),
			'key' => 'code',
			'value' => 'name',
			'order' => 'position',
			//'query_only' => true
		));
	}
	
	/** @deprecated */
	public function get_payment_options() {
		//return PC_shop_payment_option_model::get_select_options();
		$m = new PC_shop_payment_option_model();
		return $m->get_all(array(
			'content' => true,
			'where' => array(
				'enabled' => 1
			),
			'key' => 'code',
			'value' => 'name',
			'order' => 'position',
			//'query_only' => true
		));
	}
	
	public function get_data() {
		/** @var PC_shop_site $shop */
		$shop = $this->core->Get_object('PC_shop_site');
		$data = array(
			'cart_data' => $shop->cart->Get($this->_config['default_order_data']),
			'order_data' => $shop->orders->Get_preserved_order_data(),
			'coupon_data' => $shop->cart->get_preserved_coupon_data(),
			'delivery_options' => $shop->getDeliveryOptions(),
			'payment_options' => $shop->getPaymentOptions()
		);
		if ($data['coupon_data']) {
			$data['cart_data']['coupon'] = $data['coupon_data']['code'];
		}
		if (!$data['order_data']) {
			$data['order_data'] = array();
		}
		$data['order_data'] = array_merge($this->_config['default_order_data'], $data['order_data']);
		return $data;
	}
}