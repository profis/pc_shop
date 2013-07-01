<?php

class PC_shop_delivery_option_model extends PC_model {
	
	const IN_SHOP = 'shop';
	const FROM_COURIER = 'courier';
	
	protected $_table = 'shop_delivery_options';
	protected $_content_table = 'shop_delivery_option_contents';
	protected $_content_table_relation_col = 'delivery_option_id';
	
	protected function _set_tables() {
		$this->_table = 'shop_delivery_options';
		$this->_content_table = 'shop_delivery_option_contents';
		$this->_content_table_relation_col = 'delivery_option_id';
	}
        
	public function get_id_from_ref($ref) {
		return $this->get_id_from_field('ref', $ref);
	}
	
	public static function get_select_options() {
		global $core;
		return array(
			self::IN_SHOP => $core->Get_variable('delivery_option_' . self::IN_SHOP, null, 'pc_shop'),
			self::FROM_COURIER => $core->Get_variable('delivery_option_' . self::FROM_COURIER, null, 'pc_shop'),
		);
	}
	
	public static function get_option_name($option, $ln = null) {
		global $core;
		$name = $core->Get_variable('delivery_option_' . $option, $ln, 'pc_shop');
		if (empty($name)) {
			$name = $core->Get_variable('pc_shop_delivery_option_' . $option, $ln);
		}
		return $name;
	}
	
}
