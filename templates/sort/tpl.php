<div class= 'controll_top'>
	<div class="sort_select fl">
		<label class="fl"><?php echo $this->core->Get_plugin_variable('sort_by', 'pc_shop');?>:</label>
		<?php
		include $this->core->Get_tpl_path($tpl_group, 'tpl.menu');
		?>	
	</div>
	
	
	<div class="clear"></div>
</div>

<?php
//print_pre($menu);