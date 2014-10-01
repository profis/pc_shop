<?php
use \Profis\CMS\SiteMap;

final class PC_shop_plugin extends PC_base {
	public $shop;
	public function Init($plugin_name) {
		$this->plugin = $plugin_name;
		$this->categoryIcon = $this->core->Get_url('plugins', 'images/category.png', $this->plugin);
		$this->productIcon = $this->core->Get_url('plugins', 'images/product.png', $this->plugin);
	}
	
	/**
	 * 
	 * @return PC_shop_manager
	 */
	public function Get_shop() {
		if (!($this->shop instanceof PC_shop_manager)) $this->shop = $this->core->Get_object('PC_shop_manager');
		return $this->shop;
	}
	public static function ParseID($id) {
		$s = preg_match("#^(([a-z0-9\\-_]*)/)?([0-9]+)$#im", $id, $m);
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
				$pars['rename_only'] = v($params['rename_only']);
				$s = $this->Get_shop()->categories->Edit($idData['id'], $changes, $pars);
				if ($s) {
					$params['success'] = true;
					$params['data'] = $this->Get_shop()->categories->Get($idData['id']);
					$params['out']['names'] = array();
					if (isset($params['data']['contents'])) foreach ($params['data']['contents'] as $ln=>$c) {
						$params['out']['names'][$ln] = $c['name'];
					}
					$params['data']['resources'] = $this->Get_shop()->resources->Get_parsed(null, $idData['id'], PC_shop_resources::RF_IS_CATEGORY);
					$hook_params = array();
					$this->core->Init_hooks('core/cache/clear', $hook_params);
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
					$hook_params = array();
					$this->core->Init_hooks('core/cache/clear', $hook_params);
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
				'perPage'=> v($additional['perPage'], 3000)
			);
			$cParams = array('paging'=> &$paging);
			$shop->categories->debug = true;
			$shop->categories->set_instant_debug_to_file($this->cfg['path']['logs'] . 'tree/get_childs_for_tree.html');
			$categories = $shop->categories->Get(null, $parentId, v($pid), $cParams);
			$this->Parse_category_nodes($categories, $list, $additional);
			
			if (v($additional['categories_only']) != true) {
				//list products
				//$paging->Set_initial_offset(count($list));
				$paging->Set_cutout(count($list));
				$pParams = array('paging'=> &$paging);
				$products = $shop->products->Get(null, $parentId, $pParams);
				$this->Parse_product_nodes($products, $list, $additional);
				//$data['total'] = $pParams->paging->total;
				//print_pre($pParams->paging->total);
			}

		}
		
