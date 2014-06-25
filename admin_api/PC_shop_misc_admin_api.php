<?php

class PC_shop_misc_admin_api extends PC_shop_admin_api {
	
	public function test() {
		echo 'test';
		
	}
	
	
	public function category_pid_parent_fix() {
		$model = new PC_shop_category_model();
		$model->absorb_debug_settings($this);
		echo $model->update(array(
				'pid' => 0
			), array(
				'where' => array(
					'not (pid = 0 OR pid is null OR parent_id = 0)'
				),
				//'query_only' => true
			)
		);
		
	}
	
}

?>
