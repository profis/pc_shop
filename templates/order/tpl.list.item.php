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
 * @var int $key
 * @var array $item
 */
?>
<tr qty="<?php echo $item['quantity']; ?>" prid="<?php echo $item['id']; ?>" key="<?php echo $key; ?>">
	<td class="name"><a href="<?php echo htmlspecialchars($item['link']); ?>"><?php echo htmlspecialchars($item['name']); ?></a></td>
	<td class="quantity"><?php echo $item['basket_quantity']; ?></td>
	<td class="price"><span class="price_val"><?php echo number_format($item['totalPrice'], 2, ",", " "); ?></span> <?php echo $currency; ?></td>
</tr>