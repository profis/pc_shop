<?php
class PC_shop_manager extends PC_shop {
	public function Create_manufacturer($code, $name) {
		$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_manufacturers (code,name) VALUES(?,?)");
		$s = $r->execute(array($code, $name));
		if (!$s) return false;
		return $this->db->lastInsertId($this->sql_parser->Get_sequence('shop_manufacturers'));
	}
	public function Delete_manufacturer($id) {
		$r = $this->prepare("DELETE FROM {$this->db_prefix}shop_manufacturers WHERE id=?");
		$s = $r->execute(array($id));
		return $s;
	}
}
class PC_shop_categories_manager extends PC_shop_categories {
	/**
	 * 
	 * @param type $parentId
	 * @param type $pid = null Page id
	 * @param type $position
	 * @param type $data
	 * @param type $params
	 * @return false 
	 * Method returns false or id of newly created category
	 */
	public function Create($parentId=0, $pid=null, $position=0, $data, &$params=array()) {
		$this->core->Init_params($params);
		$d = array();
		if (isset($data['contents'])) {
			foreach ($data['contents'] as $ln=>$c) {
				$d['contents'][$ln] = $this->db->fields->Parse('shop_category_contents', $c, $params);
			}
			unset($data['contents'], $ln, $c);
		}
		//parse flags
		if (!isset($data['flags'])) $data['flags'] = self::CF_PUBLISHED;
		if (isset($data['published'])) {
			if ((bool)$data['published']) $data['flags'] |= self::CF_PUBLISHED; //activate
			else $data['flags'] &= ~self::CF_PUBLISHED;
			unset($data['published']);
		}
		//$this->Concat_flags($data, $params);
		$d['resources'] = v($data['resources'], false);
		if (isset($data['attributes'])) $d['attributes'] = $data['attributes'];
		$d['category'] = $this->db->fields->Parse('shop_categories', $data, $params);
		if ($parentId == 0) {
			if (!is_null($pid)) $d['category']['pid'] = $pid;
			else return false;
		}
		//$d['resources'] = $this->db->fields->Parse('shop_resources', $data, $params);
		if ($params->errors->Count()) return false;
		
		$params->Set('cols', array('parent'=> 'parent_id'));
		$params->Set('data', $d['category']);
		
		$tree = $this->core->Get_object('PC_database_tree');
		//main data
		$id = $tree->Insert('shop_categories', $parentId, $position, $d['category'], $params);
		if ($id) {
			//contents
			if (isset($d['contents']))  foreach ($d['contents'] as $ln=>$c) {
				if (!isset($c['route'])) if (isset($c['name'])) $c['route'] = Sanitize('route', $c['name']);
				if (isset($c['route'])) {
					$unique_route_scope = array(
						'where' => array()
					);
					if ($parentId != 0) {
						$unique_route_scope['where']['t.parent_id'] = $parentId;
					}
					elseif (!is_null($pid)) {
						$unique_route_scope['where']['t.pid'] = $pid;
					}
					$c['route'] = $this->get_unique_content_field($ln, 'route', $c['route'], $unique_route_scope);
				}
				$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_category_contents (category_id,ln,".implode(',', array_keys($c)).") VALUES(?,?,".implode(',', array_fill(0, count($c), '?')).")");
				$s = $r->execute(array_merge(array($id, $ln), array_values($c)));
				if (!$s) {
					$params->errors->Add('content', 'Category contents in \''.$ln.'\' language was not added.');
				}
			}
		}
		//resources
		if (is_array($d['resources'])) $this->shop->resources->Update($id, PC_shop_resources::RF_IS_CATEGORY, $d['resources']);
		//attributes
		if (isset($d['attributes'])) if (is_array($d['attributes'])) $this->shop->attributes->Save_for_item($id, PC_shop_attributes::ITEM_IS_CATEGORY, $d['attributes'], true);
		return $id;
	}
	public function Edit($categoryId, $data, &$params = array()) {
		$orig_params = $params;
		$rename_only = v($orig_params['rename_only']);
		$this->core->Init_params($params);
		
		$old_category = $this->get_data($categoryId);
		$new_parent_id = v($data['parent_id'], $old_category['parent_id']);
		$new_pid = v($data['pid'], $old_category['pid']);
		
		$d = array();
		if (isset($data['contents'])) {
			foreach ($data['contents'] as $ln=>$c) {
				$d['contents'][$ln] = $this->db->fields->Parse('shop_category_contents', $c, $params);
			}
			unset($data['contents'], $ln, $c);
		}
		$this->Encode_flags($data);
		if (isset($data['resources'])) $d['resources'] = $data['resources'];
		if (isset($data['attributes'])) $d['attributes'] = $data['attributes'];
		$d['category'] = $this->db->fields->Parse('shop_categories', $data, $params);
		if ($params->errors->Count()) return false;
		//main data
		if (!$rename_only) {
			$updates = $qparams = array();
			foreach ($d['category'] as $field=>&$value) {
				$updates[] = $field.'=?';
				$qparams[] = $value;
			}
			unset($field, $value);
			$qparams[] = $categoryId;
			if (!empty($updates)) {
				$r = $this->prepare("UPDATE {$this->db_prefix}shop_categories SET ".implode(',', array_values($updates))." WHERE id=?");
				$s = $r->execute($qparams);
			}

			if (!$s) return !$params->errors->Add('update_category', '');
		}

		//contents
		$routeLock = (bool)($d['category']['flags'] & self::CF_ROUTE_LOCK);
		if (isset($d['contents'])) foreach ($d['contents'] as $ln=>$cdata) {
			$r = $this->prepare("SELECT * FROM {$this->db_prefix}shop_category_contents WHERE category_id=? and ln=? LIMIT 1");
			$s = $r->execute(array($categoryId, $ln));
			if (!$s) continue;
			$doUpdate = $r->rowCount();
			$oldContentData = $r->fetch();
			//parse route
			if (isset($cdata['route'])) {
				if (empty($cdata['route'])) {
					if (isset($cdata['name'])) $cdata['route'] = Sanitize('route', $cdata['name']);
					else $cdata['route'] = Sanitize('route', $oldContentData['name']);
				}
				else if ($cdata['route'] == $oldContentData['route']) unset($cdata['route']);
				else $cdata['route'] = Sanitize('route', $cdata['route']);
			}
			if (!isset($cdata['route']) && !$routeLock) {
				if (isset($cdata['name'])) {
					if ($cdata['name'] != $oldContentData['name']) {
						$cdata['route'] = Sanitize('route', $cdata['name']);
					}
				}
			}
			
			if (isset($cdata['permalink'])) {
				if (!empty($cdata['permalink'])) {
					$cdata['permalink'] = Sanitize('permalink', $cdata['permalink']);
				}
			}
			
			if (isset($cdata['route'])) {
				$unique_route_scope = array(
					'where' => array()
				);
				if ($new_parent_id != 0) {
					$unique_route_scope['where']['t.parent_id'] = $new_parent_id;
				}
				elseif (!is_null($new_pid)) {
					$unique_route_scope['where']['t.pid'] = $new_pid;
				}
				$cdata['route'] = $this->get_unique_content_field($ln, 'route', $cdata['route'], $unique_route_scope);
			
			}
			
			$updates = $qparams = $updateFields = array();
			foreach ($cdata as $field=>&$value) {
				$updates[] = $field.'=?';
				$updateFields[] = $field;
				$qparams[] = $value;
			}
			unset($field, $value);
			array_push($qparams, $categoryId, $ln);
			
			if ($doUpdate) {
				$query = "UPDATE {$this->db_prefix}shop_category_contents SET ".implode(',', array_values($updates))." WHERE category_id=? and ln=?";
				$r = $this->prepare($query);
				$s = $r->execute($qparams);
				continue;
			}
			$query = "INSERT INTO {$this->db_prefix}shop_category_contents (".implode(',', $updateFields).",category_id,ln) VALUES(".implode(',', array_fill(0, count($qparams), '?')).")";
			$r = $this->prepare($query);
			$s = $r->execute($qparams);
			continue;
		}
		//resources
		if (isset($d['resources'])) if (is_array($d['resources'])) $this->shop->resources->Update($categoryId, PC_shop_resources::RF_IS_CATEGORY, $d['resources']);
		//attributes
		if (isset($d['attributes'])) if (is_array($d['attributes'])) $this->shop->attributes->Save_for_item($categoryId, PC_shop_attributes::ITEM_IS_CATEGORY, $d['attributes']);
		return true;
	}
	public function Get($id=null, $parentId=null, $pid=null, &$params=array()) {
		$this->core->Init_params($params);
		$query = "SELECT ".($params->Has_paging()?'SQL_CALC_FOUND_ROWS ':'')."* FROM {$this->db_prefix}shop_categories ".(!is_null($id)?'WHERE id'.(is_array($id)?' '.$this->sql_parser->in($id):'=? ORDER BY lft LIMIT 1'):((!is_null($parentId))?'WHERE '.($parentId != 0 ? 'parent_id=?': '1 = 1').(!is_null($pid)?' and pid=?':''):'').($params->Has_paging()?" ORDER BY lft LIMIT {$params->paging->Get_offset()},{$params->paging->Get_limit()}":''));
		$r_category = $this->prepare($query);
		$r_contents = $this->prepare("SELECT * FROM {$this->db_prefix}shop_category_contents WHERE category_id=?");
		$queryParams = array();
		if (!is_null($id)) {
			if (is_array($id)) {
				$queryParams += $id;
			}
			else $queryParams[] = $id;
		}
		else if (!is_null($parentId)) {
			if ($parentId != 0) {
				$queryParams[] = $parentId;
			}
			if (!is_null($pid)) $queryParams[] = $pid;
		}
		$s = $r_category->execute($queryParams);
		if (!$s) return false;
		if ($params->Has_paging()) {
			$rTotal = $this->query("SELECT FOUND_ROWS()");
			if ($rTotal) $params->paging->Set_total($rTotal->fetchColumn());
		}
		$categories = array();
		while ($d = $r_category->fetch()) {
			$this->Decode_flags($d);
			$d['contents'] = array();
			$s = $r_contents->execute(array($d['id']));
			if (!$s) return false;
			while ($c = $r_contents->fetch()) {
				$d['contents'][$c['ln']] = $c;
			}
			$categories[] = $d;
		}
		return (!is_null($id) && !is_array($id) && count($categories)?$categories[0]:$categories);
	}
	public function Delete_category($id, &$params=array()) {
		$this->core->Init_params($params);
		//check if exists and get left value
		$this->deleted_category_data = $c = $this->Get($id);
		if (!$c) {
			return !$params->errors->Add('doesnt_exist', 'Category was not found');
		}
		if ($this->shop->products->Count($id)) {
			return !$params->errors->Add('products_inside', 'This category has products inside');
		}
		//main data
		$query = "DELETE FROM {$this->db_prefix}shop_categories WHERE id=?";
		$rCategory = $this->prepare($query);
		$query_params = array($id);
		$s = $rCategory->execute($query_params);
		if (!$s) return !$params->errors->Add('delete_category', '');
		//contents
		$rContents = $this->prepare("DELETE FROM {$this->db_prefix}shop_category_contents WHERE category_id=?");
		$rContents->execute(array($id));
		
		$product_model = new PC_shop_product_model();
		$product_ids = $product_model->get_all(array(
			'select' => 't.id',
			'where' => array(
				'pc.lft >= ? AND pc.rgt <= ?'
			),
			'query_params' => array(
				$c['lft'], $c['rgt']
			),
			'join' => "LEFT JOIN {$this->db_prefix}shop_categories pc ON pc.id = t.category_id",
			'value' => 'id'
		));

		foreach ($product_ids as $product_id) {
			$this->shop->products->Delete($product_id);
		}
		
		if (!v($params->do_not_delete_tree_gap)) {
			$tree = $this->core->Get_object("PC_database_tree");
			$tree->Delete_gap('shop_categories', $c['lft']-1, ($c['rgt']-$c['lft']+1));
		}
		
		//resources
		$this->shop->resources->Delete(null, $id, true);
		return true;
	}
	
