<?php
/**
 * @var PC_plugin_pc_shop_order_history_widget $this
 * @var string $tpl_group
 * @var PC_params $params
 * @var array[] $orders
 * @var array $cart_data
 */
?>
<form action="" method="POST">
	
	Apply coupon: <input type="text" name="pc_shop_coupon" value="<?php echo v($cart_data["coupon"])?>">
	<input class="btn btn-default" type="submit" name="apply_coupon" value="Apply">
	
</form>

