Product template
<?php
//$shop = $this->core->Get_object('PC_shop_site');
//$shop = $this->shop (controller already got this property by calling Get_object);
echo '<h2>Current path</h2><hr />';
$path = $this->Get_full_path();
for ($a=0; isset($path[$a]); $a++) {
	$i =& $path[$a];
	//print_pre($i);
	echo ($a>0?' -> ':'');
	$isCurrent = (v($i['type'])==='product'?$this->Is_current_product($i['id']):$this->Is_current_category($i['id']));
	if ($isCurrent) echo '<b>';
	echo '<a href="'.$this->site->Get_link(null, null, true, $i['link']).'">'.$i['name'].'</a>';
	if ($isCurrent) echo '</b>';
}
echo '<hr />';
echo '<h2>Current product</h2>';
print_pre($this->currentProduct);
echo '<h2>Current category</h2>';
print_pre($this->currentCategory);
?>