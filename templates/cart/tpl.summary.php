<?php
$currency = $this->core->Get_plugin_variable('currency', $this->plugin_name);
?>
<div class="row" id="cart_prices">
	<div class="col-9 col-sm-9 col-lg-9">
		<p class="text-right"><?php echo $this->core->Get_plugin_variable('items_price', $this->plugin_name) ?>:</p>
	</div>
	<div class="col-3 col-sm-3 col-lg-3">
		<p class="text-right"><strong><span id="tprice"><?php echo number_format($cart_data["totalPrice"], 2, ",", "") ?></span> <?php echo $currency ?></strong></p>
	</div>
	<div class="col-9 col-sm-9 col-lg-9">
		<p class="text-right"><?php echo $this->core->Get_plugin_variable('delivery_price', $this->plugin_name) ?>:</p>
	</div>
	<div class="col-3 col-sm-3 col-lg-3">
		<p class="text-right"><strong><span id="dprice"><?php echo number_format($cart_data["delivery_price"] + v($list["order_cod_price"], 0), 2, ",", "") ?></span> <?php echo $currency ?></strong></p>
	</div>
	<div class="col-9 col-sm-9 col-lg-9">
		<p class="lead text-right"><?php echo $this->core->Get_plugin_variable('cart_full_price', $this->plugin_name) ?>:</p>
	</div>
	<div class="col-3 col-sm-3 col-lg-3">
		<p class="lead text-right"><span id="fprice"><?php echo number_format($cart_data["full_price"], 2, ",", "") ?></span> <?php echo $currency ?></p>
	</div>
	<div class="col-xs-12">
		<div class="btn-group pull-right">
			<!-- <button type="button" class="btn btn-primary">Continue shopping</button> -->
			<a href="<?php echo  $order_fast_url  ?>" class="btn btn-success"><?php echo $this->core->Get_plugin_variable('go_to_order', $this->plugin_name) ?></a>
		</div>
	</div>
</div>

<?php 
return
?>

<div id="cart_prices">
	<div class="cart_price"><?php echo $this->core->Get_plugin_variable('items_price', $this->plugin_name) ?>: <span><span id="tprice"><?php echo number_format($cart_data["totalPrice"], 2, ",", "") ?></span> <?php echo $this->core->Get_plugin_variable('currency', $this->plugin_name) ?></span></div>
	<div class="clear"></div>
	<div class="cart_price"><?php echo $this->core->Get_plugin_variable('delivery_price', $this->plugin_name) ?>: <span><span id="dprice"><?php echo number_format($cart_data["delivery_price"] + v($list["order_cod_price"], 0), 2, ",", "") ?></span> <?php echo $this->core->Get_plugin_variable('currency', $this->plugin_name) ?></span></div>
	<div class="clear"></div>
	<div class="full_price"><?php echo $this->core->Get_plugin_variable('cart_full_price', $this->plugin_name) ?>: <span><span id="fprice"><?php echo number_format($cart_data["full_price"], 2, ",", "") ?></span> <?php echo $this->core->Get_plugin_variable('currency', $this->plugin_name) ?></span></div>
	<div class="clear"></div>
	<a class="btn btn-success button fr" href="<?php echo  $order_fast_url  ?>"><?php echo $this->core->Get_plugin_variable('go_to_order', $this->plugin_name) ?><span></span></a>
	<div class="clear"></div>
</div>



