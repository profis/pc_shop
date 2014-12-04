<?php
	/**
	 * @var PC_plugin_pc_shop_delivery_form_widget $this
	 */
	if( !isset($data['form']) ) return;
?>
<div class="delivery-form"><?php
	foreach( $data['form'] as $inputName => $input ) {
		$value = v($data['form_data'][$inputName]);
		$id = 'delivery_form_data_' . $inputName;
		$name = 'delivery_form_data[' . $inputName . ']';
		switch( $input['type'] ) {
			case 'text':
				?><div class="form-group clearfix">
					<label class="control-label col-sm-4" for="<?php echo $id; ?>"><?php echo $input['label']; ?></label>
					<div class="col-sm-8">
						<input type="text" class="form-control" value="<?php echo htmlspecialchars($value); ?>" id="<?php echo $id; ?>" name="<?php echo $name; ?>" />
					</div>
				</div><?php
				break;

			case 'select':
				?><div class="form-group clearfix">
					<label class="control-label col-sm-4" for="<?php echo $id; ?>"><?php echo $input['label']; ?></label>
					<div class="col-sm-8">
						<select class="form-control" id="<?php echo $id; ?>" name="<?php echo $name; ?>"><?php
							if( isset($input['empty']) ) {
								?><option value=""><?php echo htmlspecialchars($input['empty']); ?></option><?php
							}
							if( isset($input['options']) ) {
								foreach( $input['options'] as $k => $v ) {
									?><option value="<?php echo htmlspecialchars($k); ?>"<?php echo ((string)$value === (string)$k) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($v); ?></option><?php
								}
							}
						?></select>
					</div>
				</div><?php
				break;
		}
	}
?></div>
