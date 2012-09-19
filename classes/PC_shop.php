<?php
abstract class PC_shop extends PC_base {
	public $categories, $products, $resources;
	
	/**
	 *
	 * @var PC_shop_attributes 
	 */
	public $attributes;
	
	/**
	 *
	 * @var PC_shop_cart 
	 */
	public $cart;
	
	/**
	 *
	 * @var PC_shop_orders 
	 */
	public $orders;
	
	final public function Init($admin=false) {
		if ($admin || is_a($this, 'PC_shop_manager')) $clsSuffix = 'manager';
		else $clsSuffix = 'site';
		$this->categories = $this->core->Get_object('PC_shop_categories_'.$clsSuffix, array($this));
		$this->products = $this->core->Get_object('PC_shop_products_'.$clsSuffix, array($this));
		$this->resources = $this->core->Get_object('PC_shop_resources'.($clsSuffix=='manager'?"_".$clsSuffix:""), array($this));
		$this->attributes = $this->core->Get_object('PC_shop_attributes', array($this));
		$this->cart = $this->core->Get_object('PC_shop_cart', array($this));
		$this->orders = $this->core->Get_object('PC_shop_orders', array($this));
		//register database fields
		$fields = array();
		$fields['categories'] = array('flags', 'discount', 'percentage_discount', 'external_id');
		$fields['category_contents'] = array('name', 'description', 'seo_title', 'seo_description', 'seo_keywords', 'route');
		$fields['products'] = array('manufacturer_id', 'mpn', 'quantity', 'flags', 'warranty', 'discount', 'percentage_discount', 'price', 'external_id');
		$fields['product_contents'] = array('name', 'short_description', 'description', 'seo_title', 'seo_description', 'seo_keywords', 'route');
		foreach ($fields as $table=>&$cols) {
			foreach ($cols as $col) {
				$this->db->fields->Register('shop_'.$table, $col, array(
					'validator'=> array(
						'callback'=> array($this, 'Validate_field'),
						'args'=> array($table, $col)
					)
				));
			}
		}
	}
	public function Validate_field(&$value, $table, $col) {
		switch ($col) {
			case 'flags': return is_numeric($value); // return (Validate('boolean', $value));
			case 'discount': case 'percentage_discount':
				if ($value == '') {
					$value = null;
					return true;
				}
				$s = preg_match("#^[0-9]+(\.[0-9]+)?$#", $value);
				if (!$s) return false;
				return true;
			case 'ln': return $this->site->Language_exists($value);
			case 'name': 
				if (empty($value)) return true;
				return Validate('name', $value, true, array('length'=>array('from'=> 1, 'to'=> 255)));
			case 'description': return true;
			case 'seo_title': return true;
			case 'seo_description': return true;
			case 'seo_keywords': return true;
			case 'manufacturer_id': return true;
			case 'mpn': return true;
			case 'quantity': return true;
			case 'warranty': return true;
			case 'price': return true;
			case 'short_description': return true;
			case 'external_id':
				if (empty($value)) $value = null;
				return true;
			case 'route': 
				//$value = Sanitize('route', $value);
				return true;
			default: return false;
		}
		return false;
	}
	public function Get_variable($key) {
		return $this->core->Get_variable('pc_shop_'.$key);
	}
}
class PC_shop_categories extends PC_base {
	protected $shop;
	const CF_DEFAULT = 0x1, CF_PUBLISHED = 0x1, CF_HOT = 0x2, CF_NOMENU = 0x4, CF_ROUTE_LOCK = 0x8;
	private $flagsMap = array(
		'hot'=> self::CF_HOT,
		'nomenu'=> self::CF_NOMENU,
		'published'=> self::CF_PUBLISHED,
		'route_lock'=> self::CF_ROUTE_LOCK
	);
	public function Init(PC_shop $shop) {
		$this->shop = $shop;
	}
	public function Exists($id) {
		$r = $this->prepare("SELECT id FROM {$this->db_prefix}shop_categories WHERE id=? LIMIT 1");
		$s = $r->execute(array($id));
		if (!$s) return false;
		return (bool)$r->rowCount();
	}
	public function Is_published($id) {
		$r = $this->prepare("SELECT flags FROM {$this->db_prefix}shop_categories WHERE id=? LIMIT 1");
		$s = $r->execute(array($id));
		if (!$s) return false;
		$flags = $r->fetchColumn();
		return ($flags & self::CF_PUBLISHED > 0);
	}
	public function Encode_flags(&$data, $createMode=true) {
		if (!isset($data['flags'])) {
			if ($createMode) $data['flags'] = self::CF_DEFAULT;
		}
		foreach ($this->flagsMap as $field=>$flag) {
			if (isset($data[$field])) {
				if ((bool)$data[$field]) $data['flags'] |= $flag; //activate
				else $data['flags'] &= ~$flag;
				unset($data[$field]);
			}
		}
		return true;
	}
	public function Decode_flags(&$data) {
		if (!isset($data['flags'])) return false;
		foreach ($this->flagsMap as $field => $flag) {
			if (($data['flags'] & $flag) != 0) $data[$field] = true;
			else $data[$field] = false;
		}
		return true;
	}
}
class PC_shop_products extends PC_base {
	protected $shop;
	const PF_DEFAULT = 0x1, PF_PUBLISHED = 0x1,
	PF_IS_PRODUCT_GROUP = 0x2, PF_PARENT_IS_PRODUCT = 0x4, PF_HOT = 0x8, PF_NOMENU = 0x16, PF_ROUTE_LOCK = 0x32;
	private $flagsMap = array(
		'hot'=> self::PF_HOT,
		'nomenu'=> self::PF_NOMENU,
		'published'=> self::PF_PUBLISHED,
		'is_product_group'=> self::PF_IS_PRODUCT_GROUP,
		'parent_is_product'=> self::PF_PARENT_IS_PRODUCT,
		'route_lock'=> self::PF_ROUTE_LOCK
	);
	public function Init(PC_shop $shop) {
		$this->shop = $shop;
	}
	public function Exists($id) {
		$r = $this->prepare("SELECT id FROM {$this->db_prefix}shop_products WHERE id=? LIMIT 1");
		$s = $r->execute(array($id));
		if (!$s) return false;
		return (bool)$r->rowCount();
	}
	public function Is_published($id) {
		$r = $this->prepare("SELECT flags FROM {$this->db_prefix}shop_products WHERE id=? LIMIT 1");
		$s = $r->execute(array($id));
		if (!$s) return false;
		$flags = $r->fetchColumn();
		return ($flags & self::PF_PUBLISHED > 0);
	}
	public function Count($categoryId=null) {
		$r = $this->prepare("SELECT count(*) FROM {$this->db_prefix}shop_products".(!is_null($categoryId)?" WHERE category_id=?":""));
		$params = array();
		if (!is_null($categoryId)) $params[] = $categoryId;
		$s = $r->execute($params);
		if (!$s) return false;
		return (int)$r->fetchColumn();
	}
	public function Encode_flags(&$data, $createMode=true) {
		if (!isset($data['flags'])) {
			if ($createMode) $data['flags'] = self::PF_DEFAULT;
			//else $data['flags'] = self::PF_UPDATE;
		}
		foreach ($this->flagsMap as $field=>$flag) {
			if (isset($data[$field])) {
				if ((bool)$data[$field]) $data['flags'] |= $flag; //activate
				else $data['flags'] &= ~$flag;
				unset($data[$field]);
			}
		}
		return true;
	}
	public function Decode_flags(&$data) {
		if (!isset($data['flags'])) return false;
		foreach ($this->flagsMap as $field => $flag) {
			if( ($data['flags'] & $flag) != 0) $data[$field] = true;
			else $data[$field] = false;
		}
		return true;
	}
	public function Get_price($id) {
		if (!is_array($id)) {
			
		}
	}
}
class PC_shop_resources extends PC_base {
	public $rawList, $list;
	protected $shop;
	const RF_IS_CATEGORY = 0x1, RF_IS_ATTACHMENT = 0x2, RF_AL_PUBLIC = 0x4, RF_AL_USER = 0xC, RF_AL_ADMIN = 0x1C;
	/* AL = access level; priejimo lygiai 3: public, users, private */
	public function Init(PC_shop $shop) {
		$this->shop = $shop;
	}
	public function Get($id=null, $itemId=null, $flags=self::RF_AL_PUBLIC) {
		if (!is_array($flags)) $flags = array($flags);
		$flagsCheck = 0x0;
		if (count($flags)) foreach ($flags as $flag) {
			$flagsCheck |= $flag;
		}
		$r = $this->prepare($qry = "SELECT * FROM {$this->db_prefix}shop_resources WHERE flags&{$flagsCheck}={$flagsCheck}".(!is_null($id)?" AND resource_id=? LIMIT 1":!is_null($itemId)?" and item_id=?":""));
		$queryParams = array();
		if (!is_null($id)) $queryParams[] = $id;
		elseif (!is_null($itemId)) $queryParams[] = $itemId;
		$s = $r->execute($queryParams);
		if (!$s) return false;
		
		$list = array();
		while ($d = $r->fetch()) {
			$this->Decode_flags($d);
			$list[] = $d;
		}
		return (!is_null($id)&&count($list)?$list[0]:$list);
	}
	public function Get_parsed($id=null, $itemId=null, $flags=null/*self::RF_AL_PUBLIC*/) {
		$r = $this->Get($id, $itemId, $flags);
		if (!is_array($r)) return false;
		$list = array();
		foreach ($r as &$f) {
			$galleryItem = $this->gallery->Get_file_by_id($f['file_id']);
			$f = array_merge($f, $galleryItem['filedata']);
			$f['filetype'] = $this->gallery->filetypes[$f['extension']];
			$f['size'] = ($f['size']<307200?number_format($f['size'] /1024).' KB':number_format($f['size'] /1024/1024, 2).' MB');
			//$f['in_use'] = ($f['in_use']>0);
			//$f['modified'] = date('Y-m-d H:i', $f['date_modified']);
			$list[] = array($f['resource_id'], $f['item_id'], $f['file_id'], $f['is_category'], $f['is_attachment'], $f['size'], $f['extension'], $f['filename'], $f['path'], $f['filetype']);
		}
		return $list;
	}
	public function GetOld($isAttachment=false, $type=null, $access=null/*self::RF_AL_PUBLIC*/) {
		//...
		$item = array(
			'name'=> $name,
			'value'=> $value, //link
			'access'=> self::RF_AL_PUBLIC
		);
		if ($isAttachment) $item['type'] = $this->core->Get_file_type($value);
		$items[] = $item;
		return $items;
	}
	public static function Decode_flags(&$data) {
		if (!isset($data['flags'])) return false;
		$data['is_category'] = (($data['flags'] & self::RF_IS_CATEGORY) != 0);
		$data['is_attachment'] = (($data['flags'] & self::RF_IS_ATTACHMENT) != 0);
		$data['access'] = 'public';
		if (($data['flags'] & self::RF_AL_PUBLIC) != 0) $data['access'] = 'user';
		return true;
	}
	public static function Encode_flags(&$data) {
		$flags = 0x0; 
		switch ($data['access']) {
			case 'user': $flags |= self::RF_AL_USER; break;
			case 'admin': $flags |= self::RF_AL_ADMIN; break;
			default: $flags |= self::RF_AL_PUBLIC;
		}
	}
}
class PC_shop_item_resources extends PC_base {
	protected $id, $isCategory;
	public function Init($itemId, $isCategory=false, $checkIfExists=false) {
		if ($checkIfExists) {
			$r = $this->prepare("SELECT id FROM {$this->db_prefix}shop_".$this->Get_table($isCategory)." WHERE id=?");
			$s = $r->execute(array($itemId));
			if (!$s) return false;
			if (!$r->rowCount()) return false;
		}
		$this->id = (int)$itemId;
		$this->isCategory = (bool)$isCategory;
	}
	public function Get_id() {
		return $this->id;
	}
	public function Is_category() {
		return $this->isCategory;
	}
	public function Get_table($isCategory=null) {
		if (is_null($isCategory)) $isCategory = $this->isCategory;
		return ($isCategory?'categories':'products');
	}
	public function Get($isAttachment=false, $cache=true) {
		//flags
		$flags = PC_shop_resources::RF_AL_PUBLIC;
		if ($this->Is_category()) $flags |= PC_shop_resources::RF_IS_CATEGORY;
		//check cache
		$cachePath = array('pc_shop', 'itemResources', $this->itemId);
		if ($cache) {
			$cached =& $this->cache->Get($cachePath);
			if ($cached) return $cached;
		}
		//get
		$r = $this->prepare("SELECT * FROM {$this->db_prefix}shop_resources WHERE id=? and flags&?=?");
		//kaip tikrinti flaga jei reikia paziureti itemId kuris priklauso produktui? Papildomas flagas?
		$s = $r->execute(array($this->itemId, $flags, $flags));
		if (!$s) return false;
		$list = array();
		while ($d = $r->fetch()) {
			$this->Decode_flags($d);
			$list[] = $d;
		}
		//save in cache and return
		return $this->cache->Cache($cachePath, $list);
	}
}
class PC_shop_attributes extends PC_base {
	const ITEM_IS_CATEGORY = 0x1, ITEM_IS_PRODUCT = 0x2;
	//related_to_category
	/**
	 * attributes
	 * @param type $id
	 * @param type $params
	 * @return boolean
	 */
	public function Get($id=null, &$params=array()) { //main
		$this->core->Init_params($params);
		$where = $queryParams = array();
		$returnOne = false;
		//paging
		if (!is_null($id)) {
			if (is_array($id)) {
				$where[] = 'a.id '.$this->sql_parser->in($id);
				$queryParams = array_merge($queryParams, $id);
			}
			else {
				$returnOne = true;
				$queryParams[] = $id;
				$where[] = 'a.id=?';
				$limit = ' LIMIT 1';
			}
		}
		elseif ($params->Has_paging()) {
			$limit = " LIMIT {$params->paging->Get_offset()},{$params->paging->Get_limit()}";
		}
		if (isset($params->filter)) if (is_array($params->filter)) if (count($params->filter)) {
			foreach ($params->filter as $field=>$value) {
				$where[] = $field.'=?';
				$queryParams[] = $value;
			}
		}
		$r = $this->prepare("SELECT ".($params->Has_paging()?'SQL_CALC_FOUND_ROWS ':'')."a.*,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'c.ln', 'c.name'), array('separator'=>'▓', 'distinct'=> true))." names"
		.($params->Get('includeValues')?
			','.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'avc.value_id', 'avc.ln', 'avc.value'), array('separator'=>'▓'))." attrValues"
		:"")
		." FROM {$this->db_prefix}shop_attributes a"
		." LEFT JOIN {$this->db_prefix}shop_attribute_contents c ON c.attribute_id=a.id"
		.($params->Get('includeValues')?
			//join attribute values
			" LEFT JOIN {$this->db_prefix}shop_attribute_values av ON av.attribute_id=a.id"
			." LEFT JOIN {$this->db_prefix}shop_attribute_value_contents avc ON avc.value_id=av.id"
		:"")
		.(count($where)?' WHERE '.implode(' and ', $where):'')." GROUP BY a.id".v($limit ,''));
		$s = $r->execute($queryParams);
		if (!$s) return !$params->errors->Add('database', '');
		if ($params->Has_paging()) {
			$rTotal = $this->query("SELECT FOUND_ROWS()");
			if ($rTotal) $params->paging->Set_total($rTotal->fetchColumn());
		}
		$list = array();
		//print_pre($r);
		//print_pre($r->fetchAll());
		//exit;
		while ($d = $r->fetch()) {
			if (is_null($d['id'])) continue;
			$this->core->Parse_data_str($d['names'], '▓', '░');
			if ($params->Get('includeValues')) {
				$tmp = explode('▓', $d['attrValues']);
				unset($d['attrValues']);
				$d['values'] = array();
				if (count($tmp) && strpos($tmp[0], '░')) {
					for ($a=0; isset($tmp[$a]); $a++) {
						$temp = explode('░', $tmp[$a]);
						$d['values'][$temp[0]][$temp[1]] = $temp[2];
					}
				}
				unset($tmp);
			}
			$list[] = $d;
		}
		if ($returnOne) {
			if (!count($list)) return false;
			return $list[0];
		}
		else return $list;
	}
	
	/**
	 * 
	 * @param type $id
	 * @param type $data
	 * @param type $params
	 * @return boolean
	 */
	public function Edit($id, $data, &$params) {
		$this->core->Init_params($params);
		$set = $queryParams = array();
		if (isset($data['is_category_attribute'])) {
			$set[] = 'is_category_attribute=?';
			$queryParams[] = $data['is_category_attribute'];
		}
		if (isset($data['is_searchable'])) {
			$set[] = 'is_searchable=?';
			$queryParams[] = $data['is_searchable'];
		}
		if (isset($data['is_custom'])) {
			$set[] = 'is_custom=?';
			$queryParams[] = $data['is_custom'];
		}
		//main attribute data
		if (count($set)) {
			$queryParams[] = $id;
			$r = $this->prepare("UPDATE {$this->db_prefix}shop_attributes SET ".implode(',', $set)." WHERE id=?");
			$s = $r->execute($queryParams);
			if (!$s) return !$params->errors->Add('database', '');
		}
		//save names
		if (is_array($data['names'])) if (count($data['names'])) {
			$rContents = $this->prepare("UPDATE {$this->db_prefix}shop_attribute_contents SET name=? WHERE attribute_id=? and ln=?");
			foreach ($data['names'] as $ln=>$name) {
				$rContents->execute(array($name, $id, $ln));
			}
		}
		return true;
	}
	
	/**
	 * 
	 * @param type $names
	 * @return type
	 */
	public function Create($isCategoryAttribute=false, $names=array(), &$params=array()) { //manager
		$this->core->Init_params($params);
		//create empty attribute
		$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_attributes (id,is_category_attribute) VALUES (null,?)");
		$s = $r->execute(array($isCategoryAttribute));
		if (!$s) return !$params->errors->Add('database', '');
		$attributeId = $this->db->lastInsertId($this->sql_parser->Get_sequence('shop_attributes'));
		//insert contents
		$vals = $queryParams = array();
		if (count($names)) foreach ($names as $ln=>$name) {
			$vals[] = '('.$attributeId.',?,?)';
			$queryParams[] = $ln;
			$queryParams[] = $name;
		}
		$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_attribute_contents (attribute_id,ln,name) VALUES ".implode(',', $vals));
		$s = $r->execute($queryParams);
		return $attributeId;
	}
	
	/**
	 * 
	 * @param type $id
	 * @return boolean
	 */
	public function Delete($id) { //manager
		$r = $this->prepare("DELETE FROM {$this->db_prefix}shop_attributes WHERE id=?");
		$s = $r->execute(array($id));
		if (!$s) return false;
		$r = $this->prepare("DELETE FROM {$this->db_prefix}shop_attribute_contents WHERE attribute_id=?");
		$r->execute(array($id));
		$r = $this->prepare("SELECT id FROM {$this->db_prefix}shop_attribute_values WHERE attribute_id=?");
		$s = $r->execute(array($id));
		if ($s) {
			$valueIds = array();
			while ($d = $r->fetchColumn()) {
				$valueIds[] = $d;
			}
			$this->DeleteValue($valueIds);
		}
		return true;
	}
	
	/**
	 * 
	 * @param type $id
	 * @return boolean
	 */
	public function DeleteValue($id) {
		$queryParams = array();
		if (!is_array($id)) $queryParams[] = $id;
		else $queryParams = array_merge($queryParams, $id);
		$r = $this->prepare("DELETE FROM {$this->db_prefix}shop_attribute_values WHERE ".(is_array($id)?'id '.$this->sql_parser->in($id):'id=?'));
		$s = $r->execute($queryParams);
		if (!$s) return false;
		//delete value contents
		$this->prepare("DELETE FROM {$this->db_prefix}shop_attribute_value_contents"
		." WHERE ".(is_array($id)?'value_id '.$this->sql_parser->in($id):'value_id=?'))
		->execute($queryParams);
		return true;
	}
	/**
	 * attribute values
	 * @param type $attributeId
	 * @param type $params
	 * @return boolean
	 */
	public function Get_values($attributeId, &$params=array()) {
		$this->core->Init_params($params);
		$where = $queryParams = array();
		$returnOne = false;
		$limit = '';
		//paging
		if (!is_null($attributeId)) {
			if (is_array($attributeId)) {
				$where[] = 'attribute_id '.$this->sql_parser->in($attributeId);
				$queryParams = array_merge($queryParams, $attributeId);
			}
			else {
				$queryParams[] = $attributeId;
				$where[] = 'attribute_id=?';
			}
		}
		elseif ($params->Has_paging()) {
			$limit = " LIMIT {$params->paging->Get_offset()},{$params->paging->Get_limit()}";
		}
		if (isset($params->filter)) if (is_array($params->filter)) if (count($params->filter)) {
			foreach ($params->filter as $field=>$value) {
				$where[] = $field.'=?';
				$queryParams[] = $value;
			}
		}
		$r = $this->prepare("SELECT ".($params->Has_paging()?'SQL_CALC_FOUND_ROWS ':'')."v.*,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'ln', 'value'), array('separator'=>'▓'))." names"
		." FROM {$this->db_prefix}shop_attribute_values v"
		." LEFT JOIN {$this->db_prefix}shop_attribute_value_contents c ON c.value_id=v.id"
		.(count($where)?' WHERE '.implode(' and ', $where):'')." GROUP BY v.id".$limit);
		$s = $r->execute($queryParams);
		if (!$s) return !$params->errors->Add('database', '');
		if ($params->Has_paging()) {
			$rTotal = $this->query("SELECT FOUND_ROWS()");
			if ($rTotal) $params->paging->Set_total($rTotal->fetchColumn());
		}
		$list = array();
		while ($d = $r->fetch()) {
			if (is_null($d['id'])) continue;
			$this->core->Parse_data_str($d['names'], '▓', '░');
			$list[] = $d;
		}
		if ($returnOne) {
			if (!count($list)) return false;
			return $list[0];
		}
		else return $list;
	}
	
	/**
	 * 
	 * @param type $attributeId
	 * @param type $values
	 * @param type $params
	 * @return type
	 */
	public function Assign_value($attributeId, $values=array(), &$params=array()) {
		$this->core->Init_params($params);
		$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_attribute_values (attribute_id) VALUES(?)");
		$s = $r->execute(array($attributeId));
		if (!$s) return !$params->errors->Add('database', '');
		$id = $this->db->lastInsertId($this->sql_parser->Get_sequence('shop_attribute_values'));
		if (is_array($values)) if (count($values)) {
			$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_attribute_value_contents (value_id,ln,value) VALUES(?,?,?)");
			foreach ($values as $ln=>$value) {
				$r->execute(array($id, $ln, $value));
			}
		}
		return $id;
	}
	
	/**
	 * 
	 * @param type $id
	 * @param type $values
	 * @return boolean
	 */
	public function Edit_value($id, $values) {
		$r = $this->prepare("SELECT id FROM {$this->db_prefix}shop_attribute_values WHERE id=?");
		$s = $r->execute(array($id));
		if (!$s) return false;
		if (!$r->rowCount()) return false;
		if (is_array($values)) if (count($values)) {
			$rExists = $this->prepare("SELECT value_id FROM {$this->db_prefix}shop_attribute_value_contents WHERE value_id=? and ln=?");
			$rInsert = $this->prepare("INSERT INTO {$this->db_prefix}shop_attribute_value_contents (value_id,ln,value) VALUES(?,?,?)");
			$rUpdate = $this->prepare("UPDATE {$this->db_prefix}shop_attribute_value_contents SET value=? WHERE value_id=? and ln=?");
			foreach ($values as $ln=>$value) {
				$s = $rExists->execute(array($id, $ln));
				if ($s) {
					if ($rExists->rowCount()) {
						$rUpdate->execute(array($value, $id, $ln));
					}
					else $rInsert->execute(array($id, $ln, $value));
				}
			}
		}
		return true;
	}
	
	/* Item attributes */
	public function Get_for_item($itemId, $itemType=self::ITEM_IS_PRODUCT, &$params) {
		if (is_null($itemType)) $itemType = self::ITEM_IS_PRODUCT;
		$this->core->Init_params($params);
		$flags = $itemType;
		$queryParams = array($itemId, $flags, $flags);
		$r = $this->prepare("SELECT * FROM {$this->db_prefix}shop_item_attributes WHERE item_id=? AND (flags&?)=?");
		$s = $r->execute($queryParams);
		if (!$s) return false;
		$list = array();
		while ($d = $r->fetch()) $list[] = $d;
		return $list;
	}
	public function Assign_to_item($itemId, $itemType=self::ITEM_IS_PRODUCT, $attributeId, $valueId=null, $value=null) {
		$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_item_attributes (item_id,attribute_id,flags,value_id,value) VALUES(?,?,?,?,?)");
		$s = $r->execute(array($itemId, $attributeId, $itemType, $valueId, $value));
		if (!$s) return false;
		return $this->db->lastInsertId($this->sql_parser->Get_sequence('shop_item_attributes'));
	}
	public function Edit_for_item($id, $valueId=null, $value=null) {
		$r = $this->prepare("UPDATE {$this->db_prefix}shop_item_attributes SET value_id=?, value=? WHERE id=?");
		$s = $r->execute(array($valueId, $value, $id));
		return $s;
	}
	public function Remove_from_item($id=null, $itemId=null, $itemType=self::ITEM_IS_PRODUCT) {
		$queryParams = $where = array();
		if (!is_null($id)) {
			if (is_array($id)) {
				$where[] = 'id '.$this->sql_parser->in($id);
				$queryParams = array_merge($queryParams, $id);
			}
			else {
				$where[] = 'id=?';
				$queryParams[] = $id;
			}
		}
		else if (!is_null($itemId)) {
			$where[] = 'item_id=? and flags&?=?';
			array_push($queryParams, $itemId, $itemType, $itemType);
		}
		if (!count($where)) return false;
		$r = $this->prepare("DELETE FROM {$this->db_prefix}shop_item_attributes WHERE ".implode(' and ', $where));
		$s = $r->execute($queryParams);
		return $s;
	}
	public function Save_for_item($itemId, $itemType=self::ITEM_IS_PRODUCT, $data) {
		if (!is_array($data)) return false;
		if (count(v($data['save'], array()))) foreach ($data['save'] as $i) {
			if ($i['id'] == 0) {
				$this->Assign_to_item($itemId, $itemType, $i['attribute_id'], $i['value_id'], $i['value']);
			}
			else {
				$this->Edit_for_item($i['id'], $i['value_id'], $i['value']);
			}
		}
		if (count(v($data['remove'], array()))) foreach ($data['remove'] as $id) {
			$this->Remove_from_item($id);
		}
		return true;
	}
	public function Get_suggestions($id) {
		$r = $this->prepare("SELECT value FROM {$this->db_prefix}shop_item_attributes WHERE attribute_id=?");
		$s = $r->execute(array($id));
		if (!$s) return false;
		$list = array();
		while ($d = $r->fetchColumn()) {
			if (is_null($d)) continue;
			if (in_array($d, $list)) continue;
			$list[] = $d;
		}
		return $list;
	}
}
class PC_shop_orders extends PC_base {
	public function Init(PC_shop $shop) {
		$this->shop = $shop;
		$this->user = $this->core->Get_object('PC_user');
	}
	public function Get($id=null, $params=array()) {
		$this->core->Init_params($params);
		$queryParams = array();
		$where = array();
		$limit = '';
		$returnOne = false;
		if (!is_null($id)) {
			if (is_array($id)) {
				$where[] = 'p.id '.$this->sql_parser->in($id);
				$queryParams = array_merge($queryParams, $id);
			}
			else {
				$returnOne = true;
				$queryParams[] = $id;
				$where[] = 'p.id=?';
				$limit = ' LIMIT 1';
			}
		}
		elseif ($params->Has_paging()) {
			$limit = " LIMIT {$params->paging->Get_offset()},{$params->paging->Get_limit()}";
		}
		if (isset($params->filter)) if (is_array($params->filter)) if (count($params->filter)) {
			foreach ($params->filter as $field=>$value) {
				$where[] = $field.'=?';
				$queryParams[] = $value;
			}
		}
		$r = $this->prepare("SELECT "
		.($params->Has_paging()?'SQL_CALC_FOUND_ROWS ':'')."* FROM {$this->db_prefix}shop_orders"
		.(count($where)?' WHERE '.implode(' and ', $where):'').$limit);
		$s = $r->execute($queryParams);
		if (!$s) return false;
		if ($params->Has_paging()) {
			$rTotal = $this->query("SELECT FOUND_ROWS()");
			if ($rTotal) $params->paging->Set_total($rTotal->fetchColumn());
		}
		$list = array();
		while ($d = $r->fetch()) {
			$d['items'] = $this->Get_items($d['id']);
			$list[] = $d;
		}
		if ($returnOne) {
			if (!count($list)) return false;
			return $list[0];
		}
		else return $list;
	}
	public function Get_items($orderId) {
		$r = $this->prepare("SELECT * FROM {$this->db_prefix}shop_order_items WHERE order_id=?");
		$s = $r->execute(array($orderId));
		if (!$s) return false;
		if (!$r->rowCount()) return array();
		$list = array();
		while ($product = $r->fetch()) {
			$list[$product['product_id']] = array(
				'id'=> $product['product_id'],
				'quantity'=> $product['quantity'],
				'price'=> $product['price']
			);
		}
		unset($product);
		//fill product data
		$products = $this->shop->products->Get(array_keys($list));
		foreach ($products as &$product) {
			$list[$product['id']]['name'] = $product['contents'][$this->site->ln]['name'];
			$list[$product['id']]['short_description'] = $product['contents'][$this->site->ln]['short_description'];
		}
		return array_values($list);
	}
	public function Assign_status($orderId, $status, $userId=null) {
		if (is_null($userId)) if ($this->user->Is_logged_in()) {
			$userId = $this->user->GetID();
		}
		$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_order_statuses (order_id,status,date,user_id) VALUES(?,?,?,?)");
		$s = $r->execute(array($orderId, $status, time(), $userId));
		return (bool)$s;
	}
	public function Delete_status($id=null, $orderId=null, $status=null) {
		if (is_null($id)) {
			if (is_null($orderId)) return false;
			if (!is_null($status)) {
				$r = $this->prepare("DELETE FROM {$this->db_prefix}shop_order_statuses WHERE order_id=? and status=?");
				$s = $r->execute(array($orderId, $status));
				return $s;
			}
			$r = $this->prepare("DELETE FROM {$this->db_prefix}shop_order_statuses WHERE order_id=?");
			$s = $r->execute(array($orderId));
			return $s;
		}
		$r = $this->prepare("DELETE FROM {$this->db_prefix}shop_order_statuses WHERE id=?");
		$s = $r->execute(array($id));
		return $s;
	}
	public function Get_statuses($orderId) {
		$r = $this->prepare("SELECT * FROM {$this->db_prefix}shop_order_statuses WHERE order_id=?");
		$s = $r->execute(array($orderId));
		if (!$s) return false;
		return $r->fetchAll();
	}
	public function Confirm($id, $userId=null) {
		return $this->Assign_status($id, 'confirmed', $userId);
	}
	public function Unconfirm($id) {
		return $this->Delete_status(null, $id, 'confirmed');
	}
	public function Delete($id) {
		$rOrder = $this->prepare("DELETE FROM {$this->db_prefix}shop_orders WHERE id=?");
		$s = $rOrder->execute(array($id));
		if (!$s) return false;
		$rItems = $this->prepare("DELETE FROM {$this->db_prefix}shop_order_items WHERE order_id=?");
		$rItems->execute(array($id));
		$rStatuses = $this->prepare("DELETE FROM {$this->db_prefix}shop_order_statuses WHERE order_id=?");
		$rStatuses->execute(array($id));
		return true;
	}
	public function Create($userId=null, $recipient, $address, $phone, $email, $comment=null, &$params=array(), $clearCart=true) {
		$this->core->Init_params($params);
		if (!isset($_SESSION['pc_shop']['cart'])) {
			$params->errors->Add('empty_cart', 'The cart is empty');
			return false;
		}
		if (!count($_SESSION['pc_shop']['cart'])) {
			$params->errors->Add('empty_cart', 'The cart is empty');
			return false;
		}
		$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_orders (date,user_id,name,email,address,phone,comment) VALUES(?,?,?,?,?,?,?)");
		$s = $r->execute(array(time(), $userId, $recipient, $email, $address, $phone, $comment));
		if (!$s) {
			$params->errors->Add('insert_into_database', 'Error while trying to insert record into the database');
			return false;
		}
		$orderId = $this->db->lastInsertId($this->sql_parser->Get_sequence('shop_orders'));
		$rInsertItem = $this->prepare("INSERT INTO {$this->db_prefix}shop_order_items (order_id,product_id,quantity,price) VALUES(?,?,?,?)");
		$totalPrice = 0;
		foreach ($_SESSION['pc_shop']['cart'] as $productId=>$quantity) {
			$product = $this->shop->products->Get($productId);
			if (!$product) {
				$params->errors->Add('unknown_product', 'Unknown product in the cart');
			}
			else {
				$price = $product['price'];
				$totalPrice += $price * $quantity;
				$s = $rInsertItem->execute(array($orderId, $productId, $quantity, $price));
				if ($s) continue;
				else $params->errors->Add('insert_order_item', '');
			}
			return false;
		}
		//set total price
		$rPrice = $this->prepare("UPDATE {$this->db_prefix}shop_orders SET total_price=? WHERE id=?");
		$s = $rPrice->execute(array($totalPrice));
		if ($clearCart) $this->shop->cart->Clear();
		$this->Assign_status($orderId, 'created');
		return true;
	}
	//order statuses: isPaid, isConfirmed, isSent, 
}
class PC_shop_cart extends PC_base {
	private $shop;
	public function Init(PC_shop $shop) {
		$this->shop = $shop;
		if (!isset($_SESSION['pc_shop']) || !is_array($_SESSION['pc_shop']))
			$_SESSION['pc_shop'] = Array();
		if (!isset($_SESSION['pc_shop']['cart']) || !is_array($_SESSION['pc_shop']['cart']))
			$this->Clear();
	}
	
