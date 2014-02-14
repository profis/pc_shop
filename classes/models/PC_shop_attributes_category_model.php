<?php

class PC_shop_attributes_category_model extends PC_model {
	
	protected function _set_tables() {
		$this->_table = 'shop_attributes_categories';
		
		//$this->_content_table = 'shop_attributes_category_contents';
		//$this->_content_table_relation_col = 'category_id';
	}
	
}
