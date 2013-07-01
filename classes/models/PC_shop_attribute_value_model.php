<?php

class PC_shop_attribute_value_model extends PC_model {
	
	protected function _set_tables() {
		$this->_table = 'shop_attribute_values';
		
		$this->_content_table = 'shop_attribute_value_contents';
		$this->_content_table_relation_col = 'value_id';
	}
}
