if (!PC_plugin_dialog_pc_shop.tabs) {
	PC_plugin_dialog_pc_shop.tabs = {};
}

PC.plugin.pc_shop.crud_payment_options = Ext.extend(PC.ux.crud, {
	api_url: 'api/plugin/pc_shop/payment_options/',
	
	sortable: true,
	
	get_store_fields: function() {
		return [
			'names', {name: 'name', mapping: 'names', convert: function(names, n){return PC.utils.extractName(names);}},
			'id', 'code', 'enabled', 'login', 'payment_key', 'test'
		];
	},
	
	get_grid_columns: function() {
		return [
			//dialog.expander,
			{header: this.ln.name, dataIndex: 'name', width: 250},
			{header: this.ln.code, dataIndex: 'code'},
			{header: this.ln.enabled, dataIndex: 'enabled', renderer: this._render_cell_yes_no},
			{header: this.ln.test, dataIndex: 'test', renderer: this._render_cell_yes_no}
		];
	},
	
	adjust_multiln_params: function(multiln_params) {
		multiln_params.labelAlign = 'top';
	},
	
	get_add_form_fields: function() {
		return [
			{
				_fld: 'enabled',
				ref: '_enabled',
				name: 'enabled',
				fieldLabel: this.ln.enabled,
				xtype: 'combo',
				mode: 'local',
				store: {
					xtype: 'arraystore',
					fields: ['value', 'title'],
					idIndex: 0,
					data: [[0, PC.i18n.no], [1, PC.i18n.yes]]
				},
				displayField: 'title',
				valueField: 'value',
				editable: false,
				forceSelection: true,
				triggerAction: 'all'
			},
			{	_fld: 'login',
				ref: '_login',
				name: 'login',
				fieldLabel: this.ln.login,
				anchor: '100%',
				xtype: 'textfield',
				mode: 'local',
				editable: false,
				forceSelection: true,
				value: '',
				allowBlank: true
			},
			
			{	_fld: 'payment_key',
				ref: '_payment_key',
				name: 'payment_key',
				fieldLabel: this.ln.key,
				anchor: '100%',
				xtype: 'textfield',
				mode: 'local',
				editable: false,
				forceSelection: true,
				value: '',
				allowBlank: true
			},
			
			{
				_fld: 'test',
				ref: '_test',
				name: 'test',
				fieldLabel: this.ln.test,
				xtype: 'combo',
				mode: 'local',
				store: {
					xtype: 'arraystore',
					fields: ['value', 'title'],
					idIndex: 0,
					data: [[0, PC.i18n.no], [1, PC.i18n.yes]]
				},
				displayField: 'title',
				valueField: 'value',
				editable: false,
				forceSelection: true,
				triggerAction: 'all'
			}
			
			
			
			
		];
	},
	get_tbar_buttons: function() {
		var buttons =  [
			//this.get_button_for_add(),
			this.get_button_for_edit()
			//this.get_button_for_del()
		];
		if (this.sortable) {
			buttons.push(this.get_button_for_move_up());
			buttons.push(this.get_button_for_move_down());
		}
		return buttons;
	}
}); 
//debugger;
