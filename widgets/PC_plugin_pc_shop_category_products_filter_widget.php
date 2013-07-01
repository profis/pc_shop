<?php


class PC_plugin_pc_shop_category_products_filter_widget extends PC_plugin_pc_shop_widget {
	
	public $plugin_name = 'pc_shop';

	protected $_template_group = 'filter';
	
	public function Init($config = array()) {
		parent::Init($config);
		$this->_template_group = ':_plugin/' . $this->plugin_name . '/' . $this->_template_group;
	}
	
	protected function _get_default_config() {
		return array(
			'url' => '',
			'page_id' => 0,
			'page_ref' => '',
			'category' => false
		);
	}
	
	public function get_data() {
		$product_model = new PC_shop_site_product_model();
		
		$site_product_scope = $product_model->get_scope();
		$site_product_scope = PC_model::change_scope($site_product_scope, array('t' => 'p'));
		
				
		$manufact_params = array(
			'select' => 't.*, count(*) as count',
			'key' => 'id',
			'join' => array(
				"LEFT JOIN {$this->db_prefix}shop_products p ON p.manufacturer_id = t.id"
			),
			'where' => array(
				'p.category_id = ?'
			),
			'query_params' => array(
				$this->_config['category']['id']
			),
			'group' => 't.id',
			//'query_only' => true
		);
				
		PC_model::absorb_scope_into_params($manufact_params, $site_product_scope);
		
		
		$manufacturer_model = new PC_shop_manufacturer_model();
		$manufacturers = $manufacturer_model->get_all($manufact_params);
		
		$products_params = array();
		$products_params['filter'] = array();
		$products_params['attribute_filter'] = array();
		if (isset($_GET['manufacturers']) and is_array($_GET['manufacturers']) and !empty($_GET['manufacturers'])) {
			$products_params['filter']['p.manufacturer_id'] = array_intersect(array_keys($manufacturers), $_GET['manufacturers']);
		}		
		
		if (isset($_GET['price_from'])) {
			$products_params['filter'][] = array(
				'field' => 'real_price',
				'op' => '>=',
				'value' => $_GET['price_from']
			);
		}
		if (isset($_GET['price_to'])) {
			$products_params['filter'][] = array(
				'field' => 'real_price',
				'op' => '<=',
				'value' => $_GET['price_to']
			);
		}
		
		
		$real_price_select = $product_model->get_price_select();
		$category_products_data = $product_model->get_all(array(
			'select' =>  "min($real_price_select) as min_price, max($real_price_select) as max_price",
			'limit' => 1,
			'where' => array(
				't.category_id' => $this->_config['category']['id']
			)
			//'query_only' => true
		));
		
		$filter_model = new PC_shop_category_product_filter_model();
		$filter_attribute_ids = $filter_model->get_all(array(
			'where' => array(
				'category_id' => $this->_config['category']['id']
			),
			'value' => 'attribute'
		));
		$attribute_model = new PC_shop_attribute_model();
		$filter_attributes = $attribute_model->get_data($filter_attribute_ids, array(
			'content' => true,
			//'key' => 'attribute_id'
		));
		//print_pre($filter_attributes);
		
		$attribute_value_model = new PC_shop_attribute_value_model();
		
		$attribute_item_model = new PC_shop_attribute_item_model();
		
		$product_query_params = array();
		$products_query = $product_model->get_all(array(
			'select' => 't.id',
			'where' => array(
				'category_id' => $this->_config['category']['id']
			),
			'query_only' => true,
			'get_query_params' => &$product_query_params
		));
		
		foreach ($filter_attributes as $key => $filter_attribute) {
			$query_params = array_merge(array($filter_attribute['id']), $product_query_params);
			
			$attribute_items_params = array(
				'select' => 'count(*) as count, t.value_id',
				
				'where' => array(
					't.attribute_id = ?',
					//"t.item_id IN (SELECT id FROM {$this->db_prefix}shop_products WHERE category_id = ?)"
					"t.item_id IN (".$products_query.")"
				),
				'query_params' => $query_params,
				'group' => 't.value_id',
				'key' => 'value_id',
				//'query_only' => true
			);
			$attribute_items_params['where'][] = $this->db->get_flag_query_condition(
				PC_shop_attribute_model::ITEM_IS_PRODUCT, 
				$attribute_items_params['query_params'], 
				'flags', 
				't'
			);
			$attribute_values = $attribute_item_model->get_all($attribute_items_params);
			//print_pre($attribute_values);
			if (!empty($attribute_values)) {
				$filter_attributes[$key]['filters'] = $attribute_value_model->get_all(array(
					'content' => true,
					'where' => array(
						't.id' => array_keys($attribute_values)
					),
					'key' => 'id'
				));
				$name = 'attribute_' . $filter_attribute['id'];
				if (isset($_GET[$name]) and is_array($_GET[$name]) and !empty($_GET[$name])) {
					$products_params['attribute_filter'][$filter_attribute['id']] = array_intersect(array_keys($attribute_values), $_GET[$name]);
				}	
				if ($filter_attributes[$key]['filters']) {
					foreach($filter_attributes[$key]['filters'] as $k => $filter) {
						$filter_attributes[$key]['filters'][$k]['count'] = $attribute_values[$k]['count'];
					}
				}
			}
			else {
				unset($filter_attributes[$key]);
			}
			
		}
		
		//print_pre($filter_attributes);
		
		
		//$shop = $this->core->Get_object('PC_shop_site');
		//print_pre($shop->attributes->Get($filter_attribute_ids));
		//$get_values_params = array();
		//print_pre($shop->attributes->Get_values($filter_attribute_ids, $get_values_params));
		
		$data = array(
			'manufacturers' => $manufacturers,
			'filters' => $filter_attributes,
			'category_products_data' => $category_products_data,
			'products_params' => $products_params,
			'base_url' => $this->_get_url()
		);
		$this->products_params = $products_params;
		
		return $data;
	}
	
	
}