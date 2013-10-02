<?php

function PC_shop_allow_pages_for_childs_for_tree($params) {
	$params['result'] = true;
}
$thisPath =  dirname(__FILE__) . '/';
$clsPath = $thisPath . 'classes/';
Register_class_autoloader('PC_shop', $clsPath.'PC_shop.php');
Register_class_autoloader('PC_shop_site', $clsPath.'PC_shop_site.php');
Register_class_autoloader('PC_shop_manager', $clsPath.'PC_shop_manager.php');
Register_class_autoloader('PC_shop_plugin', $clsPath.'PC_shop_plugin.php');

Register_class_autoloader('PC_shop_attribute_model', $clsPath.'models/PC_shop_attribute_model.php');
Register_class_autoloader('PC_shop_attribute_content_model', $clsPath.'models/PC_shop_attribute_content_model.php');
Register_class_autoloader('PC_shop_attribute_value_model', $clsPath.'models/PC_shop_attribute_value_model.php');
Register_class_autoloader('PC_shop_attribute_category_model', $clsPath.'models/PC_shop_attribute_category_model.php');
Register_class_autoloader('PC_shop_attribute_item_model', $clsPath.'models/PC_shop_attribute_item_model.php');
Register_class_autoloader('PC_shop_order_model', $clsPath.'models/PC_shop_order_model.php');
Register_class_autoloader('PC_shop_order_item_model', $clsPath.'models/PC_shop_order_item_model.php');
Register_class_autoloader('PC_shop_category_model', $clsPath.'models/PC_shop_category_model.php');
Register_class_autoloader('PC_shop_product_model', $clsPath.'models/PC_shop_product_model.php');
Register_class_autoloader('PC_shop_site_product_model', $clsPath.'models/PC_shop_site_product_model.php');
Register_class_autoloader('PC_shop_manufacturer_model', $clsPath.'models/PC_shop_manufacturer_model.php');
Register_class_autoloader('PC_shop_currency_model', $clsPath.'models/PC_shop_currency_model.php');
Register_class_autoloader('PC_shop_currency_content_model', $clsPath.'models/PC_shop_currency_content_model.php');
Register_class_autoloader('PC_shop_ln_currency_model', $clsPath.'models/PC_shop_ln_currency_model.php');
Register_class_autoloader('PC_shop_currency_rate_model', $clsPath.'models/PC_shop_currency_rate_model.php');
Register_class_autoloader('PC_shop_product_product_model', $clsPath.'models/PC_shop_product_product_model.php');
Register_class_autoloader('PC_shop_product_category_model', $clsPath.'models/PC_shop_product_category_model.php');
Register_class_autoloader('PC_shop_delivery_option_model', $clsPath.'models/PC_shop_delivery_option_model.php');
Register_class_autoloader('PC_shop_payment_option_model', $clsPath.'models/PC_shop_payment_option_model.php');
Register_class_autoloader('PC_shop_category_product_filter_model', $clsPath.'models/PC_shop_category_product_filter_model.php');
Register_class_autoloader('PC_shop_product_price_model', $clsPath.'models/PC_shop_product_price_model.php');
Register_class_autoloader('PC_shop_price_model', $clsPath.'models/PC_shop_price_model.php');

Register_class_autoloader('PC_shop_payment_method', $clsPath . 'PC_shop_payment_method.php');
Register_class_autoloader('PC_shop_price', $clsPath . 'PC_shop_price.php');

Register_class_autoloader('PC_plugin_pc_shop_widget', $thisPath . 'widgets/PC_plugin_pc_shop_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_category_menu_widget', $thisPath . 'widgets/PC_plugin_pc_shop_category_menu_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_all_category_menu_widget', $thisPath . 'widgets/PC_plugin_pc_shop_all_category_menu_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_categories_widget', $thisPath . 'widgets/PC_plugin_pc_shop_categories_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_products_widget', $thisPath . 'widgets/PC_plugin_pc_shop_products_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_hot_products_widget', $thisPath . 'widgets/PC_plugin_pc_shop_hot_products_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_newest_products_widget', $thisPath . 'widgets/PC_plugin_pc_shop_newest_products_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_product_widget', $thisPath . 'widgets/PC_plugin_pc_shop_product_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_mini_basket_widget', $thisPath . 'widgets/PC_plugin_pc_shop_mini_basket_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_cart_widget', $thisPath . 'widgets/PC_plugin_pc_shop_cart_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_order_widget', $thisPath . 'widgets/PC_plugin_pc_shop_order_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_sort_products_widget', $thisPath . 'widgets/PC_plugin_pc_shop_sort_products_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_search_form_widget', $thisPath . 'widgets/PC_plugin_pc_shop_search_form_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_search_products_widget', $thisPath . 'widgets/PC_plugin_pc_shop_search_products_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_category_products_filter_widget', $thisPath . 'widgets/PC_plugin_pc_shop_category_products_filter_widget.php');
Register_class_autoloader('PC_plugin_pc_shop_currency_selector_widget', $thisPath . 'widgets/PC_plugin_pc_shop_currency_selector_widget.php');

