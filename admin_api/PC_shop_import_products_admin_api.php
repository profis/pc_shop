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
	
	protected function _read_products_from_file() {
		$this->debug('_get_products_from_file(' . $this->_file);
		$ext = pathinfo($this->_file_name, PATHINFO_EXTENSION);
		$this->debug('extension is: ' . $ext, 1);
		$this->increase_debug_offset(2);
		switch ($ext) {
			case 'xml':
				return $this->_read_products_from_xml_file();
				break;

			default:
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
				$this->_remember_field($item_property_key, $title);
			}
			$data[] = $d;
		}
		return $data;
	}
	
	protected function _read_products_from_excel_file() {
		
	}
	
	protected function _remember_field($field, $title = '') {
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
		if (isset($this->_associations[$field])) {
			return $this->_associations[$field];
		}
		return false;
	}
	
	/**
	 * Method populates $this->_associations and $this->missing_products_stategy
	 * @param type $product_import_method
	 */
	protected function _load_fields_associations($product_import_method) {
		$this->_associations = array();
		$this->core->Init_hooks('plugin/pc_shop/import-products/import-fields-associations/' . $product_import_method, array(
			'data'=> &$this->_associations,
			'missing_products_strategy'=> &$this->missing_products_stategy,
		));
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
		$_POST['missing_products_strategy'] = 'delete';
		$this->debug('Confirm  action');
		$this->debug('$_POST', 1);
		$this->debug($_POST, 2);
			
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
				
		if (!$this->category_id) {
			$this->_out['error'] = 'wrong_category';
			$this->_out['category'] = $_POST['category_id'];
			return;
		}
		
		$this->products = $_POST['products'];
		$this->missing_products_stategy = $_POST['missing_products_strategy'];
		
		$this->shop = $this->core->Get_object('PC_shop_manager');
		$this->shop->products->debug = $this->shop->attributes->debug = $this->debug;
		$this->shop->products->absorb_debug_settings($this, 5);
		$this->shop->attributes->absorb_debug_settings($this, 10);
		$category_data = $this->shop->categories->Get($this->category_id);
		
		if (!$category_data) {
			$this->_out['error'] = 'wrong_category';
			$this->_out['category'] = $this->category_id;
			return;
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
		
		
		
		$this->debug($this->products, 3);
		
		$product_id_key = $this->_get_product_id_key();
		
		$this->_field_names = array();
		$items_imported = 0;
		foreach ($this->products as $key => $product) {
			if (!isset($product[$product_id_key])) {
				$this->debug(':( Product has no id key ' . $product_id_key, 4);
				continue;
			}
			
			$product_id = $this->_get_product_id_from_product_import_data($product_id_key, $product);
			$this->debug('Tried to detect product id: ', 4);
			$this->debug($product_id, 5);
			
			$fields = array();
			$contents = array();
			$attributes = array();
			$items_imported++;
			foreach ($product as $product_property => $product_property_value) {
				if (!isset($this->_field_names[$product_property])) {
					$this->_remember_field($product_property);
					$this->debug('Field_names:', 3);
					$this->debug($this->_field_names, 4);
				}
				if ($product_id and $product_property == $product_id_key) {
					continue;
				}
				$field_name_data = $this->_field_names[$product_property];
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
					
					default:
						break;
				}
			}
			$this->debug('Fields, contents and attributes:', 3);
			$this->debug($fields, 4); $this->debug($contents, 4); $this->debug($attributes, 4);
			
			if ($product_id) {
				$this->debug('Updating product data for existing product with id ' .$product_id, 5);
				$new_product_data = $fields;
				$new_product_data['state'] = PC_shop_product_model::STATE_IMPORTING;
				$new_product_data['import_method'] = $this->product_import_method;
				$new_product_data['contents'] = $contents;
				$edit_params = array();
				$edited = $this->shop->products->Edit($product_id, $new_product_data, $edit_params);
				if ($edited) {
					$this->debug(' :) Product was updated with an id ' . $product_id, 5);
				}
				else {
					$this->debug(' :( Product could not be edited. Errors:', 5);
					$this->debug($edit_params->errors->Get_all(), 6);
				}
			}
			else {
				$this->debug('Creating an empty product ', 5);
				$new_product_data = $fields;
				$new_product_data['state'] = PC_shop_product_model::STATE_IMPORTING;
				$new_product_data['import_method'] = $this->product_import_method;
				$new_product_data['contents'] = $contents;
				$this->debug('New product dara', 5);
				$this->debug($new_product_data, 6);
				$create_params = array();
				$product_id = $this->shop->products->Create($this->category_id, 0, $new_product_data, $create_params);
				if ($product_id) {
					$this->debug(' :) New product was created with an id ' . $product_id, 5);
				}
				else {
					$this->debug(' :( Product could not be created. Errors:', 5);
					$this->debug($create_params->errors->Get_all(), 6);
				}
			}
			if ($product_id) {
				//$this->shop->attributes->Remove_from_item(null, $product_id);
				foreach ($attributes as $ln => $attributes_for_language) {
					$this->debug("Attributes for language " . $ln, 3);
					foreach ($attributes_for_language as $attribute_key => $attribute_value) {
						$attribute_id = $this->shop->attributes->get_id_from_field('ref', $attribute_key);
						if (!$attribute_id) {
							$attribute_id = $this->shop->attributes->get_id_from_content('name', $attribute_key, $ln);
						}
						if ($attribute_id) {
							$this->debug("Id of atribute '$attribute_key' is: " . $attribute_id, 4);
							if ($ln == 'value_id') {
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
		
		$this->_import_products_scope_cond = '';
		$this->_import_products_scope_params = array();
	
		$this->_import_products_scope_cond .= " AND import_method = ?";
		$this->_import_products_scope_params[] = $this->product_import_method;
		
		$this->_apply_missing_products_strategy();
		
		$this->_restore_products_state();
		
		$this->_out['success'] = true;
		$this->_out['imported'] = $items_imported;
		
		$this->debug('$this->_field_names:', 2);
		$this->debug($this->_field_names, 3);
		
		$this->debug($this->shop->products->get_debug_string(), 3);
		$this->debug($this->shop->attributes->get_debug_string(), 3);
	}
			
	protected function _apply_missing_products_strategy() {
		$this->debug("_apply_missing_products_strategy()");
		
		$missing_products_scope_cond = $this->_import_products_scope_cond;
		$missing_products_scope_params = $this->_import_products_scope_params;
		
		$missing_products_scope_cond .= " AND state <> ?";
		$missing_products_scope_params[] = PC_shop_product_model::STATE_IMPORTING;
		
		switch ($this->missing_products_stategy) {
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
	

	protected function _get_product_id_from_product_import_data($product_id_key, &$data) {
		$this->debug("_get_product_id_from_product_import_data($product_id_key)");
		if (!isset($data[$product_id_key])) {
			$this->debug(':( there is no such key in product data', 1);
			return false;
		}
		$field_name_data = $this->_get_field_name_data($product_id_key);
		$this->debug('Field name data of ' . $product_id_key, 1);
		$this->debug($field_name_data, 2);
		switch ($field_name_data['type']) {
			case 'field':
				$this->debug("   calling products->Get_id_by_field({$field_name_data['name']}, {$data[$product_id_key]})", 1);
				return $this->shop->products->Get_id_by_field($field_name_data['name'], $data[$product_id_key]);
				break;

			case 'content':
				//Not implemented yet
				//return $this->shop->products->get_id_by_content();
				break;
			
			default:
				break;
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
