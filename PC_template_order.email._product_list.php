 <?php
	//print_pre($this->order_data);
 ?>
<br /> <?php echo $this->Get_variable('order_id');?>: <strong><?php echo $this->order_data['id'];?></strong><br /><br />


<table>
	<?php 
	$common_fields = array('name', 'phone', 'email', 'address', 'comment');
	foreach ($common_fields as $key => $field) {
		if (!empty($this->order_data[$field])) {
		?>		
		
		<tr>
			<td><?php echo $this->Get_variable('order_' . $field);?></td>
			<td><?php echo $this->order_data[$field];?></td>
		</tr>
	
		<?php
		}
	}
	?>
	
	<tr>
		<td></td>
		<td></td>
	</tr>
	
<?php

foreach ($this->order_data['data'] as $key => $value) {
	if (!empty($value['value'])) {
?>
	<tr>
		<td><?php echo $value['name'];?></td>
		<td><?php echo $value['value'];?></td>
	</tr>
<?php
	}
}
?>
</table>

<br />

<table>
	<tr>
		<td><?php echo $this->Get_variable('product_name');?></td>
		<td><?php echo $this->Get_variable('item_amount');?></td>
		<td><?php echo $this->Get_variable('item_price');?></td>
		<td><?php echo $this->Get_variable('same_items_price');?></td>
	</tr>
<?php
	
foreach ($this->order_data['items'] as $item) {

?>
	<tr>
		<td><?php echo $item['name'];?></td>
		<td><?php echo $item['quantity'];?></td>
		<td><?php echo $item['price'];?> <?php echo $this->order_data['currency'];?></td>
		<td><?php echo PC_shop_product_model::format_price($item['price'] * $item['quantity']);?> <?php echo $this->order_data['currency'];?></td>
	</tr>
<?php
}
?>
</table>

<br />

<?php
$payment = PC_shop_payment_option_model::get_option_name(v($this->order_data['payment_option']));
if ($payment) {
?>
	<strong> <?php echo $this->Get_variable('payment');?></strong>: <?php echo $payment;?><br />
<?php
}

$delivery = PC_shop_delivery_option_model::get_option_name(v($this->order_data['delivery_option']));
if ($delivery) {
?>
	<strong> <?php echo $this->Get_variable('delivery');?></strong>: <?php echo $delivery;?><br />
<?php
}

?>

<?php
if (v($this->order_data['delivery_price'], 0) > 0) {
?>
	<strong> <?php echo $this->Get_variable('delivery_price');?></strong>: <?php echo $this->order_data['delivery_price'];?> <?php echo $this->order_data['currency'];?><br />
<?php
}

if (v($this->order_data['cod_price'], 0) > 0) {
?>
	<strong> <?php echo $this->Get_variable('cod_price');?></strong>: <?php echo $this->order_data['cod_price'];?> <?php echo $this->order_data['currency'];?><br />
<?php
}

?>
<br />
<?php
?>
<strong> <?php echo $this->Get_variable('order_total_price');?></strong>: <?php echo $this->order_data['total_price'];?> <?php echo $this->order_data['currency'];?><br />
<?php

?>
