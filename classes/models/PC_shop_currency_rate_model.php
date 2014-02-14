<?php

class PC_shop_currency_rate_model extends PC_model {
	
	protected $_table_id_col = 'c_id';
	
	protected $_table = 'shop_currency_rates';
	
	protected function _set_tables() {
		$this->_table = 'shop_currency_rates';
		$this->_table_id_col = 'c_id';
	}
	
	protected function _format_relation(&$data) {
		$data['relation'] = $this->cfg['pc_shop']['currency'] . ' / ' . $data['code'];
	}
	
}
