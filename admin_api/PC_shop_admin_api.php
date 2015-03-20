<?php

class PC_shop_category_access_exception extends PC_plugin_admin_api_access_exception{}

class PC_shop_admin_api extends PC_plugin_crud_admin_api{
	
	public $permanent_category_log = true;

	/** @var PC_shop_manager */
	public $shop;

	/**
	 * 
	 */
	protected function _set_plugin_name() {
		$this->_plugin_name = 'pc_shop';
	}

	protected function _get_model() {
		return false;
	}

	/** @deprecated */
	protected function _prepare_log() {
	}
	
	/**
	 * 
	 * @param string $id
	 * @throws PC_shop_category_access_exception
	 */
	protected function _check_category_access($id, $can_be_null = false) {
		if ($can_be_null) {
			if ($id == 0 or $id == null) {
				return true;
			}
		}
		if (is_numeric($id)) {
			$id = 'pc_shop/category/' . $id;
		}
		if (!$this->_page_manager->is_node_accessible($id)) {
			throw new PC_shop_category_access_exception('Access to category ' . $id . ' denied!');
		}
	}
	
	protected function _init_data_change_hook($hook_params = array()) {
		$this->core->Init_hooks('core/cache/clear', $hook_params);
	}
	
	protected function _get_category_name_path($id, $ln = '') {
		$link_select = ' concat('.$this->sql_parser->group_concat('link_cc.name', array('separator'=>' / ','order'=>array('by'=>'link_c.lft'))).") name_path";
			
		$link_join = " LEFT JOIN {$this->db_prefix}shop_categories link_c ON c.lft BETWEEN link_c.lft and link_c.rgt"
		." LEFT JOIN {$this->db_prefix}shop_category_contents link_cc ON link_cc.category_id = link_c.id and link_cc.ln=?";
		
		$queryParams = array();
		$query = "SELECT $link_select FROM {$this->db_prefix}shop_categories c " 
		. $link_join 
		. " WHERE c.id = ?";

		$query_params[] = $ln;
		$query_params[] = $id;
		
		$r = $this->prepare($query);
		$s = $r->execute($query_params);
		
		if ($s and $d = $r->fetch()) {
			return $d['name_path'];
		}
		return false;
	}

}

?>
