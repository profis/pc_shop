<?php

class PC_shop_category_product_filter_model extends PC_model {
	
	const FILTER_TYPE_EQ = 0;
	const FILTER_TYPE_GTE = 1;
	const FILTER_TYPE_LTE = 2;
	
	public static $filter_type_operations = array(
		self::FILTER_TYPE_EQ => '=',
		self::FILTER_TYPE_GTE => '>=',
		self::FILTER_TYPE_LTE => '<=',
	);
	
	protected $_table = 'shop_category_product_filters';
	protected $_content_table = 'shop_category_product_filter_contents';
	protected $_content_table_relation_col = 'filter_id';
	
	protected function _set_tables() {
		$this->_table = 'shop_category_product_filters';
		$this->_content_table = 'shop_category_product_filter_contents';
		$this->_content_table_relation_col = 'filter_id';
	}
	
}
