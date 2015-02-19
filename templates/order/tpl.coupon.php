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
Apply coupon: <input type="text" name="pc_shop_coupon" value="<?php echo htmlspecialchars(v($cart_data["coupon"], '')); ?>">
<input class="btn btn-default" type="submit" name="apply_coupon" value="Apply">
