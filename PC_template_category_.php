<?php
/**
 * @var PC_controller_pc_shop $this
 */
?>
Category template
<?php
//$shop = $this->core->Get_object('PC_shop_site');
//$shop = $this->shop (controller already got this property by calling Get_object);
echo '<h2>Current path</h2><hr />';
$path = $this->site->Get_page_path();
print_pre($path);
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

//Testing:
function getCategoryTreeNode($shop_categories_site, $cr_id){
	$cr_level = $shop_categories_site->Get(null, $cr_id);
	$tree = array();

	if (!empty($cr_level)){

		foreach($cr_level as $key => $value){
			if ($value["nomenu"] == 1) continue;
			$tree[$key] =  $value;
			//if ($value["hot"] != 1){
				$tree[$key]["children"] = getCategoryTreeNode($shop_categories_site, $value["id"]);
			//}
		}
	} 
	return $tree;
}

echo 'testing';
/* @var $shop_categories_site PC_shop_categories_site */
$shop_categories_site = $this->core->Get_object('PC_shop_categories_site');

///*
$params = array(
	'all_children' => array(
		'category_data' => $this->currentCategory,
	),
	'parse' => array(
		//'description' => true,
		//'attributes' => true,
		//'recources' => true,
		'recources' => 'first_img_only',
	)
);
$category_children = $shop_categories_site->Get(null, null, null, $params);
//$category_children = $shop_categories_site->Get(null, $this->currentCategory['id'], null, $params);

require_once CMS_ROOT . 'libs/explore/explore.php';
echo PC_explore::get_styles();
echo PC_explore::get_javascript();
echo 'Category children:';
echo explore($category_children);
//*/


$shop_products_site = $this->core->Get_object('PC_shop_products_site');

$flats = $shop_products_site->Get(null, $this->currentCategory["id"]);
$flat =  $shop_products_site->Get(2177);

echo 'all flats';
echo explore($flats);

echo 'one flat:';
echo explore($flat);

?>