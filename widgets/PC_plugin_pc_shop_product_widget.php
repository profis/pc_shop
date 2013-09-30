<?php

class PC_plugin_pc_shop_product_widget extends PC_plugin_pc_shop_widget {
	
	public $plugin_name = 'pc_shop';

	protected $_template_group = 'product';
	
	public $shop_products_site;
	
	public $product;
	
	public function Init($config = array(), $product = null, $shop_products_site = null) {
		parent::Init($config);
		$this->product = $product;
		$this->shop_products_site = $shop_products_site;
		$this->_template_group = ':_plugin/' . $this->plugin_name . '/' . $this->_template_group;
	}
	
	protected function _get_default_config() {
		return array(
			//'thumb_types' => array('small',
		);
	}
	
	public function get_data() {
		$data = array(
			//'images' => $this->product['resources']->Get(false, 'small')
			'gallery_url' => $this->core->Get_url("gallery"),
			'item' => $this->product,
			'price' => number_format($this->shop_products_site->get_price($this->product, $discount, $percentage_discount), 2, ",", ""),
		);
		
		$data['discount'] = $discount;
		$data['percentage_discount'] = $percentage_discount;
		//print_pre($data);
		return $data;
	}
}
