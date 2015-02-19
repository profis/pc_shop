<?php
/**
 * @var PC_plugin_pc_shop_products_widget $this
 * @var string $tpl_group
 * @var string $base_url
 * @var PC_params $params
 * @var array $items
 */

?><div id="product_list"><?php
	//print_pre($items);
	$count = 0;
	foreach ($items as $item) {
		$count++;
		include $this->core->Get_tpl_path($tpl_group, 'tpl.list.item');
		if ($this->_config['per_row']) {
			if ($count % $this->_config['per_row'] != 0){ ?>
				<div class="vline fl"></div>
			<?php }
			else { ?>
				<div class="clearfix"></div><hr />
			<?php }
		}

	}
?></div>