$pluginCls = $this->core->Get_object('PC_shop_plugin', $plugin_name);

$core->Register_hook('core/editor/load-page/'.$plugin_name, array($pluginCls, 'Load_page_for_editor'));
$core->Register_hook('core/editor/save-page/'.$plugin_name, array($pluginCls, 'Save_page_for_editor'));
$core->Register_hook('core/tree/get-childs/'.$plugin_name, array($pluginCls, 'Get_childs_for_tree'));
$core->Register_hook('core/tree/get-childs/allow_pages/'.$plugin_name, 'PC_shop_allow_pages_for_childs_for_tree');
$core->Register_hook('core/tree/get-parent-id/'.$plugin_name, array($pluginCls, 'Get_parent_id_for_tree'));
$core->Register_hook('core/tree/search', array($pluginCls, 'Search_tree'));

//Hook for getting permalink for 301 redirection
$core->Register_hook('core/page/parse-page-url/'.$plugin_name, array($pluginCls, 'Get_page_url'));

//Hook for generating urls for plugin subpages (categories, products ...)
$core->Register_hook('core/page/get-page-url/'.$plugin_name, array($pluginCls, 'Get_page_url'));

//Hook for detecting permalink for plugin subpages
$core->Register_hook('core/site/request-from-permalink', array($pluginCls, 'Get_request_from_permalink'));

$core->Register_hook('after_load_page', array($pluginCls, 'After_page_load'));

$this->auth->permissions->Register($this->currentlyParsing, 'categories', 'PC_shop_permission_manager::Authorize_by_pid');


function pc_shop_after_change_config_currency($params) {
	$logger = new PC_debug();
	if (isset($params['logger'])) {
		$logger-> absorb_debug_settings($params['logger']);
		
	}
	$logger->debug("pc_shop_after_change_config_currency({$params['value']})");
	require_once CMS_ROOT .  'admin/classes/PC_plugin_admin_api.php';
	require_once CMS_ROOT .  'admin/classes/PC_plugin_crud_admin_api.php';
	require_once PLUGINS_ROOT . DS .  'pc_shop/admin_api/PC_shop_admin_api.php';
	require_once PLUGINS_ROOT . DS .  'pc_shop/admin_api/PC_shop_currency_rates_admin_api.php';
	
	$rates_api = new PC_shop_currency_rates_admin_api();
	if (isset($params['logger'])) {
		$rates_api-> absorb_debug_settings($params['logger']);
	}
	$rates_api->import();
	$output = $rates_api->get_output();
	$logger->debug('Rates api output:');
	$logger->debug($output);
	if ($output['success'] and isset($output['data']) and !empty($output['data'])) {
		$model = $rates_api->get_model();
		if (isset($params['logger'])) {
			$model-> absorb_debug_settings($params['logger']);
		}
		$model->delete();
		$_POST['data'] = $output['data'];
		$rates_api->sync();
	}
}

function pc_shop_set_user_currency($params) {
	global $core;
	if (isset($_GET['pc_currency'])) {
		$shop_price = $core->Get_object('PC_shop_price');
		$shop_price->set_user_currency($_GET['pc_currency']);
		//$row_base_url = $base_url = $this->_config['base_url'];
		$query_string = PC_utils::getCurrUrl();
		$redirect_url = preg_replace('/(\?)?(&)?(pc_currency)=[^=&]*/ui', '$1', $query_string);
		$core->Redirect_local($redirect_url, 301);
		
	}
}

$core->Register_hook('plugin/config/after-update/pc_shop:currency', 'pc_shop_after_change_config_currency');
$core->Register_hook('before_load_page', 'pc_shop_set_user_currency');

