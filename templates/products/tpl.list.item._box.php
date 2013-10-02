<div class="col-6 col-sm-6 col-lg-4">
	<div class="thumbnail">
		<img data-src="holder.js/300x200" alt="">
		<div class="caption">
			<h3><?php echo $item["name"] ?></h3>
			<p><?php echo $item["short_description"] ?> </p>
			<p>
				<?php 
				include $this->core->Get_tpl_path($tpl_group, 'tpl.list.item.price'); 
				?>
			</p>
			<p>
				<a href="<?php echo $item["link"] ?>" title="" class="btn btn-primary"><?php echo $this->Get_variable('details'); ?> »</a>
				<?php 
				include $this->core->Get_tpl_path($tpl_group, 'tpl.list.item.to_basket'); 
				?>
			</p>
		</div>
	</div>
</div>