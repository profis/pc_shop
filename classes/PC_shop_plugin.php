<?php
final class PC_shop_plugin extends PC_base {
	public $shop;
	public function Init($plugin_name) {
		$this->plugin = $plugin_name;
		$this->productIcon = $this->core->Get_url('plugins', 'product.png', $this->plugin);
	}
	public function Get_shop() {
		if (!($this->shop instanceof PC_shop_manager)) $this->shop = $this->core->Get_object('PC_shop_manager');
		return $this->shop;
	}
	public function ParseID($id) {
		$s = preg_match("#^(([a-z0-9\-_]*)/)?([0-9]+)$#im", $id, $m);
		if (!$s) return false;
		return array(
			'type'=> $m[2],
			'id'=> $m[3]
		);
	}
	public function Load_page_for_editor($params) {
		$idData = $this->ParseID($params['id']);
		if (!$idData) return false;
		switch ($idData['type']) {
			case 'category':
				$params['data'] = $this->Get_shop()->categories->Get($idData['id']);
				$params['data']['resources'] = $this->Get_shop()->resources->Get_parsed(null, $idData['id'], PC_shop_resources::RF_IS_CATEGORY);
				return true;
				break;
			case 'product':
				$params['data'] = $this->Get_shop()->products->Get($idData['id']);
				$params['data']['resources'] = $this->Get_shop()->resources->Get_parsed(null, $idData['id']);
				return true;
				break;
		}
		return false;
	}
	public function Save_page_for_editor($params) {
		$idData = $this->ParseID($params['id']);
		if (!$idData) return false;
		$changes = array('contents'=> $params['changes']['content']);
		$pars = array();
		switch ($idData['type']) {
			case 'category':
				$s = $this->Get_shop()->categories->Edit($idData['id'], $changes, $pars);
				if ($s) {
					$params['success'] = true;
					$params['data'] = $this->Get_shop()->categories->Get($idData['id']);
					$params['out']['names'] = array();
					if (isset($params['data']['contents'])) foreach ($params['data']['contents'] as $ln=>$c) {
						$params['out']['names'][$ln] = $c['name'];
					}
					$params['data']['resources'] = $this->Get_shop()->resources->Get_parsed(null, $idData['id'], PC_shop_resources::RF_IS_CATEGORY);
					return true;
				}
				break;
			case 'product':
				$s = $this->Get_shop()->products->Edit($idData['id'], $changes, $pars);
				if ($s) {
					$params['success'] = true;
					$params['data'] = $this->Get_shop()->products->Get($idData['id']);
					$params['out']['names'] = array();
					if (isset($params['data']['contents'])) foreach ($params['data']['contents'] as $ln=>$c) {
						$params['out']['names'][$ln] = $c['name'];
					}
					$params['data']['resources'] = $this->Get_shop()->resources->Get_parsed(null, $idData['id']);
					return true;
				}
				break;
			/*case 'product':
				$params['data'] = $this->Get_shop()->products->Get($idData['id']);
				$params['data']['resources'] = $this->Get_shop()->resources->Get_parsed(null, $idData['id']);
				return true;
				break;*/
		}
		return false;
	}
	public function Get_childs_for_tree($params) {
		$idData = $this->ParseID($params['id']);
		if (!$idData) return false;
		
		$additional = v($params['additional'][$this->plugin], array());
		
		$list = array();
		
		if (empty($idData['type'])) {
			$parentId = 0;
			$pid = $idData['id'];
			$idData['type'] = 'category';
		}
		else $parentId = $idData['id'];
		if ($idData['type'] == 'category') {
			//list categories
			$shop = $this->Get_shop();
			$paging = array(
				'page'=> v($additional['page'], 1),
				'perPage'=> v($additional['perPage'], 30)
			);
			$cParams = array('paging'=> &$paging);
			$categories = $shop->categories->Get(null, $parentId, v($pid), $cParams);
			$this->Parse_category_nodes($categories, $list);
			//list products
			//$paging->Set_initial_offset(count($list));
			$paging->Set_cutout(count($list));
			$pParams = array('paging'=> &$paging);
			$products = $shop->products->Get(null, $parentId, $pParams);
			$this->Parse_product_nodes($products, $list);
			//$data['total'] = $pParams->paging->total;
			//print_pre($pParams->paging->total);
		}
		
		$params['data'] = (is_array($list)?$list:array());
		return true;
	}
	public function Parse_category_nodes($dataList, &$list) {
		if (!is_array($list)) return false;
		$shop = $this->Get_shop();
		foreach ($dataList as &$d) {
			$names = array();
			foreach ($d['contents'] as $ln=>&$c) {
				$names[$ln] = $c['name'];
			}
			$listData = array(
				'id'=> $this->plugin.'/category/'.$d['id'],
				'_names'=> $names,
				'draggable' => false,
				'allowDrop'=> false,
				'hot'=> $d['hot'],
				'nomenu'=> $d['nomenu'],
				'published'=> $d['published']
			);
			if ($d['rgt'] - $d['lft'] == 1) {
				$listData['_empty'] = 1;
				$listData['childs'] = 0;
			}
			$listData['pc_shop_products_count'] = $shop->products->Count($d['id']);
			$list[] = $listData;
		}
		return true;
	}
	public function Parse_product_nodes($dataList, &$list) {
		if (!is_array($list)) return false;
		$shop = $this->Get_shop();
		foreach ($dataList as &$d) {
			$names = array();
			foreach ($d['contents'] as $ln=>&$c) {
				$names[$ln] = $c['name'];
			}
			$list[] = array(
				'id'=> $this->plugin.'/product/'.$d['id'],
				'icon'=> $this->productIcon,
				'_names'=> $names,
				'leaf'=> true,
				'draggable' => false,
				'allowDrop'=> false,
				'hot'=> $d['hot'],
				'nomenu'=> $d['nomenu'],
				'published'=> $d['published']
			);
		}
		return true;
	}
	public function Search_tree($params) {
		$search =& $params['search'];
		$list =& $params['nodes'];
		$shop = $this->Get_shop();
		//categories
		//search between contents: name, description, seo_title, seo_description, seo_keywords, route
		$r = $this->prepare("SELECT DISTINCT category_id FROM {$this->db_prefix}shop_category_contents"
		." WHERE name ".$this->sql_parser->like(':search')
		." or description ".$this->sql_parser->like(':search')
		." or seo_title ".$this->sql_parser->like(':search')
		." or seo_description ".$this->sql_parser->like(':search')
		." or seo_keywords ".$this->sql_parser->like(':search')
		." or route ".$this->sql_parser->like(':search'));
		$searchStr = '%'.$search.'%';
		$r->bindParam('search', $searchStr);
		$s = $r->execute();
		if ($s) if ($r->rowCount()) {
			$ids = array();
			while ($id = $r->fetchColumn()) {
				$ids[] = $id;
			}
			unset($id);
			$params = array();
			$categories = $shop->categories->Get($ids, null, $params);
			$this->Parse_category_nodes($categories, $list);
		}
		
		//products
		//search between contents: name, short_description, description, seo_title, seo_description, seo_keywords, route
		$r = $this->prepare("SELECT DISTINCT product_id FROM {$this->db_prefix}shop_product_contents"
		." WHERE name ".$this->sql_parser->like(':search')
		." or short_description ".$this->sql_parser->like(':search')
		." or description ".$this->sql_parser->like(':search')
		." or seo_title ".$this->sql_parser->like(':search')
		." or seo_description ".$this->sql_parser->like(':search')
		." or seo_keywords ".$this->sql_parser->like(':search')
		." or route ".$this->sql_parser->like(':search'));
		$r->bindParam('search', $searchStr);
		$s = $r->execute();
		if ($s) if ($r->rowCount()) {
			$ids = array();
			while ($id = $r->fetchColumn()) {
				$ids[] = $id;
			}
			unset($id);
			$params = array();
			$products = $shop->products->Get($ids, null, $params);
			$this->Parse_product_nodes($products, $list);
		}
	}
}