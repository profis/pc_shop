<?php
if ($this->_tpl_is_paid) {
	echo $this->Get_variable('new_paid_order_to_admin'); 
}else {
	echo $this->Get_variable('new_order_to_admin'); 
}
	
?>

<br />

<?php
	$this->Include_template('order.email._product_list');
?>