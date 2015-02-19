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
<div id="cart_prices">
	<div class="cart_price"><?php echo $this->core->Get_plugin_variable('items_price', $this->plugin_name) ?>: <span><span id="tprice"><?php echo number_format($cart_data["totalPrice"], 2, ",", " ") ?></span> <?php echo $currency; ?></span></div>
	<div class="clear"></div>
	<div class="cart_price"><?php echo $this->core->Get_plugin_variable('delivery_price', $this->plugin_name) ?>: <span><span id="dprice"><?php echo number_format($cart_data["order_delivery_price"] + v($list["order_cod_price"], 0), 2, ",", " ") ?></span> <?php echo $currency; ?></span></div>
	<div class="clear"></div>
	<div class="cart_price"><?php echo $this->core->Get_plugin_variable('cod_price', $this->plugin_name) ?>: <span><span id="pprice"><?php echo number_format($cart_data["order_cod_price"], 2, ",", " ") ?></span> <?php echo $currency; ?></span></div>
	<div class="clear"></div>
	<?php if (v($cart_data["total_discount"]) > 0) { ?>
	<div class="discount"><?php echo $this->core->Get_plugin_variable('discount', $this->plugin_name) ?>: <span> - <span id="discount_price"><?php echo number_format($cart_data["total_discount"], 2, ",", " ") ?></span> <?php echo $currency; ?></span></div>
	<div class="clear"></div>
	<?php } ?>
	<div class="full_price"><?php echo $this->core->Get_plugin_variable('cart_full_price', $this->plugin_name) ?>: <span><span id="fprice"><?php echo number_format($cart_data["order_full_price"], 2, ",", " ") ?></span> <?php echo $currency; ?></span></div>
	<div class="clear"></div>
	<input type="submit" id="next_step_btn" class="btn btn-success button fr<?php if( !empty($cart_data['errors']) ) echo ' disabled'; ?>" value="<?php echo $this->core->Get_plugin_variable('order_finish', $this->plugin_name) ?>" />
	<div class="clear"></div>
</div>
