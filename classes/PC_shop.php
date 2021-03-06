<?php
use \Profis\Db\DbException;

/**
 * Class PC_shop
 * @property PC_shop_price $price
 */
abstract class PC_shop extends PC_base {
	static $COMBINATION_ATTRIBUTE_COUNT = 1;

	/**
	 *
	 * @var PC_shop_categories_manager
	 */
	public $categories;
	
	/**
	 *
	 * @var PC_shop_products_manager
	 */
	public $products; 
	
	/**
	 *
	 * @var PC_shop_resources_manager
	 */
	public $resources;
	
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
		self::$COMBINATION_ATTRIBUTE_COUNT = max(v($this->cfg['pc_shop']['combination_attribute_count'], 1), 1);

		$this->categories = $this->core->Get_object('PC_shop_categories_'.$clsSuffix, array($this));
		$this->products = $this->core->Get_object('PC_shop_products_'.$clsSuffix, array($this));
		$this->resources = $this->core->Get_object('PC_shop_resources'.($clsSuffix=='manager'?"_".$clsSuffix:""), array($this));
		$this->attributes = $this->core->Get_object('PC_shop_attributes', array($this));
		$this->cart = $this->core->Get_object('PC_shop_cart', array($this));
		$this->orders = $this->core->Get_object('PC_shop_orders', array($this));
		$this->price = $this->core->Get_object('PC_shop_price', array($this));
		//register database fields
		$fields = array();
		$fields['categories'] = array('flags', 'discount', 'percentage_discount', 'external_id', 'redirect');
		$fields['category_contents'] = array('name', 'custom_name', 'description', 'seo_title', 'seo_description', 'seo_keywords', 'route', 'permalink');
		$fields['products'] = array('manufacturer_id', 'mpn', 'is_not_quantitive', 'quantity', 'flags', 'warranty', 'discount', 'percentage_discount', 'hot_from', 'price', 'external_id', 'import_method', 'state', 'info_1', 'info_2', 'info_3', 'weight', 'volume', 'length', 'width', 'height');
		$fields['product_contents'] = array('name', 'custom_name', 'short_description', 'description', 'seo_title', 'seo_description', 'seo_keywords', 'route', 'permalink');
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
			case 'state': return is_numeric($value);
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
			case 'custom_name': 	
				if (empty($value)) return true;
				return Validate('name', $value, true, array('length'=>array('from'=> 1, 'to'=> 255)));
			case 'description': return true;
			case 'seo_title': return true;
			case 'seo_description': return true;
			case 'seo_keywords': return true;
			case 'manufacturer_id': return true;
			case 'mpn': return true;
			case 'is_not_quantitive': return true;
			case 'quantity': return true;
			case 'warranty': return true;
			case 'price': return true;
			case 'short_description': return true;
			case 'import_method':
			case 'info_1':
			case 'info_2':
			case 'info_3':
				return true;
			case 'external_id':
				if (empty($value)) $value = null;
				return true;
			case 'route': 
				//$value = Sanitize('route', $value);
				return true;
			case 'permalink': 
				//$value = Sanitize('route', $value);
				return true;
			case 'redirect':
				return true;
				
			case 'hot_from':
				return true;
			case 'weight':
			case 'volume':
				return $value === '' || is_numeric($value);

			case 'length':
			case 'width':
			case 'height':
				return $value === '' || preg_match('#^[0-9]+$#', $value);

			default: return false;
		}
		return false;
	}
	public function Get_variable($key) {
		return $this->core->Get_variable('pc_shop_'.$key);
	}
}
class PC_shop_categories extends PC_shop_category_model {
	/**
	 *
	 * @var PC_shop
	 */
	protected $shop;
	public $distinct_route = true;
	const CF_DEFAULT = 0x1, CF_PUBLISHED = 0x1, CF_HOT = 0x2, CF_NOMENU = 0x4, CF_ROUTE_LOCK = 0x8;
	private $flagsMap = array(
		'hot'=> self::CF_HOT,
		'nomenu'=> self::CF_NOMENU,
		'published'=> self::CF_PUBLISHED,
		'route_lock'=> self::CF_ROUTE_LOCK
	);
	/**
	 * 
	 * @param PC_shop $shop
	 */
	public function Init($id = 0) {
		$shop = $id;
		$this->shop = $shop;
		$this->distinct_route = true;
	}
	public function Exists($id) {
		$r = $this->prepare("SELECT id FROM {$this->db_prefix}shop_categories WHERE id=? LIMIT 1");
		$s = $r->execute(array($id));
		if (!$s) return false;
		return (bool)$r->rowCount();
	}
	
	protected function get_flag_query_condition($flag, &$query_params = array(), $table = '', $op = '=') {
		$flag_number = $flag;
		if (strpos($flag, '0x') !== false) {
			$flag_number = substr($flag_number, 2);
		}
		if (!empty($table)) {
			$table .= '.';
		}
		$cond = "({$table}flags & ?) $op ?";
		$query_params[] = $flag_number;
		$query_params[] = $flag_number;
		return $cond;
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
	public function Get_page_id($categoryId) {
		$r = $this->prepare("SELECT parent.pid FROM {$this->db_prefix}shop_categories c JOIN {$this->db_prefix}shop_categories parent ON c.lft BETWEEN parent.lft and parent.rgt WHERE c.id=? ORDER BY parent.lft ASC LIMIT 1");
		$s = $r->execute(array($categoryId));
		if (!$s) return false;
		return $r->fetchColumn();
	}
	
		public function Get_item($id, $select = '*') {
		$r_category = $this->prepare("SELECT $select FROM {$this->db_prefix}shop_categories WHERE id = ? LIMIT 1");
		$queryParams = array();
		$queryParams[] = $id;

		$s = $r_category->execute($queryParams);
		if (!$s) return false;

		if ($d = $r_category->fetch()) {
			return $d;
		}
		return false;
	}
	
	/**
	 * 
	 * @param type $name
	 * @param type $value
	 * @param type $ln
	 * @param type $limit = 1 
	 * @return int or array
	 * If $limit = 1, then category_id will be returned,  
	 * otherwise array categories ids will be returned
	 */
	public function Get_id_by_content($name, $value, &$ln = null, $limit = 1) {
		$queryParams = array();
		$queryParams[] = $value;
		
		$where_ln = '';
		$flag_s = '';
		$join_c = '';
		
		if (!is_null($ln)) {
			$queryParams[] = $ln;
			$where_ln = ' AND ln = ?';
		}
		
		if ($limit != 1) {
			$flag_s = ' AND ' . $this->get_flag_query_condition(self::CF_PUBLISHED, $queryParams, 'c');
			$join_c = " LEFT JOIN {$this->db_prefix}shop_categories c ON c.id = cc.category_id ";
		}
		
		$limit_s = '';
		if ($limit > 0) {
			$limit_s = ' LIMIT 1';
		}
		$query = "SELECT category_id,ln FROM {$this->db_prefix}shop_category_contents cc
		 $join_c 
		 WHERE $name = ? $where_ln" . $flag_s . $limit_s;
		$r_category = $this->prepare($query);

		$s = $r_category->execute($queryParams);
		if (!$s) return false;

		if ($limit == 1) {
			if ($d = $r_category->fetch()) {
				$ln = $d['ln'];
				return $d['category_id'];
			}
		}
		else {
			$data = array();
			while ($d = $r_category->fetch()) {
				$data[] = $d['category_id'];
			}
			return $data;
		}
		return false;
	}
	
	public function Get_items($select = '*', $where = '', $query_params = array()) {
		$where_s = '';
		if (!empty($where)) {
			$where_s = ' WHERE ' . $where;
		}
		$r_category = $this->prepare("SELECT $select FROM {$this->db_prefix}shop_categories $where_s");

		$s = $r_category->execute($query_params);
		if (!$s) return false;

		$items = array();
		while ($d = $r_category->fetch()) {
			$items[] = $d;
		}
		return $items;
	}
	
	
	public function Get_data___($id = null, $params = array()) {
		$select = 't.*';
		$join_cc = '';
		$select_cc = '';
		
		$query_params = array();
		
		if (v($params['select'])) {
			$select = $params['select'];
		}
		
		if (v($params['content'])) {
			$join_cc = " LEFT JOIN {$this->db_prefix}shop_category_contents ct ON ct.category_id=t.id and ct.ln=? ";
			$select_cc = ', ct.*';
			if (is_array($params['content']) and v($params['content']['select'])) {
				$select_cc = ', ' . $params['content']['select'];
			}
			$ln = $this->site->ln;
			if (isset($params['ln'])) {
				$ln = $params['ln'];
			}
			$query_params[] = $ln;
		}
		
		$where_s = '';
		
		if (!is_null($id)) {
			if (!is_array($id)) {
			$where_s .= ' t.id = ? ';
				$query_params[] = $id;
			}
			else {
				$where_s .= ' t.id ' . $this->sql_parser->in($id);
				$query_params = array_merge($query_params, $id);
			}
		}
		
		if (v($params['query_params']) and is_array($params['query_params'])) {
			$query_params = array_merge($query_params, $params['query_params']);
		}
		
		if (isset($params['where'])) {
			$additional_where = '';
			if (is_array($params['where'])) {
				$additional_where = implode(' AND ', $params['where']);
			}
			else {
				$additional_where = $params['where'];
			}
			if (!empty($additional_where)) {
				if (!empty($where_s)) {
					$where_s .= ' AND ';
				}
				$where_s .= $additional_where;
			}
		}
		
		$query = "SELECT {$select}{$select_cc} FROM {$this->db_prefix}shop_categories t $join_cc 
			WHERE $where_s";
		$r_categories = $this->prepare($query);

		$s = $r_categories->execute($query_params);
		if (!$s) return false;

		$items = array();
		while ($d = $r_categories->fetch()) {
			$items[] = $d;
		}
		return $items;
	}
	
	public function Get_dynamic_data($category_id, &$dynamic_attribute_data) {
		$sub_item_attribute_id = $this->shop->attributes->get_id_from_ref($dynamic_attribute_data['sub_item_attribute_ref']);
		if (!$sub_item_attribute_id) {
			return false;
		}

		$select = '';
		$fetch_all = false;
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
				$select = 'a.value';
				$fetch_all = true;
				break;
		}
		
		
		$dynamic_data = false;

		switch ($dynamic_attribute_data['sub_item_type']) {
			case 'category':
				$dynamic_data = $this->shop->attributes->Get_aggregate_data_for_category_children($sub_item_attribute_id, $category_id, $select, $where_s, $fetch_all);
				break;

			case 'page_category':
				$dynamic_data = $this->shop->attributes->Get_aggregate_data_for_page_categories($sub_item_attribute_id, $category_id, $select, $where_s, $fetch_all);
				break;
			
			default:
				break;
		}

		if ($dynamic_data === false) {
			return false;
		}

		if (is_array($dynamic_data) and $dynamic_attribute_data['type'] == 'set_union') {
			$union = array();
			foreach ($dynamic_data as $key => $value) {
				$union = array_merge($union, explode(',', $value));
				$union = array_unique($union);
			}
			sort($union);
			$dynamic_data = implode(',', $union);
		}
		
		if (is_array($dynamic_data)) {
			return false;
		}
		return $dynamic_data;
	}
	
}
class PC_shop_products extends PC_shop_product_model {
	/**
	 *
	 * @var PC_shop
	 */
	protected $shop;

