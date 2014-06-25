<?php

function pc_shop_install($controller) {
	global $core, $logger;
	
	$models_path = $core->Get_path('plugins', 'classes/models/', 'pc_shop');
	require_once $models_path . 'PC_shop_payment_option_model.php';
	require_once $models_path . 'PC_shop_delivery_option_model.php';
	require_once $models_path . 'PC_shop_currency_model.php';
	require_once $models_path . 'PC_shop_currency_content_model.php';
	$payment_option_model = new PC_shop_payment_option_model();
	$payment_option_model->absorb_debug_settings($logger);
	
	$payment_option_model->insert(array('code' => 'cash'), array(
		'lt' => array(
			'name' => 'Grynais'
		),
		'en' => array(
			'name' => 'Cash'
		),
		'ru' => array(
			'name' => 'Наличные'
		)
	), array('ignore' => true));
	
	$payment_option_model->insert(array('code' => 'bank'), array(
		'lt' => array(
			'name' => 'Bankiniu pavedimu'
		),
		'en' => array(
			'name' => 'Bank transfer'
		),
		'ru' => array(
			'name' => 'Банковский перевод'
		)
	), array('ignore' => true));
	
	
	$payment_option_model->insert(array('code' => 'check'), array(
		'lt' => array(
			'name' => 'Čekiu'
		),
		'en' => array(
			'name' => 'Check'
		),
		'ru' => array(
			'name' => 'Банковский чек'
		)
	), array('ignore' => true));
	
	
	$delivery_option_model = new PC_shop_delivery_option_model();
	$delivery_option_model->absorb_debug_settings($logger);
	
	$delivery_option_model->insert(array('code' => 'shop'), array(
		'lt' => array(
			'name' => 'Atsiėmimas parduotuvėje'
		),
		'en' => array(
			'name' => 'Collect from the shop'
		),
		'ru' => array(
			'name' => 'Забрать из магазина'
		)
	), array('ignore' => true));
	
	$delivery_option_model->insert(array('code' => 'courier'), array(
		'lt' => array(
			'name' => 'Atsiėmimas iš kurjerio'
		),
		'en' => array(
			'name' => 'Collect from a courier'
		),
		'ru' => array(
			'name' => 'Курьерская служба доставки'
		)
	), array('ignore' => true));
	
	//$core->Set_config('delivery_price', '', 'pc_shop');
	//$core->Set_config('cod_price', '', 'pc_shop');
	//$core->Set_config('amount_for_free_delivery', '', 'pc_shop');
	$core->Set_config_if('currency', 'LTL', 'pc_shop');
	$core->Set_config_if('new_order_email_admin', '', 'pc_shop');
	
	$core->Set_config_if('checkout_offer_to_register', '', 'pc_shop');
	
	//Import currency list
	require_once CMS_ROOT .  'admin/classes/PC_plugin_admin_api.php';
	require_once CMS_ROOT .  'admin/classes/PC_plugin_crud_admin_api.php';
	require_once PLUGINS_ROOT . DS .  'pc_shop/admin_api/PC_shop_admin_api.php';
	require_once 'admin_api/PC_shop_currencies_admin_api.php';
	$currencies_api = new PC_shop_currencies_admin_api();
	$currencies_api->import();
	
	return true;
}