	/**
	 * 
	 * @param boolean $raw
	 * @return type
	 */
	public function Get($raw=false) {
		if ($raw) {
			return $_SESSION['pc_shop']['cart'];
		}

		$d = array(
			'total'=> 0,
			'totalQuantity'=> $_SESSION['pc_shop']['cart']['totalQuantity'],
			'items'=> array(),
			'totalPrice'=> 0
		);
		
		$productList = $this->shop->products->Get(array_keys($_SESSION['pc_shop']['cart']["productIndex"]));
		$products = Array();
		foreach( $productList as &$p )
			$products[$p["id"]] = &$p;
		
		foreach ($_SESSION['pc_shop']['cart']['items'] as $ciid => $cartItemInfo) {
			$d['total']++;
			$p = &$products[$cartItemInfo[0]];
			$d['items'][$ciid] = &$p;
			// $d['totalPrice'] += $p['totalPrice'] = $shop->products->Get_price($cartItemInfo[0], $cartItemInfo[1]);
			$d['totalPrice'] += $p['totalPrice'] = $p['price'] * ($p["quantity"] = $cartItemInfo[1]);
		}
		return $d;
	}
	/** Finds a product entry CIID by productId and attributes */
	public function Find($productId, $attributes=null) {
		if( empty($_SESSION['pc_shop']['cart']['productIndex'][$productId]) )
			return null;

		// There are products with that productId in the cart.
		// For now only one variation of product (attributes are ignored) can be
		// added to cart so "search" algorithm is quite simple.
		// Later some hook might be needed to add possibility to create custom
		// search algorithm implementations.
		
		reset($_SESSION['pc_shop']['cart']['productIndex'][$productId]);
		return key($_SESSION['pc_shop']['cart']['productIndex'][$productId]);
	}
	
