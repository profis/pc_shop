<?php
/**
 * @var PC_controller_pc_shop $this
 */

if ($this->_tpl_is_paid) {
	echo $this->Get_variable('new_paid_order_to_buyer'); 
}else {
	echo $this->Get_variable('new_order_to_buyer'); 
}
?>

<br />

<?php
	$this->Include_template('order.email._product_list'); 
?>

