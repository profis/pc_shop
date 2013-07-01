<div id="order_success">
	<div class="ln_1"><span><div></div><?php
		
		echo $this->Get_variable('order_success') . '!';
		
	?></span></div>
	<div class="ln2"><?php
		
		$this->Include_template('ordered_success_summary');
		
	?></div>
</div>

