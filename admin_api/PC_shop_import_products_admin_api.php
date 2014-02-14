<?php

class PC_shop_import_products_admin_api extends PC_shop_admin_api {
	
	const MISSING_PRODUCTS_STRATEGY_DELETE = 'delete';
	const MISSING_PRODUCTS_STRATEGY_HIDE = 'hide';
	
	protected function _import_set_arguments() {
		//$this->categoryId = v($_POST['categoryId']);
		//$this->ln = v($_POST['ln']);
	}
	
	protected function _import_check_file() {
		if (!isset($_FILES['file'])) {
			$this->_out['error'] = 'no_file';
			return;
		}
		if ($_FILES['file']['error'] > 0) {
			$this->_out['error'] = 'file_upload_error';
			return;
		}
		if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
			$this->_out['error'] = 'uploaded_file_not_found';
			return;
		}
	}
	
	protected function _get_products() {
		
		$this->product_import_method;
		
		$products = array();
		
		$this->core->Init_hooks('plugin/pc_shop/import-products/get_products/' . $this->product_import_method, array(
			'products'=> &$products
		));
		
		return $products;
	}
	
	protected function _read_products_from_file() {
		$this->debug('_get_products_from_file(' . $this->_file);
		$ext = pathinfo($this->_file_name, PATHINFO_EXTENSION);
		$this->debug('extension is: ' . $ext, 1);
		$this->increase_debug_offset(2);
		switch ($ext) {
			case 'xml':
				return $this->_read_products_from_xml_file();
				break;

			case 'xls':
			case 'xlsx':	
				return $this->_read_products_from_excel_file();
				break;
			
			default:
				$items = $this->_get_products();
				return $this->_read_products_from_array($items);
				return array();
				break;
		}
	}
	
	protected function _read_products_from_xml_file() {
		$this->debug('_read_products_from_xml_file');
		
		//$xml = new SimpleXMLElement();
		$xml = simplexml_load_file($this->_file);
		
		$data = array();
		foreach ($xml->item as $item) {
			$d = array();
			foreach ($item as $item_property_key => $item_property) {
				$this->_append_data($d, $item_property_key, $item_property);
			}
			$data[] = $d;
		}
		return $data;
	}
	
	protected function _read_products_from_excel_file() {
		$this->debug('_read_products_from_excel_file');
		require_once $this->cfg['path']['libs'] . 'xls_reader/PC_shop_excel_reader.php';
		$excel_reader = new PC_shop_excel_reader();
		$excel_reader->absorb_debug_settings($this);
		
		$data = array();
		$parse_params = array('file' => $this->_file, 'data' => & $parse_data);
		$parsed = $excel_reader->parse($parse_params);
		
		$this->debug('$parsed: ' . $parsed, 2);
		
		
		
		$data = array();
			
		if ($parsed) {
			//$this->debug('Parse data: ', 2);
			//$this->debug($parse_data, 3);
			foreach ($parse_data as $sheet) {
				$this->debug('$sheet: ', 2);
				$this->debug($sheet, 3);
				foreach ($sheet['items'] as $item) {
					$d = array();
					foreach ($item as $key => $value) {
						$this->_append_data($d, v($sheet['headers'][$key]), $value);
					}
					$data[] = $d;
				}
			}
		}
		return $data;
	}
	
	protected function _read_products_from_array(&$items) {
		$this->debug('_read_products_from_array');
		
		$data = array();
		foreach ($items as $item) {
			$d = array();
			foreach ($item as $item_property_key => $item_property) {
				$this->_append_data($d, $item_property_key, $item_property);
			}
			$data[] = $d;
		}
		return $data;
	}
	
	protected function _append_data(&$d, $item_property_key, $item_property) {
		$association_data = $this->_get_association($item_property_key);
		$title = $item_property_key;
		if ($association_data) {
			if (is_array($association_data)) {
				$item_property_key = $association_data['field'];
				if (isset($association_data['title'])) {
					$title = $association_data['title'];
				}
			}
			else {
				$item_property_key = $association_data;
			}
		}
		$d[$item_property_key] = (string) trim($item_property);
		$this->_remember_field($item_property_key, $title, v($association_data['on']));
	}
	
	protected function _remember_field($field, $title = '', $on = null) {
		if (!isset($this->_field_names[$field])) {
			$parts = explode('-', $field, 3);
			$type = false;
			$name = $field;
			$lang = false;
						
			$parts_count = count($parts);
			if ($parts_count >= 2) {
				$type = $parts[0];
				$name = $parts[$parts_count - 1];
			}
			if($parts_count == 3) {
				$lang = $parts[1];
			}
			
			if (empty($title)) {
				$title = $name;
			}
			
			$this->_field_names[$field] = array(
				'full_name' => $field,
				'type' => $type,
				'name' => $name,
				'lang' => $lang,
				'title' => $title,
				'on' => $on
			);
			
		}
	}
	
	protected function _get_field_name_data($key) {
		if (!isset($this->_field_names[$key])) {
			$this->_remember_field($key);
		}
		return $this->_field_names[$key];
	}
	
	protected function _get_columns() {
		$columns = array();
		foreach ($this->_field_names as $key => $field_data) {
			$columns [] = array(
				'header' => $field_data['title'],
				'dataIndex' => $field_data['full_name']
			);
		}
		return $columns;
	}
	
	protected function _get_association($field) {
		$field = mb_strtolower($field);
		if (isset($this->_associations[$field])) {
			return $this->_associations[$field];
		}
		return false;
	}
	
	/**
	 * Method populates $this->_associations and $this->missing_products_strategy
	 * @param type $product_import_method
	 */
	protected function _load_fields_associations($product_import_method) {
		$this->_id_fields = array();
		$this->_associations = array();
		$aliases = array();
		$this->_additional_product_data = array();
		$this->_import_only_on = array();
		$this->_product_scope = '';
		$this->_missing_products_scope = '';
		$this->_hook_row = '';
		$this->core->Init_hooks('plugin/pc_shop/import-products/import-fields-associations/' . $product_import_method, array(
			'id_fields'=> &$this->_id_fields,
			'data'=> &$this->_associations,
			'missing_products_strategy'=> &$this->missing_products_strategy,
			'missing_products_scope'=> &$this->_missing_products_scope,
			'aliases' => &$aliases,
			'additional_product_data' => &$this->_additional_product_data,
			'import_only_on' => &$this->_import_only_on,
			'product_scope' => &$this->_product_scope,
			'hook_row' => &$this->_hook_row
		));
		foreach ($this->_associations as $key => $assoc) {
			if (!isset($assoc['title'])) {
				$this->_associations[$key]['title'] = $key;
			}
		}
		foreach ($aliases as $key => $alias_array) {
			if (isset($this->_associations[$key])) {
				foreach ($alias_array as $alias) {
					$this->_associations[$alias] = $this->_associations[$key];
					$this->_associations[$alias]['title'] = $alias;
				}
			}
		}
		$lower_associations = array();
		foreach ($this->_associations as $key => $value) {
			//echo '<hr />' . $key . ' => ' . mb_strtolower($key);
			$lower_associations[mb_strtolower($key)] = $value;
		}
		$this->_associations = $lower_associations;
		if (empty($this->_id_fields)) {
			$this->_id_fields = array(
				'field-external_id'
			);
		}
		$this->_id_fields_flipped = array_flip($this->_id_fields);
		$this->debug('Fields associations:');
		$this->debug($this->_associations, 1);
	}
	
	/**
	 * Method must generate $this->_out['columns'] array where each element is array with keys 'header' and 'dataIndex'
	 * and $this->_out['data'] array where each element is an array representing product, 
	 * every key of which must be prepended by one of this: <ul><li>field-</li><li>content-{ln}</li><li>attribute-{ln}</li></ul>
	 * {ln} is specific language.
	 * For example, product array can be 
	 * <ul>
	 *	<li>field-external_id => EGT-213443</li>
	 *	<li>content-en-name => Disc brakes</li>
	*	<li>content-lt-name => Diskiniai stabdziai</li>
	 *	<li>attribute-en-color => Black</li>
	 *	<li>attribute-lt-color => Juoda</li>
	 * </ul>
	 * 
	 */
	protected function _get_products_from_file() {
		$this->debug('_get_products_from_file');
		$this->increase_debug_offset(2);
		$this->_field_names = array();
		$this->_load_fields_associations($this->product_import_method);
		
		$this->_out['data'] = $this->_read_products_from_file();
		$this->_out['columns'] = $this->_get_columns();
		$this->_out['success'] = true;
		
		$this->debug('$this->_field_names:', 1);
		$this->debug($this->_field_names, 2);
	}
	
	/**
	 * Action for generating products from file.
	 * This method expects uploaded file.
	 * Action generates $this->_out['columns'] and $this->_out['data'] to display products to be imported.
	 * @return type
	 */
	public function default_action() {
		@ini_set('max_execution_time', 300);
		$this->debug('mbstring.func_overload:');
		$this->debug(ini_get('mbstring.func_overload'), 1);
		@ini_set('mbstring.func_overload', 0);
		$this->debug('mbstring.func_overload:');
		$this->debug(ini_get('mbstring.func_overload'), 1);
		set_time_limit(300);
		$this->debug('Default action');
		$this->debug($_POST);
		
		$this->product_import_method = v($_POST['product_import_method']);
		
		$this->_import_set_arguments();

		
		$this->_import_check_file();
		if (isset($this->_out['error'])) {
			return;
		}
		$this->_file = $_FILES['file']['tmp_name'];
		$this->_file_name = $_FILES['file']['name'];
		
		$this->debug('$_FILES[file]', 1);
		$this->debug($_FILES['file'], 1);
		
		$this->increase_debug_offset(2);
		$this->_get_products_from_file();
		
	}
	
	/**
	 * Method for performing actual import.
	 * Method expects $_POST variables:
	 * <ul>
	 *	<li>category_id: must be valid category page node id (pc_shop/category/16)</li>
	 *  <li>product_import_method</li>
	 *	<li>products: array</li>
	 * </ul>
	 * Action must generate $this->_out['imported'] - number of imported products
	 */
	public function confirm() {
		@ini_set('max_execution_time', 300);
		set_time_limit(300);
		$_POST['missing_products_strategy'] = 'delete';
		$this->debug('Confirm  action');
		//$this->debug('$_POST', 1);
		//$this->debug($_POST, 2);
			
		
		//$this->debug = false;
		$this->product_import_method = v($_POST['product_import_method']);
		
		$this->category_id = false;
		
		$controller_data = $this->page->get_controller_data_from_id($_POST['category_id']);
			
		if ($controller_data and v($controller_data['plugin']) == 'pc_shop') {
			$id_data = PC_shop_plugin::ParseID(v($controller_data['id']));
			if ($id_data and v($id_data['type']) == 'category') {
				$this->category_id = $id_data['id'];
				$this->debug('Category id: ' . $this->category_id, 1);
			}
		}
				
		if (false and !$this->category_id) {
			$this->_out['error'] = 'wrong_category';
			$this->_out['category'] = $_POST['category_id'];
			return;
		}
		
		$this->products = $_POST['products'];
		$this->missing_products_strategy = $_POST['missing_products_strategy'];
		
		$this->shop = $this->core->Get_object('PC_shop_manager');
		//$this->shop->products->debug = /*$this->shop->attributes->debug = */ $this->debug;
		//$this->shop->products->absorb_debug_settings($this, 5);
		//$this->shop->attributes->absorb_debug_settings($this, 10);
		
		$category_data = false;
		
		if ($this->category_id) {
			$category_data = $this->shop->categories->Get($this->category_id);
		}
		
		
		if (!$category_data) {
			$this->category_id = false;
			/*
			$this->_out['error'] = 'wrong_category';
			$this->_out['category'] = $this->category_id;
			return;
			*/
		}
		
		$this->debug('Category data:', 1);
		$this->debug($category_data, 2);
		
		$this->_load_fields_associations($this->product_import_method);
		
		if (!is_array($this->products )) {
			$this->products = json_decode($this->products, true);
		}
		if (!$this->products) {
			$this->_out['error'] = 'wrong_data';
			return;
		}
		if (!count($this->products)) {
			$this->_out['error'] = 'no_items';
			return;
		}
		
		//$this->debug('products:', 3);
		//$this->debug($this->products, 3);
		
		$this->_attr_model = new PC_shop_attribute_model();
		$this->_attr_value_model = new PC_shop_attribute_value_model();
		
		$this->_attr_category_item_model = new PC_shop_attribute_item_model();
		$this->_attr_category_item_model->set_category_attribute_scope();
		
		$this->_manufacturer_model = new PC_shop_manufacturer_model();
		
		$this->_field_names = array();
		$items_imported = 0;
		$items_inserted = 0;
		$items_updated = 0;
		$items_skipped = 0;
		$items_unidentifyable = 0;
		$items_not_inserted = 0;
		$updated_products_ids = array();
		$not_updated_products_ids = array();
		foreach ($this->products as $key => $product) {
			$this->debug('<hr />', 4);
			$this->debug('product array key: ' . $key, 5);
			if (!empty($this->_additional_product_data)) {
				$product = array_merge($this->_additional_product_data, $product);
			}
			if ($this->_hook_row !== false and is_callable($this->_hook_row)) {
				call_user_func_array($this->_hook_row, array(&$product));
			}
			
			$category_id = false;
			$logs = array();
			$product_id = $this->_get_product_id_from_product_import_data($product, $category_id, $logs);
			if (!$category_id) {
				$category_id = $this->category_id;
			}
				
			$this->debug('Tried to detect product id: ', 4);
			$this->debug($product_id, 5);
			
			if ($product_id == -1) {
				$this->debug('Queries for category detection: ', 7);
				$this->debug($logs, 7);
			
				$this->debug('Will skip, coz no way to detect product id: ', 4);
				$items_unidentifyable++;
				continue;
			}
			
			$fields = array();
			$contents = array();
			$attributes = array();
			$category_attributes = array();
			$category_attribute_values = array();
			$items_imported++;
			foreach ($product as $product_property => $product_property_value) {
				if (!isset($this->_field_names[$product_property])) {
					$this->_remember_field($product_property);
					//$this->debug('Field_names:', 3);
					//$this->debug($this->_field_names, 4);
				}
				if ($product_id and isset($this->_id_fields_flipped[$product_property])) {
					continue;
				}
				$field_name_data = $this->_field_names[$product_property];
				if (!$product_id and isset($this->_import_only_on['update']) and in_array($product_property, $this->_import_only_on['update'])) {
					$this->debug(':( this property is for update only', 8);
					continue;
				}
				if ($product_id and isset($this->_import_only_on['insert']) and in_array($product_property, $this->_import_only_on['insert'])) {
					$this->debug(':( this property is for insert only', 8);
					continue;
				}
				
				switch (v($field_name_data['type'])) {
					case 'field':
						$fields[$field_name_data['name']] = $product_property_value;
						break;

					case 'content':
						if (v($field_name_data['lang'])) {
							v($contents[$field_name_data['lang']], array());
						}
						$contents[$field_name_data['lang']][$field_name_data['name']] = $product_property_value;
						break;
					
					case 'attribute':
						if (v($field_name_data['lang'])) {
							v($attributes[$field_name_data['lang']], array());
						}
						$attributes[$field_name_data['lang']][$field_name_data['name']] = $product_property_value;
						break;
					
					case 'category_attribute':
						$category_attributes[$field_name_data['name']] = $product_property_value;
						break;	
						
					case 'category_attribute_value':
						if (v($field_name_data['lang'])) {
							v($category_attribute_values[$field_name_data['lang']], array());
						}
						$category_attribute_values[$field_name_data['lang']][$field_name_data['name']] = $product_property_value;
						break;
					
					case 'manufacturer':
						$fields['manufacturer_id'] = $this->_manufacturer_model->get_id_from_field('name', $product_property_value);
						$product['field-manufacturer_id'] = $fields['manufacturer_id'];
						break;
						
					default:
						break;
				}
			}
			
			$missing_id_fields = array_diff_key($this->_id_fields_flipped, $product);
			if (!empty($missing_id_fields)) {
				$this->debug($this->_id_fields_flipped, 6);
				$this->debug(':( Product has missing id keys ' . implode(',', array_keys($this->_id_fields_flipped)), 4);
				$items_skipped++;
				continue;
			}
			
			$this->debug('Fields, contents and attributes:', 3);
			$this->debug($fields, 4); $this->debug($contents, 4); $this->debug($attributes, 4);
			
			$this->debug('$category_attributes and $category_attribute_values:', 3);
			$this->debug($category_attributes, 4); $this->debug($category_attribute_values, 4);
			
			if ($product_id) {
				$this->debug('Updating product data for existing product with id ' .$product_id, 5);
				$new_product_data = $fields;
				$new_product_data['state'] = PC_shop_product_model::STATE_IMPORTING;
				$new_product_data['import_method'] = $this->product_import_method;
				$new_product_data['contents'] = $contents;
				$edit_params = array();
				$edited = $this->shop->products->Edit($product_id, $new_product_data, $edit_params);
				if ($edited) {
					$items_updated++;
					$this->debug(' :) Product was updated with an id ' . $product_id, 5);
					$updated_products_ids[] = $product_id;
				}
				else {
					$this->debug(' :( Product could not be edited. Errors:', 5);
					$this->debug($edit_params->errors->Get_all(), 6);
					$not_updated_products_ids[] = $product_id;
				}
			}
			elseif ($category_id) {
				$this->debug('Creating an empty product ', 5);
				$new_product_data = $fields;
				$new_product_data['state'] = PC_shop_product_model::STATE_IMPORTING;
				$new_product_data['import_method'] = $this->product_import_method;
				$new_product_data['contents'] = $contents;
				//$this->debug('New product dara', 5);
				//$this->debug($new_product_data, 6);
				$create_params = array();
				$product_id = $this->shop->products->Create($category_id, 0, $new_product_data, $create_params);
				if ($product_id) {
					$this->debug(' :) New product was created with an id ' . $product_id, 5);
					$items_inserted++;
				}
				else {
					$this->debug(' :( Product could not be created. Errors:', 5);
					$this->debug($create_params->errors->Get_all(), 6);
					$items_not_inserted++;
				}
			}
			else {
				$this->debug(':( Skipping coz no product id (for updating) and no category_id (for iserting) was detected ', 5);
				$items_skipped++;
			}
			if ($product_id) {
				//$this->shop->attributes->Remove_from_item(null, $product_id);
				foreach ($attributes as $ln => $attributes_for_language) {
					$this->debug("Attributes for language " . $ln, 3);
					foreach ($attributes_for_language as $attribute_key => $attribute_value) {
						$attribute_id = $this->_attr_model->get_one(array(
							'where' => array(
								'ref' => $attribute_key,
								'is_category_attribute' => 0
							),
							'value' => 'id'
						));
						if (!$attribute_id) {
							$attribute_id = $this->shop->attributes->get_id_from_content('name', $attribute_key, $ln);
						}
						if ($attribute_id) {
							$this->debug("Id of atribute '$attribute_key' is: " . $attribute_id, 4);
							if (false and $ln == 'value_id') {
								$this->shop->attributes->Assign_or_edit_for_item($product_id, $attribute_id, PC_shop_attributes::ITEM_IS_PRODUCT, $attribute_value);
							}
							else {
								$this->shop->attributes->Assign_or_edit_for_item($product_id, $attribute_id, PC_shop_attributes::ITEM_IS_PRODUCT, null, $attribute_value);
							}
						}
					}
				}
			}
		}
		$items_imported = $items_inserted + $items_updated;
		
		$this->_import_products_scope_cond = '';
		$this->_import_products_scope_params = array();
	
		if ($this->category_id and $this->_missing_products_scope == 'category') {
			$this->_import_products_scope_cond .= " AND category_id = ?";
			$this->_import_products_scope_params[] = $this->category_id;
		}
		else {
			$this->_import_products_scope_cond .= " AND import_method = ?";
			$this->_import_products_scope_params[] = $this->product_import_method;
		}
		
		$this->_apply_missing_products_strategy();
		
		$this->_restore_products_state();
		
		$this->_out['success'] = true;
		$this->_out['imported'] = $items_imported;
		$this->_out['inserted'] = $items_inserted;
		$this->_out['updated'] = $items_updated;
		$this->_out['skipped'] = $items_skipped;
		
		$updated_products_ids = array_unique($updated_products_ids);
		$this->_out['updated_ids_count'] = count($updated_products_ids);
		
		$this->_out['not_updated_products_ids'] = count($not_updated_products_ids);
		
		$this->_out['unidentifyable'] = $items_unidentifyable;
		$this->_out['not_inserted'] = $items_not_inserted;
		
		$this->debug('$this->_field_names:', 2);
		$this->debug($this->_field_names, 3);
		
		$this->debug($this->shop->products->get_debug_string(), 3);
		$this->debug($this->shop->attributes->get_debug_string(), 3);
	}
		
	public function import($import_method, $category_id, $file, $missing_products_strategy = PC_shop_import_products_admin_api::MISSING_PRODUCTS_STRATEGY_DELETE) {
		@ini_set('max_execution_time', 300);
		set_time_limit(300);
		$this->product_import_method = $import_method;
		$this->category_id = $category_id;
		$this->_file = $this->_file_name = $file;
		
		
		$this->increase_debug_offset(2);
		$this->_get_products_from_file();
		
		$_POST['category_id'] = $category_id;
		$_POST['product_import_method'] = $import_method;
		$_POST['missing_products_strategy'] = $missing_products_strategy;
		$_POST['products'] = $this->_out['data'];
		
		$this->confirm();
	}
	
	protected function _apply_missing_products_strategy() {
		$this->debug("_apply_missing_products_strategy()");
		
		$missing_products_scope_cond = $this->_import_products_scope_cond;
		$missing_products_scope_params = $this->_import_products_scope_params;
		
		$missing_products_scope_cond .= " AND state <> ?";
		$missing_products_scope_params[] = PC_shop_product_model::STATE_IMPORTING;
		
		switch ($this->missing_products_strategy) {
			case PC_shop_import_products_admin_api::MISSING_PRODUCTS_STRATEGY_DELETE:
				$this->shop->products->delete_all($missing_products_scope_cond, $missing_products_scope_params);
				break;

			case PC_shop_import_products_admin_api::MISSING_PRODUCTS_STRATEGY_HIDE:
				$this->shop->products->hide_all($missing_products_scope_cond, $missing_products_scope_params);
				break;
			
			default:
				break;
		}
	}
	
	protected function _restore_products_state() {
		$this->debug("_restore_products_state()");
		$query = "UPDATE {$this->db_prefix}shop_products SET state = " . PC_shop_product_model::STATE_DEFAULT . " WHERE 1 = 1" . $this->_import_products_scope_cond;
		$r = $this->prepare($query);
		$this->debug_query($query, $this->_import_products_scope_params, 1);
		$s = $r->execute($this->_import_products_scope_params);
		
		if ($s) {
			$affected = $r->rowCount();
			$this->debug($affected . ' product(s) were restored to default state', 2);
		}
	}
	
	/**
	 * 
	 * @return string
	 */
	protected function _get_product_id_key() {
		return 'field-external_id';
	}
	

	protected function _get_product_id_from_product_import_data(&$data, &$category_id = false, &$logs = array()) {
		$this->debug("_get_product_id_from_product_import_data()");
		$logs = array();
		$category_id = false;
		$scope = PC_model::create_scope();
		$category_ids_arrays = array();
		foreach ($this->_id_fields as $key => $id_field) {
			if (empty($data[$id_field])) {
				continue;
			}
			$field_name_data = $this->_get_field_name_data($id_field);
			$this->debug('Field name data of ' . $id_field, 1);
			$this->debug($field_name_data, 2);
			switch ($field_name_data['type']) {
				case 'field':
					$scope['where']['t.' . $field_name_data['name']] = $data[$id_field];
					break;

				case 'content':
					$scope['content'] = true;
					$scope['where']['ct.' . $field_name_data['name']] = $data[$id_field];
					$scope['ln'] = $field_name_data['lang'];
					//Not implemented yet
					//return $this->shop->products->get_id_by_content();
					break;

				case 'category_attribute':
					$attribute_id = $this->_attr_model->get_one(array(
						'where' => array(
							'ref' => $field_name_data['name'],
							'is_category_attribute' => 1
						),
						'value' => 'id'
					));
					$my_log = array();
					$my_log['type'] = 'category_attribute';
					$my_log['attribute-id'] = $attribute_id;
					if ($attribute_id) {
						$attr_scope = PC_model::create_scope();
						$attr_scope['where']['attribute_id'] = $attribute_id;
						$attr_scope['where']['value'] = $data[$id_field];
						$attr_scope['value'] = 'item_id';
						$this->debug('$attr_scope:', 2);
						$this->debug($attr_scope, 3);
						$category_ids = $this->_attr_category_item_model->get_all($attr_scope);
						$category_ids_arrays[] = $category_ids;
						$attr_scope['query_only'] = true;
						$my_log['category_ids'] = $category_ids;
						$my_log['category query'] = $this->_attr_category_item_model->get_all($attr_scope);
					}
					$logs[] = $my_log;
					
					break;
				
				case 'category_attribute_value':
					$attribute_id = $this->_attr_model->get_one(array(
						'where' => array(
							'ref' => $field_name_data['name'],
							'is_category_attribute' => 1
						),
						'value' => 'id'
					));
					$my_log = array();
					$my_log['type'] = 'category_attribute_value';
					$my_log['attribute-id'] = $attribute_id;
					if ($attribute_id) {
						$value_id = $this->_attr_value_model->get_one(array(
							'content' => true,
							'ln' => $field_name_data['lang'],
							'where' => array(
								'attribute_id' => $attribute_id,
								'value' => $data[$id_field]
							),
							'value' => 'id'
						));
						
						
						$attr_scope = PC_model::create_scope();
						$attr_scope['where']['attribute_id'] = $attribute_id;
						$attr_scope['where']['value_id'] = $value_id;
						$attr_scope['value'] = 'item_id';
						$this->debug('$attr_scope:', 2);
						$this->debug($attr_scope, 3);
						$category_ids = $this->_attr_category_item_model->get_all($attr_scope);
						$category_ids_arrays[] = $category_ids;
						$attr_scope['query_only'] = true;
						$my_log['category_ids'] = $category_ids;
						$my_log['category query'] = $this->_attr_category_item_model->get_all($attr_scope);
				
					}
					$logs[] = $my_log;
					break;	
					
				default:
					break;
			}
		}
		
		if (empty($scope['where'])) {
			return -1;
		}
		
		if ($this->category_id and $this->_product_scope == 'category') {
			$scope['where']['t.category_id'] = $this->category_id;
		}
		elseif($this->_product_scope == 'import_method') {
			$scope['where']['t.import_method'] = $this->product_import_method;
		}
		
		if (!empty($category_ids_arrays)) {
			$category_ids = array_shift($category_ids_arrays);
			foreach ($category_ids_arrays as $key => $category_ids_array) {
				$category_ids = array_intersect($category_ids, $category_ids_array);
			}
			if (count($category_ids) == 1) {
				$category_id = array_pop($category_ids);
				$scope['where']['t.category_id'] = $category_id;
			}
		}
		
		$scope['value'] = 'id';
		
		$this->debug('product_scope:', 2);
		$this->debug($scope, 3);
				
		$product_ids = $this->shop->products->get_all($scope);
		
		$this->debug('$product_ids:', 2);
		$this->debug($product_ids, 3);
		
		if (count($product_ids) == 1) {
			return $product_ids[0];
		}
		return false;
	}
	
	/**
	 * Method deletes all products (and all its attributes) of the category.
	 * @param int $category_id
	 */
	protected function _delete_products($product_import_method = null, $category_id = null) {
		$query_params = array();
		

		$shop = $this->core->Get_object('PC_shop_manager');
		$flags_cond = $this->db->get_flag_query_condition(PC_shop_attributes::ITEM_IS_PRODUCT, $query_params);

		$import_method_cond = '';
		if (!is_null($product_import_method)) {
			$import_method_cond = " AND import_method = ? ";
			$query_params[] = $product_import_method;
		}
		
		$category_cond = '';
		if (!is_null($category_id)) {
			$category_cond = " AND category_id = ? ";
			$query_params[] = $category_id;
		}
		
		$query = "DELETE FROM {$this->cfg['db']['prefix']}shop_item_attributes
			where item_id in (
				SELECT id 
				FROM {$this->cfg['db']['prefix']}shop_products 
				WHERE 1 = 1 $import_method_cond $category_cond
			)
			AND $flags_cond";

		$r = $this->db->prepare($query);
		$this->debug_query($query, $query_params);
		if ($s = $r->execute($query_params)) {
			$this->_out['success'] = true;
			$this->_out['deleted_attributes'] = $r->rowCount();
			$query_2_params = array($category_id);

			$delete_query = "DELETE FROM {$this->cfg['db']['prefix']}shop_products WHERE category_id=?";

			$this->debug('Delete all products in this category:');
			$this->debug_query($delete_query, $query_2_params);

			$r_hide = $this->db->prepare($delete_query);
			$s_hide = $r_hide->execute($query_2_params);
			if ($s_hide) {
				$this->_out['deleted_products'] = $r_hide->rowCount();
			}
		}
	}
	
}

?>
