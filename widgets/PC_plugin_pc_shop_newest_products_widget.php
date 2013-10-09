<?php 

class PC_plugin_pc_shop_newest_products_widget extends PC_plugin_pc_shop_products_widget {
	
	public function get_template_group() {
		 return parent::get_template_group() . ':_plugin/' . $this->plugin_name . '/newest_products';
	}
	
	public function get_params() {
		$params = parent::get_params();
		$params['order_by'] = 'p.created_on desc';
		return $params;
	}
	
}