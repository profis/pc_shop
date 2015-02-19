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
 * @var bool $hide_empty_cart
 */
?>
<div class="empty_cart alert alert-error" <?php echo ($hide_empty_cart?'style="display:none;"':'') ?>><?php echo $this->core->Get_plugin_variable('cart_empty', $this->plugin_name)?></div>

