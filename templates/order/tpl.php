<div id="product_order">
	<h1><?php echo $this->core->Get_plugin_variable('order', $this->plugin_name); ?></h1>
	<div class="row">
		<div class="col-xs-12">
			<?php 
			include $this->core->Get_tpl_path($tpl_group, 'tpl.form');
			include $this->core->Get_tpl_path($tpl_group, 'tpl.list');
			include $this->core->Get_tpl_path($tpl_group, 'tpl.summary');
			?>
		</div>
	</div>	
</div>