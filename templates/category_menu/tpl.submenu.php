<?php
/**
 * @var string $tpl_group
 * @var array[] $menu
 * @var bool $showProductCount
 */
if (count($menu)) {
	?><ul><?php
		foreach ($menu as $menu_item) {
			?><li <?php if (v($menu_item['_active'])) {?> class="active" <?php }?>><a href="<?php echo $menu_item['link']; ?>"><?php echo $menu_item['name']?><?php echo ((isset($showProductCount) && $showProductCount) ? (' <span>(' . $menu_item['productCount'] . ')</span>') : ''); ?></a></li><?php

			if (v($menu_item['_submenu']))
				$menu = $menu_item['_submenu'];
			elseif (v($menu_item['children']))
				$menu = $menu_item['children'];
			else
				$menu = null;

			if ($menu)
				include $this->core->Get_tpl_path($tpl_group, 'tpl.submenu');
		}
	?></ul><?php
}



