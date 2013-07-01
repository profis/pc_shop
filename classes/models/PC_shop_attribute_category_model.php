<?php

class PC_shop_attribute_category_model extends PC_model {
	
	protected function _set_tables() {
		$this->_table = 'shop_attribute_categories';
		
		$this->_content_table = 'shop_attribute_category_contents';
		$this->_content_table_relation_col = 'attr_category_id';
	}
        
	public function get_id_from_ref($ref) {
		return $this->get_id_from_field('ref', $ref);
	}
	
}
