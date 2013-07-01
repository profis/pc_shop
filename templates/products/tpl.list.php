<div>
<?php
//print_pre($items);
$count = 0;
foreach ($items as $item) {
	$count++;
	include $this->core->Get_tpl_path($tpl_group, 'tpl.list.item');
	if ($count % $this->_config['per_row'] != 0){ ?>
		<div class="vline fl"></div>
	<?php }else { ?>
		<div class="clearfix"></div><hr />
	<?php } 
} ?>
</div>
