<?php
$out = array();
switch (v($routes->Get(1))) {
	/*case 'categories':
		switch (v($routes->Get(2))) {
			case 'get':
				$out['success'] = true;
				print_pre($routes->list);
				break;
			default: $out['error'] = 'Invalid category action';
		}
		break;*/
	/*case 'products':
		switch (v($routes->Get(2))) {
			case 'get':
				switch (v($routes->Get(2))) {
					case 'attributes':
						
						break;
					default:
						//get product
				}
				break;
			default: $out['error'] = 'Invalid category action';
		}
		break;*/
	case 'resources':
		switch (v($routes->Get(2))) {
			case 'get':
				$type = v($_POST['type']);
				$id = v($_POST['id']);
				//$type = 'product'; $id = 1;
				$shop = $core->Get_object('PC_shop_site');
				$flags = 0x0;
				switch ($type) {
					case 'category':
						$flags |= PC_shop_resources::RF_IS_CATEGORY;
						break;
					case 'product': break;
					default: $out['error'] = 'Invalid resource item type'; break 2;
				}
				$r = $shop->resources->Get_parsed(null, $id, $flags);
				$out = $r;
				break;
			default: $out['error'] = 'Invalid resources action';
		}
		break;
	case 'save':
		$d = json_decode(v($_POST['data'], '{}'), true);
		//print_pre($d);
		$shop = $core->Get_object('PC_shop_manager');
		if (!isset($d['id'])) {
			$out['error'] = 'Invalid ID specified';
			break;
		}
		//SAVE CATEGORY
		if (v($routes->Get(2)) == 'category') {
			$params = array();
			if ($d['id'] == 0) {
				$s = $shop->categories->Create($d['parent_id'], v($d['pid']), 0, $d, $params);
				$out['id'] = $d['id'] = $s;
			}
			else $s = $shop->categories->Edit($d['id'], $d, $params);
			if (!$s) {
				$out['error'] = 'Error while saving main category data';
				break;
			}
			$out['data'] = $shop->categories->Get($d['id']);
			$out['data']['resources'] = $shop->resources->Get_parsed(null, $d['id'], PC_shop_resources::RF_IS_CATEGORY);
			$out['success'] = true;
		}
		//SAVE PRODUCT
		else if (v($routes->Get(2)) == 'product') {
			$params = array();
			if ($d['id'] == 0) {
				$s = $shop->products->Create($d['category_id'], 0, $d, $params);
				$out['id'] = $d['id'] = $s;
			}
			else $s = $shop->products->Edit($d['id'], $d, $params);

			if (!$s) {
				$out['error'] = 'Error while saving main product data';
				break;
			}
			$out['data'] = $shop->products->Get($d['id']);
			$out['data']['resources'] = $shop->resources->Get_parsed(null, $d['id']);
			$out['success'] = true;
		}
		else {
			$out['error'] = 'Invalid action specified';
			break;
		}
		break;
	case 'delete':
		$shop = $core->Get_object('PC_shop_manager');
		if (!isset($_POST['id'])) {
			$out['error'] = 'Invalid ID specified';
			break;
		}
		if (v($routes->Get(2)) == 'category') {
			$params = array();
			$out['success'] = $shop->categories->Delete($_POST['id'], $params);
			if (!$out['success']) $out['error'] = $params->errors->Get();
		}
		else if (v($routes->Get(2)) == 'product') {
			$params = array();
			$out['success'] = $shop->products->Delete($_POST['id'], $params);
			if (!$out['success']) $out['error'] = $params->errors->Get();
		}
		else {
			$out['error'] = 'Invalid action specified';
			break;
		}
		break;
	case 'orders':
		/* @var $shop PC_shop_manager */
		$shop = $core->Get_object('PC_shop_manager');
		switch ($routes->Get(2)) {
			case 'get':
				$start = (int)v($_POST['start']);
				$limit = (int)v($_POST['limit']);
				if ($start < 0) $start = 0;
				if ($limit < 1) $limit = $items_per_page;
				$date_from = v($_POST['date_from']);
				$date_to = v($_POST['date_to']);
				$search_phrase = v($_POST['search_phrase']);
				$site_id = v($_POST['site']);
				if (!ctype_digit($site_id)) $site_id = 0; //all sites
				//$ln = $routes->Get(3);
				//---
				//
				$where = array();
				$parameters = array();
				//---
				if (!empty($date_from)) {
					if (!empty($date_to)) {
						$where[] = 'date between ? and ?';
						array_push($parameters, strtotime($date_from), strtotime($date_to)+86400);
					}
					else {
						$where[] = 'date >= ?';
						$parameters[] = strtotime($date_from);
					}
				}
				elseif (!empty($date_to)) {
					$where[] = 'date <= ?';
					$parameters[] = strtotime($date_to)+86400;
				}
				//---
				if (!empty($search_phrase)) {
					$where[] = 'comment like ?';
					$parameters[] = '%'.$search_phrase.'%';
				};
				if ($site_id>0) {
					$where[] = 'site=?';
					$parameters[] = $site_id;
				}
				$shop = $core->Get_object('PC_shop_manager');
				$params = array(
					'paging'=> &$paging
				);
				$out['list'] = $shop->orders->Get(null, $params);
				$out['total'] = $paging->Get_total();
				//---
				break;
			default: $out['error'] = 'Unknown action';
		}
		break;
	case 'attributes':
		/* @var $shop PC_shop_manager */
		$shop = $core->Get_object('PC_shop_manager');
		switch ($routes->Get(2)) {
			case 'get':
				$paging = array(
					'start'=> v($_POST['start'], 0),
					'perPage'=> v($_POST['limit'], 1000)
				);
				$params = array('paging'=> &$paging);
				//$search = v($_POST['params']);
				//if (!is_null($search)) $params['filter'] = array('c.name like ?'=> $search);
				$out['list'] = $shop->attributes->Get(null, $params);
				$out['total'] = $paging->Get_total();
				break;
			case 'getWithValues':
				$params = array('includeValues'=> true);
				$out = $shop->attributes->Get(null, $params);
				break;
			case 'getForItem':
				$itemId = v($_POST['itemId']);
				$type = v($_POST['type']);
				if ($type == 'category') $type = PC_shop_attributes::ITEM_IS_CATEGORY;
				else if ($type == 'product') $type = PC_shop_attributes::ITEM_IS_PRODUCT;
				else {
					$out['error'] = 'Invalid item type';
					break;
				}
				$params = array();
				$out = $shop->attributes->Get_for_item($itemId, $type, $params);
				break;
			case 'delete':
				if (!isset($_POST['id'])) {
					$out['error'] = 'Invalid ID specified';
					break;
				}
				$params = array();
				$out['success'] = $shop->attributes->Delete($_POST['id'], $params);
				if (!$out['success']) $out['error'] = $params->errors->Get();
				break;
			case 'create':
				$params = array();
				$names = json_decode(v($_POST['names'], '{}'), true);
				$out['success'] = $shop->attributes->Create(v($_POST['is_category_attribute']), $names, $params);
				if ($out['success']) {
					$out = array_merge($out, $shop->attributes->Get($out['success']));
				}
				else $out['error'] = $params->errors->Get();
				break;
			case 'save':
				$params = array();
				$id = v($_POST['id']);
				$data = array(
					'names'=> json_decode(v($_POST['names'], '{}'), true),
					'is_category_attribute'=> v($_POST['is_category_attribute']),
					'is_searchable'=> v($_POST['is_searchable']),
					'is_custom'=> v($_POST['is_custom'])
				);
				$out['success'] = $shop->attributes->Edit($id, $data, $params);
				if (!$out['success']) $out['error'] = $params->errors->Get();
				break;
			case 'getSuggestions':
				$out = $shop->attributes->Get_suggestions(v($_POST['attributeId']));
				break;
			case 'values':
				switch ($routes->Get(3)) {
					case 'save':
						$values = json_decode(v($_POST['values'], '{}'), true);
						$out['success'] = $shop->attributes->Edit_value(v($_POST['id']), $values);
						break;
					case 'create':
						$params = array();
						$values = json_decode(v($_POST['values'], '{}'), true);
						$out['success'] = $shop->attributes->Assign_value(v($_POST['attribute_id']), $values, $params);
						if ($out['success']) {
							$out['id'] = $out['success'];
						}
						else $out['error'] = $params->errors->Get();
						break;
					case 'get':
						$paging = array(
							'start'=> v($_POST['start'], 0),
							'perPage'=> v($_POST['limit'], 1000)
						);
						$params = array('paging'=> &$paging);
						$out['list'] = $shop->attributes->Get_values(v($_POST['attribute_id'], null), $params);
						if (count($out['list'])) {
							foreach ($out['list'] as &$i) {
								foreach ($i['names'] as $ln=>$name) {
									$i['value_'.$ln] = $name;
								}
							}
						}
						$out['total'] = $paging->Get_total();
						break;
					case 'delete':
						if (!isset($_POST['id'])) {
							$out['error'] = 'Invalid ID specified';
							break;
						}
						$params = array();
						$out['success'] = $shop->attributes->DeleteValue($_POST['id'], $params);
						if (!$out['success']) $out['error'] = $params->errors->Get();
						break;
					default: $out['error'] = 'Unknown action';
				}
				break;
			default: $out['error'] = 'Unknown action';
		}
		break;
	default: $out['error'] = 'Invalid action';
}
echo json_encode($out);