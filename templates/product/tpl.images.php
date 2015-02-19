<?php
/**
 * @var PC_plugin_pc_shop_product_widget $this
 * @var string $tpl_group
 * @var string $gallery_url
 * @var array $product_variants
 * @var array $item
 * @var string $price
 * @var string $discount
 * @var string $percentage_discount
 */

$images = array();
if( $this->product['resources'] ) {
	foreach( $this->product['resources']->list as $img )
		if( !$img['is_attachment'] )
			$images[] = $img['file_id'];
}

if( !empty($images) ) {
	?><div class="images pull-left"><?php
	echo $this->site->Get_widget_text('PC_gallery_widget', array(
		'thumbnailMode' => 'contain',
		'items' => $images,
		'style' => 'light',
	));
	?></div><?php
}