	public function Update_dynamic_attribute_due_products($category_id, $dynamic_attribute_data) {
		$attribute_id = false;
		$search = $this->shop->attributes->Find($dynamic_attribute_data['category_attribute_name'], true);
		if ($search) {
			$attribute_id = $search[0]['attribute_id'];
			//$attribute = $shop->attributes->Get($search[0]['attribute_id']);
		}
		else {
			$attCreateParams = array('is_custom'=> true);
			$attribute_id = $this->shop->attributes->Create(true, array(
				$dynamic_attribute_data['ln'] => $dynamic_attribute_data['category_attribute_name']
			), $attCreateParams);
		}
		
		if (!$attribute_id) {
			return false;
		}

		$product_attribute_id = false;
		$product_attributes = $this->shop->attributes->Find($dynamic_attribute_data['product_attribute_name'], false);
		if ($product_attributes) {
			$product_attribute_id = $product_attributes[0]['attribute_id'];
			//$attribute = $shop->attributes->Get($search[0]['attribute_id']);
		}
		
		if (!$product_attribute_id) {
			return false;
		}
		
		$select = '';
		$where_s = v($dynamic_attribute_data['where'], '');
		$where_s = str_replace(':value:', 'a.value', $where_s);
		switch ($dynamic_attribute_data['type']) {
			case 'min':
				$select = 'min(CONVERT(a.value, SIGNED INTEGER))';
				break;

			case 'max':
				$select = 'max(CONVERT(a.value, SIGNED INTEGER))';
				break;
			
			case 'group_concat':
				$select = $this->sql_parser->group_concat('a.value', array(
					'distinct' => true,
					'separator' => $dynamic_attribute_data['group_concat_separator'],
					'order' => array(
						'by' => $dynamic_attribute_data['group_concat_order_by']
					)
				));
				break;
			
			default:
				break;
		}
		
		
		$dynamic_data = $this->shop->attributes->Get_aggregate_data_for_category_products($product_attribute_id, $category_id, $select, $where_s);

		$this->shop->attributes->Assign_or_edit_for_item($category_id, $attribute_id, PC_shop_attributes::ITEM_IS_CATEGORY, null, $dynamic_data);
		
		//$this->shop->attributes->Edit_for_item($id, $valueId=null, $value=null) {
	}
	
