<?php

class PC_shop_categories_admin_api extends PC_shop_admin_api{
	
	/**
	 * Access is not being checked: not needed
	 */
	public function getUrl() {
		$this->_out['success'] = false;
		$id = v($this->routes->Get(3));
		$this->site->Identify();
		$ln = v($this->routes->Get(4));
		if (!empty($ln)) $this->site->Set_language($ln);
		$shop = $this->core->Get_object('PC_shop_site');
		$d = $shop->categories->Get($id);
		if ($d) {
			$pageId = $shop->categories->Get_page_id($id);
			//$p = $page->Get_page($pageId);
			//$this->_out['url'] = $p['route'].'/'.$d['link'];
			$this->_out['url'] = $this->page->Get_encoded_page_link_by_id($pageId, $ln) . $d['link'];
			$this->_out['success'] = true;
		}
	}
	
	/**
	 * Access is being checked for:
	 *  <ul>
	 *	 <li>Category being moved</li>
	 *   <li>New parent category (can be null)</li>
	 *   <li>New parent page (if needed)</li>
	 *  </ul>
	 */
	public function move() {
		$tree = $this->core->Get_object('PC_database_tree');
		$this->_prepare_log($tree);
		$params = array();
		if (v($_POST['position_is_anchor_leaf'])) $params['position_is_anchor_leaf'] = true;
		$this->_check_category_access(v($_POST['id']));
		$this->_check_category_access(v($_POST['parentId']), true);
		$this->_out['success'] = $tree->Move('shop_categories', v($_POST['id']), v($_POST['parentId']), v($_POST['position'], 0), $params);
		if ($this->_out['success'] && isset($_POST['parent_pid'])) {
			$this->_check_page_access($_POST['parent_pid']);
			$query = "UPDATE {$this->cfg['db']['prefix']}shop_categories SET pid=? WHERE id=?";
			$r = $this->db->prepare($query);
			$query_params = array($_POST['parent_pid'], $_POST['id']);
			$tree->debug('----', 2);
			$tree->debug_query($query, $query_params, 3);
			$r->execute($query_params);
		}
		$this->_init_data_change_hook();
	}
	
	/**
	 * Access is being checked for:
	 *  <ul>
	 *	 <li>Category being copied</li>
	 *   <li>Category being pasted into</li>
	 *  </ul>
	 */
	public function copy() {
		$this->debug($_POST);
		$cat_id = v($_POST['id']);
		$copy_into_cat_id = v($_POST['copy_into']);
		
		$this->_check_category_access($cat_id);
		$this->_check_category_access($copy_into_cat_id, true);
		
		$cat_id = PC_shop_category_model::parse_id($cat_id);
		$copy_into_cat_id = PC_shop_category_model::parse_id($copy_into_cat_id);
		
		if (!$cat_id or !$copy_into_cat_id) {
			return false;
		}
		
		$shop = $this->core->Get_object('PC_shop_manager');
		$shop->categories->absorb_debug_settings($this, 5);
		$shop->products->absorb_debug_settings($this, 10);
		$shop->attributes->absorb_debug_settings($this, 15);
		$shop->resources->absorb_debug_settings($this, 20);
		$this->_prepare_log($shop->categories);
		$shop->categories->Copy($cat_id, $copy_into_cat_id);
		
		$this->_out['success'] = true;
		
		$this->_init_data_change_hook();
	}
	
}

?>
