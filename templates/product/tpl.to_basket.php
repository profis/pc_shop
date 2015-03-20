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

if ($item['quantity'] > 0){  ?>
	<form method="POST" action="">
		<input type="hidden" name="product_id" value="<?php echo $item["id"] ?>" />
		<input type="hidden" name="add_to_basket" value="1" />
		<?php
		if (v($product_variant['post_price_attributes']) and is_array(v($product_variant['post_price_attributes']))) {
			foreach ($product_variant['post_price_attributes'] as $product_variant_price_attr_id => $product_variant_price_attr_value) {
			?>
				<input type="hidden" name="attributes[<?php echo $product_variant_price_attr_id ?>]" value="<?php echo $product_variant_price_attr_value ?>" />
			<?php
			}
		}
		?>
		<div class="to_cart btn btn-success" onclick="this.parentNode.submit();return false;"><?php echo $this->core->Get_plugin_variable('to_basket', 'pc_shop') ?></div>
	</form>
<?php 
} else {  
?>
	<div class="sold_out btn"><?php echo $this->core->Get_plugin_variable('sold_out', 'pc_shop') ?></div>
<?php 
}  
?>



