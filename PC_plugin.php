<?php
$clsPath = dirname(__FILE__).'/classes/';
Register_class_autoloader('PC_shop', $clsPath.'/PC_shop.php');
Register_class_autoloader('PC_shop_site', $clsPath.'/PC_shop_site.php');
Register_class_autoloader('PC_shop_manager', $clsPath.'/PC_shop_manager.php');
Register_class_autoloader('PC_shop_plugin', $clsPath.'/PC_shop_plugin.php');

$pluginCls = $this->core->Get_object('PC_shop_plugin', $plugin_name);

$core->Register_hook('core/editor/load-page/'.$plugin_name, array($pluginCls, 'Load_page_for_editor'));
$core->Register_hook('core/editor/save-page/'.$plugin_name, array($pluginCls, 'Save_page_for_editor'));
$core->Register_hook('core/tree/get-childs/'.$plugin_name, array($pluginCls, 'Get_childs_for_tree'));
$core->Register_hook('core/tree/search', array($pluginCls, 'Search_tree'));