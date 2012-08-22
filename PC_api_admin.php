<?php
$out = array();
switch (v($routes->Get(1))) {
	case 'categories':
		switch (v($routes->Get(2))) {
			case 'get':
				$out['success'] = true;
				print_pre($routes->list);
				break;
			default: $out['error'] = 'Invalid category action';
		}
		break;
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
				$s = $shop->categories->Create($d['parent_id'], 0, $d, $params);
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
	default: $out['error'] = 'Invalid action';
}
echo json_encode($out);