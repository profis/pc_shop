<?php

use \Profis\Db\DbException;

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
			'category' => false,
			'condition' => array(
				'where' => '',
				'params' => array(),
			),
			'include_subcategories' => false,
			'auto_attributes' => false,
			'only_existing_values' => true,
			'filter_value_numbers' => true,
			'filters' => array(
				'manufacturer',
				'price'
			),
		);
	}
	
	public function get_data() {
		$product_model = new PC_shop_site_product_model();
		
		$site_product_scope = $product_model->get_scope();
		$site_product_scope = PC_model::change_scope($site_product_scope, array('t' => 'p'));
		
		$products_params = array();
		$products_params['filter'] = array();
		$products_params['attribute_filter'] = array();
		$products_params['custom_attribute_filter'] = array();

		if( $this->_config['include_subcategories'] )
			$categoryJoin = "{$this->db_prefix}shop_categories c
				INNER JOIN {$this->db_prefix}shop_categories sc ON sc.lft BETWEEN c.lft AND c.rgt
				INNER JOIN {$this->db_prefix}shop_products p ON p.category_id=sc.id";
		else
			$categoryJoin = "{$this->db_prefix}shop_categories c
				INNER JOIN {$this->db_prefix}shop_products p ON p.category_id=c.id";

		$categoryWhere = array();
		$categoryParams = array();

		if( isset($this->_config['category']['id']) ) {
			$categoryWhere[] = "c.id=?";
			$categoryParams[] = $this->_config['category']['id'];
		}
		else if( isset($this->_config['page_id']) ) {
			$categoryWhere[] = "c.pid=?";
			$categoryParams[] = $this->_config['page_id'];
		}
		else
			throw new Exception("Either 'category' or 'page_id' parameter is required");

		if( isset($this->_config['condition']) && !empty($this->_config['condition']['where']) ) {
			if( is_array($this->_config['condition']['where']) )
				$categoryWhere = array_merge($categoryWhere, $this->_config['condition']['where']);
			else
				$categoryWhere[] = $this->_config['condition']['where'];
			$categoryParams = array_merge($categoryParams, $this->_config['condition']['params']);
		}

		$categoryWhere = '(' . implode(') AND (', $categoryWhere) . ')';

		// get list of manufacturers
		if (in_array('manufacturer', $this->_config['filters'])) {
			$q = "SELECT m.*, COUNT(*) AS `count`
				FROM {$categoryJoin}
				INNER JOIN {$this->db_prefix}shop_manufacturers m ON m.id=p.manufacturer_id
				WHERE {$categoryWhere}
				GROUP BY m.id
				ORDER BY m.name";

			$s = $this->db->prepare($q);
			if( !$s->execute($categoryParams) )
				throw new DbException($s->errorInfo(), $q, $categoryParams);

			$manufacturers = array();
			while( $row = $s->fetch() )
				$manufacturers[$row['id']] = $row;

			if (isset($_REQUEST['manufacturers']) && !empty($_REQUEST['manufacturers'])) {
				if( is_array($_REQUEST['manufacturers']) ) {
					$search = array_intersect(array_keys($manufacturers), $_REQUEST['manufacturers']);
					if( !empty($search) )
						$products_params['filter']['p.manufacturer_id'] = $search;
				}
				else if( isset($manufacturers[$_REQUEST['manufacturers']]) )
					$products_params['filter']['p.manufacturer_id'] = $_REQUEST['manufacturers'];
			}
		}

		// get min and max prices
		if (in_array('price', $this->_config['filters'])) {
			$real_price_select = $product_model->get_price_select('p');

			$q = "SELECT MIN($real_price_select) AS min_price, MAX($real_price_select) AS max_price
				FROM {$categoryJoin}
				WHERE {$categoryWhere}";

			$s = $this->db->prepare($q);
			if( !$s->execute($categoryParams) )
				throw new DbException($s->errorInfo(), $q, $categoryParams);

			$category_products_data = $s->fetch();

			if (isset($_REQUEST['price_from']) && !empty($_REQUEST['price_from'])) {
				$products_params['filter'][] = array(
					'field' => 'real_price',
					'op' => '>=',
					'value' => $_REQUEST['price_from']
				);
			}
			if (isset($_REQUEST['price_to']) && !empty($_REQUEST['price_to'])) {
				$products_params['filter'][] = array(
					'field' => 'real_price',
					'op' => '<=',
					'value' => $_REQUEST['price_to']
				);
			}
		}

		$filter_attributes = array();

		$attributeJoin = "INNER JOIN {$this->db_prefix}shop_item_attributes ia ON ia.item_id=p.id AND (ia.flags & " . PC_shop_attribute_model::ITEM_IS_PRODUCT . ") = " . PC_shop_attribute_model::ITEM_IS_PRODUCT;

		if( v($this->_config['auto_attributes']) ) {
			$q = "SELECT DISTINCT ia.attribute_id
				FROM {$categoryJoin} {$attributeJoin}
				INNER JOIN {$this->db_prefix}shop_attributes a ON a.id=ia.attribute_id
				WHERE {$categoryWhere} AND ia.attribute_id != 0 AND (ia.value_id IS NOT NULL OR (ia.value IS NOT NULL AND ia.value != '')) AND a.is_searchable=1
				ORDER BY a.position ASC, a.id ASC";

			$s = $this->db->prepare($q);
			if( !$s->execute($categoryParams) )
				throw new DbException($s->errorInfo(), $q, $categoryParams);

			$filter_attribute_ids = array();
			while( $row = $s->fetch() )
				$filter_attribute_ids[] = $row['attribute_id'];
		}
		else {
			// load attributes according to filter builder
			$filter_model = new PC_shop_category_product_filter_model();
			$category_filters = $filter_model->get_all(array(
				'content' => true,
				'where' => array(
					'category_id' => $this->_config['category']['id']
				),
				'key' => 'attribute',
				'order' => 't.position'
			));

			$filter_attribute_ids = array_keys($category_filters);
		}

		$attribute_model = new PC_shop_attribute_model();
		if (!empty($filter_attribute_ids)) {
			$filter_attributes = $attribute_model->get_data($filter_attribute_ids, array(
				'content' => true,
				'order' => "FIELD(t.id, " . implode(', ', $filter_attribute_ids) . ")",
				//'key' => 'attribute_id'
				//'query_only' => true
			));
		}
		else {
			$filter_attributes = array();
		}
		if( isset($category_filters) ) {
			foreach ($filter_attributes as $key => $filter_attribute) {
				$filter_attributes[$key]['filter_data'] = v($category_filters[$filter_attribute['id']], array());
			}
		}
		else {
			foreach( $filter_attributes as $key => $filter_attribute ) {
				$filter_attributes[$key]['filter_data'] = array(
					'id' => $filter_attribute['id'],
					'input_type' => 'select',
					'filter_type' => PC_shop_category_product_filter_model::FILTER_TYPE_EQ,
					'disabled' => 0,
					'name' => $filter_attribute['name'],
				);
			}
		}

		foreach ($filter_attributes as $key => $filter_attribute) {
			if (!in_array(v($filter_attribute['filter_data']['input_type']), array('', 'select'))) {
				$name = 'filter_' . v($filter_attribute['filter_data']['id']);
				$get_var = v($_REQUEST[$name], '');
				$get_var = trim($get_var);
				if (!empty($get_var)) {
					$products_params['custom_attribute_filter'][$filter_attribute['id']] = array(
						'op' => PC_shop_category_product_filter_model::$filter_type_operations[$filter_attribute['filter_data']['filter_type']],
						'value' => $_REQUEST[$name]
					);
				}
				continue;
			}

			$name = 'attribute_' . $filter_attribute['id'];

			// get all existing values of the attribute
			$values = array();
			if( $filter_attribute['is_custom'] ) {
				$q = "SELECT ia.value, COUNT(*) AS `count`
				FROM {$categoryJoin} {$attributeJoin}
				WHERE {$categoryWhere} AND ia.attribute_id = ? AND ia.value IS NOT NULL AND ia.value != ''
				GROUP BY ia.value
				ORDER BY ia.value";

				$s = $this->db->prepare($q);
				if( !$s->execute($p = array_merge($categoryParams, array($filter_attribute['id']))) )
					throw new DbException($s->errorInfo(), $q, $p);

				while( $row = $s->fetch() )
					$values[$row['value']] = array(
						'id' => $row['value'],
						'value' => $row['value'],
						'count' => $row['count'],
					);

				if (isset($_REQUEST[$name]) && !empty($_REQUEST[$name])) {
					if( is_array($_REQUEST[$name]) ) {
						$search = array_intersect(array_keys($values), $_REQUEST[$name]);
						if( !empty($search) )
							$products_params['custom_attribute_filter'][$filter_attribute['id']] = array(
								'clause' => 'ia.value IN (?' . str_repeat(',?', count($search) - 1) . ')',
								'query_params' => $search,
							);
					}
					else if( isset($values[$_REQUEST[$name]]) )
						$products_params['custom_attribute_filter'][$filter_attribute['id']] = array(
							'clause' => 'ia.value = ?',
							'query_params' => array($_REQUEST[$name]),
						);
				}
			}
			else {
				$q = "SELECT ia.value_id, avc.value, COUNT(*) AS `count`
				FROM {$categoryJoin} {$attributeJoin}
				INNER JOIN {$this->db_prefix}shop_attribute_value_contents avc ON avc.value_id=ia.value_id AND avc.ln=?
				WHERE {$categoryWhere} AND ia.attribute_id = ? AND ia.value_id IS NOT NULL AND ia.value_id != 0
				GROUP BY ia.value_id
				ORDER BY avc.value";

				$s = $this->db->prepare($q);
				if( !$s->execute($p = array_merge($categoryParams, array($this->site->ln, $filter_attribute['id']))) )
					throw new DbException($s->errorInfo(), $q, $p);

				while( $row = $s->fetch() )
					$values[$row['value_id']] = array(
						'id' => $row['value_id'],
						'value' => $row['value'],
						'count' => $row['count'],
					);

				if (isset($_REQUEST[$name]) && !empty($_REQUEST[$name])) {
					if( is_array($_REQUEST[$name]) ) {
						$search = array_intersect(array_keys($values), $_REQUEST[$name]);
						if( !empty($search) )
							$products_params['attribute_filter'][$filter_attribute['id']] = $search;
					}
					else if( isset($values[$_REQUEST[$name]]) )
						$products_params['attribute_filter'][$filter_attribute['id']] = $_REQUEST[$name];
				}
			}

			$filter_attributes[$key]['filters'] = $values;
		}

		//print_pre($filter_attributes);

		//$shop = $this->core->Get_object('PC_shop_site');
		//print_pre($shop->attributes->Get($filter_attribute_ids));
		//$get_values_params = array();
		//print_pre($shop->attributes->Get_values($filter_attribute_ids, $get_values_params));

		if ($this->_config['include_subcategories']) {
			$products_params['all_products_of_category'] = true;
		}
		//echo 'product params:';
		//print_pre($products_params);
		$data = array(
			'manufacturers' => v($manufacturers, false),
			'filters' => $filter_attributes,
			'category_products_data' => v($category_products_data, array()),
			'products_params' => $products_params,
			'base_url' => $this->_get_url()
		);
		$this->products_params = $products_params;
		
		return $data;
	}
	
	
}