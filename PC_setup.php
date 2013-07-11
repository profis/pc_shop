<?php

function pc_shop_install($controller) {
	global $core, $logger;
	
	$models_path = $core->Get_path('plugins', 'classes/models/', 'pc_shop');
	require_once $models_path . 'PC_shop_payment_option_model.php';
	require_once $models_path . 'PC_shop_delivery_option_model.php';
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
	$core->Set_config('currency', 'LTL', 'pc_shop');
	
	return true;
}