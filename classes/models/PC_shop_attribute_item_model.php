<?php

class PC_shop_attribute_item_model extends PC_model {
	
	protected $_table = 'shop_item_attributes';
	
	protected function _set_tables() {
		$this->_table = 'shop_item_attributes';
	}
	
	public function set_category_attribute_scope() {
		$this->_where[] = $this->db->get_flag_query_condition(
			PC_shop_attributes::ITEM_IS_CATEGORY, 
			$this->_query_params, 
			'flags', 
			't'
		);
	}
	
	public function set_product_attribute_scope() {
		$this->_where[] = $this->db->get_flag_query_condition(
			PC_shop_attributes::ITEM_IS_PRODUCT, 
			$this->_query_params, 
			'flags', 
			't'
		);
	}
	
}
