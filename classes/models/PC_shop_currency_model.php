<?php

class PC_shop_currency_model extends PC_model {
	
	protected $_table = 'shop_currencies';
	
	protected function _set_tables() {
		$this->_table = 'shop_currencies';

		$this->_content_table = 'shop_currency_contents';
		$this->_content_table_relation_col = 'currency_id';
	}
	
}