	// For those who don't understand: product flag constants are given in HEXADECIMAL numbers, NOT IN DECIMAL!
	// Next numbers will be: 0x40, 0x80, 0x100, 0x200, 0x400, 0x800, 0x1000, ...
	const PF_DEFAULT = 0x1;
	const PF_PUBLISHED = 0x1;
	const PF_IS_PRODUCT_GROUP = 0x2;
	const PF_PARENT_IS_PRODUCT = 0x4;
	const PF_HOT = 0x8;
	const PF_NOMENU = 0x10;
	const PF_ROUTE_LOCK = 0x20;

	private $flagsMap = array(
		'hot'=> self::PF_HOT,
		'nomenu'=> self::PF_NOMENU,
		'published'=> self::PF_PUBLISHED,
		'is_product_group'=> self::PF_IS_PRODUCT_GROUP,
		'parent_is_product'=> self::PF_PARENT_IS_PRODUCT,
		'route_lock'=> self::PF_ROUTE_LOCK
	);

	/**
	 * @param PC_shop $id
	 */
	public function Init($id = null) {
		$shop = $id;
		parent::Init();
		$this->shop = $shop;
	}
	public function Exists($id) {
		$r = $this->prepare("SELECT quantity FROM {$this->db_prefix}shop_products WHERE id=? LIMIT 1");
		$s = $r->execute(array($id));
		if (!$s) return false;
		$this->last_existing_item_quantity = $r->fetchColumn();
		return (bool)$r->rowCount();
	}
	
	public function Exists_in_content($col, $value, $lang, $category_id = '') {
		$where = '';
		$params = array();
		$params[] = $value; 
		if ($category_id != '') {
			$where .= ' AND p.category_id = ?';
			$params[] = $category_id; 
		}
		$query = "SELECT p.id
			FROM {$this->db_prefix}shop_products p
			JOIN {$this->db_prefix}shop_product_contents c ON c.product_id=p.id AND c.ln = '$lang' 
			WHERE " . $col . " = ? " . $where . "
			LIMIT 1";
		$r = $this->prepare($query);
		$s = $r->execute($params);
		if (!$s) {
			return false;
		}
		
		if ($product_id = $r->fetchColumn()) {
			return $product_id;
		}
		return false;
		/*
		$row_count = $r->rowCount();
		return (bool)$row_count;
		*/
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
	public function Get_price_($id) {
		if (!is_array($id)) {
			
		}
	}
	public function Get_page_id($productId) {
		$r = $this->prepare("SELECT parent.pid FROM {$this->db_prefix}shop_products p JOIN {$this->db_prefix}shop_categories c ON c.id=p.category_id JOIN {$this->db_prefix}shop_categories parent ON c.lft BETWEEN parent.lft and parent.rgt WHERE p.id=? ORDER BY parent.lft ASC LIMIT 1");
		$s = $r->execute(array($productId));
		if (!$s) return false;
		return $r->fetchColumn();
	}
	
	/**
	 * 
	 * @param type $id - product id
	 * @param type $select = '*' - fields to select
	 * @param type $ln = false. If set, content for specified language will be joined
	 * @return boolean
	 */
	public function Get_item($id, $select = '*', $ln = false) {
		$join = '';
		$queryParams = array();
		if ($ln) {
			$join = " LEFT JOIN {$this->db_prefix}shop_product_contents pc ON pc.product_id = p.id and pc.ln = ?";
			$queryParams[] = $ln;
		}
		$query = "SELECT $select FROM {$this->db_prefix}shop_products p $join WHERE p.id = ? LIMIT 1";
		$r_category = $this->prepare($query);
		
		$queryParams[] = $id;

		
		
		$s = $r_category->execute($queryParams);
		if (!$s) return false;

		if ($d = $r_category->fetch()) {
			return $d;
		}
		return false;
	}
	
	public function Get_id_by_field($name, $value, $limit = 1) {
		$join = '';
		$query = "SELECT id FROM {$this->db_prefix}shop_products p $join WHERE p.$name = ? LIMIT 1";
		$r_category = $this->prepare($query);
		
		$queryParams[] = $value;

		$s = $r_category->execute($queryParams);
		if (!$s) return false;

		if ($d = $r_category->fetchColumn()) {
			return $d;
		}
		return false;
	}
}
class PC_shop_resources extends PC_base {
	public $rawList, $list;
	protected $shop;
	const RF_DEFAULT = 0x0, RF_IS_CATEGORY = 0x1, RF_IS_ATTACHMENT = 0x2, RF_AL_PUBLIC = 0x4, RF_AL_USER = 0xC, RF_AL_ADMIN = 0x1C;
	/* AL = access level; priejimo lygiai 3: public, users, private */
	public function Init(PC_shop $shop) {
		$this->shop = $shop;
	}
	public function Get($id=null, $itemId=null, $flags=self::RF_AL_PUBLIC) {
		if (!is_array($flags)) {
			$flags = array($flags);
		}
		$flagsCheck = 0x0;
		//print_r($flags);
		//echo $this->get_callstack();
		$category_flag_used = false;
		$queryParams = array();
		$flag_clauses = array();
		if (count($flags)) foreach ($flags as $flag) {
			if ($flag == '' or is_null($flag)) {
				continue;
			}
			if ($flag == self::RF_IS_CATEGORY) {
				$category_flag_used = true;
			}
			$flagsCheck |= $flag;
			$flag_clauses[] = $this->db->get_flag_query_condition($flag, $queryParams, 'flags', 'r');
		}
		if (!$category_flag_used) {
			$flag_clauses[] = $this->db->get_flag_query_condition(self::RF_IS_CATEGORY, $queryParams, 'flags', 'r', '!=');
		}
		$flag_clause = implode(' AND ', $flag_clauses);
		//$queryParams = array();
		//$flag_clause = "flags&{$flagsCheck}={$flagsCheck}";
		
		$query = $qry = "SELECT r.* FROM {$this->db_prefix}shop_resources r INNER JOIN {$this->db_prefix}gallery_files g ON g.id=r.file_id WHERE $flag_clause ".(!is_null($id)?" AND r.resource_id=?":!is_null($itemId)?" and r.item_id=?":"")." ORDER BY r.flags&?, r.position".(!is_null($id)?" LIMIT 1":"");
		$r = $this->prepare($query);
		
		if (!is_null($id)) $queryParams[] = $id;
		elseif (!is_null($itemId)) $queryParams[] = $itemId;
		$queryParams[] = PC_shop_resources::RF_IS_ATTACHMENT;
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

	public static function Decode_flags(&$data) {
		if (!isset($data['flags'])) return false;
		$data['is_category'] = (($data['flags'] & self::RF_IS_CATEGORY) != 0);
		$data['is_attachment'] = (($data['flags'] & self::RF_IS_ATTACHMENT) != 0);
		//$data['access'] = 'public';
		//if (($data['flags'] & self::RF_AL_PUBLIC) != 0) $data['access'] = 'user';
		return true;
	}
	public static function Encode_flags(&$data) {
		$flags = 0x0; 
		/*switch ($data['access']) {
			case 'user': $flags |= self::RF_AL_USER; break;
			case 'admin': $flags |= self::RF_AL_ADMIN; break;
			default: $flags |= self::RF_AL_PUBLIC;
		}*/
	}
}
/* Currently not used because of issue when printing class that extends PC_base (script crashes because of lots of references in the class, so no printing is functionable) */
class PC_shop_item_resources{
	protected $itemId, $isCategory;
	
	public static $item_resources = array();
	public static $item_files = array();

