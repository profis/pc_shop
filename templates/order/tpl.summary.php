	<div id="cart_prices">
		<div class="cart_price"><?php echo $this->core->Get_plugin_variable('items_price', $this->plugin_name) ?>: <span><span id="tprice"><?php echo number_format($cart_data["totalPrice"], 2, ",", "") ?></span> <?php echo $this->core->Get_plugin_variable('currency', $this->plugin_name) ?></span></div>
		<div class="clear"></div>
		<div class="cart_price"><?php echo $this->core->Get_plugin_variable('delivery_price', $this->plugin_name) ?>: <span><span id="dprice"><?php echo number_format($cart_data["delivery_price"] + v($list["order_cod_price"], 0), 2, ",", "") ?></span> <?php echo $this->core->Get_plugin_variable('currency', $this->plugin_name) ?></span></div>
		<div class="clear"></div>
		<?php if (true or $this->cfg['pc_shop']['cod_price'] > 0) { ?>
		<div class="cart_price"><?php echo $this->core->Get_plugin_variable('cod_price', $this->plugin_name) ?>: <span><span id="pprice"><?php echo number_format($cart_data["order_cod_price"], 2, ",", "") ?></span> <?php echo $this->core->Get_plugin_variable('currency', $this->plugin_name) ?></span></div>
		<div class="clear"></div>
		<?php } ?>
		<div class="full_price"><?php echo $this->core->Get_plugin_variable('cart_full_price', $this->plugin_name) ?>: <span><span id="fprice"><?php echo number_format($cart_data["full_price"], 2, ",", "") ?></span> <?php echo $this->core->Get_plugin_variable('currency', $this->plugin_name) ?></span></div>
		<div class="clear"></div>
		<input type="submit" class="btn btn-success button fr" value="<?php echo $this->core->Get_plugin_variable('order_finish', $this->plugin_name) ?>" />
		<div class="clear"></div>
	</div>
</form>
