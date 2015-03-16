<?php 

class PC_plugin_pc_shop_categories_widget extends PC_plugin_pc_shop_widget {
	
	protected $_template_group = 'categories';
	
	protected $_category_id;
	
	public function Init($config = array()) {
		parent::Init($config);
		$this->_category_id = isset($this->_config['category']['id']) ? $this->_config['category']['id'] : null;
	}
	
	public function get_params() {
		
		$params = array();
		if (isset($this->_config['params']) and is_array($this->_config['params'])) {
			$params = $this->_config['params'];
		}
		
		vv($params['flags'], array());
		//$params['flags'][] = PC_shop_products::PF_PUBLISHED;
		
		$params['parse'] = array(
			//'description' => true,
			//'attributes' => true,
			//'resources' => true,
			'resources' => 'first_img_only'
		);
		$params['full_links'] = true;
		return $params;
	}
	
	public function get_data() {
		$data = array();

		$params = $this->get_params();
		$data['categories']  = $this->shop->categories->Get(null, $this->_category_id, null, $params);
		
		return $data;
	}
	
}