	public function __construct($itemId, $isCategory=false, $checkIfExists=false) {
		if ($checkIfExists) {
			$r = $this->prepare("SELECT id FROM {$this->db_prefix}shop_".$this->Get_table($isCategory)." WHERE id=?");
			$s = $r->execute(array($itemId));
			if (!$s) return false;
			if (!$r->rowCount()) return false;
		}
		$this->itemId = (int)$itemId;
		$this->isCategory = (bool)$isCategory;
		$this->Load();
	}
	private function Get_id() {
		return $this->itemId;
	}
	private function Is_category() {
		return $this->isCategory;
	}
	private function Get_table($isCategory=null) {
		if (is_null($isCategory)) $isCategory = $this->isCategory;
		return ($isCategory?'categories':'products');
	}
	private function Load() {
		global $db, $cfg, $gallery;
		//flags
		$flags = 0x0;//PC_shop_resources::RF_AL_PUBLIC;
		
		$item_resources = array();
		
		if (isset(self::$item_resources[$this->itemId])) {
			$item_resources = self::$item_resources[$this->itemId];
		}
		else {
			$flag_op = '=';
			$flags |= PC_shop_resources::RF_IS_CATEGORY;
			if (!$this->Is_category()) {
				$flag_op = '!=';
			}
			$query = "SELECT * FROM {$cfg['db']['prefix']}shop_resources WHERE item_id=? and flags & ? " . $flag_op . " ? ORDER BY position";
			$r = $db->prepare($query);
			$query_params = array($this->itemId, $flags, $flags);
			$s = $r->execute($query_params);

			if (!$s) return false;
			
			while ($d = $r->fetch()) {
				$item_resources[] = $d;
			}
		}
		
		$list = array();
		$ids = array();

		foreach ($item_resources as $d) {
			$ids[] = $d['file_id'];
			PC_shop_resources::Decode_flags($d);
			$list[] = $d;
		}
		
		if (empty($ids)) {
			$this->list = $list;
			return $this->list;
		}
		
		if (isset(self::$item_files[$this->itemId])) {
			$files = self::$item_files[$this->itemId];
		}
		else {
			$files = $gallery->Get_file_by_id($ids);
		}
		foreach ($files as $file) {
			foreach ($list as &$res) {
				if ($res['file_id'] == $file['id']) {
					$res['data'] = $file;
					break;
				}
			}
		}
		$this->list = $list;
		//save in cache and return
		return $this->list;
	}
	public function Get($isAttachment=false, $thumbnailType=null) {
		global $gallery;
		if (!$this->list) $this->Load();
		$list = array();
		foreach ($this->list as $res) {
			if (!is_null($isAttachment)) {
				if ((bool)$res['is_attachment'] != (bool)$isAttachment) {
					continue;
				}
			}
			if ($res['data']['type'] == 'image') {
				$list[] = $gallery->Get_image_thumbnail($res['data']['link'], $thumbnailType);
				continue;
			}
			$list[] = $res['data']['link'];
		}
		return $list;
	}
	public function Get_main_image($thumbnailType=null, $nr = 1) {
		global $gallery;
		if (!$this->list) $this->Load();
		$counter = 0;
		foreach ($this->list as $res) {
			if ($res['data']['type'] == 'image') {
				$counter++;
				if ($counter == $nr) {
					return $gallery->Get_image_thumbnail($res['data']['link'], $thumbnailType);
				}
			}
		}
		return false;
	}
}
class PC_shop_attributes extends PC_shop_attribute_model {
	const AF_DEFAULT = 0x1;
	const ITEM_IS_CATEGORY = 0x1;
	const ITEM_IS_PRODUCT = 0x2;

