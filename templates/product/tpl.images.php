<div>
<?php
$images = $this->product['resources']->Get(false, 'thumb-small');
$big_images = $this->product['resources']->Get(false);
//print_pre($images);
foreach ($images as $key => $image) {
	?>
	<a href="<?php echo $gallery_url . $big_images[$key] ?>"><img src="<?php echo $gallery_url . $image ?>" /></a>
	<?php 
} ?>
</div>



