<?php

class PC_shop_order_model extends PC_model {
	
	const STATUS_DEFAULT = 0;
	const STATUS_NEW = 1;
	const STATUS_WAITING_FOR_PAYMENT = 2;
	const STATUS_BEING_PROCESSED = 3;
	const STATUS_COMPLETED = 4;
	const STATUS_CANCELED = 5;
	
	protected $_table = 'shop_orders';
	
	protected function _set_tables() {
		$this->_table = 'shop_orders';
	}
	
}
