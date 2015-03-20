<?php

class PC_shop_category_model extends PC_model {
	
	protected $_table_lft_col = 'lft';
	protected $_table_rgt_col = 'rgt';
	
	protected $_table = 'shop_categories';
	protected $_table_parent_col = 'parent_id';
	protected $_content_table = 'shop_category_contents';
	protected $_content_table_relation_col = 'category_id';
	
	protected function _set_tables() {
		$this->_table = 'shop_categories';
		$this->_table_parent_col = 'parent_id';
		
		$this->_content_table = 'shop_category_contents';
		$this->_content_table_relation_col = 'category_id';
	}

	public static function parse_id($category_page_id) {
		global $page;
		$id = false;
		
		$controller_data = $page->get_controller_data_from_id($category_page_id);
			
		if ($controller_data and v($controller_data['plugin']) == 'pc_shop') {
			$id_data = PC_shop_plugin::ParseID(v($controller_data['id']));
			if ($id_data and v($id_data['type']) == 'category') {
				return intval($id_data['id']);
			}
		}
		return $id;
	}
	
	public function is_node(&$category_data) {
		return ($category_data['rgt'] - $category_data['lft'] == 1);
	}
	
	public function get_top_parent_id($category_data) {
		if (!is_array($category_data)) {
			$category_data = $this->get_data($category_data, 
				array(
					'select' => 't.id, t.'.$this->_table_lft_col . ',t.'.$this->_table_rgt_col,
					),
				1
			);
		}
		if (!$category_data) {
			return false;
		}
		$top_parent_id = false;
		
		$query = "SELECT id FROM {$this->db_prefix}{$this->_table} t
			WHERE t.{$this->_table_lft_col} <= ? and t.{$this->_table_rgt_col} >= ?
			ORDER BY t.{$this->_table_lft_col}
			LIMIT 1";
		
		$query_params = array($category_data[$this->_table_lft_col], $category_data[$this->_table_rgt_col]);

		$r = $this->prepare($query);
		$s = $r->execute($query_params);
		
		$parents = array();
		
		if ($s ) {
			$top_parent_id = $r->fetchColumn();
		}
		return $top_parent_id;
	}
	
	public function get_top_category_data($id, $params = array()) {
		$top_parent_id = $this->get_top_parent_id($id);
		if ($top_parent_id) {
			return $this->get_data($top_parent_id, $params);
		}
	} 
	
	public function get_category_page($category_id) {
		$top_parent_id = $this->get_top_parent_id($category_id);
		if ($top_parent_id) {
			$category_id = $top_parent_id;
		}
		$category_data = $this->get_data($category_id, 
			array(
				'select' => 't.pid',
				),
			1
		);
		if ($category_data['pid']) {
			return $category_data['pid'];
		}
		return false;
	}
	
	public function is_descendant($descendant, $ancestor) {
		if (!is_array($descendant)) {
			$descendant = $this->get_one($descendant);
		}
		if (!is_array($ancestor)) {
			$ancestor = $this->get_one($ancestor);
		}
		if (!is_array($descendant) or !is_array($ancestor)) {
			return false;
		}
		if ($descendant['lft'] >= $ancestor['lft'] and $descendant['rgt'] <= $ancestor['rgt']) {
			return true;
		}
		return false;
	}
	
	/**
	 * 
	 * @param array $category_data
	 * @return array of parents' id's
	 * Parents are sorted from top level to bottom level
	 */
	public function get_all_parents(&$category_data) {
		$query = "SELECT id FROM {$this->db_prefix}shop_categories c
			WHERE c.lft < ? and c.rgt > ?
			ORDER by c.lft";
		
		$query_params = array($category_data['lft'], $category_data['rgt']);
		
		$r = $this->prepare($query);
		$s = $r->execute($query_params);
		
		$parents = array();
		
		if ($s ) {
			while($d = $r->fetch()) {
				$parents[] = $d['id'];
			}
		}
		return $parents;
	}
	
	
	public function adjust_page_scope(&$params, $page_id) {
		$params['where'][] = 't.pid = ?';
		$params['query_params'][] = $page_id;
	}
	
		
	public function set_hot_scope() {
		$this->_where[] = $this->db->get_flag_query_condition(
			PC_shop_categories::CF_HOT, 
			$this->_query_params, 
			'flags', 
			't'
		);
	}
	
	public function set_published_scope() {
		$this->_where[] = $this->db->get_flag_query_condition(
			PC_shop_categories::CF_PUBLISHED, 
			$this->_query_params, 
			'flags', 
			't'
		);
	}
	
	public function set_in_menu_scope() {
		$this->_where[] = $this->db->get_flag_query_condition(
			PC_shop_categories::CF_NOMENU, 
			$this->_query_params, 
			'flags', 
			't',
			'<>'
		);
	}
	
	public function set_branch_scope($category_data, $published = true, $in_menu = true) {
		$this->_where[] = PC_database_tree::get_between_condition_for_range(
			$category_data, 
			$this->_query_params, 
			$table = 't'
		);
	}
	
	public function set_parent_scope($parent_id, $published = true, $in_menu = true) {
		$this->_where[] = 't.parent_id = ?';
		$this->_query_params[] = $parent_id;
	}
	
	public function get_id_by_ref($ref) {
		return $this->get_one(array(
			'where' => array(
				'external_id' => $ref
			), 
			'value' => 'id'
		));
	}
	
}
