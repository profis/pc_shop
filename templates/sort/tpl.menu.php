<?php
/**
 * @var PC_plugin_pc_shop_sort_products_widget $this
 * @var string $tpl_group
 * @var string $base_url_full
 * @var string $base_url_row_full
 * @var bool $list_type_row
 * @var array $sort_option
 * @var array[] $menu
 */
?>
<select class="fl">
	<?php if (false) { ?>
		<option value="0" <?php echo !v($sort_option)?' selected':''?>> --- </option>
	<?php 
	} 
	foreach ($menu as $key => $menu_item) {
		$selected = v($menu_item['active']);
		if (!$selected and empty($key) and !v($sort_option)) {
			$selected = true;
		}
	?>	
		<option value="<?php echo $menu_item['link'] ?>" <?php echo $selected?' selected':''?>><?php echo $menu_item['name']?></option>
	<?php 
	} 
	?>
</select>

<?php
include $this->core->Get_tpl_path($tpl_group, 'tpl.menu.js');
?>	