	private $flagsMap = array(
		'item_is_category'=> self::ITEM_IS_CATEGORY,
		'item_is_product'=> self::ITEM_IS_PRODUCT
	);
	public function Encode_flags(&$data) {
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
	public function ParseSQLResult(&$d) {
		$attribs = array();
		$multiple_attributes = array();
		if (!empty($d) && $d != '▓') {
			$temp = explode('▓', $d);
			foreach ($temp as $attr) {
				if (empty($attr)) continue;
				$attrData = array();
				$attr = explode('░', $attr);
				//print_pre($attr);
				$name = '';
				foreach ($attr as $k => $v) {
					$n_array = explode(PC_sql_parser::SP3, $v, 2);
					if (count($n_array) > 1) {
						$name = $n_array[0];
						continue;
					}
					if (!empty($name)) {
						$attrData[$name] = $v;
					}
					
				}
				$key = v($attrData['ref']);

				if (isset($attrData['flags'])) {
					$this->Decode_flags($attrData);
				}
				
				if (!v($attrData['is_custom']) and isset($attrData['avc_value'])) {
					$attrData['value'] = $attrData['avc_value'];
				}
				//print_pre($attrData);
						
				if (!empty($key)) {
					if (isset($attribs[$key])) {
						$check_key = $key;
						if (v($attribs[$key]['value_id'])) {
							///$check_key = $attribs[$key]['value_id'];
						}
						//echo $check_key . '<hr />';
						if (!isset($multiple_attributes[$check_key])) {
							$multiple_attributes[$key] = array();
							if (v($attribs[$key]['value_id'])) {
								$multiple_attributes[$key][$attribs[$key]['value_id']] = $attribs[$key];
							}
							elseif (v($attribs[$key]['ia_id'])) {
								$multiple_attributes[$key]['#' . $attribs[$key]['ia_id']] = $attribs[$key];
							}
							else {
								$multiple_attributes[$key][] = $attribs[$key];
							}
						}
						if (v($attrData['value_id'])) {
							$multiple_attributes[$key][$attrData['value_id']] = $attrData;
						}
						elseif (v($attribs[$key]['ia_id'])) {
							$multiple_attributes[$key]['#' . $attrData['ia_id']] = $attrData;
						}
						else {
							$multiple_attributes[$key][] = $attrData;
						}
						
					}
					$attribs[$key] = $attrData;
				}
				else {
					$attribs[] = $attrData;
				}
				
			}
		}
		$d = $attribs;
		return $multiple_attributes;
	}
	
	public function Get_categorized_keys(&$attributes) {
		$groups = array();
		$attr_caregory_model = $this->core->Get_object('PC_shop_attribute_category_model');
		$categories = $attr_caregory_model->get_all(array(
			'content' => true,
			'key' => 'id'
		));
				
		$categories[0] = array();
		
		foreach ($categories as $key => $value) {
			$categories[$key]['attributes'] = array();
		}
		
		
		
		foreach ($attributes as $key => $value) {
			v($value['category_id'], 0);
			if (isset($categories[$value['category_id']])) {
				$categories[$value['category_id']]['attributes'][] = $key;
			}
		}
		
		return $categories;		
	}
	
	public function Get_id_by_content($name, $value, $ln, $limit = 1) {
		$join = '';
		$query = "SELECT attribute_id FROM {$this->db_prefix}shop_attribute_contents p $join 
			WHERE p.$name = ? AND ln = ?
			LIMIT 1";
		$r_category = $this->prepare($query);
		
		$queryParams[] = $value;
		$queryParams[] = $ln;

		$s = $r_category->execute($queryParams);
		if (!$s) return false;

		if ($d = $r_category->fetchColumn()) {
			return $d;
		}
		return false;
	}
	
	
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
		
		$order = '';
		$order = " ORDER BY a.is_category_attribute DESC, a.position ";	
				
		$query = "SELECT ".($params->Has_paging()?'SQL_CALC_FOUND_ROWS ':'')."a.*,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'c.ln', 'c.name'), array('separator'=>'▓', 'distinct'=> true))." names"
		.($params->Get('includeValues')?
			','.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'avc.value_id', 'avc.ln', 'avc.value'), array('separator'=>'▓', 'order' => array('by' => 'av.position, av.id')))." attrValues"
		:"")
		." FROM {$this->db_prefix}shop_attributes a"
		." LEFT JOIN {$this->db_prefix}shop_attribute_contents c ON c.attribute_id=a.id"
		.($params->Get('includeValues')?
			//join attribute values
			" LEFT JOIN {$this->db_prefix}shop_attribute_values av ON av.attribute_id=a.id"
			." LEFT JOIN {$this->db_prefix}shop_attribute_value_contents avc ON avc.value_id=av.id"
		:"")
		.(count($where)?' WHERE '.implode(' and ', $where):''). " GROUP BY a.id" . $order .v($limit ,'');
		
			
		$r = $this->prepare($query);
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
				$d['values_order'] = array_keys($d['values']);
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
		if (isset($data['position'])) {
			$set[] = 'position=?';
			$queryParams[] = $data['position'];
		}
		if (isset($data['ref'])) {
			$set[] = 'ref=?';
			$queryParams[] = $data['ref'];
		}
		if (isset($data['category_id'])) {
			$set[] = 'category_id=?';
			$queryParams[] = $data['category_id'];
		}
		//main attribute data
		if (count($set)) {
			$queryParams[] = $id;
			$r = $this->prepare("UPDATE {$this->db_prefix}shop_attributes SET ".implode(',', $set)." WHERE id=?");
			$s = $r->execute($queryParams);
			if (!$s) return !$params->errors->Add('database', '');
		}
		//save names
		v($data['names']);
		if (is_array($data['names'])) if (count($data['names'])) {
			$attribute_content_model = new PC_shop_attribute_content_model();
			$query = "UPDATE {$this->db_prefix}shop_attribute_contents SET name=? WHERE attribute_id=? and ln=?";
			$rContents = $this->prepare($query);
			foreach ($data['names'] as $ln=>$name) {
				$update_data = array(
					'name' => $name,
				);
				$update_result = $attribute_content_model->update($update_data, array(
					'where' => array(
						'ln' => $ln,
						'attribute_id' => $id
					)
				));
				if (!$update_result) {
					$update_data['attribute_id'] = $id;
					$update_data['ln'] = $ln;
					$attribute_content_model->insert($update_data, array(), array('ignore' => true));
				}
				//$query_params = array($name, $id, $ln);
				//$rContents->execute($query_params);
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
		$r = $this->prepare("INSERT INTO {$this->db_prefix}shop_attributes (id,is_category_attribute,is_custom) VALUES (null,?,?)");
		$s = $r->execute(array($isCategoryAttribute, ($params->Get('is_custom')?1:0)));
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
	public function Delete($params = array()) { //manager
		$id = $params;
		$query_delete_attribute = "DELETE FROM {$this->db_prefix}shop_attributes WHERE id=?";
		$r = $this->prepare($query_delete_attribute);
		$params_delete_attribute = array($id);
		$s = $r->execute($params_delete_attribute);
		if (!$s) return false;
		
		$query_delete_attribute_contents = "DELETE FROM {$this->db_prefix}shop_attribute_contents WHERE attribute_id=?";
		$r = $this->prepare($query_delete_attribute_contents);
		$params_delete_attribute_contents = array($id);
		$r->execute($params_delete_attribute_contents);
		
		$query_select_values = "SELECT id FROM {$this->db_prefix}shop_attribute_values WHERE attribute_id=?";
		$params_select_values = array($id);
		$r = $this->prepare($query_select_values);
		$s = $r->execute($params_select_values);
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
		if (empty($id)) {
			return true;
		}
		$queryParams = array();
		if (!is_array($id)) $queryParams[] = $id;
		else $queryParams = array_merge($queryParams, $id);
		$query = "DELETE FROM {$this->db_prefix}shop_attribute_values WHERE ".(is_array($id)?'id '.$this->sql_parser->in($id):'id=?');
		$r = $this->prepare($query);
		$s = $r->execute($queryParams);

		if (!$s) return false;
		//delete value contents
		
		$query_2 = "DELETE FROM {$this->db_prefix}shop_attribute_value_contents"
		." WHERE ".(is_array($id)?'value_id '.$this->sql_parser->in($id):'value_id=?');
		
		$this->prepare($query_2)->execute($queryParams);
		
		return true;
	}
	/**
	 * attribute values
	 * @param type $attributeId
	 * @param type $params
	 * @return boolean
	 */
	public function Get_values($attributeId, &$params=array(), $ln = '') {
		if (!is_array($params)) {
			$ln = $params;
			$params = array();
		}
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
		.(count($where)?' WHERE '.implode(' and ', $where):'')." GROUP BY v.id".$limit . " ORDER BY v.position");
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
			if (!empty($ln)) {
				$d['name'] = v($d['names'][$ln]);
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
	public function Get_for_item($itemId, $itemType=self::ITEM_IS_PRODUCT, &$params = array()) {
		if (is_null($itemType)) $itemType = self::ITEM_IS_PRODUCT;
		$this->core->Init_params($params);
		$flags = $itemType;
		$queryParams = array($itemId, $flags, $flags);
		
		$select = 'a.*';
		$join = '';
		$order = '';

		if ($params->Get('includeName')) {
			$select .= ', c.*';
			$join .= " LEFT JOIN {$this->db_prefix}shop_attribute_contents c ON c.attribute_id=a.attribute_id and c.ln=?";
			array_unshift($queryParams, $this->site->ln);
		}
		
		if (true) {
			//$select .= ', at.position';
			$join .= " LEFT JOIN {$this->db_prefix}shop_attributes at ON at.id=a.attribute_id";
			$order .= ' ORDER BY at.position, at.id, CAST(a.value as UNSIGNED INTEGER), a.value';
		}
		
		if ($itemType == self::ITEM_IS_PRODUCT) {
			$select .= ', pp.price, pp.price_diff, pp.discount, pp.items_left, pp.info_1, pp.info_2, pp.info_3, pp.`weight`, pp.`volume`, pp.`length`, pp.`width`, pp.`height`';
			$join .= " LEFT JOIN {$this->db_prefix}shop_product_prices pp 
						ON pp.product_id=a.item_id AND pp.attribute_id=a.attribute_id
						AND (
							pp.attribute_value_id <> 0 AND pp.attribute_value_id = a.value_id 
							OR 
							pp.attribute_item_id <> 0 AND pp.attribute_item_id = a.id 
						)";
			for( $i = 2; $i <= PC_shop::$COMBINATION_ATTRIBUTE_COUNT; $i++ ) {
				$psuf = ($i > 2) ? ($i - 1) : '';
				$select .= ", a{$i}.id AS id{$i}, a{$i}.attribute_id AS attribute{$i}_id, a{$i}.value_id AS value{$i}_id, a{$i}.value AS value{$i}";
				$join .= " LEFT JOIN {$this->db_prefix}shop_item_attributes a{$i} ON a{$psuf}.next_attribute_id=a{$i}.id";
			}
		}
		
		$query = "SELECT $select FROM {$this->db_prefix}shop_item_attributes a"
		. $join
		." WHERE a.item_id=? AND a.level=1 AND (a.flags&?)=?" . $order;
		$r = $this->prepare($query);
		$s = $r->execute($queryParams);
		if (!$s) return false;
		$list = array();
		while ($d = $r->fetch()) {
			if( isset($d['weight']) ) {
				$d['weight'] = preg_replace('#(?:\\.0+|(\\.[0-9]*[1-9])0*)$#', '$1', $d['weight']); // remove trailing zeroes
				$d['volume'] = preg_replace('#(?:\\.0+|(\\.[0-9]*[1-9])0*)$#', '$1', $d['volume']); // remove trailing zeroes
			}
			$list[] = $d;
		}
		return $list;
	}
	
	public function Get_single_for_item($item_id, $attr_id) {
		$query_params = array();
		$query_params[] = $item_id;
		$query_params[] = $attr_id;
		$query = "SELECT *
			FROM {$this->db_prefix}shop_item_attributes a
			WHERE item_id = ? AND attribute_id = ? LIMIT 1";
		
		$r = $this->prepare($query);
		$s = $r->execute($query_params);
		if (!$s) return false;
		
		return $r->fetch();
	}
	
	public function Get_aggregate_data_for_category_products($id, $category_id = 0, $select = "count(*)", $where_s = '', $group_by = '', $fetch_all = false) {
		if (empty($select)) {
			$select = "count(*)";
		}
		$query_params = array();
		
		$published_flag_cond = $this->db->get_flag_query_condition(PC_shop_products::PF_PUBLISHED, $query_params, 'flags', 'p');
		$product_flag_conf = $this->db->get_flag_query_condition(self::ITEM_IS_PRODUCT, $query_params, 'flags', 'a');
		
		$query_params[] = $id;
		
		$join_s = " LEFT JOIN {$this->db_prefix}shop_products p ON p.id = a.item_id";
		
		if ($category_id) {
			if (is_array($category_id)) {
				if (!empty($category_id)) {
					$where_s .= ' AND p.category_id '.$this->sql_parser->in($category_id);
					$query_params = array_merge($query_params, $category_id);
				}
			}
			else {
				$where_s .= ' AND p.category_id = ?';
				$query_params[] = $category_id;
			}
			
		}
		
		$group_by_s = '';
		if (!empty($group_by)) {
			$group_by_s = ' GROUP BY ' . $group_by;
		}
		
		$query = "SELECT $select
			FROM {$this->db_prefix}shop_item_attributes a
			LEFT JOIN {$this->db_prefix}shop_attributes at ON at.id=a.attribute_id
			$join_s
			WHERE $published_flag_cond AND $product_flag_conf AND at.id = ?" . $where_s . $group_by_s;
		
		$r = $this->prepare($query);
		$s = $r->execute($query_params);
		if (!$s) return false;
		$fetchType = (strpos($select, ",") !== FALSE) ? PDO::FETCH_ASSOC : PDO::FETCH_COLUMN;
		if ($fetch_all) {
			return $r->fetchAll($fetchType);
		}
		
		return $r->fetch($fetchType);
	}
	
	public function Get_aggregate_data_for_page_categories($attribute_id, $page_id = 0, $select = "count(*)", $where_s = '', $fetch_all = false) {
		if (empty($select)) {
			$select = "count(*)";
		}
		$query_params = array();
		
		$published_flag_cond = $this->db->get_flag_query_condition(PC_shop_categories::CF_PUBLISHED, $query_params, 'flags', 'c');
		$category_attribute_flag_cond = $this->db->get_flag_query_condition(self::ITEM_IS_CATEGORY, $query_params, 'flags', 'a');
		
		$query_params[] = $attribute_id;
		
		$join_s = " LEFT JOIN {$this->db_prefix}shop_categories c ON c.id = a.item_id";
		
		if ($page_id) {
			$where_s .= ' AND c.pid = ?';
			$query_params[] = $page_id;
		}
		
		$query = "SELECT $select
			FROM {$this->db_prefix}shop_item_attributes a
			LEFT JOIN {$this->db_prefix}shop_attributes at ON at.id=a.attribute_id
			$join_s
			WHERE $published_flag_cond AND $category_attribute_flag_cond AND at.id = ?" . $where_s;
		
		$r = $this->prepare($query);
		$s = $r->execute($query_params);
		if (!$s) return false;
		
		if ($fetch_all) {
			return $r->fetchAll(PDO::FETCH_COLUMN);
		}
		
		return $r->fetchColumn();
	}
	
	public function Get_aggregate_data_for_category_children($attribute_id, $category_id = 0, $select = "count(*)", $where_s = '', $fetch_all = false) {
		if (empty($select)) {
			$select = "count(*)";
		}
		$query_params = array();
		
		$published_flag_cond = $this->db->get_flag_query_condition(PC_shop_categories::CF_PUBLISHED, $query_params, 'flags', 'c');
		$category_attribute_flag_cond = $this->db->get_flag_query_condition(self::ITEM_IS_CATEGORY, $query_params, 'flags', 'a');
		
		$query_params[] = $attribute_id;
		
		$join_s = " LEFT JOIN {$this->db_prefix}shop_categories c ON c.id = a.item_id";
		
		if ($category_id) {
			$where_s .= ' AND c.parent_id = ?';
			$query_params[] = $category_id;
		}
		
		$query = "SELECT $select
			FROM {$this->db_prefix}shop_item_attributes a
			LEFT JOIN {$this->db_prefix}shop_attributes at ON at.id=a.attribute_id
			$join_s
			WHERE $published_flag_cond AND $category_attribute_flag_cond AND at.id = ?" . $where_s;
		
		$r = $this->prepare($query);
		$s = $r->execute($query_params);
		if (!$s) return false;
		
		if ($fetch_all) {
			return $r->fetchAll(PDO::FETCH_COLUMN);
		}
		
		return $r->fetchColumn();
	}
        
	public function Assign_or_edit_for_item($itemId, $attributeId, $itemType=self::ITEM_IS_PRODUCT, $valueId=null, $value=null) {
		$attr_data = $this->Get_single_for_item($itemId, $attributeId);
		if ($attr_data && v($attr_data['id'])) {
			$this->Edit_for_item($attr_data['id'], $valueId, $value);
		}
		else {
			$this->Assign_to_item($itemId, $itemType, $attributeId, $valueId, $value);
		}
	}
	
	public function Assign_to_item($itemId, $itemType=self::ITEM_IS_PRODUCT, $attributeId, $valueId=null, $value=null, $nextAttributeId = null, $level = 1) {
		$query = "INSERT INTO {$this->db_prefix}shop_item_attributes (item_id,attribute_id,flags,value_id,value,next_attribute_id, level) VALUES(?,?,?,?,?,?,?)";
		$r = $this->prepare($query);
		$params = array($itemId, $attributeId ? $attributeId : 0, $itemType, $valueId, $value, $nextAttributeId, $level);
		$s = $r->execute($params);
		if (!$s) return false;
		return $this->db->lastInsertId($this->sql_parser->Get_sequence('shop_item_attributes'));
	}
	public function Edit_for_item($id, $valueId=null, $value=null) {
		$query = "UPDATE {$this->db_prefix}shop_item_attributes SET value_id=?, value=? WHERE id=?";
		$r = $this->prepare($query);
		$params = array($valueId, $value, $id);
		$s = $r->execute($params);
		return $s;
	}
	
	public function Edit_fields_for_item($id, $fields = array()) {
		$set_array = array_keys($fields);
		foreach ($set_array as $key => $value) {
			$set_array[$key] = $value . ' = ?';
		}
		$fields_s = implode(', ', $set_array);
		$query = "UPDATE {$this->db_prefix}shop_item_attributes SET " . $fields_s . " WHERE id=?";
		$r = $this->prepare($query);
		$params = array_values($fields);
		$params[] = $id;
		$s = $r->execute($params);
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
        if (empty($where))
			return false;

		$removeIds = array();
		$priceIdData = array();
		$nextIdList = array();

		// collect all root level attributes
		$r = $this->prepare($q = "SELECT id, item_id, attribute_id, value_id, next_attribute_id FROM {$this->db_prefix}shop_item_attributes WHERE " . implode(' and ', $where));
		if( !$r->execute($queryParams) )
			throw new DbException($r->errorInfo(), $q, $queryParams);
		while( $f = $r->fetch() ) {
			$removeIds[] = $f['id'];
			if( $f['next_attribute_id'] )
				$nextIdList[] = $f['next_attribute_id'];
			$priceIdData[] = array(
				'item_id' => $f['item_id'],
				'attribute_id' => $f['attribute_id'],
				'value_id' => $f['value_id'],
				'id' => $f['id'],
			);
		}

		// collect all attributes in chain with found root attributes
		while( !empty($nextIdList) ) {
			$removeIds = array_merge($removeIds, $nextIdList);
			$r = $this->prepare($q = "SELECT next_attribute_id FROM {$this->db_prefix}shop_item_attributes WHERE id " . $this->sql_parser->in($nextIdList));
			if( !$r->execute($nextIdList) )
				throw new DbException($r->errorInfo(), $q);
			$nextIdList = array();
			while( $f = $r->fetch() ) {
				if( $f['next_attribute_id'] )
					$nextIdList[] = $f['next_attribute_id'];
			}
		}

		// remove all collected attributes and prices associated with attribute chains
		if( !empty($removeIds) ) {
			$r = $this->prepare($q = "DELETE FROM {$this->db_prefix}shop_product_prices WHERE product_id = :item_id AND attribute_id = :attribute_id AND ((attribute_value_id <> 0 AND attribute_value_id = :value_id) OR (attribute_item_id <> 0 AND attribute_item_id = :id))");
			foreach( $priceIdData as $params ) {
				if( !$r->execute($params) )
					throw new DbException($r->errorInfo(), $q, $params);
			}

			$r = $this->prepare($q = "DELETE FROM {$this->db_prefix}shop_item_attributes WHERE id " . $this->sql_parser->in($removeIds));
			if( !$r->execute($removeIds) )
				throw new DbException($r->errorInfo(), $q);
		}

		return true;
	}
	public function Save_for_item($itemId, $itemType=self::ITEM_IS_PRODUCT, $data, $insert = false) {
		if (!is_array($data)) return false;
		if (count(v($data['save'], array()))) foreach ($data['save'] as $i) {

			// Save attribute combination.
			$prev_attribute_item_id = null;
			for( $idx = PC_shop::$COMBINATION_ATTRIBUTE_COUNT; $idx >= 1; $idx-- ) {
				$suffix = ($idx > 1) ? $idx : '';
				if ($i['id' . $suffix] == 0) {
					$prev_attribute_item_id = $this->Assign_to_item($itemId, $itemType, $i['attribute' . $suffix . '_id'], $i['value' . $suffix . '_id'], $i['value' . $suffix . ''], $prev_attribute_item_id, $idx);
				} else {
					$prev_attribute_item_id = $i['id' . $suffix];
					$this->Edit_for_item($i['id' . $suffix], $i['value' . $suffix . '_id'], $i['value' . $suffix]);
				}
			}

			if ($itemType == self::ITEM_IS_PRODUCT && (isset($i['price']) || isset($i['price_diff']))) {
				$product_price_model = $this->core->Get_object('PC_shop_product_price_model');
//				if ($i['value_id']) {
                // Bug. Updates multiple items
//					$key_fields = array(
//						'product_id' => $itemId,
//						'attribute_id' => $i['attribute_id'],
//						'attribute_value_id' => $i['value_id'],
//					);
//				}
//				else {
					$key_fields = array(
						'product_id' => $itemId,
						'attribute_id' => $i['attribute_id'],
						'attribute_item_id' => $prev_attribute_item_id,
					);
//				}

				if (false and v($i['price'], 0) == 0 and v($i['price_diff'], 0) == 0) {
					$product_price_model->delete(array(
						'where' => $key_fields
					));
				}
				else {
					$existing_price_data = $product_price_model->get_one(array(
						'where' => $key_fields
					));
					$other_fields  = array(
						'price' => $i['price'],
						'price_diff' => $i['price_diff'],
						'items_left' => v($i['items_left'], null),
						'info_1' => v($i['info_1']),
						'info_2' => v($i['info_2']),
						'info_3' => v($i['info_3']),
						'weight' => v($i['weight']),
						'volume' => v($i['volume']),
						'length' => v($i['length']),
						'width' => v($i['width']),
						'height' => v($i['height']),
					);
					if (trim($other_fields['items_left']) == '') {
						$other_fields['items_left'] = null;
					}
					if (v($i['price']) and $i['price'] > 0 or v($i['price_diff']) and $i['price_diff'] > 0) {
						if (v($i['discount']) and $i['discount'] > 0) {
							$other_fields['discount'] = $i['discount'];
						}
						else {
							$other_fields['discount'] = null;
						}
					}
					else {
						$other_fields['discount'] = null;
					}
					if ($existing_price_data) {

						$product_price_model->update(
							$other_fields,
							array(
								'where' => array(
									'id' => $existing_price_data['id'],
							)
					));
					}
					else {
						$product_price_model->insert(
							array_merge($other_fields, $key_fields)
						);
					}
				}
			}
		}
		if (count(v($data['remove'], array())))
			$this->Remove_from_item($data['remove']);

		$position = 0;
		if (count(v($data['positions'], array()))) foreach ($data['positions'] as $id) {
			$position++;
			$this->Edit_fields_for_item($id, array('position' => $position));
		}
		
		return true;
	}
	public function Get_suggestions($id, $limit = 0) {
		$limit_s = '';
		if ($limit > 0) {
			$limit_s = ' LIMIT ' . $limit;
		}
		$query = "SELECT value FROM {$this->db_prefix}shop_item_attributes WHERE attribute_id=?" . $limit_s;
		$r = $this->prepare($query);
		$query_params = array($id);
		$s = $r->execute($query_params);
		if (!$s) {
			return false;
		}
		$list = array();
		$limit_left = $limit;
		while ($d = $r->fetchColumn() or $d === null) {
			$limit_left--;
			if ($limit > 0 and $limit_left == -1) {
				break;
			}
			if (is_null($d)) continue;
			if (in_array($d, $list)) continue;
			$list[] = $d;
		}
		return $list;
	}
	public function Find($name, $isCategoryAttribute=false) {
		$r = $this->prepare("SELECT * FROM {$this->db_prefix}shop_attribute_contents c LEFT JOIN {$this->db_prefix}shop_attributes a ON a.id=c.attribute_id WHERE c.name ".$this->sql_parser->like("?")." and is_category_attribute=? GROUP BY a.id");
		$s = $r->execute(array($name, $isCategoryAttribute));
		if (!$s) return false;
		if (!$r->rowCount()) return false;
		return $r->fetchAll();
	}
}
class PC_shop_orders extends PC_shop_order_model {
	/**
	 *
	 * @var PC_shop
	 */
	private $shop;
	
	/**
	 * 
	 * @param PC_shop $shop
	 */
	public function Init($id = 0) {
		$this->shop = $id;
		$this->user = $this->core->Get_object('PC_user');

		if( $id instanceof PC_shop_site ) {
			// ensure payment and delivery options are initially selected
			$orderData = $this->Get_preserved_order_data();
			$update = false;
			if (!is_array($orderData)) {
				$orderData = array();
				$update = true;
			}
			if (!isset($orderData['delivery_option'])) {
				$options = $this->shop->getDeliveryOptions();
				$orderData['delivery_option'] = key($options);
				$update = true;
			}
			if (!isset($orderData['payment_option'])) {
				$options = $this->shop->getPaymentOptions();
				$orderData['payment_option'] = key($options);
				$update = true;
			}
			if ($update)
				$this->Preserve_order_data($orderData);
		}
	}
	public function Get($id=null, &$params=array()) {
		$this->core->Init_params($params);
		$queryParams = array();
		$where = array();
		$limit = '';
		$returnOne = false;
		if (!is_null($id)) {
			if (is_array($id)) {
				$where[] = 'o.id '.$this->sql_parser->in($id);
				$queryParams = array_merge($queryParams, $id);
			}
			else {
				$returnOne = true;
				$queryParams[] = $id;
				$where[] = 'o.id=?';
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
		
		
		if (isset($params->scope)) if (is_array($params->scope)) if (count($params->scope)) {
			$where = array_merge($where, $params->scope['where']);
			$queryParams = array_merge($queryParams, $params->scope['query_params']);
		}
		
		$order_s = '';
		if (isset($params->order)) {
			$order_s = ' ORDER BY ' . $params->order;
			if (isset($params->order_dir)) {
				$order_s .= ' ' . $params->order_dir;
			}
		}
		
		$query = "SELECT "
		.($params->Has_paging()?'SQL_CALC_FOUND_ROWS ':'')."o.*, coupons.code AS coupon_code FROM {$this->db_prefix}shop_orders o"
		." LEFT JOIN {$this->db_prefix}shop_coupons coupons ON coupons.id=o.coupon_id"
		.(count($where)?' WHERE '.implode(' and ', $where):''). $order_s . ' ' . $limit;

		$r = $this->prepare($query);
		$s = $r->execute($queryParams);
		if (!$s) return false;
		if ($params->Has_paging()) {
			$rTotal = $this->query("SELECT FOUND_ROWS()");
			if ($rTotal) $params->paging->Set_total($rTotal->fetchColumn());
		}
		$list = array();
		while ($d = $r->fetch()) {
			$d['items'] = $this->Get_items($d['id']);
			if (!empty($d['data'])) {
				$data = array();
				$ser_data = unserialize($d['data']);
				foreach ($ser_data as $name => $value) {
					$raw_value = $value;
					$d_name = $this->core->Get_variable('pc_shop_order_data_' . $name);
					if (empty($d_name)) {
						$d_name = $this->core->Get_variable('pc_shop_order_' . $name);
					}
					if (empty($d_name)) {
						$d_name = $this->core->Get_variable('order_' . $name, null, 'pc_shop');
					}
					if (empty($d_name)) {
						$d_name = $name;
					}
					if (in_array($name, array('is_company'))) {
						if ($value) {
							$value = $this->core->Get_variable('yes');
						}
						else {
							$value = $this->core->Get_variable('no');
						}
					}
					$data[] = array(
						'key' => $name,
						'name' => $d_name,
						'value' => $value,
						'raw_value' => $raw_value
					);
				}
				$d['data'] = $data;
			}
			else {
				$d['data'] = array();
			}
			$list[] = $d;
		}
		if ($returnOne) {
			if (!count($list)) return false;
			return $list[0];
		}
		else return $list;
	}
	public function Get_items($orderId) {
		$r = $this->prepare($q = "SELECT oi.*, p.external_id FROM {$this->db_prefix}shop_order_items oi
			LEFT JOIN {$this->db_prefix}shop_products p ON p.id = oi.product_id
			WHERE order_id=?");
		if( !$r->execute($p = array($orderId)) )
			throw new DbException($r->errorInfo(), $q, $p);
		if (!$r->rowCount()) return array();
		$list = array();
		$product_ids = array();
		while ($product = $r->fetch()) {
			$product_ids[] = $product['product_id'];
			$list[] = array(
				'id'=> $product['product_id'],
				'external_id'=> $product['external_id'],
				'quantity'=> $product['quantity'],
				'price'=> $product['price'],
				'attributes' => PC_utils::string_to_array($product['attributes'])
			);
		}
		unset($product);
		$product_ids = array_unique($product_ids);
		//fill product data
		//$products = $this->shop->products->Get($product_ids);
		$shop_site = $this->core->Get_object('PC_shop_site');
		$shop_products_params = array(
			'id_keys' => true
		);
		$shop_site_products = $shop_site->products->Get($product_ids, null, $shop_products_params);
		//print_pre($products);
		//print_pre($shop_site_products);
		foreach ($list as $key => &$list_item) {
			//$product = 
			if (!isset($shop_site_products[$list_item['id']])) {
				continue;
			}
			$list[$key]['name'] = $shop_site_products[$list_item['id']]['name'];
			$price = $shop_site->products->get_price($shop_site_products[$list_item['id']]);
			$price_data = $shop_site->products->adjust_price($price, $shop_site_products[$list_item['id']], $list_item['attributes']);
			//print_pre($price_data);
			if (!empty($price_data['attributes_string'])) {
				$list[$key]['name'] .= ' (' . $price_data['attributes_string'] . ')';
			}
			$list[$key]['short_description'] = $shop_site_products[$list_item['id']]['short_description'];
			continue;
			if (isset($product['contents'])) {
				$list[$product['id']]['name'] = v($product['contents'][$this->site->ln]['name']);
				$list[$product['id']]['short_description'] = v($product['contents'][$this->site->ln]['short_description']);
			}
			else {
				$list[$product['id']]['name'] = v($product['name']);
				$list[$product['id']]['short_description'] = v($product['short_description']);
			}
		}
		return array_values($list);
	}
	
	public function Set_is_paid($orderId) {
		$s = $this->update(array('is_paid' => 1), array(
			'where' => 'id = ?',
			'query_params' => array($orderId
			)
		));
		return (bool)$s;
	}
	
	public function Assign_status($orderId, $status) {
		$s = $this->update(array('status' => $status), array(
			'where' => 'id = ?',
			'query_params' => array($orderId
			)
		));
		return (bool)$s;
	}
	public function Assign_status_old($orderId, $status, $userId = 0) {
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
	public function Confirm($id, $userId = 0) {
		return $this->Assign_status($id, 'confirmed', $userId);
	}
	public function Unconfirm($id) {
		return $this->Delete_status(null, $id, 'confirmed');
	}
	public function Delete($params = array()) {
		$id = $params;
		$rOrder = $this->prepare("DELETE FROM {$this->db_prefix}shop_orders WHERE id=?");
		$s = $rOrder->execute(array($id));
		if (!$s) return false;
		$rItems = $this->prepare("DELETE FROM {$this->db_prefix}shop_order_items WHERE order_id=?");
		$rItems->execute(array($id));
		$rStatuses = $this->prepare("DELETE FROM {$this->db_prefix}shop_order_statuses WHERE order_id=?");
		$rStatuses->execute(array($id));
		return true;
	}
	
	public function Preserve_order_data($order_data = null) {
		$current_order_data = isset($_SESSION['pc_shop']['order']) ? $_SESSION['pc_shop']['order'] : array();
		if (is_null($order_data) or !is_array($order_data)) {
			if (isset($_POST['order']))
				$order_data = $_POST['order'];
		}
		if (is_array($order_data)) {
			$current_order_data = array_merge($current_order_data, $order_data);

			if( isset($current_order_data['coupon']) ) {
				if( empty($current_order_data['coupon']) ) {
					$this->shop->cart->cancel_coupon();
				}
				else {
					$coupon_model = $this->core->Get_object('PC_shop_coupon_model');
					$coupon_data = $coupon_model->get_valid_coupon($current_order_data['coupon']);
					if ($coupon_data)
						$this->shop->cart->set_coupon_data($coupon_data);
				}
				unset($current_order_data['coupon']);
			}

			$_SESSION['pc_shop']['order'] = $current_order_data;
		}
		return $current_order_data;
	}
	
	public function Get_preserved_order_data() {
		if (isset($_SESSION['pc_shop']) and isset($_SESSION['pc_shop']['order']) and is_array($_SESSION['pc_shop']['order'])) {
			return pc_e($_SESSION['pc_shop']['order']);
		} 
		return false;
	}
	
	public function Clear_preserved_order_data() {
		if (isset($_SESSION['pc_shop']) and isset($_SESSION['pc_shop']['order'])) {
			unset($_SESSION['pc_shop']['order']);
		} 
	}
	
	public function Create($userId=null, $recipient, $address, $phone, $email, $comment=null, &$params=array(), $clearCart=true, $payment_option = 0, $delivery_option = 0, $is_paid = 0, $data = array()) {
		$this->last_order_id = 0;
		$this->core->Init_params($params);
		if (!isset($_SESSION['pc_shop']['cart'])) {
			$params->errors->Add('empty_cart', 'The cart is empty');
			return false;
		}
		if (!count($_SESSION['pc_shop']['cart'])) {
			$params->errors->Add('empty_cart', 'The cart is empty');
			return false;
		}
		$data = pc_sanitize_value($data, 'strip_tags');
		$this->data = $data;
		$data = serialize($data);

		$this->db->beginTransaction();
		try {
			$insert_query = "INSERT INTO {$this->db_prefix}shop_orders (date,user_id,name,email,address,phone,comment,payment_option,delivery_option,currency,is_paid,data) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";
			$r = $this->prepare($insert_query);
			$insert_params = array(time(), $userId, $recipient, $email, $address, $phone, $comment, $payment_option, $delivery_option, $this->shop->price->get_user_currency(), $is_paid);
			$insert_params = pc_sanitize_value($insert_params, 'strip_tags');
			$insert_params[] = $data;
			$s = $r->execute($insert_params);
			if (!$s) {
				$params->errors->Add('insert_into_database', 'Error while trying to insert record into the database');
				return false;
			}

			$this->last_order_id = $orderId = $this->db->lastInsertId($this->sql_parser->Get_sequence('shop_orders'));
			$insert_item_query = "INSERT INTO {$this->db_prefix}shop_order_items (order_id,product_id,quantity,attributes,price) VALUES(?,?,?,?,?)";
			$rInsertItem = $this->prepare($insert_item_query);

			$totalPrice = 0;
			$eligible_discount = 0;
			$order_items_model = $this->core->Get_object('PC_shop_order_item_model');
			$order_items_model->delete(array(
				'where' => array(
					'order_id' => $orderId
				)
			));

			$preserved_coupon_data = $this->shop->cart->get_preserved_coupon_data(true);

			$shop_cart_data = $this->shop->cart->Get();

			$cart_data = array(
				'products' => $shop_cart_data['products'],
				'items' => array()
			);

			//foreach ($_SESSION['pc_shop']['cart']['items'] as $ciid => $product_basket_data) {
			foreach ($shop_cart_data['items'] as $ciid => $product_basket_data) {
				$productId = $product_basket_data['id'];
				$quantity = $product_basket_data['basket_quantity'];
				$product = $this->shop->products->Get($productId);
				if (!$product) {
					$params->errors->Add('unknown_product', 'Unknown product in the cart');
					$this->db->rollBack();
					return false;
				}
				else {
					$price = $this->shop->products->get_price($product, $discount, $discount_percentage, $product_basket_data['attributes']);
					$insert_item_query_params = array($orderId, $productId, $quantity, PC_utils::array_to_string($product_basket_data['attributes']), $price);
					$s = $rInsertItem->execute($insert_item_query_params);
					if (!$s) {
						$params->errors->Add('insert_order_item', '');
						$this->db->rollBack();
						return false;
					}
					$total_item_price = $price * $quantity;
					$totalPrice += $total_item_price;
					$cart_data['items'][$ciid] = $product_basket_data;

					$e_discount = $this->shop->products->get_eligible_coupon_discount($preserved_coupon_data, $total_item_price, $product);
					if( $preserved_coupon_data['percentage_discount'] <= 0 && $e_discount > 0 )
						$preserved_coupon_data['discount'] -= $e_discount;
					$eligible_discount += $e_discount;
				}
			}

			$cart_data['discounts'] = array();

			$cart_data['totalPrice'] = $totalPrice;
			$cart_data['eligible_discount'] = number_format($eligible_discount, 2, ".", "");

			$coupon_id = null;
			if ($preserved_coupon_data and v($preserved_coupon_data['id'])) {
				$coupon_id = $preserved_coupon_data['id'];
				$r = $this->prepare($q = "UPDATE {$this->db_prefix}shop_coupons SET used = used + 1 WHERE id = ?");
				if( !$r->execute($p = array($coupon_id)) )
					throw new DbException($r->errorInfo(), $q, $p);
			}

			$this->shop->cart->Calculate_prices($cart_data, array(), $preserved_coupon_data);
			//set total price
			$rPrice = $this->prepare("UPDATE {$this->db_prefix}shop_orders SET delivery_price = ?, cod_price = ? , total_price = ?, coupon_id = ?, discount = ? WHERE id=?");
			$s = $rPrice->execute(array(v($cart_data['order_delivery_price'], 0), v($cart_data['order_cod_price'], 0), v($cart_data['order_full_price'], $totalPrice), $coupon_id, v($cart_data['total_discount'], 0), $orderId));
			if( !$s ) {
				$this->db->rollBack();
				$params->errors->Add('update_in_database', 'Error while trying to update order record in the database');
				return false;
			}
			$this->Assign_status($orderId, PC_shop_order_model::STATUS_NEW);
			$this->db->commit();
		}
		catch(Exception $ex) {
			$this->db->rollBack();
			throw $ex;
		}

		$this->shop->cart->Clear();
		$this->Clear_preserved_order_data();
		$this->shop->cart->cancel_coupon();

		return true;
	}
	//order statuses: isPaid, isConfirmed, isSent, 
}
class PC_shop_cart extends PC_base {
	/**
	 *
	 * @var PC_shop_site
	 */
	private $shop;
	public function Init(PC_shop $shop) {
		$this->shop = $shop;
		if (!isset($_SESSION['pc_shop']) || !is_array($_SESSION['pc_shop']))
			$_SESSION['pc_shop'] = Array();
		if (!isset($_SESSION['pc_shop']['cart']) || !is_array($_SESSION['pc_shop']['cart']))
			$this->Clear();
	}
	
	public function set_coupon_data($coupon_data) {
		$_SESSION['pc_shop']['coupon'] = $coupon_data;
	}
	
	public function cancel_coupon() {
		if (isset($_SESSION['pc_shop']['coupon'])) {
			unset($_SESSION['pc_shop']['coupon']);
		}
	}
	
	public function get_preserved_coupon_data($refresh = false) {
		if (isset($_SESSION['pc_shop']['coupon'])) {
			$shop_price = $this->core->Get_object('PC_shop_price');
			$user_currency_id = $shop_price->get_user_currency_id();
			if ($user_currency_id != v($_SESSION['pc_shop']['coupon']['_currency'])) {
				$refresh = true;
			}
			if ($refresh) {
				$coupon_model = $this->core->Get_object('PC_shop_coupon_model');
				$coupon_data = $coupon_model->get_valid_coupon($_SESSION['pc_shop']['coupon']['code']);
				if (!$coupon_data) {
					$this->cancel_coupon();
					return false;
				}
				$this->set_coupon_data($coupon_data);
				return $coupon_data;
			}
			return $_SESSION['pc_shop']['coupon'];
		}
		return false;
	}
	
	/**
	 * @param array $data An associative array that is filled in with calculated values. The only key that is required to be in the array is 'totalPrice' - total price of items.
	 * - float 'totalPrice': Price of all items in the order.
	 * - float 'eligible_discount': (optional) A coupon discount amount (not percent) that must be applied.
	 * - float 'delivery_price': (optional) Price of the delivery. This field is replaced by calculated value.
	 * - float 'cod_price': (optional) "Cash On Delivery", or in other words how much is added to the price due to the payment in cash when using courier delivery service. This field is replaced by calculated value.
	 * - float 'full_price': (optional) Total price (items + delivery + COD). This field is replaced by calculated value.
	 * - float 'order_delivery_price': (optional) Price of the delivery. This field is replaced by calculated value.
	 * - float 'order_cod_price': (optional) "Cash On Delivery", or in other words how much is added to the price due to the payment in cash when using courier delivery service. This field is replaced by calculated value.
	 * - float 'order_full_price': (optional) Total price (items + delivery + COD). It is recalculated after invoking 'pc_shop/cart/calculate_prices' event from 'order_delivery_price', 'order_cod_price' and 'discounts'. This field is replaced by calculated value.
	 * - float[] 'discounts': (optional) An associative array of applied discounts. This field is replaced by calculated values and may contain 'eligible_discount'.
	 * - string[] 'errors': (optional) A list of errors that happened while trying to calculate the price. If the array is not empty then the final price is most probably incorrect.
	 * - array 'delivery_info': (optional) Information filled in by delivery plugins. All array elements are optional and may not be filled in by plugins.
	 *   - int 'period_min': (optional) Minimum number of days for delivery.
	 *   - int 'period_max': (optional) Maximum number of days for delivery.
	 *   - string 'date_min': (optional) Minimum date of delivery.
	 *   - string 'date_max': (optional) Maximum date of delivery.
	 *   - float 'cod_limit': (optional) Maximum price that may be paid in cash to the courier upon delivery.
	 * @param array $default_order_data
	 * @param array $coupon_data
	 */
	public function Calculate_prices(&$data, $default_order_data = array(), $coupon_data = array()) {
		$data['errors'] = array();
		$data['delivery_info'] = array();

		$order_data = $this->shop->orders->Get_preserved_order_data();
		
		if (!$order_data)
			$order_data = $default_order_data;

		if( !isset($order_data['payment_option']) )
			$order_data['payment_option'] = null;

		//$delivery_price = v($this->cfg['pc_shop']['delivery_price'], 0);
		$delivery_price = 0;
		$cod_price = 0;
		$delivery_option_data = false;

		/** @var PC_shop_price_model $price_model */
		$price_model = $this->core->Get_object('PC_shop_price_model');
		
		if (isset($order_data['delivery_option'])) {
			$delivery_option_model = new PC_shop_delivery_option_model();
			$delivery_option_data = $delivery_option_model->get_one(array(
				'where' => array(
					'code' => $order_data['delivery_option'],
					'enabled' => 1
				)
			));
		}
		else {
			$order_data['delivery_option'] = null;
		}
		
		$items_price = $data['totalPrice'];

		$amount_for_free_delivery = 0;

		if ($delivery_option_data) {
			$delivery_price = $price_model->get_price('delivery_' . $order_data['delivery_option']);
			if (empty($delivery_price)) {
				$delivery_price = 0;
			}
			$amount_for_free_delivery = $price_model->get_price('free_delivery_from_' . $order_data['delivery_option']);
			if ($amount_for_free_delivery > 0 and $items_price >= $amount_for_free_delivery) {
				$delivery_price = 0;
			}
			
			if (v($order_data['payment_option']) == PC_shop_payment_option_model::CASH) {
				$cod_price = $price_model->get_price('cod_' . $order_data['delivery_option']);
				$no_cod_price_from = $price_model->get_price('free_cod_from_' . $order_data['delivery_option']);
				if ($no_cod_price_from > 0 and $items_price >= $no_cod_price_from) {
					$cod_price = 0;
				}
			}
		}

		$data['order_delivery_price'] = $data['delivery_price'] = $delivery_price;
		$data['order_cod_price'] = $data['cod_price'] = $cod_price;
		
		$total_price = $items_price + $delivery_price + $cod_price;
		$data['order_full_price'] = $data['full_price'] = $total_price;

		$data['discounts'] = array();

		$form_data = isset($order_data['delivery_form_data'][$order_data['delivery_option']]) ? $order_data['delivery_form_data'][$order_data['delivery_option']] : array();

		$eventArgs = array(
			'data'=> &$data,
			'order_data' => $order_data,
			'coupon_data' => $coupon_data,
			'delivery_option_data' => $delivery_option_data,
			'delivery_form_data' => $form_data,
		);

		if( $delivery_option_data ) {
			if( $amount_for_free_delivery <= 0 || $items_price < $amount_for_free_delivery )
				$this->core->Init_callback($delivery_option_data['code'] . '.calculateDeliveryPrice', $eventArgs);
		}

		$this->core->Init_hooks('pc_shop/cart/calculate_prices', $eventArgs);

		$data['total_discount'] = 0;
		
		if (v($data['eligible_discount'])) {
			$coupon_discount = $data['eligible_discount'];
			$max_coupon_discount = $data['totalPrice'] * v($this->cfg['pc_shop']['max_coupon_percentage'], 25) / 100;
			$max_coupon_discount = round($max_coupon_discount, 2);
			if ($coupon_discount > $max_coupon_discount) {
				$coupon_discount = $max_coupon_discount;
			}
			
			$data['discounts']['coupon'] = $coupon_discount;
		}
		
		foreach ($data['discounts'] as &$discount) {
			if (!is_array($discount)) {
				$data['total_discount'] += $discount;
			}
			else {
				if (isset($discount['percent'])) {
					if ($discount['percent'] < 0) {
						$discount['percent'] = 0;
					}
					if ($discount['percent'] > 100) {
						$discount['percent'] = 100;
					}
					$discount['amount'] = floor(($items_price - $data['total_discount']) * $discount['percent']) / 100;
				}
				if (isset($discount['amount'])) {
					$data['total_discount'] += $discount['amount'];
				}
			}
		}
		
		$total_price = $items_price + $data['order_delivery_price'] + $data['order_cod_price'] - $data['total_discount'];
		$data['order_full_price'] = $total_price;

		$data['order_delivery_prices'] = $data['order_cod_price'] + $data['order_delivery_price'];

		if( isset($order_data['payment_option'], $data['delivery_info']['cod_limit'])
			&& $order_data['payment_option'] == PC_shop_payment_option_model::CASH
			&& $data['delivery_info']['cod_limit'] < $data['order_full_price']
		)
			$data['errors'][] = $this->core->Get_variable('order_total_exceeds_cod_limit', null, 'pc_shop');

		// format numbers
		$data['total_discount'] = number_format($data['total_discount'], 2, ".", "");
		
		$data['cod_price'] = number_format($data['cod_price'], 2, ".", "");
		$data['delivery_price'] = number_format($data['delivery_price'], 2, ".", "");
		$data['full_price'] = number_format($data['full_price'], 2, ".", "");
		
		$data['order_cod_price'] = number_format($data['order_cod_price'], 2, ".", "");
		$data['order_delivery_price'] = number_format($data['order_delivery_price'], 2, ".", "");
		$data['order_full_price'] = number_format($data['order_full_price'], 2, ".", "");

		if( !empty($data['errors']) )
			$data['errors'] = '<ul><li>' . implode('</li><li>', $data['errors']) . '</li></ul>';
		else
			$data['errors'] = '';
	}
	
	/**
	 * 
	 * @param bool|array $raw
	 * @param array $params
	 * @return array
	 */
	public function Get($raw=false, &$params = array()) {
		$my_params = $params;
		if (!is_array($raw) and $raw) {
			return $_SESSION['pc_shop']['cart'];
		}

		$d = array(
			'total'=> 0,
			'totalQuantity'=> $_SESSION['pc_shop']['cart']['totalQuantity'],
			'items'=> array(),
			'totalPrice' => 0,
			'totalFullPrice' => 0,
			'totalFullPriceNoPromos' => 0,
		);
		$paging_params = false;
		if (isset($my_params['paging'])) {
			$paging_params = $my_params['paging'];
			unset($my_params['paging']);
		}
		$my_params['full_links'] = true;
		$product_ids = array_keys($_SESSION['pc_shop']['cart']["productIndex"]);
		$product_ids = array_unique($product_ids);
		$productList = $this->shop->products->Get($product_ids, null, $my_params);
		$params = $my_params;
		$products = Array();
		foreach( $productList as &$p )
			$products[$p["id"]] = &$p;
		
		
		$preserved_coupon_data = $this->get_preserved_coupon_data();
		
		$amount_eligible_for_coupon = 0;
		
		foreach ($_SESSION['pc_shop']['cart']['items'] as $ciid => $cartItemInfo) {
			$d['total']++;
			$p = &$products[$cartItemInfo[0]];
			$item_regular_price = $item_price = $this->shop->products->get_price($p);
			$item_price_data = $this->shop->products->adjust_price($item_price, $p, $cartItemInfo[3]);
			//print_pre($item_price_data);
			$item_price = $item_price_data['price'];
			$item_full_price = $item_price_data['full_price'];
			$item_total_price = $item_price * $cartItemInfo[1];
			$item_total_full_price = $item_full_price * $cartItemInfo[1];

			$cart_item = array(
				'id' => $cartItemInfo[0],
				'product_id' => $cartItemInfo[0],
				'basket_quantity' => $cartItemInfo[1],
				'attributes' => $cartItemInfo[3],
				'price' => $item_price,
				'fullPrice' => $item_full_price,
				'price_data' => $item_price_data,
				'total_price' => $item_total_price,
				'totalPrice' => $item_total_price,
				'totalFullPrice' => $item_total_full_price,
				'name' => $products[$cartItemInfo[0]]['name'],
				'link' => $products[$cartItemInfo[0]]['link']
			);

			$eligible_discount = $this->shop->products->get_eligible_coupon_discount($preserved_coupon_data, $item_total_price, $p);
			if( $preserved_coupon_data['percentage_discount'] <= 0 && $eligible_discount > 0 )
				$preserved_coupon_data['discount'] -= $eligible_discount;
			$amount_eligible_for_coupon += $eligible_discount;

			$cart_item['original_name'] = $cart_item['name'];
			if (v($cart_item['price_data']['attributes_string'])) {
				$cart_item['name'] .= ' (' . $cart_item['price_data']['attributes_string'] . ')';
			}
			//$cart_item['attributes_info'] = $this->shop->products->get_attributes_info($p, $cart_item['attributes']);
			//$cart_item['attributes_string'] = $attributes_string;
			$d['items'][$ciid] = $cart_item;
			// $d['totalPrice'] += $p['totalPrice'] = $shop->products->Get_price($cartItemInfo[0], $cartItemInfo[1]);
			$d['totalPrice'] += $item_total_price;
			$d['totalFullPrice'] += $item_total_full_price;
			if( $item_total_full_price == $item_total_price )
				$d['totalFullPriceNoPromos'] += $item_total_full_price;
		}
		$d['products'] = $products;
		$d['totalPrice'] = number_format($d['totalPrice'], 2, ".", "");
		$d['totalFullPrice'] = number_format($d['totalFullPrice'], 2, ".", "");
		$d['totalFullPriceNoPromos'] = number_format($d['totalFullPriceNoPromos'], 2, ".", "");
		$d['eligible_discount'] = number_format($amount_eligible_for_coupon, 2, ".", "");
		$default_order_data = array();
		if (is_array($raw)) {
			$default_order_data = $raw;
		}
		$this->Calculate_prices($d, $default_order_data, $preserved_coupon_data);
		return $d;
	}
	/** Finds a product entry CIID by productId and attributes */
	public function Find($productId, $attributes = null) {
		foreach ($_SESSION['pc_shop']['cart']['items'] as $key => $item) {
			if ($productId == $item[0]) {
				$diff1 = array_diff_assoc($attributes, $item[3]);
				$diff2 = array_diff_assoc($item[3], $attributes);
				if ( empty($diff1) && empty($diff2) )
					return $key;
			}
		}
		return null;
	}
	
	/**
	 * 
	 * @param int $productId
	 * @param int $quantity
	 * @param array $attributes
	 * @param bool $do_not_increment
	 * @return bool|int
	 */
	public function Add($productId, $quantity=1, $attributes=null, $do_not_increment = false) {
		$this->product_was_added = false;
		if (!$this->shop->products->Exists($productId)) return false;
		if (!isset($_SESSION['pc_shop']['cart']['productIndex'][$productId]))
			$_SESSION['pc_shop']['cart']['productIndex'][$productId] = Array();
		$quantity = intval($quantity);
		if( !is_array($attributes) )
			$attributes = array();
		$ciid = $this->Find($productId, $attributes);
		if( is_null($ciid) ) {
			// Product is not in the cart yet so add it as a new item

			// TODO: add "infinite" available quantity
			$available_quantity = $this->shop->products->Get_product_quantity($productId, $attributes);
			if ($available_quantity !== null && !$available_quantity)
				return 0;
			if( $quantity < 1 )
				return 0; // no need to add it
			
			$ciid = $_SESSION['pc_shop']['cart']['nextCIID']++;
			
			// We just need to store CIID (cart item id). Storing value into key
			// will give us faster search and removal in Remove method.
			$_SESSION['pc_shop']['cart']['productIndex'][$productId][$ciid] = 1;
			
			// At the moment only productId, quantity and attributes are needed for item
			// entries in the cart.
			$_SESSION['pc_shop']['cart']['items'][$ciid] = Array(
				$productId,
				0,
				$available_quantity,
				$attributes
			);
			$qty = $this->_SetQuantity($ciid, $quantity);
			$this->product_was_added = true;
		}
		elseif (!$do_not_increment) {
			$old_qty = $_SESSION['pc_shop']['cart']['totalQuantity'];
			$qty = $this->AddAt($ciid, $quantity);
			$new_qty = $_SESSION['pc_shop']['cart']['totalQuantity'];
			if ($new_qty > $old_qty) {
				$this->product_was_added = true;
			}
		}
		else {
			return false;
		}
					
		
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
		$this->product_was_added = false;
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
		if (!isset($_SESSION['pc_shop']['cart']['items'][$ciid])) {
			return 0;
		}
			
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
		$available_quantity = $_SESSION['pc_shop']['cart']['items'][$ciid][2];
		$quantity = max($quantity, 0);
		$quantity = ($available_quantity === null) ? $quantity : min($_SESSION['pc_shop']['cart']['items'][$ciid][2], $quantity);
		$_SESSION['pc_shop']['cart']['items'][$ciid][1] = $quantity;
		if( $quantity == 0 ) {
			$productId = $_SESSION['pc_shop']['cart']['items'][$ciid][0];
			unset(
				$_SESSION['pc_shop']['cart']['items'][$ciid],
				$_SESSION['pc_shop']['cart']['productIndex'][$productId][$ciid]
			);
			if( empty($_SESSION['pc_shop']['cart']['productIndex'][$productId]) )
				unset($_SESSION['pc_shop']['cart']['productIndex'][$productId]);
		}
		$_SESSION['pc_shop']['cart']['totalQuantity'] += $quantity - $old_qty;
		return $quantity;
	}
	
	/**
	 * Method used to remove item from cart
	 * @param type $ciid
	 * @param type $quantity
	 * @return int
	 */
	public function Remove($ciid, $quantity=0) {
		if (!isset($_SESSION['pc_shop']['cart']['items'][$ciid]))
			return 0;
		if ($quantity != 0) {
			return $this->AddAt($ciid, -$quantity);
		}
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
	public function Is_empty() {
		if (!isset($_SESSION['pc_shop']) or !isset($_SESSION['pc_shop']['cart']) or !isset($_SESSION['pc_shop']['cart']['items'])) {
			return true;
		}
		return empty($_SESSION['pc_shop']['cart']['items']);
	}
	
	public function Order() {
		//
	}
	public function Count($uniqueItemsOnly=true) {
		return $uniqueItemsOnly ? count($_SESSION['pc_shop']['cart']['items']) : $_SESSION['pc_shop']['cart']['totalQuantity'];
	}
}