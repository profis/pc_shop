<?php

class PC_shop_category_product_filters_admin_api extends PC_shop_admin_api {

	/**
	 *
	 * @var int
	 */
	public $category_id;

	protected $_default_order = 'position';
	
	protected $_content_fields = array(
		'name'
	);
	
	protected function _get_model() {
		return $this->core->Get_object('PC_shop_category_product_filter_model');
	}
	
	
	protected function _get_available_order_columns() {
		return array();
	}
	
	/**
	 * Plugin access is being checked
	 */
	protected function _before_action() {
		$this->category_id = intval(v($_POST['category_id']));
		$this->_check_plugin_access();
	}
	
	protected function _adjust_search(&$params) {
		$params['where']['category_id'] = intval(v($_POST['category_id']));
	}
	
	protected function _before_insert(&$data, &$content) {
		$data['category_id'] = $this->category_id;
	}
	
	protected function _before_update(&$data, &$content) {
		$this->_before_insert($data, $content);
		unset($data['category_id']);
	}
	
}

?>
