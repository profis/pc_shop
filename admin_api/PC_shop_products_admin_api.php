<?php

class PC_shop_products_admin_api extends PC_shop_admin_api {

	
	protected function _after_action_success() {
		if (!in_array($this->_method, array('getUrl'))) {
			$this->_init_data_change_hook();
		}
	}
	
	
	/**
	 * Access is not being checked: not needed
	 */
	public function getUrl() {
		$this->_out['success'] = false;
		$id = v($this->routes->Get(3));
		$this->site->Identify();
		$ln = v($this->routes->Get(4));
		if (!empty($ln))
			$this->site->Set_language($ln);
		$shop = $this->core->Get_object('PC_shop_site');
		$d = $shop->products->Get($id);
		if ($d) {
			$pageId = $shop->products->Get_page_id($id);
			//$p = $this->page->Get_page($pageId);
			//$this->_out['url'] = $p['route'].'/'.$d['link'];
			$this->_out['url'] = $this->page->Get_encoded_page_link_by_id($pageId, $ln) . $d['link'];
			$this->_out['success'] = true;
		}
	}

	/**
	 * Access is being checked
	 */
	public function move() {
		$this->_check_category_access($_POST['categoryId']);
		$shop = $this->core->Get_object('PC_shop_manager');
		$this->_out['success'] = $shop->products->Move(v($_POST['id']), v($_POST['categoryId']), v($_POST['position'], 0));
	}

	/**
	 * Access is not being checked
	 */
	public function recalculate_positions() {
		$shop = $this->core->Get_object('PC_shop_manager');
		$this->_out['success'] = $shop->products->Recalculate_positions();
	}

}

?>
