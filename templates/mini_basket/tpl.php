<?php 
	
?>

<div id="shop_mini_basket" class="side_element blue <?php echo $highlight_cart?'highlight_cart':''; ?>">
	<div class="top"></div>
	<div class="middle">
		<div class="cart_left">
			<div class="title ptsans fb"> <?php echo $this->core->Get_plugin_variable('cart', $this->plugin_name) ?> <span>(<span id="cqty"><?php echo $cart_data["totalQuantity"] ?></span>)</span></div>
			<div class="line fl"> <?php echo $this->core->Get_plugin_variable('delivery_price', $this->plugin_name) ?>:</div>
			<div class="line strong fr"><span id="cdprice"> <?php echo $cart_data["delivery_price"] ?></span>  <?php echo $this->core->Get_plugin_variable('currency', $this->plugin_name) ?></div>
			<div class="line fl"> <?php echo $this->core->Get_plugin_variable('cart_full_price', $this->plugin_name) ?>:</div>
			<div class="line strong fr"><span id="cfprice"> <?php echo $cart_data["full_price"] ?></span>  <?php echo $this->core->Get_plugin_variable('currency', $this->plugin_name) ?></div>
			<div class="clear"></div>
			<a class="button btn btn-success" href=" <?php echo $cart_url ?>"> <?php echo $this->core->Get_plugin_variable('go_to_cart', $this->plugin_name) ?><span></span></a>
			<div class="clear"></div>
		</div>
	</div>
	<div class="bottom"></div>
</div>
<?php
if ($highlight_cart) {
?>
<script type="text/javascript">
	$(document).ready(function(){
		var highlight_cart = $("#shop_mini_basket");
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