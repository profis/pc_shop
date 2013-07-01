<?php
class PC_plugin_pc_shop_search_form_widget extends PC_plugin_pc_shop_widget {
	
	protected $_template_group = 'search_form';
	
	protected function _get_default_config() {
		return array(
			'url' => '',
			'page_id' => 0,
			'page_ref' => '',
		);
	}
	
	public function get_data() {
		$data = array(
			'search_values' => pc_e(v($_GET, array())),
			'search_url' => $this->_get_url(true)
		);
		//print_pre($data);
		return $data;
	}
}