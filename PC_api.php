<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache');
$out = array();
if (!$site->Is_loaded()) $site->Identify();
if (isset($_POST['ln'])) {
	$site->Set_language($_POST['ln']);
}
switch (v($routes->Get(1))) {
	case 'cart':
		$shop = $core->Get_object('PC_shop_site');
		/* @var $shop PC_shop_site */
		$sendCartState = false;
		$ciid = null;
		switch (v($routes->Get(2))) {
			case 'get':
				$out['items'] = $shop->cart->Get();
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
				$out['success'] = $shop->cart->Remove(v($routes->Get(3)));
				$sendCartState = true;
				break;
			default: $out['error'] = 'Invalid cart action';
		}
		if( $sendCartState ) {
			$cart_data = $shop->cart->Get();
			$out['totalUnique'] = $shop->cart->Count();
			$out['total'] = $shop->cart->Count(false);
			$out['totalPrice'] = number_format($cart_data["totalPrice"], 2, ".", "");
			if( !is_null($ciid) ) {
				$out["item"] = Array(
					"price" => number_format($cart_data['items'][$ciid]["price"], 2, ".", ""),
					"totalPrice" => number_format($cart_data['items'][$ciid]["totalPrice"], 2, ".", "")
				);
			}
		}
		
		break;
	default: $out['error'] = 'Invalid action';
}
echo json_encode($out);