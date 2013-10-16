<?php 

class PC_plugin_pc_shop_popular_products_widget extends PC_plugin_pc_shop_products_widget {
	
	public function get_template_group() {
		 return parent::get_template_group() . ':_plugin/' . $this->plugin_name . '/popular_products';
	}
	
	protected function _get_default_config() {
		$config = parent::_get_default_config();
		$config['attribute_ref'] = '';
		return $config;
	}
	
	public function get_params() {
		$params = parent::get_params();
		//$params['flags'][] = PC_shop_products::PF_HOT;
		$popular_params = array(
			'select' => 'product_id, sum(quantity) as suma',
			'where' => array(
				'o.is_paid' => 1
			),
			'join' => "LEFT JOIN {$this->db_prefix}shop_orders o ON o.id = t.order_id",
			'group' => 'product_id',
			'order' => 'suma DESC',
			'value' => 'product_id'
			//'limit'
			//'query_only' => true
		);
		if (isset($params['paging']) and v($params['paging']['perPage'])) {
			$popular_params['limit'] = $params['paging']['perPage'];
		}
		$order_items_model = new PC_shop_order_item_model();
		$most_popular_item_ids = $order_items_model->get_all($popular_params);
		//print_pre($most_popular_item_ids);
		
		//$most_popular_item_ids = array($most_popular_item_ids[0]);
		
		$most_popular_item_ids_count = count($most_popular_item_ids);
		if ($this->_config['limit'] and $most_popular_item_ids_count < $this->_config['limit'] and !empty($this->_config['attribute_ref'])) {
			$attribute_model = new PC_shop_attribute_model();
			$attribute_id = $attribute_model->get_id_from_ref($this->_config['attribute_ref']);
			if ($attribute_id) {
				$item_attribute_model = new PC_shop_attribute_item_model();
				$item_attribute_model->set_product_attribute_scope();
				$attr_params = array(
					'where' => array(
						'attribute_id' => $attribute_id,
						
					),
					'limit' => $this->_config['limit'] - $most_popular_item_ids_count,
					'group' => 'item_id',
					'value' => 'item_id',
					//'query_only' => true
				);
				if (!empty($most_popular_item_ids)) {
					$attr_params['where']['item_id NOT'] = $most_popular_item_ids;
				}
				$most_popular_item_ids_2 = $item_attribute_model->get_all($attr_params);
					
				if ($most_popular_item_ids_2) {
					$most_popular_item_ids = array_merge($most_popular_item_ids, $most_popular_item_ids_2);
				}
				
			}
			
			
		}
		if (!empty($most_popular_item_ids)) {
			$params['product_id'] = $most_popular_item_ids;
		}
		//print_pre($params);
		return $params;
	}

	
}