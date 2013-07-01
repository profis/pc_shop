<?php

class PC_shop_product_products_admin_api extends PC_shop_admin_api {
	
	public function get($product_id, $ln = 'lt') {
		$this->debug($_POST);
		if (empty($ln)) {
			$ln = 'lt';
		}
		if (isset($_POST['product_id'])) {
			$product_id = intval($_POST['product_id']);
		}
		if (isset($_POST['ln'])) {
			$ln = $_POST['ln'];
		}
		$this->debug("for_part($product_id, $ln)");
		
		$product_model = new PC_shop_product_model();
		$product_model->absorb_debug_settings($this);
		$related_products = $product_model->get_related_product_ids($product_id);
		
		if (v($this->routes->Get(4)) == 'for_page_tree') {
			$related_products = array_values($related_products);
			foreach ($related_products as $key => $id) {
				$related_products[$key] = 'pc_shop/product/' . $related_products[$key];
			}
			$this->_out['products'] = $related_products;
			$this->_out['success'] = true;
			return;
		};
		
		$this->debug('Related product ids:', 3);
		$this->debug($related_products, 3);
		
		$products = array();
		
		$get_data_params = array(
			'content' => true,
			'ln' => $ln
		);
		if (!empty($related_products)) {
			$products = $product_model->get_data($related_products, $get_data_params);
		}
		
		foreach ($products as $key => $d) {
			$products[$key]['full_name'] = $this->_get_category_name_path($d['category_id'], $ln) . ' - ' . $d['name'];
		}
		
		$this->debug('Related products:', 3);
		$this->debug($products, 3);

		$autos = array();
		$this->_out['data'] = $products;
		
		$this->_out['success'] = true;
	}
	
	/**
	 * Access is not being checked
	 */
	public function save() {
		$this->product_id = intval($_POST['product_id']);
		$this->debug("Save");
		$products = json_decode($_POST['products']);
		$this->debug($products, 1);
		
		if (!is_array($products)) {
			return;
		}
		
		$delete_query = "DELETE FROM {$this->cfg['db']['prefix']}shop_product_products 
			WHERE product_id = ? OR product_id_2 = ?";
		$delete_params = array($this->product_id, $this->product_id);
		
		$this->debug_query($delete_query, $delete_params, 1);
		
		$r = $this->db->prepare($delete_query);
		$s = $r->execute($delete_params);
		
		$insert_query = "INSERT INTO {$this->cfg['db']['prefix']}shop_product_products 
			(product_id,product_id_2) values($this->product_id, ?)";
		$r = $this->db->prepare($insert_query);
		
		foreach ($products as $product) {
			$controller_data = $this->page->get_controller_data_from_id($product);
			$this->debug($controller_data, 5);
			if ($controller_data and v($controller_data['plugin']) == 'pc_shop') {
				$id_data = PC_shop_plugin::ParseID(v($controller_data['id']));
				$this->debug($id_data, 7);
				if ($id_data and v($id_data['type']) == 'product') {
					$product_product = intval($id_data['id']);
					if ($product_product != $this->product_id) {
						$insert_params = array($product_product);
						$s = $r->execute($insert_params);
					}
				}
			}
		}		
	}
	
	
	public function delete() {
		$this->product_id = intval($_POST['product_id']);
		$this->debug("delete");
		$products = json_decode($_POST['products']);
		$this->debug($products, 1);
		
		if (!is_array($products)) {
			return;
		}
		$delete_params = array();
		
		$in_product_set = $this->sql_parser->in($products);
		
		$delete_query = "DELETE FROM {$this->cfg['db']['prefix']}shop_product_products 
			WHERE (
				product_id = ? AND product_id_2 $in_product_set 
					OR 
				product_id_2 = ? AND product_id $in_product_set
			)";
		$delete_params[] = $this->product_id;
		$delete_params = array_merge($delete_params, $products);
		
		$delete_params[] = $this->product_id;
		$delete_params = array_merge($delete_params, $products);
		
		$this->debug_query($delete_query, $delete_params, 1);
		
		$r = $this->db->prepare($delete_query);
		$s = $r->execute($delete_params);
		
		$this->_out['success'] = true;
	}

}

?>
