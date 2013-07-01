<?php if ($discount) {  ?>
	<div class="shop_price fl"><?php echo $item['price'] ?></div>
<?php } ?>
	
<?php if ($percentage_discount){  ?>
	<div class="discount ptsans fb fi">- <?php echo floor($percentage_discount) ?>%</div>
<?php } ?>

<div class="price fr"><?php echo $this->core->Get_plugin_variable('price', 'pc_shop') . ': ' . $price ?></div>