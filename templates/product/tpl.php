<?php 
$product_name = trim($this->product['custom_name']);
if (empty($product_name)) {
	$product_name = $this->product['name'];
}
//print_pre($this->product['attributes']);
$attribute_categories = $this->shop->attributes->Get_categorized_keys($this->product['attributes']);
//print_pre($attribute_categories);
?>

<h1><?php echo $product_name?></h1>

<div>
<?php
include $this->core->Get_tpl_path($tpl_group, 'tpl.images');
?>
</div>

<?php
//print_pre($product_variants);
foreach ($product_variants as $product_variant) {

?>
	<h4><?php echo $product_variant['name']?></h4>
	<div>
	<?php
	include $this->core->Get_tpl_path($tpl_group, 'tpl.price');
	?>
	</div>

	<div>
	<?php
	include $this->core->Get_tpl_path($tpl_group, 'tpl.to_basket');
	?>
	</div>

<?php
}
?>

<!-- ATTRIBUTES -->
<div id="product_attributes">
	<ul class="nav nav-tabs" id="myTab">
		<li class="active"><a href="#description"><?php echo $this->core->Get_plugin_variable('description', $this->plugin_name)?></a></li>
		<?php
		foreach ($attribute_categories as $key => $attribute_category) {
			if (empty($attribute_category['attributes'])) {
				continue;
			}
			?>
			<li><a href="#attr_category_<?php echo $key?>"><?php echo v($attribute_category['name'], $this->core->Get_plugin_variable('attributes', $this->plugin_name))?></a></li>
			<?php
		}
		?>
	</ul>

	<div class="tab-content">
		
		<div class="tab-pane active" id="description"><?php echo $this->product['description'];?></div>
		<?php
		foreach ($attribute_categories as $key => $attribute_category) {
			if (empty($attribute_category['attributes'])) {
				continue;
			}
			?>
			<div class="tab-pane" id="attr_category_<?php echo $key?>">
				<table class="table table-hover table-striped">
					<!--<thead>
						<tr>
							<th>Object</th>
							<th>Description</th>
						</tr>
					</thead>-->
					<tbody>
				<?php
				foreach ($attribute_category['attributes'] as $attribute_key) {
				?>
					<tr>
						<td><?php echo $this->product['attributes'][$attribute_key]['name']?></td>
						<td><?php echo $this->product['attributes'][$attribute_key]['value']?></td>
					</tr>
				<?php
				}
				?>
					</tbody>
				</table>
			</div>
					
			<?php
		}
		?>
	</div>
</div>
<!-- / ATTRIBUTES -->

<?php

//print_pre($this->product);
//return;
?>
