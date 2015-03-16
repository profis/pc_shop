<?php
/**
 * @var PC_plugin_pc_shop_categories_widget $this
 * @var string $tpl_group
 * @var array $categories
 */
?><div><?php
	//print_pre($items);
	$count = 0;
	foreach ($categories as $category) {
		$count++;
		include $this->core->Get_tpl_path($tpl_group, 'tpl.list.item');
		if (isset($this->_config['per_row'])) {
			if ($count % $this->_config['per_row'] != 0){ ?>
			<div class="vline fl"></div>
			<?php }else { ?>
				<div class="clearfix"></div><hr />
			<?php }
		}
	}
?></div>