<form action="<?php echo $base_url ?>">
<?php 
//print_pre($manufacturers);
//print_pre($_GET);
//print_pre($category_products_data);
if (!empty($manufacturers)) {
?>
	<fieldset><legend><?php echo $this->core->Get_plugin_variable('manufacturer', $this->plugin_name)?></legend>
	<?php
	foreach ($manufacturers as $key => $manufacturer) {
		$id = 'manufacturer_' . $manufacturer['id'];
		$checked = '';
		if (isset($_GET['manufacturers']) and in_array($manufacturer['id'], $_GET['manufacturers'])) {
			$checked = ' CHECKED ';
		}
		?>
		<label for="<?php echo $id ?>"><?php echo $manufacturer['name'] ?>(<?php echo $manufacturer['count'] ?>)</label><input <?php echo $checked ?> name="manufacturers[]" id="<?php echo $id ?>" type="checkbox" value="<?php echo $manufacturer['id'] ?>">
		<?php
	}
	?>
	</fieldset>
<?php
}
if (isset($category_products_data) and !empty($category_products_data)) {
	$price_from = $category_products_data['min_price'];
	$price_to = $category_products_data['max_price'];
	$price_from_in_field = v($_GET['price_from'], $price_from);
	$price_to_in_field = v($_GET['price_to'], $price_to);
	
?>
	<fieldset><legend><?php echo $this->core->Get_plugin_variable('price', $this->plugin_name) ?></legend>
	<?php echo $this->core->Get_plugin_variable('price_from', $this->plugin_name) ?> (<?php echo $price_from ?>)<input type="text" value="<?php echo pc_e($price_from_in_field) ?>" name="price_from">
	<?php echo $this->core->Get_plugin_variable('price_to', $this->plugin_name) ?> (<?php echo $price_to ?>)<input type="text" value="<?php echo pc_e($price_to_in_field) ?>" name="price_to">
	</fieldset>
<?php
}

foreach ($filters as $filter) {
	?>
	<fieldset><legend><?php echo $filter['name']?></legend>
	<?php
	$name = 'attribute_' . $filter['id'];
	foreach ($filter['filters'] as $filter_value) {
		$id = 'attribute_'.$filter['id'] . '_' . $filter_value['id'];
		$checked = '';
		if (isset($_GET[$name]) and in_array($filter_value['id'], $_GET[$name])) {
			$checked = ' CHECKED ';
		}
		?>
		<label for="<?php echo $id ?>"><?php echo $filter_value['value'] ?>(<?php echo $filter_value['count'] ?>)</label><input <?php echo $checked ?> name="<?php echo $name?>[]" id="<?php echo $id ?>" type="checkbox" value="<?php echo $filter_value['id'] ?>">
		<?php
	}
	?>
	</fieldset>
	<?php
}

?>
<input class="btn btn-success" type="submit" value="<?php echo $this->core->Get_plugin_variable('filter_button', $this->plugin_name) ?>" name="filter">
<a class="btn btn-inverse" href="<?php echo $this->site->Get_link() ?>"><?php echo $this->core->Get_plugin_variable('filter_cancel', $this->plugin_name) ?></a>
</form>

<?php

//print_pre($cart_data);