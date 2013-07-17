<?php

class PC_shop_delete_admin_api extends PC_shop_admin_api {

	protected function _before_action() {
		if (!isset($_POST['id'])) {
			$this->_out['error'] = 'Invalid ID specified';
			return false;
		}
		$this->shop = $this->core->Get_object('PC_shop_manager');
		$this->_prepare_log();
	}
	
	protected function _after_action_success() {
		$this->_init_data_change_hook();
	}

	/**
	 * Access is being checked for category
	 */
	public function category() {
		$this->_check_category_access(v($_POST['id']));
		$params = array();
		$this->_out['success'] = $this->shop->categories->Delete_category($_POST['id'], $params);
		
		if ($this->_out['success'] and $this->core->Count_hooks('plugin/pc_shop/delete/category')) {
			$this->_out['success'] = true;
			$this->_out['data'] = array();
			$hook_object = false;
			$this->debug('Init_hooks(plugin/pc_shop/delete/category');
			$this->core->Init_hooks('plugin/pc_shop/delete/category', array(
				'category' => $_POST['id'],
				'category_data' => v($this->shop->categories->deleted_category_data, false),
				'hook_object' => &$hook_object,
				'logger' => $this,
			));
			if ($hook_object and $hook_object instanceof PC_debug) {
				$this->debug('Debug from hook object:', 1);
				$this->debug($hook_object->get_debug_string(), 2);
			}
		}
		
		if (!$this->_out['success'])
			$this->_out['error'] = $params->errors->Get();
	}

	/**
	 * Access is being checked for product category
	 */
	public function product() {
		$this->debug("Deleting product");
		$params = array();
		$category_id = false;
		$product_data = $this->shop->products->Get_item($_POST['id']);
		if ($product_data) {
			$category_id = v($product_data['category_id'], false);
			$this->_check_category_access($category_id);
		}
		$this->debug('Product data:');
		$this->debug($product_data);

		$this->_out['success'] = $this->shop->products->Delete($_POST['id'], $params);

		if ($category_id and $this->core->Count_hooks('plugin/pc_shop/save/product')) {
			$this->_out['success'] = true;
			$this->_out['data'] = array();
			$hook_object = false;
			$this->debug('Init_hooks(plugin/pc_shop/save/product');
			$this->core->Init_hooks('plugin/pc_shop/save/product', array(
				'success' => &$this->_out['success'],
				'category' => $category_id,
				'out' => &$this->_out,
				'hook_object' => &$hook_object,
				'logger' => $this,
			));
			if ($hook_object and $hook_object instanceof PC_debug) {
				$this->debug('Debug from hook object:', 1);
				$this->debug($hook_object->get_debug_string(), 2);
			}
		}
		if (!$this->_out['success'])
			$this->_out['error'] = $params->errors->Get();
	}

}

?>
