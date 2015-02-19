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
foreach ($cart_data['items'] as $key => $item) {
	include $this->core->Get_tpl_path($tpl_group, 'tpl.list.item');
}
