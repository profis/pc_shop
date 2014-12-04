<?php
/**
 * @var PC_plugin_pc_shop_order_widget $this
 * @var string $tpl_group
 * @var array $cart_data
 * @var array $order_data
 */
?>
<div id="product_order">
	<h1><?php echo $this->core->Get_plugin_variable('order', $this->plugin_name); ?></h1>
	<div class="row">
		<div class="col-xs-12">
			<form method="post" id="order_form" action="" class="form-horizontal" role="form"><?php
				include $this->core->Get_tpl_path($tpl_group, 'tpl.form');
				include $this->core->Get_tpl_path($tpl_group, 'tpl.list');
				include $this->core->Get_tpl_path($tpl_group, 'tpl.summary');
				include $this->core->Get_tpl_path($tpl_group, 'tpl.coupon');
			?></form>
		</div>
	</div>	
</div>