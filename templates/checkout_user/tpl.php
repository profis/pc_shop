
<div>
	<?php echo $this->Get_variable('go_to_order_anon') ?>
		<a href="<?php echo  $order_url  ?>" class="btn btn-success"><?php echo $this->core->Get_plugin_variable('go_to_order', $this->plugin_name) ?></a>
</div>

<div id="checkout_login">
	<?php echo $this->site->Get_widget_text('PC_plugin_pc_shop_checkout_login_widget', array(
		'redirect_url' => $order_url
	)); ?>
</div>

<div id="checkout_register">
	<?php echo $this->site->Get_widget_text('PC_plugin_pc_shop_checkout_register_widget', array()); ?>
</div>