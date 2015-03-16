<?php
/**
 * @var PC_plugin_pc_shop_categories_widget $this
 * @var string $tpl_group
 * @var array $categories
 */

/** @var PC_shop_item_resources $res */
$res = isset($category["resources"]) ? $category["resources"] : null;

if( $res && !empty($res->list) ) {
	$image = $this->core->Get_url("gallery").$res->Get_main_image(v($this->_config['list_item_thumb_type'], 'small'));
}
else {
	$image = $this->core->Get_theme_path().'img/no_photo_220x180.jpg';
}
?>

<div class="pull-left fl">
	
	<a href="<?php echo $category["link"] ?>">
		<div class="product_image center_image">
			<span><span><img src="<?php echo $image ?>" alt="<?php echo $category["name"] ?>" /></span></span>
		</div>
	</a>
	<a href="<?php echo $category["link"] ?>"><div class="name"><?php echo $category["name"] ?></div></a>
	
</div>



