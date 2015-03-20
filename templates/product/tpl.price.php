<?php
/**
 * @var PC_plugin_pc_shop_product_widget $this
 * @var string $tpl_group
 * @var string $gallery_url
 * @var array $product_variants
 * @var array $product_variant
 * @var array $item
 * @var string $price
 * @var string $discount
 * @var string $percentage_discount
 */
?>
<?php if ($product_variant['discount'] or $product_variant['percentage_discount']) {  ?>
	<div class="shop_price fl"><?php echo $product_variant['price'] ?></div>
<?php } ?>
	
<?php if ($product_variant['percentage_discount']){  ?>
	<div class="discount ptsans fb fi">- <?php echo floor($product_variant['percentage_discount']) ?>%</div>
<?php } ?>

<div class="price fr"><?php echo $this->core->Get_plugin_variable('price', 'pc_shop') . ': ' . $product_variant['real_price'] ?></div>