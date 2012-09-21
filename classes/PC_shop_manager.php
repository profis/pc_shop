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
		$d['resources'] = $data['resources'];
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
				$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_category_contents (category_id,ln,".implode(',', array_keys($c)).") VALUES(?,?,".implode(',', array_fill(0, count($c), '?')).")");
				$s = $r->execute(array_merge(array($id, $ln), array_values($c)));
				if (!$s) {
					$params->errors->Add('content', 'Category contents in \''.$ln.'\' language was not added.');
				}
			}
		}
		//resources
		if (is_array($d['resources'])) $this->shop->resources->Update($id, PC_shop_resources::RF_IS_CATEGORY, $d['resources']);
		return $id;
	}
	public function Edit($categoryId, $data, &$params) {
		$this->core->Init_params($params);
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
		$updates = $qparams = array();
		foreach ($d['category'] as $field=>&$value) {
			$updates[] = $field.'=?';
			$qparams[] = $value;
		}
		unset($field, $value);
		$qparams[] = $categoryId;
		$r = $this->prepare("UPDATE {$this->db_prefix}shop_categories SET ".implode(',', array_values($updates))." WHERE id=?");
		$s = $r->execute($qparams);
		if (!$s) return !$params->errors->Add('update_category', '');
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
			$updates = $qparams = $updateFields = array();
			foreach ($cdata as $field=>&$value) {
				$updates[] = $field.'=?';
				$updateFields[] = $field;
				$qparams[] = $value;
			}
			unset($field, $value);
			array_push($qparams, $categoryId, $ln);
			
			if ($doUpdate) {
				$r = $this->prepare("UPDATE {$this->db_prefix}shop_category_contents SET ".implode(',', array_values($updates))." WHERE category_id=? and ln=?");
				$s = $r->execute($qparams);
				continue;
			}
			$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_category_contents (".implode(',', $updateFields).",category_id,ln) VALUES(".implode(',', array_fill(0, count($qparams), '?')).")");
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
		$r_category = $this->prepare("SELECT ".($params->Has_paging()?'SQL_CALC_FOUND_ROWS ':'')."* FROM {$this->db_prefix}shop_categories ".(!is_null($id)?'WHERE id'.(is_array($id)?' '.$this->sql_parser->in($id):'=? LIMIT 1'):(!is_null($parentId)?'WHERE parent_id=?'.(!is_null($pid)?' and pid=?':''):'').($params->Has_paging()?" LIMIT {$params->paging->Get_offset()},{$params->paging->Get_limit()}":'')));
		$r_contents = $this->prepare("SELECT * FROM {$this->db_prefix}shop_category_contents WHERE category_id=?");
		$queryParams = array();
		if (!is_null($id)) {
			if (is_array($id)) {
				$queryParams += $id;
			}
			else $queryParams[] = $id;
		}
		else if (!is_null($parentId)) {
			$queryParams[] = $parentId;
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
	public function Delete($id, &$params=array()) {
		$this->core->Init_params($params);
		if ($this->shop->products->Count($id)) {
			return !$params->errors->Add('products_inside', 'This category has products inside');
		}
		//main data
		$rCategory = $this->prepare("DELETE FROM {$this->db_prefix}shop_categories WHERE id=?");
		$s = $rCategory->execute(array($id));
		if (!$s) return !$params->errors->Add('delete_category', '');
		//contents
		$rContents = $this->prepare("DELETE FROM {$this->db_prefix}shop_category_contents WHERE category_id=?");
		$rContents->execute(array($id));
		//resources
		$this->shop->resources->Delete(null, $id, true);
		return true;
	}
}
class PC_shop_products_manager extends PC_shop_products {
	public function Create($categoryId, $position=0, $data, &$params=array()) {
		$this->core->Init_params($params);
		if (!$this->shop->categories->Exists($categoryId)) {
			$params->errors->Add('category', 'Category was not found');
		}
		$d = array();
		if (isset($data['contents'])) {
			foreach ($data['contents'] as $ln=>$c) {
				$d['contents'][$ln] = $this->db->fields->Parse('shop_product_contents', $c, $params);
			}
			unset($data['contents'], $ln, $c);
		}
		$this->Encode_flags($data);
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
				if ($position+1 > $maxPosition) $position = $maxPosition + 1;
			}
			else if ($position > 1) $position = 1;
		}
		$r = $this->prepare("UPDATE {$this->db_prefix}shop_products SET position=position+1 WHERE category_id=? and position>=?");
		$s = $r->execute(array($categoryId, $position));
		if (!$s) {
			$params->errors->Add('position', 'Error while pushing positions to the right');
			return false;
		}
		
		$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_products (category_id,".implode(',', array_keys($d['product'])).") VALUES(?,".implode(',', array_fill(0, count($d['product']), '?')).")");
		$s = $r->execute(array_merge(array($categoryId), array_values($d['product'])));
		if (!$s) {
			$params->errors->Add('create', 'Error while trying to insert product into database.');
			return false;
		}
		$id = $this->db->lastInsertId($this->sql_parser->Get_sequence('shop_products'));
		
		if (isset($d['contents'])) foreach ($d['contents'] as $ln=>$c) {
			$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_product_contents (product_id,ln,".implode(',', array_keys($c)).") VALUES(?,?,".implode(',', array_fill(0, count($c), '?')).")");
			$s = $r->execute(array_merge(array($id, $ln), array_values($c)));
			if (!$s) {
				$params->errors->Add('content', 'Product contents in \''.$ln.'\' language was not added.');
			}
		}
		return $id;
	}
	public function Edit($productId, $data, &$params) {
		$this->core->Init_params($params);
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
		$d['product'] = $this->db->fields->Parse('shop_products', $data, $params);
		if ($params->errors->Count()) return false;
		//main data
		$updates = $qparams = array();
		foreach ($d['product'] as $field=>&$value) {
			$updates[] = $field.'=?';
			$qparams[] = $value;
		}
		unset($field, $value);
		$qparams[] = $productId;
		$r = $this->prepare("UPDATE {$this->db_prefix}shop_products SET ".implode(',', array_values($updates))." WHERE id=?");
		$s = $r->execute($qparams);
		//print_pre($d);
		if (!$s) return !$params->errors->Add('update_product', '');
		//contents
		if (isset($d['contents']))  foreach ($d['contents'] as $ln=>$cdata) {
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
		$r_product = $this->prepare("SELECT ".($params->Has_paging()?'SQL_CALC_FOUND_ROWS ':'')."* FROM {$this->db_prefix}shop_products ".(!is_null($id)?'WHERE id'.(is_array($id)?' '.$this->sql_parser->in($id):'=? LIMIT 1'):(!is_null($categoryId)?'WHERE category_id=?':'').($params->Has_paging()?" LIMIT {$params->paging->Get_offset()},{$params->paging->Get_limit()}":'')));
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
			$d['contents'] = array();
			$s = $r_contents->execute(array($d['id']));
			if (!$s) return false;
			while ($c = $r_contents->fetch()) {
				$d['contents'][$c['ln']] = $c;
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
		$r = $this->prepare("INSERT IGNORE INTO {$this->db_prefix}shop_resources (item_id,file_id,flags) VALUES(?,?,?)");
		$s = $r->execute(array($itemId, $fileId, $flagsCheck));
		if ($s) return $this->db->lastInsertId($this->sql_parser->Get_sequence('shop_resources'));
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
			$this->Add($itemId, $res['id'], false, (bool)$res['is_attachment'], $flags);
		}
		//delete resources
		if (isset($data['remove'])) if (count($data['remove']))
		foreach ($data['add'] as $res) {
			$this->Add($itemId, $res['id'], false, (bool)$res['is_attachment'], $flags);
		}
	}
}