<script language="JavaScript">
$(document).ready(function(){
		$(".sort_select select").change(function(){
			if(this.value && this.value != '') {
				document.location = this.value;
			}
		});
	});
</script>
