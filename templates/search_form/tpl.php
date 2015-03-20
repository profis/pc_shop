<?php
/**
 * @var PC_plugin_pc_shop_search_form_widget $this
 * @var string $tpl_group
 * @var string $search_url
 * @var string[] $search_values
 */
?>
<div id="search_bar">
	<form action="<?php echo $search_url ?>" method="_get">
		<div class="input over input-append fl">
			<label style="width: 895px; display: block;"><?php echo $this->core->Get_plugin_variable('search_label', $this->plugin_name) ?></label>
			<input name="q" id="q" type="text" class="text_input" value="<?php echo v($search_values['q']) ?>">
		</div>
		<input type="submit" class="search_submit fl" value="<?php echo $this->core->Get_plugin_variable('search_button', $this->plugin_name) ?>">
		<div class="clear"></div>
	</form>
</div>


