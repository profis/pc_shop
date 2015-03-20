<?php
/**
 * @var PC_plugin_pc_shop_order_history_widget $this
 * @var string $tpl_group
 * @var PC_params $params
 * @var array[] $orders
 * @var array $cart_data
 */
global $site_users;
if ($site_users and $site_users->Is_logged_in()) {
	//$user_id = $site_users->GetID();
	include $this->core->Get_tpl_path($tpl_group, 'tpl.list');
}
else {
	echo $this->site->Get_widget_text('PC_plugin_pc_shop_checkout_login_widget', array(
		//'redirect_url' => $order_url
	));
}


return;
?>


<div id="product_order">
	<h1><?php echo $this->core->Get_plugin_variable('order', $this->plugin_name); ?></h1>
	<div class="row">
		<div class="col-xs-12">
			<?php 
			include $this->core->Get_tpl_path($tpl_group, 'tpl.form');
			include $this->core->Get_tpl_path($tpl_group, 'tpl.list');
			include $this->core->Get_tpl_path($tpl_group, 'tpl.summary');
			include $this->core->Get_tpl_path($tpl_group, 'tpl.coupon');
			?>
		</div>
	</div>	
</div>