	public function Update_dynamic_attribute($category_id, &$dynamic_attribute_data) {
		$attribute_id = $this->shop->attributes->get_id_from_ref($dynamic_attribute_data['dynamic_attribute_ref']);
		
		if (!$attribute_id) {
			return false;
		}

		$dynamic_data = $this->Get_dynamic_data($category_id, $dynamic_attribute_data);
		if ($dynamic_data === false) {
			return false;
		}
		$this->shop->attributes->Assign_or_edit_for_item($category_id, $attribute_id, PC_shop_attributes::ITEM_IS_CATEGORY, null, $dynamic_data);
	}
	
	/**
	 * Copies category into another category 
	 * (icluding contents, attributes, resources, children categories, products with contents, attributes and resources)
	 * @param int $id Category id to be copied
	 * @param int $into_id Category id to be copied into
	 */
	public function Copy($id, $into_id) {
		$category_data = $this->Get($id);

		$category_data['resources'] = array();
		$category_data['resources']['add'] = $this->shop->resources->Get(null, $id, PC_shop_resources_manager::RF_IS_CATEGORY);

		$category_data['attributes'] = array();
		$category_data['attributes']['save'] = $this->shop->attributes->Get_for_item($id, PC_shop_attributes::ITEM_IS_CATEGORY);

		$params = array();
		$new_category_id = $this->Create($into_id, 0, null, $category_data, $params);
		if (!$new_category_id) {
			return false;
		}
		
		$products = $this->shop->products->Get(null, $id);

		foreach ($products as $product) {
			$product['resources'] = array();
			$product['resources']['add'] = $this->shop->resources->Get(null, $product['id'], PC_shop_resources_manager::RF_DEFAULT);

			$product['attributes'] = array();
			$product['attributes']['save'] = $this->shop->attributes->Get_for_item($product['id'], PC_shop_attributes::ITEM_IS_PRODUCT);

			$create_product_params = array();
			$this->shop->products->Create($new_category_id, 0, $product, $create_product_params);
		}
		
		if ($id == $into_id) {
			return;
		}
	
		$categories = $this->shop->categories->get_all(array(
			'select' => 'id',
			'where' => 'parent_id = ?',
			'query_params' => array(
				$id
			),
			//'query_only' => true
		));
		
		foreach ($categories as $category) {
			$this->Copy($category['id'], $new_category_id);
		}
		
	}
	
}
class PC_shop_products_manager extends PC_shop_products {
	
	
	/**
	 * 
	 * @param int $categoryId
	 * @param int $position
	 * @param array $data product data array with possible additional keys:
	 * <ul><li>contents</li><li>resourses</li><li>attributes</li></ul>
	 * @param array $params
	 * @return boolean
	 */
	public function Create($categoryId, $position=0, $data, &$params=array()) {
		$this->core->Init_params($params);
		if (!$this->shop->categories->Exists($categoryId)) {
			$params->errors->Add('category', 'Category was not found');
		}
		$current_time = time();
		if (isset($data['hot']) and $data['hot']) {
			$data['hot_from'] = $current_time;
		}
		
		$this->core->Init_hooks('plugin/pc_shop/product/create', array(
			'data'=> &$data,
			'category_id' => $categoryId,
			'shop' => &$this->shop
		));
		
		$d = array();
		if (isset($data['contents'])) {
			foreach ($data['contents'] as $ln=>$c) {
				if (isset($c['name']) and !isset($c['route'])) {
					$c['route'] = Sanitize('route', $c['name']);
				}
				$d['contents'][$ln] = $this->db->fields->Parse('shop_product_contents', $c, $params);
				if (isset($d['contents'][$ln]['route'])) {
					$d['contents'][$ln]['route'] = $this->get_unique_content_field($ln, 'route', $d['contents'][$ln]['route'], array(
						'where' => array('t.category_id' => $categoryId) 
					));
				}
			}
			unset($data['contents'], $ln, $c);
		}
		$this->Encode_flags($data);
		if (isset($data['resources'])) $d['resources'] = $data['resources'];
		if (isset($data['attributes'])) $d['attributes'] = $data['attributes'];
		$d['product'] = $this->db->fields->Parse('shop_products', $data, $params);
		if ($params->errors->Count()) return false;
		
		if ($position == -1) $position = 1;
		else {
			$r = $this->prepare("SELECT max(position) FROM {$this->db_prefix}shop_products WHERE category_id=?");
			$s = $r->execute(array($categoryId));
			if (!$s) {
				$params->errors->Add('database', 'Select relative position');
				return false;
			}
			if ($r->rowCount()) {
				$maxPosition = $r->fetchColumn();
				if ($position+1 > $maxPosition or $position == 0) $position = $maxPosition + 1;
			}
			else if ($position > 1 or $position == 0) $position = 1;
		}
		$query = "UPDATE {$this->db_prefix}shop_products SET position=position+1 WHERE category_id=? and position>=?";
		$r = $this->prepare($query);
		$s = $r->execute(array($categoryId, $position));
		if (!$s) {
			$params->errors->Add('position', 'Error while pushing positions to the right');
			return false;
		}
		if (!isset($d['product']['position'])) {
			$d['product']['position'] = $position;
		}
		$d['product']['created_on'] = $current_time;
		$auth_user_id = $this->auth->Get_current_user_id();
		if (!$auth_user_id) {
			$auth_user_id = 0;
		}
		$query_params = array_merge(array($auth_user_id, $categoryId), array_values($d['product']));
		$query = "INSERT INTO {$this->db_prefix}shop_products (auth_user_id,category_id,".implode(',', array_keys($d['product'])).") VALUES(?,?,".implode(',', array_fill(0, count($d['product']), '?')).")";
		$r = $this->prepare($query);
		$s = $r->execute($query_params);
		if (!$s) {
			$params->errors->Add('create', 'Error while trying to insert product into database.');
			return false;
		}
		$id = $this->db->lastInsertId($this->sql_parser->Get_sequence('shop_products'));
		
		if (isset($d['contents'])) {
			foreach ($d['contents'] as $ln=>$c) {
				if (!isset($c['route']) or empty($c['route'])) {
					$c['route'] = $id;
				}
				$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_product_contents (product_id,ln,".implode(',', array_keys($c)).") VALUES(?,?".(count($c)?",".implode(',', array_fill(0, count($c), '?')):"").")");
				$s = $r->execute(array_merge(array($id, $ln), array_values($c)));
				if (!$s) {
					$params->errors->Add('content', 'Product contents in \''.$ln.'\' language was not added.');
				}
			}
		}
		//resources
		if (isset($d['resources'])) if (is_array($d['resources'])) $this->shop->resources->Update($id, null, $d['resources']);
		//attributes
		if (isset($d['attributes'])) if (is_array($d['attributes'])) $this->shop->attributes->Save_for_item($id, PC_shop_attributes::ITEM_IS_PRODUCT, $d['attributes'], true);
		
		return $id;
	}
	public function Edit($productId, $data, &$params) {
		$this->core->Init_params($params);
		$old_product = $this->get_data($productId);
		$categoryId = v($data['category_id'], $old_product['category_id']);
		if (isset($data['hot']) and $data['hot']  and (!isset($old_product['hot_from']) or is_null($old_product['hot_from']))) {
			$data['hot_from'] = time();
		}
		elseif(!isset($data['hot']) or !$data['hot']) {
			$data['hot_from'] = NULL;
		}
		
		$this->core->Init_hooks('plugin/pc_shop/product/edit', array(
			'data'=> &$data,
			'product_id' => $productId,
			'category_id' => $categoryId,
			'shop' => &$this->shop
		));
		
		$d = array();
		// print_pre($data['contents']);
		if (isset($data['contents'])) {
			foreach ($data['contents'] as $ln=>$c) {
				$d['contents'][$ln] = $this->db->fields->Parse('shop_product_contents', $c, $params);
			}
			unset($data['contents'], $ln, $c);
		}
		$this->Encode_flags($data);
		if (isset($data['resources'])) $d['resources'] = $data['resources'];
		if (isset($data['attributes'])) $d['attributes'] = $data['attributes'];
		if (isset($data['prices'])) $d['prices'] = $data['prices'];
		$d['product'] = $this->db->fields->Parse('shop_products', $data, $params);
		// print_pre($d['product']);
		if ($params->errors->Count()) return false;
		//main data
		$nullable = array('quantity');
		$updates = $qparams = array();
		foreach ($d['product'] as $field=>&$value) {
			if( in_array($field, $nullable) && $value === '' )
				$value = null;
			$updates[] = $field.'=?';
			$qparams[] = $value;
		}
		unset($field, $value);
		$qparams[] = $productId;
		$r = $this->prepare("UPDATE {$this->db_prefix}shop_products SET ".implode(',', array_values($updates))." WHERE id=?");
		$s = $r->execute($qparams);
		//print_pre($d);
		if (!$s) return !$params->errors->Add('update_product', '');
		$routeLock = (bool)($d['product']['flags'] & self::PF_ROUTE_LOCK);
		//contents
		if (isset($d['contents']))  foreach ($d['contents'] as $ln=>$cdata) {
			
			$r = $this->prepare("SELECT * FROM {$this->db_prefix}shop_product_contents WHERE product_id=? and ln=? LIMIT 1");
			$s = $r->execute(array($productId, $ln));
			if (!$s) continue;
			$doUpdate = $r->rowCount();
			$oldContentData = $r->fetch();
			//parse route
			if (isset($cdata['route'])) {
				if (empty($cdata['route'])) {
					if (isset($cdata['name'])) $cdata['route'] = Sanitize('route', $cdata['name']);
					else $cdata['route'] = Sanitize('route', $oldContentData['name']);
				}
				else if ($cdata['route'] == $oldContentData['route']) unset($cdata['route']);
				else $cdata['route'] = Sanitize('route', $cdata['route']);
			}
			if (!isset($cdata['route']) && !$routeLock) {
				if (isset($cdata['name'])) {
					if ($cdata['name'] != $oldContentData['name']) {
						$cdata['route'] = Sanitize('route', $cdata['name']);
					}
				}
			}
			
			if (isset($cdata['route'])) {
				$cdata['route'] = $this->get_unique_content_field($ln, 'route', $cdata['route'], array(
					'where' => array('t.category_id' => $categoryId) 
				));
			}
			
			if (isset($cdata['permalink'])) {
				if (!empty($cdata['permalink'])) {
					$cdata['permalink'] = Sanitize('permalink', $cdata['permalink']);
				}
			}
			$updates = $qparams = $updateFields = array();
			foreach ($cdata as $field=>&$value) {
				$updates[] = $field.'=?';
				$updateFields[] = $field;
				$qparams[] = $value;
			}
			unset($field, $value);
			array_push($qparams, $productId, $ln);
			$r = $this->prepare("SELECT 1 FROM {$this->db_prefix}shop_product_contents WHERE product_id=? and ln=? LIMIT 1");
			$s = $r->execute(array($productId, $ln));
			if (!$s) continue;
			if ($r->rowCount()) {
				$r = $this->prepare("UPDATE {$this->db_prefix}shop_product_contents SET ".implode(',', array_values($updates))." WHERE product_id=? and ln=?");
				$s = $r->execute($qparams);
				continue;
			}
			$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_product_contents (".implode(',', $updateFields).",product_id,ln) VALUES(".implode(',', array_fill(0, count($qparams), '?')).")");
			$s = $r->execute($qparams);
			continue;
		}
		//resources
		if (isset($d['resources'])) if (is_array($d['resources'])) $this->shop->resources->Update($productId, null, $d['resources']);
		//attributes
		if (isset($d['attributes'])) if (is_array($d['attributes'])) $this->shop->attributes->Save_for_item($productId, PC_shop_attributes::ITEM_IS_PRODUCT, $d['attributes']);
		
		if (isset($d['prices']) and is_array($d['prices'])) {
			$product_price_model = new PC_shop_product_price_model();
			foreach ($d['prices'] as $key => $product_price_data) {
				if (!empty($product_price_data['id'])) {
					$pp_id = $product_price_data['id'];
					$product_price_data = PC_utils::filterArray(array('price'), $product_price_data);
					$product_price_model->update($product_price_data, $pp_id);
				}
				else {
					$product_price_data = PC_utils::filterArray(array('c_id', 'price'), $product_price_data);
					$product_price_data['product_id'] = $productId;
					$product_price_model->insert($product_price_data);
				}
			}
		}
		
		
		return true;
	}
	public function Get($id=null, $categoryId=null, &$params=array()) {
		/*$params = array(
			'filter'=> array(
				'mpn'=> 1,
				//arba
				'mpn'=> array(1, 2, 3, 4, 5)
			)
		);*/
		$this->core->Init_params($params);
		$r_product = $this->prepare("SELECT ".($params->Has_paging()?'SQL_CALC_FOUND_ROWS ':'')."* FROM {$this->db_prefix}shop_products ".(!is_null($id)?'WHERE id'.(is_array($id)?' '.$this->sql_parser->in($id):'=? LIMIT 1'):(!is_null($categoryId)?'WHERE category_id=?':'').($params->Has_paging()?" ORDER BY position LIMIT {$params->paging->Get_offset()},{$params->paging->Get_limit()}":'')));
		$r_contents = $this->prepare("SELECT * FROM {$this->db_prefix}shop_product_contents WHERE product_id=?");
		$queryParams = array();
		if (!is_null($id)) {
			if (is_array($id)) {
				$queryParams += $id;
			}
			else $queryParams[] = $id;
		}
		else if (!is_null($categoryId)) $queryParams[] = $categoryId;
		$s = $r_product->execute($queryParams);
		if (!$s) return false;
		if ($params->Has_paging()) {
			$rTotal = $this->query("SELECT FOUND_ROWS()");
			if ($rTotal) $params->paging->Set_total($rTotal->fetchColumn());
		}
		$list = array();
		while ($d = $r_product->fetch()) {
			$this->Decode_flags($d);
			$d['weight'] = preg_replace('#(?:\\.0+|(\\.[0-9]*[1-9])0*)$#', '$1', $d['weight']); // remove trailing zeroes
			$d['volume'] = preg_replace('#(?:\\.0+|(\\.[0-9]*[1-9])0*)$#', '$1', $d['volume']); // remove trailing zeroes
			$d['contents'] = array();
			$s = $r_contents->execute(array($d['id']));
			if (!$s) return false;
			while ($c = $r_contents->fetch()) {
				$d['contents'][$c['ln']] = $c;
			}
			if (!is_null($id)) {
				$d['auth_user_name'] = '';
				if (!is_null($id) and $d['auth_user_id']) {
					$auth_user_data = $this->auth->users->Get($d['auth_user_id']);
					if ($auth_user_data) {
						$d['auth_user_name'] = $auth_user_data['username'];
					}
				}
			}
			
			$list[] = $d;
		}
		return (!is_null($id) && !is_array($id) && count($list)?$list[0]:$list);
	}
	public function Delete($id=null, $categoryId=null) {
		$this->core->Init_params($params);
		//main data
		$rProduct = $this->prepare("DELETE FROM {$this->db_prefix}shop_products WHERE id=?");
		$s = $rProduct->execute(array($id));
		if (!$s) return !$params->errors->Add('delete_product', '');
		//contents
		$rContents = $this->prepare("DELETE FROM {$this->db_prefix}shop_product_contents WHERE product_id=?");
		$rContents->execute(array($id));
		//resources
		$this->shop->resources->Delete(null, $id, false);
		return true;
	}
	