	/**
	 * 
	 * @param type $productId
	 * @param type $quantity
	 * @param type $attributes
	 * @return boolean|int
	 */
	public function Add($productId, $quantity=1, $attributes=null) {
		if (!$this->shop->products->Exists($productId)) return false;
		if (!isset($_SESSION['pc_shop']['cart']['productIndex'][$productId]))
			$_SESSION['pc_shop']['cart']['productIndex'][$productId] = Array();
		$quantity = intval($quantity);
		$ciid = $this->Find($productId, $attributes);
		if( is_null($ciid) ) {
			// Product is not in the cart yet so add it as a new item
			
			if( $quantity < 1 )
				return 0; // no need to add it
			
			$ciid = $_SESSION['pc_shop']['cart']['nextCIID']++;
			
			// We just need to store CIID (cart item id). Storing value into key
			// will give us faster search and removal in Remove method.
			$_SESSION['pc_shop']['cart']['productIndex'][$productId][$ciid] = 1;
			
			// At the moment only productId and quantity are needed for item
			// entries in the cart.
			$_SESSION['pc_shop']['cart']['items'][$ciid] = Array(
				$productId,
				$qty = $quantity
			);
			$_SESSION['pc_shop']['cart']['totalQuantity'] += $qty;
		}
		else
			$qty = $this->AddAt($ciid, $quantity);
		
		return $qty;
	}
	/** Add a quantity to item in cart
	*
	* This method adds a specified quantity to an item already placed in the
	* cart. CIID is used to identify the item.
	*
	* @param int $ciid CIID (Cart Item ID) of the item entry in the cart to which specified quantity should be added.
	* @param int $quantity=1 Specifies how much should be added to the item entry.
	* @return int Returns quantity of item in the cart after adding.
	*/
	public function AddAt($ciid, $quantity=1) {
		if (!isset($_SESSION['pc_shop']['cart']['items'][$ciid]))
			return 0;
		return $this->_SetQuantity($ciid, $_SESSION['pc_shop']['cart']['items'][$ciid][1] + intval($quantity));
	}
	
