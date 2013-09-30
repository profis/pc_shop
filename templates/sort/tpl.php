<div id="visual" class="btn-group">
	<a href="<?php echo $base_url_full ?>" class="btn btn-default <?php echo !$list_type_row?'active':'' ?>"><span class="glyphicon glyphicon-th"></span></a>
	<a href="<?php echo $base_url_row_full ?>" class="btn btn-default <?php echo $list_type_row?'active':'' ?>"><span class="glyphicon glyphicon-th-list"></span></a>

	<div class="btn-group sort_select">
		<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?php echo $this->core->Get_plugin_variable('sort_by', 'pc_shop');?>
			<span class="caret"></span>
		</button>
		<?php
		include $this->core->Get_tpl_path($tpl_group, 'tpl.menu');
		?>	
	</div>
</div>

<?php
return;
?>

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