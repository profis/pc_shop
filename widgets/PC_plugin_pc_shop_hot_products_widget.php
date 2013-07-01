<?php 

class PC_plugin_pc_shop_hot_products_widget extends PC_plugin_pc_shop_products_widget {
	
	public function get_template_group() {
		 return parent::get_template_group() . ':_plugin/' . $this->plugin_name . '/hot_products';
	}
	
	public function get_params() {
		$params = parent::get_params();
		$params['flags'][] = PC_shop_products::PF_HOT;
		return $params;
	}

	
}