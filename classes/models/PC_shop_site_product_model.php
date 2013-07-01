<?php

class PC_shop_site_product_model extends PC_shop_product_model {
	
	protected function _set_base_scope() {
		$this->_scope_where[] = $this->db->get_flag_query_condition(
			PC_shop_products::PF_PUBLISHED, 
			$this->_scope_query_params, 
			'flags', 
			't'
		);
	}

}
