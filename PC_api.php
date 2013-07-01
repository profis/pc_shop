<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache');
$out = array();
if (!$site->Is_loaded()) $site->Identify();
if (isset($_POST['ln'])) {
	$site->Set_language($_POST['ln']);
}
$route = v($routes->Get(1));
switch ($route) {
	case 'cart':
		$out = process_api_for_cart();
		break;
	case 'order':
		$out = process_api_for_order();
		break;
		
	default: $out['error'] = 'Invalid action';
		break;
}


function process_api_for_cart($route = '') {
	global $core, $cfg, $routes;
	if (empty($route)) {
		$route = v($routes->Get(2));
	}
	$out = array();
	$shop = $core->Get_object('PC_shop_site');
	/* @var $shop PC_shop_site */


	$shop->cart->debug = true;
	$shop->cart->set_instant_debug_to_file($cfg['path']['logs'] . 'pc_shop/cart_api.html', null, 5);

	$sendCartState = false;
	$ciid = null;
	switch ($route) {
		case 'get':
			$out['items'] = $shop->cart->Get();
			$sendCartState = true;
			break;
		case 'clear':
			$out['success'] = $shop->cart->Clear();
			$sendCartState = true;
			break;
		case 'addAt':
			$out['success'] = $shop->cart->AddAt($ciid = v($routes->Get(3)), v($routes->Get(4), 1));
			$sendCartState = true;
			break;
		case 'add':
			$out['success'] = $shop->cart->Add(v($routes->Get(3)), v($routes->Get(4), 1));
			$sendCartState = true;
			break;
		case 'set':
			$out['success'] = $shop->cart->Set($ciid = v($routes->Get(3)), v($routes->Get(4), 1));
			$sendCartState = true;
			break;
		case 'remove':
			$out['success'] = $shop->cart->Remove($ciid = v($routes->Get(3)), v($routes->Get(4), 0));
			$sendCartState = true;
			break;
		default: $out['error'] = 'Invalid cart action';
	}
	if( $sendCartState ) {
		$cart_data = $shop->cart->Get();
		//print_pre($cart_data);
		$out['totalUnique'] = $shop->cart->Count();
		$out['total'] = $shop->cart->Count(false);
		$out['totalPrice'] = number_format($cart_data["totalPrice"], 2, ".", "");
		$shop->cart->Calculate_prices($out);
		if( !is_null($ciid) ) {
			$item_real_key = false;

			/*
			foreach ($cart_data['items'] as $key => $item) {
				if ($item['id'] == $ciid) {
					$item_real_key = $key;
					break;
				}
			}
			*/
			if (isset($cart_data['items'][$ciid])) {
				$item_real_key = $ciid;
			}
			if ($item_real_key) {
				$price = $shop->products->get_price($cart_data['items'][$item_real_key]);
				$out["item"] = Array(
					"price" => number_format($price, 2, ".", ""),
					"totalPrice" => number_format($cart_data['items'][$item_real_key]["totalPrice"], 2, ".", "")
				);
			}

		}
	}
	return $out;
}

function process_api_for_order() {
	global $core, $cfg, $routes;
	$out = array();
	$shop = $core->Get_object('PC_shop_site');
	/* @var $shop PC_shop_site */
		

	$shop->cart->debug = true;
	$shop->cart->set_instant_debug_to_file($cfg['path']['logs'] . 'pc_shop/order_api.html', null, 5);

	switch (v($routes->Get(2))) {
		case 'get':
			$out['order'] = $shop->orders->Get_preserved_order_data();
			break;
		case 'save':
			$shop->orders->Preserve_order_data();
			$out = process_api_for_cart('get');
			if (isset($_GET['temp']) == 'temp') {
				$shop->orders->Clear_preserved_order_data();
			}
			break;
	}

	return $out;
}

echo json_encode($out);