	public function delete_all($scope_cond = '', $scope_params = array()) {
		//Delete products' attributes
		$query_params = $scope_params;
		
		$product_attributes_cond = $this->db->get_flag_query_condition(PC_shop_attributes::ITEM_IS_PRODUCT, $query_params);
			
		$query = "DELETE FROM {$this->cfg['db']['prefix']}shop_item_attributes
			WHERE item_id in (
				SELECT id 
				FROM {$this->db_prefix}{$this->_table} 
				WHERE 1 = 1 $scope_cond
			)
			AND $product_attributes_cond";

		$r = $this->db->prepare($query);
		$s = $r->execute($query_params);
		if ($s) {
			$affected = $r->rowCount();
		}
		
		
		//Delete products' contents
		$query_params = $scope_params;
		$query = "DELETE FROM {$this->cfg['db']['prefix']}shop_product_contents
			WHERE product_id in (
				SELECT id 
				FROM {$this->db_prefix}{$this->_table} 
				WHERE 1 = 1 $scope_cond
			)";

		$r = $this->db->prepare($query);
		$s = $r->execute($query_params);
		if ($s) {
			$affected = $r->rowCount();
		}
		
		//Delete products' categories (many-many)
		$query_params = $scope_params;
		$query = "DELETE FROM {$this->cfg['db']['prefix']}shop_product_categories
			WHERE product_id in (
				SELECT id 
				FROM {$this->db_prefix}{$this->_table} 
				WHERE 1 = 1 $scope_cond
			)";

		$r = $this->db->prepare($query);
		$s = $r->execute($query_params);
		if ($s) {
			$affected = $r->rowCount();
		}
		
		
		//Delete products resources
		$query_params = $scope_params;
		$query = "SELECT id FROM {$this->db_prefix}{$this->_table}
				WHERE 1 = 1 $scope_cond";

		$r = $this->db->prepare($query);
		$s = $r->execute($query_params);
		if ($s) {
			while($d = $r->fetch()) {
				$this->shop->resources->Delete(null, $d['id'], false);
			}
		}
		
		
		//Delete products
		$query_params = $scope_params;
		$query = "DELETE FROM {$this->db_prefix}{$this->_table}
				WHERE 1 = 1 $scope_cond";

		$r = $this->db->prepare($query);
		$s = $r->execute($query_params);
		if ($s) {
			$affected = $r->rowCount();
		}
	}
	