	/**
	 * 
	 * @param type $ciid
	 * @param type $quantity
	 * @return int
	 */
	public function Set($ciid, $quantity=1) {
		if (!isset($_SESSION['pc_shop']['cart']['items'][$ciid]))
			return 0;
		return $this->_SetQuantity($ciid, intval($quantity));
	}
	/** Sets the quantity to an item in the cart (internal use only)
	*
	* This method assumes that item already exists in the cart so it does not
	* check wether it is true. If the quantity is 0 then item is automatically
	* removed from the cart.
	*/
	protected function _SetQuantity($ciid, $quantity) {
		$old_qty = $_SESSION['pc_shop']['cart']['items'][$ciid][1];
		if( 0 == ($qty = $_SESSION['pc_shop']['cart']['items'][$ciid][1] = max($quantity, 0)) ) {
			$productId = $_SESSION['pc_shop']['cart']['items'][$ciid][0];
			unset(
				$_SESSION['pc_shop']['cart']['items'][$ciid],
				$_SESSION['pc_shop']['cart']['productIndex'][$productId][$ciid]
			);
			if( empty($_SESSION['pc_shop']['cart']['productIndex'][$productId]) )
				unset($_SESSION['pc_shop']['cart']['productIndex'][$productId]);
		}
		$_SESSION['pc_shop']['cart']['totalQuantity'] += $qty - $old_qty;
		return $qty;
	}
	
	/**
	 * Method used to remove item from cart
	 * @param type $ciid
	 * @param type $quantity
	 * @return int
	 */
	public function Remove($ciid, $quantity=null) {
		if (!isset($_SESSION['pc_shop']['cart']['items'][$ciid]))
			return 0;
		if (!is_null($quantity))
			return $this->AddAt($ciid, -$quantity);
		$this->_SetQuantity($ciid, 0);
		return 0;
	}	
	
	/**
	 * Method used to clear cart session data
	 * @return boolean
	 */
	public function Clear() {
		$_SESSION['pc_shop']['cart'] = Array(
			"nextCIID" => 1,
			"totalQuantity" => 0,
			"items" => Array(),
			"productIndex" => Array()
		);
		return true;
	}
	public function Order() {
		//
	}
	public function Count($uniqueItemsOnly=true) {
		return $uniqueItemsOnly ? count($_SESSION['pc_shop']['cart']['items']) : $_SESSION['pc_shop']['cart']['totalQuantity'];
	}
}