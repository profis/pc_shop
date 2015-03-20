<?php
/**
 * @var PC_plugin_pc_shop_order_history_widget $this
 * @var string $tpl_group
 * @var PC_params $params
 * @var array[] $orders
 * @var array $cart_data
 * @var int $key
 * @var array $item
 */
?>
<tr qty="<?php echo $item['quantity'] ?>" prid="<?php echo $item['id'] ?>" key="<?php echo $key ?>">
	<td class="name"><a href="<?php echo $item['link'] ?>"><?php echo $item['name'] ?></a></td>
	<td class="quantity"><?php echo $item['basket_quantity'] ?></td>			
	<td class="price"><span class="price_val"><?php echo  number_format($item['totalPrice'], 2, ",", "") ?></span> <?php echo $this->price->get_user_currency() ?><span class="del_from_cart"></span></td>
</tr>