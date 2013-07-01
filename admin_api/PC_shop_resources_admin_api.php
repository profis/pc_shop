<?php

class PC_shop_resources_admin_api extends PC_shop_admin_api {
	
	/**
	 * Access is being checked for category only
	 */
	public function get() {
		$type = v($_POST['type']);
		$id = v($_POST['id']);
		//$type = 'product'; $id = 1;
		$shop = $this->core->Get_object('PC_shop_site');
		$flags = 0x0;
		switch ($type) {
			case 'category':
				$this->_check_category_access($id);
				$flags |= PC_shop_resources::RF_IS_CATEGORY;
				break;
			case 'product': break;
			default: $this->_out['error'] = 'Invalid resource item type'; break 2;
		}
		$r = $shop->resources->Get_parsed(null, $id, $flags);
		$this->_out = $r;
	}
	
	/**
	 * Access is not being checked
	 */
	public function move() {
		//Access cannot be checked
		$shop = $this->core->Get_object('PC_shop_manager');
		$r = $shop->resources->Move(v($_POST['resourceId']), v($_POST['difference']), true);
		if ($this->_out['success'] = $r) {
			$this->_out['data'] = $r;
			$this->_init_data_change_hook();
		}
	}
	
}

?>
