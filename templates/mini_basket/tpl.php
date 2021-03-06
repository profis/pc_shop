<?php
/**
 * @var PC_plugin_pc_shop_checkout_user_widget $this
 * @var string $tpl_group
 * @var string $cart_url
 * @var bool $highlight_cart
 * @var array $cart_data
 */
?>
<!-- CART -->
<div id="mini_cart" class="panel panel-default side_block <?php echo $highlight_cart?'highlight_cart':''; ?>">
	<div class="panel-heading"><?php echo $this->core->Get_plugin_variable('cart', $this->plugin_name) ?> <span class="badge pull-right"><?php echo $cart_data["totalQuantity"] ?></span></div>
	<div class="panel-body">
		<div class="row">
			<div class="col-8 col-sm-8 col-lg-8 col-xs-8 col-sm-8"><p><?php echo $this->core->Get_plugin_variable('delivery_price', $this->plugin_name) ?>:</p></div>
			<div class="col-4 col-sm-4 col-lg-4 col-xs-4 col-sm-2"><p class="pull-right"><?php echo $cart_data["delivery_price"] ?> <?php echo $this->price->get_user_currency() ?></p></div>
		</div>
		<div class="row">
			<div class="col-8 col-sm-8 col-lg-8 col-xs-8 col-sm-8"><p><?php echo $this->core->Get_plugin_variable('items_price', $this->plugin_name) ?>:</p></div>
			<div class="col-4 col-sm-4 col-lg-4 col-xs-4 col-sm-2"><p class="pull-right"><?php echo $cart_data["totalPrice"] ?> <?php echo $this->price->get_user_currency() ?></p></div>
		</div>
		<div class="row">
			<div class="col-8 col-sm-8 col-lg-8 col-xs-8 col-sm-8"><p><?php echo $this->core->Get_plugin_variable('cart_full_price', $this->plugin_name) ?>:</p></div>
			<div class="col-4 col-sm-4 col-lg-4 col-xs-4 col-sm-2"><p class="pull-right"><?php echo $cart_data["full_price"] ?> <?php echo $this->price->get_user_currency() ?></p></div>
		</div>
		<a href="<?php echo $cart_url; ?>" onclick="return !$(this).is('.disabled');" class="go_to_cart_btn btn btn-primary pull-right<?php if( !$cart_data['total'] ) echo ' disabled'; ?>" rel="nofollow" title=""><?php echo $this->core->Get_plugin_variable('go_to_cart', $this->plugin_name) ?> »</a>
	</div>
</div>
<!-- / CART -->


<?php
if ($highlight_cart) {
?>
<script type="text/javascript">
	$(document).ready(function(){
		var highlight_cart = $("#mini_cart");
		highlight_cart.fadeOut(100, function(){
			highlight_cart.fadeIn(100, function(){
				highlight_cart.fadeOut(100, function(){
					highlight_cart.fadeIn(100, function(){
						highlight_cart.fadeOut(100, function(){
							highlight_cart.fadeIn(100, function(){

							});
						});
					});
				});
			});
		});
	});
</script>

<?php
}
//print_pre($cart_data);