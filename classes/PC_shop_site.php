<?php
class PC_shop_site extends PC_shop {}
class PC_shop_categories_site extends PC_shop_categories {
	public function Get($id=null, $parentId=null, &$params=array()) {
		$this->core->Init_params($params);
		$returnOne = !is_null($id);
		//format query params and parse filters
		$queryParams = array($this->site->ln);
		$where = array();
		$limit = '';
		if (!is_null($id)) {
			$queryParams[] = $id;
			$where[] = 'c.id=?';
			$limit = ' LIMIT 1';
		}
		else {
			if (!is_null($parentId)) {
				$queryParams[] = $parentId;
				$where[] = 'c.parent_id=?';
			}
			if ($params->Has_paging()) {
				$limit = " LIMIT {$params->paging->Get_offset()},{$params->paging->Get_limit()}";
			}
		}
		if (isset($params->filter)) if (is_array($params->filter)) if (count($params->filter)) {
			foreach ($params->filter as $field=>$value) {
				$where[] = $field.'=?';
				$queryParams[] = $value;
			}
		}
		//query!
		$r = $this->prepare("SELECT ".($params->Has_paging()?'SQL_CALC_FOUND_ROWS ':'')."c.*,cc.*,"
		.' concat('.$this->sql_parser->group_concat('link_cc.route', array('distinct'=>true,'separator'=>'/')).",'/') link, count(cp.id) products"
		." FROM {$this->db_prefix}shop_categories c"
		." LEFT JOIN {$this->db_prefix}shop_category_contents cc ON cc.category_id=c.id and cc.ln=?"
		//count products in this category
		." LEFT JOIN {$this->db_prefix}shop_products cp ON cp.category_id=c.id"
		//generate full route path
		." LEFT JOIN {$this->db_prefix}shop_categories link_c ON c.lft BETWEEN link_c.lft and link_c.rgt"
		." LEFT JOIN {$this->db_prefix}shop_category_contents link_cc ON link_cc.category_id = link_c.id and link_cc.ln=cc.ln"
		//filters
		.(count($where)?' WHERE '.implode(' and ', $where):'')
		.(!$returnOne?" GROUP BY c.id":"").$limit);
		$s = $r->execute($queryParams);
		if (!$s) return false;
		if ($params->Has_paging()) {
			$rTotal = $this->query("SELECT FOUND_ROWS()");
			if ($rTotal) $params->paging->Set_total($rTotal->fetchColumn());
		}
		$list = array();
		while ($d = $r->fetch()) {
			$this->Parse($d);
			$list[] = $d;
		}
		if (v($params->load_path, false)) {
			if (is_array($list[0])) $this->Load_path($list[0]);
		}
		if ($returnOne) {
			if (!count($list)) return false;
			return $list[0];
		}
		else return $list;
	}
	public function Parse(&$d) {
		$this->Decode_flags($d);
		if (isset($d['description'])) $this->page->Parse_html_output($d['description']);
		//$d['path'] = $this->Get_path($d);
		//$d['resources'] = $this->
	}
	public function Load_path(&$c) {
		if (!is_array($c)) return false;
		if (!isset($c['id'])) return false;
		$r = $this->prepare("SELECT p.id,p.flags,cc.name,cc.route,"
		.' concat('.$this->sql_parser->group_concat('link_cc.route', array('distinct'=>true,'separator'=>'/','order'=>array('by'=>'link_c.lft'))).",'/') link, count(cp.id) products"
		." FROM {$this->db_prefix}shop_categories c"
		." LEFT JOIN {$this->db_prefix}shop_categories p ON c.lft between p.lft and p.rgt"
		." LEFT JOIN {$this->db_prefix}shop_category_contents cc ON cc.category_id=p.id and cc.ln=?"
		//count products in this category
		." LEFT JOIN {$this->db_prefix}shop_products cp ON cp.category_id=p.id"
		//generate full route path
		." LEFT JOIN {$this->db_prefix}shop_categories link_c ON p.lft BETWEEN link_c.lft and link_c.rgt"
		." LEFT JOIN {$this->db_prefix}shop_category_contents link_cc ON link_cc.category_id = link_c.id and link_cc.ln=cc.ln"
		." WHERE c.id=?"
		." GROUP BY p.id ORDER BY p.lft");
		$s = $r->execute(array($this->site->ln, $c['id']));
		if (!$s) return false;
		$list = array();
		if ($r->rowCount()) while ($d = $r->fetch()) {
			$this->Parse($d);
			$list[] = $d;
		}
		$c['path'] = $list;
		return true;
	}
}
class PC_shop_products_site extends PC_shop_products {
	public function Get($id=null, $categoryId=null, &$params=array()) {
		$this->core->Init_params($params);
		//format query params and parse filters
		$queryParams = array($this->site->ln);
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
		else {
			if (!is_null($categoryId)) {
				$queryParams[] = $categoryId;
				$where[] = 'c.id=?';
			}
			if ($params->Has_paging()) {
				$limit = " LIMIT {$params->paging->Get_offset()},{$params->paging->Get_limit()}";
			}
		}
		if (isset($params->filter)) if (is_array($params->filter)) if (count($params->filter)) {
			foreach ($params->filter as $field=>$value) {
				$where[] = $field.'=?';
				$queryParams[] = $value;
			}
		}
		//query!
		$r = $this->prepare($qry = "SELECT ".($params->Has_paging()?'SQL_CALC_FOUND_ROWS ':'')."p.*,pc.*,"
		.' concat('.$this->sql_parser->group_concat('link_cc.route', array('distinct'=>true,'separator'=>'/')).",'/',pc.route,'/') link"
		." FROM {$this->db_prefix}shop_products p"
		." LEFT JOIN {$this->db_prefix}shop_product_contents pc ON pc.product_id=p.id and pc.ln=?"
		//generate full route path
		." LEFT JOIN {$this->db_prefix}shop_categories c ON c.id=p.category_id"
		." LEFT JOIN {$this->db_prefix}shop_categories link_c ON c.lft BETWEEN link_c.lft and link_c.rgt"
		." LEFT JOIN {$this->db_prefix}shop_category_contents link_cc ON link_cc.category_id = link_c.id and link_cc.ln=pc.ln"
		//filters
		.(count($where)?' WHERE '.implode(' and ', $where):'')
		." GROUP BY p.id".$limit);
		$s = $r->execute($queryParams);
		if (!$s) return false;
		if ($params->Has_paging()) {
			$rTotal = $this->query("SELECT FOUND_ROWS()");
			if ($rTotal) $params->paging->Set_total($rTotal->fetchColumn());
		}
		$list = array();
		while ($d = $r->fetch()) {
			$this->Decode_flags($d);
			$this->page->Parse_html_output($d['description'], $d['short_description']);
			//$d['resources'] = new PC_shop_item_resources($d['id']);//$this->shop->resources->Get(null, $d['id']);
			$list[] = $d;
		}
		if ($returnOne) {
			if (!count($list)) return false;
			return $list[0];
		}
		else return $list;
	}
}