<?php

if (count($menu)) {
?>
<ul>
	<?php
	foreach ($menu as $menu_item) {
		?>
		<li <?php if (v($menu_item['_active'])) {?> class="active" <?php }?>><a href="<?php echo $menu_item['link']?>"><?php echo $menu_item['name']?></a></li>
		<?php
		$menu = false;
		if (v($menu_item['_submenu'])) {
			$menu = $menu_item['_submenu'];
		}
		elseif (v($menu_item['children'])) {
			$menu = $menu_item['children'];
		}
		if ($menu) {
			include $this->core->Get_tpl_path($tpl_group, 'tpl.submenu');	
		}
	}
	?>
</ul>
<?php
}



