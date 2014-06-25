<?php 

class PC_plugin_pc_shop_checkout_user_widget extends PC_plugin_pc_shop_widget {
	
	protected $_template_group = 'checkout_user';
	
	public function get_data() {
		$shop = $this->core->Get_object('PC_shop_site');
		$order_url = pc_append_route($this->page->Get_current_page_link(), 'order');
		$order_fast_url = pc_append_route($order_url, 'fast');
		$data = array(
			'order_url' => $order_fast_url
		);
		
		
		return $data;
	}
	
}