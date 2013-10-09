<?php
class PC_plugin_pc_shop_search_products_widget extends PC_plugin_pc_shop_products_widget {
	
	public function get_template_group() {
		 return parent::get_template_group() . ':_plugin/' . $this->plugin_name . '/search_products';
	}
	
	
	protected function _get_search_options() {
		return array(
			array(
				'input' => 'q',
				'op' => 'full_text_all',
				'field' => array('pc.name', 'pc.description'),
				'group' => 'text'
			),
			array(
				'input' => 'q',
				'op' => 'like',
				'like_pref' => false,
				'like_suff' => false,
				'field' => 'p.mpn',
				'group' => 'text'
			),
			array(
				'input' => 'q',
				'op' => 'like',
				'like_pref' => false,
				'like_suff' => false,
				'field' => 'pm.name',
				'group' => 'text'
			)
		);
	}
	
	protected function _get_default_config() {
		$config = parent::_get_default_config();
		$config['search_options'] = $this->_get_search_options();
		return $config;
	}
	
	protected function _search(&$params) {
		foreach ($this->_config['search_options'] as $key => $search_option) {
			//print_pre($search_option);
			$filter = $search_option;
			$filter['value'] = trim(v($_GET[$search_option['input']]));
			if (empty($filter['value'])) {
				continue;
			}
			if ($search_option['op'] == 'like') {
				if ($search_option['like_pref']) {
					$filter['value'] = '%' . $filter['value'];
				}
				if ($search_option['like_suff']) {
					$filter['value'] .= '%';
				}
			}
			
			$params['filter'][] = $filter;
		}
	
		vv($params['joins'], array());
		$params['joins'][] = "LEFT JOIN {$this->db_prefix}shop_manufacturers pm ON pm.id = p.manufacturer_id";
	
	}
	
	public function get_params() {
		$params = parent::get_params();
		$this->_search($params);
		return $params;
	}
	
	
	
}