		$params['data'] = (is_array($list)?$list:array());
		return true;
	}
	
	public function Get_parent_id_for_tree($params) {
		$idData = $this->ParseID($params['id']);
		if (!$idData) return false;
		
		$shop = $this->Get_shop();
		
		if ($idData['type'] == 'category') {
			$data =  $shop->categories->Get_item($idData['id'], 'parent_id,pid');
			if ($data) {
				if ($data['parent_id'] != 0) {
					$params['data'] = $this->_get_category_node_id($data['parent_id']);
					return true; 
				}
				else {
					$params['data'] = $data['pid'];
					return true;
				}
			}
		}
		elseif($idData['type'] == 'product') {
			$data = $shop->products->Get_item($idData['id'], 'category_id');
			if ($data) {
				$params['data'] = $this->_get_category_node_id($data['category_id']);
				return true;
			}
		}
		return false;
	}
	
	protected function _get_category_node_id($id) {
		return $this->plugin . '/category/' . $id;
	}
	
	public function Parse_category_nodes($dataList, &$list, $additional = array()) {
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
				//'draggable' => false,
				//'allowDrop'=> false,
				'hot'=> $d['hot'],
				'nomenu'=> $d['nomenu'],
				'icon' => $this->categoryIcon,
				'published'=> $d['published']
			);
			if (in_array(v($additional['checkbox_for']), array('category', 'all'))) {
				$listData['checked'] = false;
				$listData['checkbox'] = true;
			}
			if ($d['rgt'] - $d['lft'] == 1 or v($additional['no_children'])) {
				$listData['_empty'] = 1;
				$listData['childs'] = 0;
			}
			if ($d['redirect']) {
				$listData['_redir'] = true;
			}
			$listData['pc_shop_products_count'] = $shop->products->Count($d['id']);
			if ($listData['pc_shop_products_count'] and v($additional['no_children']) != true) {
				unset($listData['_empty']);
			}
			$list[] = $listData;
		}
		return true;
	}
	public function Parse_product_nodes($dataList, &$list, $additional = array()) {
		if (!is_array($list)) return false;
		$shop = $this->Get_shop();
		foreach ($dataList as &$d) {
			$names = array();
			foreach ($d['contents'] as $ln=>&$c) {
				$names[$ln] = $c['name'];
			}
			$listData = array(
				'id'=> $this->plugin.'/product/'.$d['id'],
				'icon'=> $this->productIcon,
				'_names'=> $names,
				'leaf'=> true,
				//'draggable' => false,
				'allowDrop'=> false,
				'hot'=> $d['hot'],
				'nomenu'=> $d['nomenu'],
				'published'=> $d['published']
			);
			if (in_array(v($additional['checkbox_for']), array('product', 'all'))) {
				$listData['checked'] = false;
				$listData['checkbox'] = true;
			}
			$list[] = $listData;
		}
		return true;
	}
	public function Search_tree($params) {
		if (v($params['logger'], false)) {
			$this->absorb_debug_settings($params['logger']);
		}
		$params['hook_object'] = $this;
		
		$this->debug('Search_tree');
		if (v($params['accessible_pages_concat_query']) and v($params['accessible_pages_concat_query_params'])) {
			$this->debug('Accessible pages concat query:');
			$this->debug_query($params['accessible_pages_concat_query'], $params['accessible_pages_concat_query_params']);
		}
		
		
		$search =& $params['search'];
		$list =& $params['nodes'];
		$accessible_page_sets = & $params['accessible_page_sets'];
		
		$shop = $this->Get_shop();
			
		$ranges = array();
		$use_ranges = false;
		$betweens = array();
		$query_params = array();
		$between_cond = '';
		$join_s = '';
		
		
		$this->debug('Manage accessible pages:', 2);
		if(v($params['accessible_pages_concat_query']) and v($params['accessible_pages_concat_query_params'])) {
			$use_ranges = true;
			$params['accessible_pages_concat_query'] .= ' and p.controller = ?';
			$params['accessible_pages_concat_query_params'][] = 'pc_shop';

			$this->debug('Real accessible pages concat query:', 3);
			$this->debug_query($params['accessible_pages_concat_query'], $params['accessible_pages_concat_query_params'], 4);

			$r = $this->db->prepare($params['accessible_pages_concat_query']);
			$success = $r->execute($params['accessible_pages_concat_query_params']);
			if ($success) {
				$ids = $r->fetchColumn();
				if (!empty($ids)) {
					$categories = $shop->categories->Get_items('lft,rgt', "pid IN ($ids)");
					if ($categories) {
						foreach ($categories as $cat) {
							$ranges[] = $cat;
						}
					}
				}
			}		
		}
		
		if ($accessible_page_sets) {
			$this->debug('Search is restricted to accessible nodes only!', 1);
			$this->debug('Manage accessible controller_nodes:', 2);
			if (isset($accessible_page_sets['controller_nodes']) and isset($accessible_page_sets['controller_nodes']['pc_shop'])) {
				$use_ranges = true;
				foreach ($accessible_page_sets['controller_nodes']['pc_shop'] as $key => $id) {
					$id_data = $this->ParseID($id);
					if ($id_data and $id_data['type'] == 'category') {
						$this->debug($id_data, 3);
						$category_data = $shop->categories->Get_item($id_data['id']);
						if ($category_data) {
							$add_range = true;
							foreach ($ranges as $k => $range) {
								if ($range['lft'] <= $category_data['lft'] and $range['rgt'] >= $category_data['rgt']) {
									$add_range = false;
									break;
								}
							}
							if ($add_range) {
								$ranges[$category_data['lft']] = array(
									'lft' => $category_data['lft'],
									'rgt' => $category_data['rgt']
								);
							}
						}
					}
				}
			}
		}
		
		if ($use_ranges) {
			$this->debug('Ranges:', 2);
			$this->debug($ranges);
			foreach ($ranges as $range) {
				$betweens[] = "(c.lft BETWEEN {$range['lft']} AND {$range['rgt']})";
				//$query_params[] = $range['lft'];
				//$query_params[] = $range['rgt'];
			}
			if (!empty($betweens)) {
				$join_s .= ' LEFT JOIN pc_shop_categories c ON c.id = category_id ';
				$between_cond = '(' . implode(' OR ', $betweens) . ') AND ';
				$this->debug('$between_cond: ' . $between_cond, 3);
			}
		}
		
		//categories
		//search between contents: name, description, seo_title, seo_description, seo_keywords, route
		$query = "SELECT DISTINCT category_id FROM {$this->db_prefix}shop_category_contents"
		. $join_s
		." WHERE " . $between_cond . "(name ".$this->sql_parser->like(':search')
		." or description ".$this->sql_parser->like(':search')
		." or seo_title ".$this->sql_parser->like(':search')
		." or seo_description ".$this->sql_parser->like(':search')
		." or seo_keywords ".$this->sql_parser->like(':search')
		." or route ".$this->sql_parser->like(':search') . ')';
		$r = $this->prepare($query);
			
		$searchStr = '%'.$search.'%';
		//$r->bindParam('search', $searchStr);
		$query_params['search'] = $searchStr;
		
		$this->debug('Search categories query:', 1);
		$this->debug_query($query, $query_params, 2);
		$s = $r->execute($query_params);
		if ($s) if ($r->rowCount()) {
			$ids = array();
			while ($id = $r->fetchColumn()) {
				$ids[] = $id;
			}
			unset($id);
			$params = array();
			$categories = $shop->categories->Get($ids, null, $params);
			$this->Parse_category_nodes($categories, $list, array('no_children' => true));
		}
		
		//products
		//search between contents: name, short_description, description, seo_title, seo_description, seo_keywords, route
		
		$between_join = ' LEFT JOIN pc_shop_products p ON p.id = pc.product_id';
		if (!empty($between_cond)) {
			$between_join .= " LEFT JOIN pc_shop_categories c ON c.id = p.category_id ";
		}
		
		$products_query = "SELECT DISTINCT product_id FROM {$this->db_prefix}shop_product_contents pc"
		.$between_join
		." WHERE " . $between_cond . " (pc.name ".$this->sql_parser->like(':search')
		." or pc.short_description ".$this->sql_parser->like(':search')
		." or pc.description ".$this->sql_parser->like(':search')
		." or p.external_id ".$this->sql_parser->like(':search')
		." or pc.seo_title ".$this->sql_parser->like(':search')
		." or pc.seo_description ".$this->sql_parser->like(':search')
		." or pc.seo_keywords ".$this->sql_parser->like(':search')
		." or pc.route ".$this->sql_parser->like(':search') . ')';
		
		$products_query_params = array();
		
		$r = $this->prepare($products_query);
		//$r->bindParam('search', $searchStr);
		$products_query_params['search'] = $searchStr;
		
		$this->debug('Search products query:', 1);
		$this->debug_query($products_query, $products_query_params, 2);
		
		$s = $r->execute($products_query_params);
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
	
	public function Get_page_url($params) {
		$url = '';
		$id_parts = explode('/', $params['id']);
		$ln = v($params['ln'], $this->site->ln);
		/* @var $shop PC_shop_site */ 
		if (count($id_parts) >= 2) {
			$type = $id_parts[0];
			$id = $id_parts[1];
			if ($type == 'category') {
				$shop = $this->core->Get_object('PC_shop_site');
				if (isset($params['instant_debug_to_file'])) {
					$shop->categories->debug = true;
					$shop->categories->set_instant_debug_to_file($params['instant_debug_to_file'], false, 5);
				}
				$params['url'] = $url = $shop->categories->Get_full_link_by_id($id, $ln);
				if ($url) {
					if (v($params['get_page_id'])) {
						$params['page_id'] = $shop->categories->last_page_id;
					}
				}
				return;
			}
			elseif ($type == 'product') {
				$shop = $this->core->Get_object('PC_shop_site');
				if (isset($params['instant_debug_to_file'])) {
					$shop->categories->debug = true;
					$shop->categories->set_instant_debug_to_file($params['instant_debug_to_file'], false, 5);
				}
				$params['url'] = $url = $shop->products->Get_full_link_by_id($id, $ln);
				if ($url) {
					if (v($params['get_page_id'])) {
						$params['page_id'] = $shop->categories->last_page_id;
					}
				}
				return;
			}
		}
	}
	
	public function Get_request_from_permalink($params) {
		if (v($params['logger'], false)) {
			$this->absorb_debug_settings($params['logger']);
		}
		$params['hook_object'] = $this;
		
		v($params['request']);
		v($params['ln'], null);
		
		$params['request'] = trim($params['request']);
		$params['request'] = trim($params['request'], '/');
		
		if (empty($params['request'])) {
			return;
		}
			
		$this->debug("Get_request_from_permalink({$params['request']}, {$params['ln']})");
		
		$shop = $this->core->Get_object('PC_shop_site');
		$shop_manager = $this->Get_shop();
		
		$shop->categories->absorb_debug_settings($this, 4);
		$shop_manager->categories->absorb_debug_settings($this, 10);
		
		$category_id = $shop_manager->categories->Get_id_by_content('permalink', $params['request'], $params['ln']);
		
		$category_params = array('parse' => array());
		
		if ($category_id) {
			$page_ln = $params['ln'];
			if (!is_null($params['ln'])) {
				$this->site->ln = $params['ln'];
				$page_ln = '';
			}
			$category_data = $shop->categories->Get($category_id, null, null, $category_params);
			if ($category_data) {
				$shop->categories->Load_path($category_data);
				$this->debug('Ln: '.$params['ln'].'; $category_data:', 2);
				$this->debug($category_data, 2);
				
				$page_link = '';
				if ($category_data['path'][0]['pid']) {
					$page_link = $this->page->Get_page_link_by_id($category_data['path'][0]['pid'], $page_ln);
				}
				$this->debug("Page link: " . $page_link, 3);
				
				$last_path_item = $category_data['path'][count($category_data['path']) - 1];
				$category_link = $last_path_item['link'];
				if (v($last_path_item['real_link'])) {
					$category_link = $last_path_item['real_link'];
				}
				pc_remove_trailing_slash($page_url);
				
				$params['permalink_request'] = $page_link . '/' . $category_link;
				
				$this->debug("permalink_request is set to: " . $params['permalink_request'], 3);
			}
			
		}
		
	}
	
	public function After_page_load() {
		/** @var PC_shop_site $shop */
		$shop = $this->core->Get_object('PC_shop_site');
		$highlight_cart = false;
	
		if (isset($_POST['add_to_basket']) and isset($_POST['product_id'])) {
			$shop->product_was_added_to_cart = $shop->cart->Add(intval($_POST['product_id']), intval(v($_POST['quantity'], 1)), v($_POST['attributes']));
			$highlight_cart = $shop->cart->product_was_added;
			if( isset($_POST['_ajax']) ) {
				@header('Content-Type: application/json; charset=utf-8');
				echo json_encode(array(
					'success' => $shop->product_was_added_to_cart,
					'itemCount' => $shop->cart->Count(true),
					'totalQuantity' => $shop->cart->Count(false),
				));
				exit;
			}
		}
		
		$this->site->Register_data('pc_shop_highlight_cart', $highlight_cart);
	}

	public function generateSiteMap($params) {
		global $page, $site, $db;
		/** @var \Profis\CMS\SiteMap $map */
		$map = $params['map'];
		$pageId = $params['pageId'];

		$s = $db->prepare($q = "SELECT lft,rgt FROM `{$this->db_prefix}shop_categories` WHERE `pid`=:pageId");
		if( !$s->execute($p = array('pageId' => $pageId)) )
			throw new \DbException($s->errorInfo(), $q, $p);
		$where = array();
		$queryParams = array();
		while( $row = $s->fetch() ) {
			$where[] = 'c.lft BETWEEN ? AND ?';
			$queryParams[] = $row['lft'];
			$queryParams[] = $row['rgt'];
		}

		if( !empty($where) ) {
			$baseUrls = array();
			foreach( $params['languages'] as $lng )
				$baseUrls[$lng] = $page->Get_page_link_by_id($pageId, $lng);

			$s = $db->prepare($q = "SELECT c.id, c.lft, c.rgt, cc.ln, cc.route, pc.route AS product_route FROM `{$this->db_prefix}shop_categories` c INNER JOIN `{$this->db_prefix}shop_category_contents` cc ON cc.category_id=c.id AND cc.route != '' LEFT JOIN `{$this->db_prefix}shop_products` p ON p.category_id=c.id LEFT JOIN `{$this->db_prefix}shop_product_contents` pc ON pc.product_id=p.id AND pc.route != '' AND pc.ln = cc.ln WHERE " . implode(' OR ', $where) . " ORDER BY cc.ln, c.lft");
			if( !$s->execute($queryParams) )
				throw new \DbException($s->errorInfo(), $q, $queryParams);

			$lng = null;
			$categoryId = null;
			$route = null;
			while( $cat = $s->fetch() ) {
				if( $lng != $cat['ln'] ) {
					$routes = array();
					$bounds = array();
					$lng = $cat['ln'];
				}

				if( $categoryId != $cat['id'] ) {
					while( !empty($bounds) && end($bounds) < $cat['lft'] ) {
						array_pop($routes);
						array_pop($bounds);
					}
					array_push($routes, $cat['route']);
					array_push($bounds, $cat['rgt']);

					$route = implode('/', $routes) . '/';
					$map->addURL($baseUrls[$cat['ln']] . $route);
					$categoryId = $cat['id'];
				}

				if( $cat['product_route'] )
					$map->addURL($baseUrls[$cat['ln']] . $route . $cat['product_route'] . '/', 'weekly', 0.6);
			}
		}
	}
}