<div id="currency-selector">
	<div class="dropdown">
		<label><?php echo $this->core->Get_plugin_variable('currency', $this->plugin_name)?>:</label>
		<a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0)"><?php echo $this->price->get_user_currency()?><span class="caret"></span></a>
		<ul class="dropdown-menu pull-right">
			<?php
			foreach ($currencies as $currency) {
			?>
				<li><a href="<?php echo $currency['link']?>"><?php echo $currency['code']?></a></li>
			<?php
			}
			?>
		</ul>
	</div>			
</div>