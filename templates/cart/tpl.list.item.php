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
 * @var int $key
 * @var array $item
 */

/*
// this might be helpful if you need to show an image in the cart
$product = $cart_data['products'][$item['product_id']];
if (v($product["resources"]->list)){
	$image = $this->core->Get_url("gallery").$product["resources"]->Get_main_image('thumb-cart-item');
}else {
	$image = $this->core->Get_theme_path().'img/no_photo_80x80.png';
}
*/

?>
<div class="row" qty="<?php echo $item['basket_quantity']; ?>" prid="<?php echo $item['id']; ?>" key="<?php echo $key; ?>">
	<div class="col-6 col-sm-6 col-lg-6">
		<strong><a href="<?php echo htmlspecialchars($item['link']); ?>"><?php echo htmlspecialchars($item['name']); ?></a></strong>
	</div>
	<div class="col-3 col-sm-3 col-lg-3">
		<div class="input-group">
			<span class="input-group-btn">
				<button class="btn btn-primary minus" type="button"><span class="glyphicon glyphicon-minus"></span></button>
			</span>
			<input type="text" class="form-control cart_quantity" value="<?php echo $item['basket_quantity']; ?>">
			<span class="input-group-btn">
				<button class="btn btn-primary plus" type="button"><span class="glyphicon glyphicon-plus"></span></button>
			</span>
		</div>
	</div>
	<div class="col-1 col-sm-1 col-lg-1 pull-right">
		<button class="btn btn-danger del_from_cart" type="button"><span class="glyphicon glyphicon-remove"></span></button>
	</div>
	<div class="col-2 col-sm-2 col-lg-2 pull-right">
		<strong><span class="total_price_val"><?php echo number_format($item['totalPrice'], 2, ",", " "); ?></span> <?php echo $currency; ?></strong>
	</div>
</div>

