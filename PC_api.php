<?php
/**
 * @var PC_core $core
 * @var PC_site $site
 * @var PC_database $db
 * @var PC_routes $routes
 */
header('Content-Type: application/json');
header('Cache-Control: no-cache');
$out = array();
if (!$site->Is_loaded()) $site->Identify();
if (isset($_POST['ln'])) {
	$site->Set_language($_POST['ln']);
}
$route = $routes->Get(1);
switch ($route) {
	case 'cart':
		$out = process_api_for_cart();
		break;
	case 'order':
		$out = process_api_for_order();
		break;
	case 'addView':
		$out = process_api_add_view($routes->Get(2));
		break;
		
	default: $out['error'] = 'Invalid action';
		break;
}


function process_api_for_cart($route = '') {
	global $core, $cfg, $routes;
	if (empty($route)) {
		$route = $routes->Get(2);
	}
	$out = array();
	$shop = $core->Get_object('PC_shop_site');
	/* @var $shop PC_shop_site */

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
			$out['success'] = $shop->cart->AddAt($ciid = $routes->Get(3), $routes->Get(4, 1));
			$sendCartState = true;
			break;
		case 'add':
			$out['success'] = $shop->cart->Add($routes->Get(3), $routes->Get(4, 1), v($_POST['attributes'], null));
			$sendCartState = true;
			break;
		case 'set':
			$out['success'] = $shop->cart->Set($ciid = $routes->Get(3), $routes->Get(4, 1));
			$sendCartState = true;
			break;
		case 'remove':
			$out['success'] = $shop->cart->Remove($ciid = $routes->Get(3), $routes->Get(4, 0));
			$sendCartState = true;
			break;
		default: $out['error'] = 'Invalid cart action';
	}
	if( $sendCartState ) {
		$cart_data = $shop->cart->Get();
		//print_pre($cart_data);
		$out = array_merge($out, $cart_data);
		unset($out['items']);
		unset($out['products']);
		//$out['totalUnique'] = $shop->cart->Count();
		//$out['total'] = $shop->cart->Count(false);
		//$out['totalPrice'] = number_format($cart_data["totalPrice"], 2, ".", "");
		//$shop->cart->Calculate_prices($out);
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
				// $price = $shop->products->get_price($cart_data['items'][$item_real_key]);
				$out["item"] = Array(
					"price" => number_format($cart_data['items'][$item_real_key]["price"], 2, ".", ""),
					"totalPrice" => number_format($cart_data['items'][$item_real_key]["totalPrice"], 2, ".", "")
				);
			}

		}
	}
	return $out;
}

function process_api_for_order() {
	global $core, $site, $cfg, $routes;
	$out = array();
	$shop = $core->Get_object('PC_shop_site');
	/* @var $shop PC_shop_site */
		
	switch ($routes->Get(2)) {
		case 'get':
			$out['order'] = $shop->orders->Get_preserved_order_data();
			break;
		case 'save':
			$prevOrder = $shop->orders->Get_preserved_order_data();

			if( !is_array($prevOrder) )
				$prevOrder = array();
			$isNew = empty($prevOrder);

			if( isset($_REQUEST['order']) && is_array($_REQUEST['order']) )
				$order = array_merge($prevOrder, $_REQUEST['order']);
			else
				$order = $prevOrder;

			if( $isNew ) {
				$shop->orders->Preserve_order_data($order);
				$order = $shop->orders->Get_preserved_order_data();
			}

			if (isset($_POST['pc_shop_coupon']))
				$order['coupon'] = $_POST['pc_shop_coupon'];

			$deliveryOption = v($order['delivery_option']);
			if( $deliveryOption && $deliveryOption == v($prevOrder['delivery_option']) ) {
				if( isset($_REQUEST['delivery_form_data']) ) {
					if( !isset($order['delivery_form_data']) )
						$order['delivery_form_data'] = array();
					$order['delivery_form_data'][$deliveryOption] = $_REQUEST['delivery_form_data'];
				}
			}
			$shop->orders->Preserve_order_data($order);

			$out = process_api_for_cart('get');
			$out['delivery_form'] = $site->Get_widget_text('PC_plugin_pc_shop_delivery_form_widget');

			if (isset($_GET['temp']) == 'temp') {
				$shop->orders->Clear_preserved_order_data();
			}

			//$core->Init_callback()
			break;
	}

	return $out;
}

function process_api_add_view($id) {
	global $core;
	$shop = $core->Get_object('PC_shop_site');
	/* @var $shop PC_shop_site */
	return $shop->products->addView($id);
}

echo json_encode($out);