<?php

class PC_shop_payment_option_model extends PC_model {
	
	const CASH = 'cash';
	const WEB2PAY = 'web2pay';
	
	protected $_table = 'shop_payment_options';
	protected $_content_table = 'shop_payment_option_contents';
	protected $_content_table_relation_col = 'payment_option_id';
	
	protected function _set_tables() {
		$this->_table = 'shop_payment_options';
		$this->_content_table = 'shop_payment_option_contents';
		$this->_content_table_relation_col = 'payment_option_id';
	}
        
	public function get_id_from_ref($ref) {
		return $this->get_id_from_field('ref', $ref);
	}
	
	public static function get_select_options() {
		global $core;
		return array(
			self::CASH => $core->Get_variable('payment_option_' . self::CASH, null, 'pc_shop'),
			self::WEB2PAY => $core->Get_variable('payment_option_' . self::WEB2PAY, null, 'pc_shop'),
		);
	}
	
	public static function get_payment_option_name($payment_option, $ln = null) {
		global $core;
		$name = $core->Get_variable('payment_option_' . $payment_option, $ln, 'pc_shop');
		if (empty($name)) {
			$name = $core->Get_variable('pc_shop_payment_option_' . $payment_option, $ln);
		}
		return $name;
	}
	
	public static function get_option_name($option, $ln = null) {
		return self::get_payment_option_name($option, $ln);
	}
	
}
