<?php

class PC_shop_category_products_admin_api extends PC_shop_admin_api {

	
	/**
	 *
	 * @var int
	 */
	public $category_id;

	//protected $_default_order = 'position';
	
	protected function _get_model() {
		return $this->core->Get_object('PC_shop_product_model');
	}
	
	
	protected function _get_available_order_columns() {
		return array(
			'mpn' => 't.mpn',
			'info_1' => 't.info_1',
			'manufacturer' => 'm.name',
		);
	}
	
	protected function _get_available_filters() {
		return array(
			'manufacturer_id' => 't.manufacturer_id',
		);
	}
	
	/**
	 * Plugin access is being checked
	 */
	protected function _before_action() {
		$this->category_id = intval(v($_POST['category_id']));
		$this->_check_plugin_access();
	}
	
	protected function _adjust_search(&$params) {
		parent::_adjust_search($params);
		
		$params['where']['category_id'] = intval(v($_POST['category_id']));
		
		vv($params['select'], 't.*');
		vv($params['join'], array());
		
		$params['select'] .= ',m.name as manufacturer';
		$params['join'][] = "LEFT JOIN {$this->db_prefix}shop_manufacturers m ON t.manufacturer_id = m.id";
		
	}
	
	protected function _before_insert(&$data, &$content) {
		$data['category_id'] = $this->category_id;
		$this->core->Get_object('PC_shop_manager');
		$data['flags'] = PC_shop_products_manager::PF_DEFAULT;
	}
	
	protected function _before_update(&$data, &$content) {
		$this->_before_insert($data, $content);
		unset($data['category_id']);
	}
	
}

?>
