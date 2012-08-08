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
		switch (v($routes->Get(2))) {
			case 'get':
				$out['items'] = $shop->cart->Get();
				break;
			case 'clear':
				$out['success'] = $shop->cart->Clear();
				break;
			case 'add':
				$out['success'] = $shop->cart->Add(v($routes->Get(3)), v($routes->Get(4), 1));
				break;
			case 'set':
				$out['success'] = $shop->cart->Set(v($routes->Get(3)), v($routes->Get(4), 1));
				break;
			default: $out['error'] = 'Invalid cart action';
		}
		break;
	default: $out['error'] = 'Invalid action';
}
echo json_encode($out);