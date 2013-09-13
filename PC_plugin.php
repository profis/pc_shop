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

