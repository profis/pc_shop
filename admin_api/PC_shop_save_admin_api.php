<?php

class PC_shop_save_admin_api extends PC_shop_admin_api {
	
	protected $d;

	protected function _before_action() {
		$this->d = json_decode(v($_POST['data'], '{}'), true);
		$this->shop = $this->core->Get_object('PC_shop_manager');
		if (!isset($this->d['id'])) {
			$this->_out['error'] = 'Invalid ID specified';
			return false;
		}
	}
	
	protected function _after_action_success() {
		$this->_init_data_change_hook();
	}
	
	protected function _after_action() {
	}
	
	/**
	 * Access is being checked
	 * @return type
	 */
	public function category() {
		$params = array();
		if ($this->d['id'] == 0) {
			if ($this->d['parent_id'] > 0) {
				$this->_check_category_access(v($this->d['parent_id']));
			}
			else {
				$this->_check_page_access(v($this->d['pid']));
			}
			$s = $this->shop->categories->Create($this->d['parent_id'], v($this->d['pid']), 0, $this->d, $params);
			$this->_out['id'] = $this->d['id'] = $s;
		}
		else {
			$this->_check_category_access(v($this->d['id']));
			$s = $this->shop->categories->Edit($this->d['id'], $this->d, $params);
			
			if ($s and $this->core->Count_hooks('plugin/pc_shop/save/category')) {
				$this->_out['success'] = true;
				$this->_out['data'] = array();
				$hook_object = false;
				$this->core->Init_hooks('plugin/pc_shop/save/category', array(
					'success'=> &$this->_out['success'],
					'category' => $this->d['id'],
					'category_data' => $this->d,
					'out'=> &$this->_out,
					'hook_object' => &$hook_object,
				));
			}
			
		}
		if (!$s) {
			$this->_out['error'] = 'Error while saving main category data';
			return;
		}
		$this->_out['data'] = $this->shop->categories->Get($this->d['id']);
		$this->_out['data']['resources'] = $this->shop->resources->Get_parsed(null, $this->d['id'], PC_shop_resources::RF_IS_CATEGORY);
		$this->_out['success'] = true;
	}
	
	/**
	 * Access is being checked
	 * @return type
	 */
	public function product() {
		$this->_check_category_access($this->d['category_id']);
		$params = array();
		if ($this->d['id'] == 0) {
			$s = $this->shop->products->Create($this->d['category_id'], 0, $this->d, $params);
			$this->_out['id'] = $this->d['id'] = $s;
		}
		else {
			$s = $this->shop->products->Edit($this->d['id'], $this->d, $params);

			if ($this->core->Count_hooks('plugin/pc_shop/save/product')) {
				$this->_out['success'] = true;
				$this->_out['data'] = array();
				$hook_object = false;
				$this->core->Init_hooks('plugin/pc_shop/save/product', array(
					'success'=> &$this->_out['success'],
					'category' => $this->d['category_id'],
					'out'=> &$this->_out,
					'hook_object' => &$hook_object,
				));
			}
		}

		if (!$s) {
			$this->_out['error'] = 'Error while saving main product data';
			return;
		}
		$this->_out['data'] = $this->shop->products->Get($this->d['id']);
		$this->_out['data']['resources'] = $this->shop->resources->Get_parsed(null, $this->d['id']);
		$this->_out['success'] = true;
	}
}

?>
