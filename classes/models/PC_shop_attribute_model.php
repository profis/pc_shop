<?php

class PC_shop_attribute_model extends PC_model {
	const AF_DEFAULT = 0x1;
	const ITEM_IS_CATEGORY = 0x1;
	const ITEM_IS_PRODUCT = 0x2;
	protected function _set_tables() {
		$this->_table = 'shop_attributes';
		
		$this->_content_table = 'shop_attribute_contents';
		$this->_content_table_relation_col = 'attribute_id';
	}
        
	public function get_id_from_ref($ref) {
		return $this->get_id_from_field('ref', $ref);
	}
	
	public function set_category_attribute_scope() {
		$this->_where['is_category_attribute'] = 1;
	}
	
	public function set_product_attribute_scope() {
		$this->_where['is_category_attribute'] = 0;
	}
	
}
