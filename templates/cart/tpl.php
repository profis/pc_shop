<?php
/**
 * @var PC_plugin_pc_shop_cart_widget $this
 * @var string $tpl_group
 * @var string $order_url
 * @var string $order_fast_url
 * @var string $currency
 * @var array $coupon_data
 * @var array $order_data
 * @var array $cart_data
 */

$hide_empty_cart = false;

if (isset($cart_data['items']) and count($cart_data['items'])) {
	?><div id="product_cart">
		<h1><?php echo $this->core->Get_plugin_variable('cart', $this->plugin_name) ?></h1>
		<?php
			include $this->core->Get_tpl_path($tpl_group, 'tpl.list');
			include $this->core->Get_tpl_path($tpl_group, 'tpl.summary');
			$hide_empty_cart = true;
		?>
	</div><?php
}

include $this->core->Get_tpl_path($tpl_group, 'tpl.empty');