	public function hide_all($scope_cond = '', $scope_params = array()) {
		$query_params = array();
		$query_params[] = PC_shop_products::PF_PUBLISHED;
		
		//$query = "UPDATE {$this->db_prefix}{$this->_table} SET flags=flags^? 
		$query = "UPDATE {$this->db_prefix}{$this->_table} SET flags=flags & (255-?)	
			WHERE 1 = 1 $scope_cond";
		$r = $this->db->prepare($query);
		
		$query_params = array_merge($query_params, $scope_params);
		
		$s = $r->execute($query_params);

		if ($s) {
			$total_hidden = $r->rowCount();
		}
		
	}
	
	
	
	public function Move($id, $categoryId, $position=0) {
		if ($position == 0) {
			$query = "SELECT max(position) FROM {$this->db_prefix}shop_products WHERE category_id=?";
			$r = $this->prepare($query);
			$query_params = array($categoryId);
			$s = $r->execute($query_params);
			if (!$s) return false;
			$pos = $r->fetchColumn();
		}
		else if ($position == -1) {
			$pos = 1;
		}
		else {
			$query = "SELECT position FROM {$this->db_prefix}shop_products WHERE id=? and category_id=? LIMIT 1";
			$r = $this->prepare($query);
			$query_params = array($position, $categoryId);
			$s = $r->execute($query_params);
			if (!$s) return false;
			$pos = $r->fetchColumn()+1;
		}
		//delete old position
		$this->prepare("UPDATE {$this->db_prefix}shop_products SET position=position-1 WHERE id=? and position>?")->execute(array($id, $pos));
		//create gap
		if ($position != 0) {
			$r = $this->prepare("UPDATE {$this->db_prefix}shop_products SET position=position+1 WHERE position>=?");
			$s = $r->execute(array($pos));
			if (!$s) return false;
		}
		$data = $this->get_data($id, array('content' => array('select' => 'route'), 'ln' => false));
		$new_data = array(
			'category_id' => $categoryId,
			'position' => $pos
		);
		$new_contents = array();
		foreach ($data['routes'] as $ln => $route) {
			$new_contents[$ln] = array();
			$unique_route = $this->get_unique_content_field($ln, 'route', $route, array(
				'where' => array('t.category_id' => $categoryId) 
			));
			if ($route != $unique_route) {
				$new_contents[$ln]['route'] = $unique_route;
			}
		}
		$new_data['_content'] = $new_contents;
		$this->update($new_data, $id);
		/*
		$r = $this->prepare("UPDATE {$this->db_prefix}shop_products SET category_id=?, position=?  WHERE id=?");
		$s = $r->execute(array($categoryId, $pos, $id));
		*/
		//if ($s) $this->Recalculate_positions($categoryId);
		return $s;
	}
	public function Recalculate_positions($categoryId=null) {
		$params = array();
		if (!is_null($categoryId)) $params[] = $categoryId;
		$r = $this->prepare("SELECT id,category_id,position FROM {$this->db_prefix}shop_products ".(!is_null($categoryId)?'WHERE category_id=? ':'')."ORDER BY category_id,position");
		$s = $r->execute($params);
		if (!$s) return false;
		$rUpdate = $this->prepare("UPDATE {$this->db_prefix}shop_products SET position=? WHERE id=?");;
		$map = array();
		while ($d = $r->fetch()) {
			if (!isset($map[$d['category_id']])) $map[$d['category_id']] = 1;
			$rUpdate->execute(array($map[$d['category_id']], $d['id']));
			$map[$d['category_id']]++;
		}
		return true;
	}
}
class PC_shop_resources_manager extends PC_shop_resources {
	public function Add($itemId, $fileId, $isCategory=false, $isAttachment=false, $flags=array()) {
		$flagsCheck = 0x0;
		if ($isCategory) $flagsCheck |= self::RF_IS_CATEGORY;
		if ($isAttachment) $flagsCheck |= self::RF_IS_ATTACHMENT;
		//custom flags
		if (!is_null($flags)) if (!is_array($flags)) $flags = array($flags);
		if (count($flags)) foreach ($flags as $flag) {
			$flagsCheck |= $flag;
		}
		//calculate position for the new resource
		$r = $this->prepare("SELECT max(position) FROM {$this->db_prefix}shop_resources WHERE item_id=? and flags&?=?");
		$s = $r->execute(array($itemId, $flagsCheck, $flagsCheck));
		if ($s) {
			$position = $r->fetchColumn()+1;
		}
		else $position = 1000;
		//insert!
		$query = "INSERT IGNORE INTO {$this->db_prefix}shop_resources (item_id,file_id,flags,position) VALUES(?,?,?,?)";
		$r = $this->prepare($query);
		$query_params = array($itemId, $fileId, $flagsCheck, $position);
		$s = $r->execute($query_params);
		if ($s) {
			$id = $this->db->lastInsertId($this->sql_parser->Get_sequence('shop_resources'));
			return $id;
		}
		return $s;
	}
	public function Delete($id=null, $itemId=null, $isCategory=false) {
		$where = $params = array();
		if (!is_null($id)) {
			$where[] = 'resource_id=?';
			$params[] = $id;
		}
		else if (!is_null($itemId)) {
			$where[] = 'item_id=?';
			$params[] = $itemId;
			$where[] = 'flags&'.self::RF_IS_CATEGORY.'='.self::RF_IS_CATEGORY;
		}
		else return false;
		$r = $this->prepare("DELETE FROM {$this->db_prefix}shop_resources WHERE ".implode(' and ', $where));
		$s = $r->execute($params);
		return $s;
	}
	public function Clear($itemId) {
		$r = $this->prepare("DELETE FROM {$this->db_prefix}shop_resources WHERE item_id=?");
		$s = $r->execute(array($itemId));
		return $s;
	}
	public function Update($itemId, $flags=array(), $data) {
		//add new resources
		if (isset($data['add'])) if (count($data['add']))
		foreach ($data['add'] as $res) {
			if (isset($res['file_id'])) {
				$res['id'] = $res['file_id'];
			}
			if (!$res['id']) {
				continue;
			}
			$this->Add($itemId, $res['id'], false, (bool)$res['is_attachment'], $flags);
		}
		//delete resources
		if (isset($data['remove'])) if (count($data['remove']))
		foreach ($data['remove'] as $res) {
			$this->Delete($res['id']);
		}
	}
	public function Move($resourceId, $difference=0, $returnData = false) {
		//select resource data
		$r = $this->prepare("SELECT * FROM {$this->db_prefix}shop_resources WHERE resource_id=?");
		$s = $r->execute(array($resourceId));
		if (!$s) return false;
		if ($r->rowCount() != 1) return false;
		$resource = $r->fetch();
		$flags = ($resource['flags'] & (PC_shop_resources::RF_IS_CATEGORY | PC_shop_resources::RF_IS_ATTACHMENT));
		//delete old position
		$this->prepare("UPDATE {$this->db_prefix}shop_resources SET position=position-1 WHERE item_id=? and position>? and flags&?=?")->execute(array($resource['item_id'], $resource['position'], $flags, $flags));
		//create gap
		$newPosition = $resource['position']+$difference;
		$this->prepare("UPDATE {$this->db_prefix}shop_resources SET position=position+1 WHERE item_id=? and position>=? and flags&?=?")->execute(array($resource['item_id'], $newPosition, $flags, $flags));;
		$r = $this->prepare("UPDATE {$this->db_prefix}shop_resources SET position=? WHERE resource_id=?");
		$s = $r->execute(array($newPosition, $resourceId));
		$this->Recalculate_positions($resource['item_id'], ($resource['flags'] & (PC_shop_resources::RF_IS_CATEGORY)));
		if ($returnData) {
			if ($s) {
				return $this->Get_parsed(null, $resource['item_id'], ($resource['flags'] & (PC_shop_resources::RF_IS_CATEGORY)));
			}
		}
		return $s;
	}
	public function Recalculate_positions($itemId, $flags) {
		$r = $this->prepare("SELECT * FROM {$this->db_prefix}shop_resources WHERE item_id=? and flags&?=? ORDER BY position");
		$s = $r->execute(array($itemId, $flags, $flags));
		if (!$s) return false;
		$rUpdate = $this->prepare("UPDATE {$this->db_prefix}shop_resources SET position=? WHERE resource_id=?");
		$position = array('attachment'=>0, 'image'=>0);
		while ($d = $r->fetch()) {
			$identifier = (($d['flags'] & PC_shop_resources::RF_IS_ATTACHMENT)?'attachment':'image');
			$position[$identifier]++;
			$rUpdate->execute(array($position[$identifier], $d['resource_id']));
		}
		return true;
	}
}