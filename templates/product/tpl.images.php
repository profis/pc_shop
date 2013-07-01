<div>
<?php
$images = $this->product['resources']->Get(false, 'small');
//print_pre($images);
foreach ($images as $image) {
	?>
		<img src="<?php echo $gallery_url . $image ?>" />
	<?php 
} ?>
</div>



