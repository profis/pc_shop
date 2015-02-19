<?php
/**
 * @var PC_plugin_pc_shop_products_widget $this
 * @var PC_shop_products_site $shop_products_site
 * @var string $tpl_group
 * @var string $base_url
 * @var PC_params $params
 * @var array $items
 * @var array $item
 * @var string $image
 * @var string $price
 * @var float $discount
 * @var float $percentage_discount
 */

if ($discount) {
	?><div class="shop_price fl"><?php echo $item['price']; ?></div><?php
}
	
if ($percentage_discount) {
	?><div class="discount ptsans fb fi">- <?php echo floor($item['percentage_discount']); ?>%</div><?php
}

?><div class="price fr"><?php echo $this->core->Get_plugin_variable('price', 'pc_shop'); ?>: <strong><?php echo $price; ?> <?php echo $this->price->get_user_currency(); ?></strong></div>