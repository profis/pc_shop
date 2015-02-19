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

if ($item['quantity'] > 0) {
	?><form method="POST" action="">
		<input type="hidden" name="product_id" value="<?php echo $item["id"] ?>" />
		<input type="hidden" name="add_to_basket" value="1" />
		<span class="to_cart btn btn-success" onclick="this.parentNode.submit();return false;"><?php echo $this->core->Get_plugin_variable('to_basket', 'pc_shop') ?></span>
	</form><?php
} else {  
	?><span class="sold_out btn"><?php echo $this->core->Get_plugin_variable('sold_out', 'pc_shop') ?></span><?php
}  
