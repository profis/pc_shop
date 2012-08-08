<?php
abstract class PC_shop extends PC_base {
	public $categories, $products, $resources;
	final public function Init($admin=false) {
		if ($admin || is_a($this, 'PC_shop_manager')) $clsSuffix = 'manager';
		else $clsSuffix = 'site';
		$this->categories = $this->core->Get_object('PC_shop_categories_'.$clsSuffix, array($this));
		$this->products = $this->core->Get_object('PC_shop_products_'.$clsSuffix, array($this));
		$this->resources = $this->core->Get_object('PC_shop_resources'.($clsSuffix=='manager'?"_".$clsSuffix:""), array($this));
		$this->cart = $this->core->Get_object('PC_shop_cart', array($this));
		$this->orders = $this->core->Get_object('PC_shop_orders', array($this));
		//register database fields
		$fields = array();
		$fields['categories'] = array('flags', 'discount', 'percentage_discount', 'external_id'); //parent_id, active ?
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
				return Validate('name', $value, array('length'=>array('to'=> 255)));
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
			case 'route': return true;
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
	const CF_DEFAULT = 0x1, CF_IS_ACTIVE = 0x1;
	public function Init(PC_shop $shop) {
		$this->shop = $shop;
	}
	public function Exists($id) {
		$r = $this->prepare("SELECT id FROM {$this->db_prefix}shop_categories WHERE id=? LIMIT 1");
		$s = $r->execute(array($id));
		if (!$s) return false;
		return (bool)$r->rowCount();
	}
	public function Is_active($id) {
		$r = $this->prepare("SELECT active FROM {$this->db_prefix}shop_categories WHERE id=? LIMIT 1");
		$s = $r->execute(array($id));
		if (!$s) return false;
		return (bool)$r->fetchColumn();
	}
	public function Encode_flags(&$data, $createMode=true) {
		if (!isset($data['flags'])) {
			if ($createMode) $data['flags'] = self::CF_DEFAULT;
		}
		$fields = array(
			'active'=> self::CF_IS_ACTIVE
		);
		foreach ($fields as $field=>$flag) {
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
		$fields = array(
			'active'=> self::CF_IS_ACTIVE
		);
		foreach ($fields as $field => $flag) {
			if (($data['flags'] & $flag) != 0) $data[$field] = true;
			else $data[$field] = false;
		}
		return true;
	}
}
class PC_shop_products extends PC_base {
	protected $shop;
	const PF_DEFAULT = 0x1, PF_IS_ACTIVE = 0x1,
	PF_IS_PRODUCT_GROUP = 0x2, PF_PARENT_IS_PRODUCT = 0x4;
	public function Init(PC_shop $shop) {
		$this->shop = $shop;
	}
	public function Exists($id) {
		$r = $this->prepare("SELECT id FROM {$this->db_prefix}shop_products WHERE id=? LIMIT 1");
		$s = $r->execute(array($id));
		if (!$s) return false;
		return (bool)$r->rowCount();
	}
	public function Is_active($id) {
		$r = $this->prepare("SELECT active FROM {$this->db_prefix}shop_products WHERE id=? LIMIT 1");
		$s = $r->execute(array($id));
		if (!$s) return false;
		return (bool)$r->fetchColumn();
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
		$fields = array(
			'active'=> self::PF_IS_ACTIVE,
			'is_product_group'=> self::PF_IS_PRODUCT_GROUP,
			'parent_is_product'=> self::PF_PARENT_IS_PRODUCT
		);
		foreach ($fields as $field=>$flag) {
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
		$fields = array(
			'active'=> self::PF_IS_ACTIVE,
			'is_product_group'=> self::PF_IS_PRODUCT_GROUP,
			'parent_is_product'=> self::PF_PARENT_IS_PRODUCT
		);
		foreach ($fields as $field => $flag) {
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
	}
	public function Get($raw=false) {
		$d = array(
			'total'=> 0,
			'totalQuantity'=> 0,
			'items'=> array(),
			'totalPrice'=> 0
		);
		if (!count(v($_SESSION['pc_shop']['cart']))) return array();
		if ($raw) {
			return $_SESSION['pc_shop']['cart'];
		}
		$d['items'] = $this->shop->products->Get(array_keys($_SESSION['pc_shop']['cart']));
		foreach ($d['items'] as &$p) {
			$d['total']++;
			$d['totalQuantity'] += $_SESSION['pc_shop']['cart'][$p['id']];
			//price = $this->shop->products->Get_price($d)
			$d['totalPrice'] += $p['price'] * $p['quantity'];
			$p['quantity'] = $_SESSION['pc_shop']['cart'][$p['id']];
		}
		return $d;
	}
	public function Add($productId, $quantity=1) {
		if (!$this->shop->products->Exists($productId)) return false;
		if (!isset($_SESSION['pc_shop']['cart'][$productId])) $_SESSION['pc_shop']['cart'][$productId] = 0;
		$_SESSION['pc_shop']['cart'][$productId] += $quantity;
		return $_SESSION['pc_shop']['cart'][$productId];
	}
	public function Delete($productId, $quantity=null) {
		if (is_null($quantity)) {
			unset($_SESSION['pc_shop']['cart'][$productId]);
			return true;
		}
		if (!isset($_SESSION['pc_shop']['cart'][$productId])) return 0;
	}
	public function Set($productId, $quantity=1) {
		$_SESSION['pc_shop']['cart'][$productId] = $quantity;
		return true;
	}
	public function Clear() {
		$_SESSION['pc_shop']['cart'] = array();
		return true;
	}
	public function Order() {
		//
	}
	public function Count($uniqueItemsOnly=true) {
		if (!isset($_SESSION['pc_shop']['cart'])) return 0;
		if ($uniqueItemsOnly) return count($_SESSION['pc_shop']['cart']);
		return array_sum($_SESSION['pc_shop']['cart']);
	}
}