<?php
$hide_empty_cart = false;
if (isset($cart_data['items']) and count($cart_data['items'])) {
	include $this->core->Get_tpl_path($tpl_group, 'tpl.list');
	include $this->core->Get_tpl_path($tpl_group, 'tpl.summary');
	$this->site->Add_script('plugins/' . $this->plugin_name . '/js/number.format.min.js');
	$this->site->Add_script('plugins/' . $this->plugin_name . '/js/shop.cart.js');
	$hide_empty_cart = true;
}

include $this->core->Get_tpl_path($tpl_group, 'tpl.empty');
