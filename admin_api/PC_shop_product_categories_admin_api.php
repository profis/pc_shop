<?php

class PC_shop_product_categories_admin_api extends PC_shop_admin_api {
	
	public function update() {
		$this->debug($_POST);
		$m = new PC_shop_product_category_model();
		$m->absorb_debug_settings($this);
		$data = json_decode($_POST['data'], true);
		$this->debug($data);
		$m->update($data, $_POST['id']);
	}
	
	public function get() {
		$this->product_id = intval(v($this->routes->Get(3)));
		if (isset($_POST['part_id'])) {
			$this->product_id = intval($_POST['part_id']);
		}
		
		$this->debug("get($this->product_id)");
		
		$query = "SELECT category_id FROM {$this->cfg['db']['prefix']}shop_product_categories 
			WHERE product_id = ?";
		$query_params = array($this->product_id);
		
		$r = $this->db->prepare($query);
		$s = $r->execute($query_params);
		if ($s) {
			$categories = $r->fetchAll();
			foreach ($categories as $key => $category) {
				$categories[$key] = $category['category_id'];
				if (v($this->routes->Get(4)) == 'for_page_tree') {
					$categories[$key] = 'pc_shop/category/' . $categories[$key];
				};
					
			}
			$this->_out['categories'] = $categories;
		}
		else {
			$this->_out['categories'] = array();
		}
		$this->_out['success'] = true;
	}
	
	
	/**
	 * Access is not being checked
	 */
	public function save() {
		$this->product_id = intval($_POST['product_id']);
		$this->debug("Save");
		$categories = json_decode($_POST['categories']);
		$this->debug($categories, 1);
		$fields = false;
		$additional_field_keys = false;
		$additional_field_keys_s = '';
		$additional_values_s = '';
		if (isset($_POST['preserve_fields'])) {
			$fields = json_decode($_POST['preserve_fields']);
			if (is_array($fields)) {
				$m = new PC_shop_product_category_model();
				$m->absorb_debug_settings($this);
				$field_values = $m->get_all(array(
					'where' => array(
						'product_id' => $this->product_id
					),
					'key' => 'category_id'
				));
				$this->debug($field_values);
				if (count($field_values)) {
					$array_keys = array_keys($field_values);
					$additional_field_keys = array_intersect($fields, array_keys($field_values[$array_keys[0]]));
					$this->debug('$additional_field_keys:', 3);
					$this->debug($additional_field_keys, 4);
					$additional_field_keys_s = ',' . implode(',', $additional_field_keys);
					$additional_values_s = ',' . implode(',', array_fill(0, count($additional_field_keys), '?'));
				}
			}
		}
		$this->debug($fields, 1);
		
		if (!is_array($categories)) {
			return;
		}
		
		$delete_query = "DELETE FROM {$this->cfg['db']['prefix']}shop_product_categories 
			WHERE product_id = ?";
		$delete_params = array($this->product_id);
		
		$this->debug_query($delete_query, $delete_params, 1);
		
		$r = $this->db->prepare($delete_query);
		$s = $r->execute($delete_params);
		
		$insert_query = "INSERT INTO {$this->cfg['db']['prefix']}shop_product_categories 
			(product_id,category_id) values($this->product_id, ?)";
		$r = $this->db->prepare($insert_query);
		
		$insert_query_2 = "INSERT INTO {$this->cfg['db']['prefix']}shop_product_categories 
			(product_id,category_id$additional_field_keys_s) values($this->product_id, ?$additional_values_s)";
		$r_2 = $this->db->prepare($insert_query_2);
		
		
		foreach ($categories as $category) {
			$controller_data = $this->page->get_controller_data_from_id($category);
			
			if ($controller_data and v($controller_data['plugin']) == 'pc_shop') {
				$id_data = PC_shop_plugin::ParseID(v($controller_data['id']));
				if ($id_data and v($id_data['type']) == 'category') {
					$product_category = intval($id_data['id']);
					$insert_params = array($product_category);
					if ($additional_field_keys and isset($field_values[$product_category])) {
						$this->debug(array_flip($additional_field_keys));
						$this->debug($field_values[$product_category]);
						$add_fields = array_intersect_key($field_values[$product_category], array_flip($additional_field_keys));
						$this->debug($add_fields);
						$insert_params = array_merge($insert_params, array_values($add_fields));
						$this->debug_query($insert_query_2, $insert_params, 1);
						$s = $r_2->execute($insert_params);
					}
					else {
						$this->debug_query($insert_query, $insert_params, 1);
						$s = $r->execute($insert_params);
					}
					
				}
			}
		}		
	}
	
	
	public function delete() {
		$this->product_id = intval($_POST['product_id']);
		$this->debug("delete");
		$categories = json_decode($_POST['categories']);
		$this->debug($categories, 1);
		
		if (!is_array($categories)) {
			return;
		}
		$delete_params = array();
		
		$delete_query = "DELETE FROM {$this->cfg['db']['prefix']}shop_product_categories 
			WHERE product_id = ? AND category_id " . $this->sql_parser->in($categories);
		$delete_params[] = $this->product_id;
		$delete_params = array_merge($delete_params, $categories);
		
		$this->debug_query($delete_query, $delete_params, 1);
		
		$r = $this->db->prepare($delete_query);
		$s = $r->execute($delete_params);
		
		$this->_out['success'] = true;
	}

}

?>
