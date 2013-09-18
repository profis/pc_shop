<?php
foreach ($cart_data['items'] as $key => $item) {
	include $this->core->Get_tpl_path($tpl_group, 'tpl.list.item');
}

?>