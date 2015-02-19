<?php
/**
 * @var PC_plugin_pc_shop_order_widget $this
 * @var string $tpl_group
 * @var array $cart_data
 * @var array $order_data
 * @var array $coupon_data
 * @var array $delivery_options
 * @var array $payment_options
 * @var string $currency
 */
?>
<table class="cart" style="width:100%;">
	<tr class="header">
		<td class="first"><?php echo $this->core->Get_plugin_variable('product_name', $this->plugin_name) ?></td>
		<td><?php echo $this->core->Get_plugin_variable('item_amount', $this->plugin_name) ?></td>
		<td><?php echo $this->core->Get_plugin_variable('price', $this->plugin_name) ?></td>
	</tr>
<?php

foreach ($cart_data['items'] as $key => $item) {
	include $this->core->Get_tpl_path($tpl_group, 'tpl.list.item');
}

?>
</table>
