<?php
/**
 * @var PC_plugin_pc_shop_sort_products_widget $this
 * @var string $tpl_group
 */
?>
<script language="JavaScript">
$(document).ready(function(){
		$(".sort_select select").change(function(){
			if(this.value && this.value != '') {
				document.location = this.value;
			}
		});
	});
</script>
