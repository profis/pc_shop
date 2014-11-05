<?php
class PC_plugin_pc_shop_delivery_form_widget extends PC_widget {
	public $plugin_name = 'pc_shop';

	protected $_template_group = 'delivery_form';

	public function Init($config = array(), $product = null, $shop_products_site = null) {
		parent::Init($config);
		$this->_template_group = ':_plugin/' . $this->plugin_name . '/' . $this->_template_group;
	}

	protected function _get_default_config() {
		return array();
	}

	public function get_data() {
		global $core;
		/** @var PC_shop_site $shop */
		$shop = $this->core->Get_object('PC_shop_site');
		$order = $shop->orders->Get_preserved_order_data();
		$data = array();
		$deliveryOption = v($order['delivery_option']);
		if( $deliveryOption ) {
			$form_data = isset($order['delivery_form_data'][$deliveryOption]) ? $order['delivery_form_data'][$deliveryOption] : array();
			$data['form'] = $core->Init_callback($order['delivery_option'] . '.getDeliveryForm', array(
				'cart' => $shop->cart->Get(),
				'order' => $order,
				'form_data' => $form_data,
			));
			$data['form_data'] = $form_data;
		}
		return $data;
	}
}