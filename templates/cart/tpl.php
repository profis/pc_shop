<?php
$hide_empty_cart = false;
if (isset($cart_data['items']) and count($cart_data['items'])) {
	?>
	<!-- CART -->
	<div id="product_cart">
		<h1><?php echo $this->core->Get_plugin_variable('cart', $this->plugin_name) ?></h1>
	<?php
	include $this->core->Get_tpl_path($tpl_group, 'tpl.list');
	include $this->core->Get_tpl_path($tpl_group, 'tpl.summary');
	$hide_empty_cart = true;
	?>
	</div>
	<!-- / CART -->
	<?php
}

include $this->core->Get_tpl_path($tpl_group, 'tpl.empty');
