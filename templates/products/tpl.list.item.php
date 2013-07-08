<?php

if (v($item["resources"]->list)){
	$image = $this->core->Get_url("gallery").$item["resources"]->Get_main_image($this->_config['list_item_thumb_type']);
}else {
	$image = $this->core->Get_theme_path().'img/no_photo_220x180.jpg';
}
$shop_products_site->get_price($item, $discount, $percentage_discount);
$price = number_format($shop_products_site->get_price($item, $discount, $percentage_discount), 2, ",", "");

$item['price'] = number_format($item['price'], 2, ",", "");

//$price = $shop_products_site->number_format($price);

?>

<div class="pull-left fl">
	
	<a href="<?php echo $item["link"] ?>">
		<div class="product_image center_image">
			<span><span><img src="<?php echo $image ?>" alt="<?php echo $item["name"] ?>" /></span></span>
		</div>
	</a>
	<a href="<?php echo $item["link"] ?>"><div class="name"><?php echo $item["name"] ?></div></a>

	<?php 
	include $this->core->Get_tpl_path($tpl_group, 'tpl.list.item.price'); 
	?>
	
	<div class="clear"></div>
	<?php 
	include $this->core->Get_tpl_path($tpl_group, 'tpl.list.item.to_basket'); 
	?>
	
</div>


