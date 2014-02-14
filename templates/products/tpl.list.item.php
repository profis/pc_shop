<?php

if (v($item["resources"]->list)){
	$image = $this->core->Get_url("gallery").$item["resources"]->Get_main_image($this->_config['list_item_thumb_type']);
}else {
	$image = $this->core->Get_theme_path().'img/no_photo_220x180.jpg';
}
$price = number_format($shop_products_site->get_price($item, $discount, $percentage_discount), 2, ",", "");

$item['price'] = number_format($item['price'], 2, ",", "");

//$price = $shop_products_site->number_format($price);

if (isset($_GET['rows'])) {
	include $this->core->Get_tpl_path($tpl_group, 'tpl.list.item._row'); 
}
else {
	include $this->core->Get_tpl_path($tpl_group, 'tpl.list.item._box');
}

?>



