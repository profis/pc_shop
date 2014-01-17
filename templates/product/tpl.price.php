<?php if ($product_variant['discount'] or $product_variant['percentage_discount']) {  ?>
	<div class="shop_price fl"><?php echo $product_variant['price'] ?></div>
<?php } ?>
	
<?php if ($product_variant['percentage_discount']){  ?>
	<div class="discount ptsans fb fi">- <?php echo floor($product_variant['percentage_discount']) ?>%</div>
<?php } ?>

<div class="price fr"><?php echo $this->core->Get_plugin_variable('price', 'pc_shop') . ': ' . $product_variant['real_price'] ?></div>