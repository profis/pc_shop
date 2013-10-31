if (!PC_plugin_dialog_pc_shop.tabs) {
	PC_plugin_dialog_pc_shop.tabs = {};
}

PC.plugin.pc_shop.crud_coupons = Ext.extend(PC.ux.crud, {
	api_url: 'api/plugin/pc_shop/coupons/',
	
	no_ln_fields: true,
	
	get_store_fields: function() {
		return [
			'time_from', 'time_to', 'category_id',
			'id', 'code', 'is_for_hot', 'use_limit', 'used'
		];
	},
	
	get_grid_columns: function() {
		return [
			//dialog.expander,
			{header: this.ln.code, dataIndex: 'code'},
			{header: this.ln.time_from, dataIndex: 'time_from', width: 150},
			{header: this.ln.time_to, dataIndex: 'time_to', width: 150},
			{header: this.ln.use_limit, dataIndex: 'use_limit'},
			{header: this.ln.category, dataIndex: 'category_id'},
			{header: this.ln.is_for_hot, dataIndex: 'is_for_hot', renderer: this._render_cell_yes_no}
		];
	},
	
	adjust_multiln_params: function(multiln_params) {
		//multiln_params.labelAlign = 'top';
	},
	
	get_add_form_fields: function() {
		return [
			
			{	_fld: 'code',
				ref: '_code',
				name: 'code',
				fieldLabel: this.ln.code,
				anchor: '100%',
				xtype: 'textfield',
				mode: 'local',
				editable: false,
				forceSelection: true,
				value: '',
				allowBlank: true
			},
			
			{
				_fld: 'is_for_hot',
				ref: '_is_for_hot',
				name: 'is_for_hot',
				fieldLabel: this.ln.is_for_hot,
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
			
			{	_fld: 'use_limit',
				ref: '_use_limit',
				name: 'use_limit',
				fieldLabel: this.ln.use_limit,
				anchor: '100%',
				xtype: 'textfield',
				mode: 'local',
				editable: false,
				forceSelection: true,
				value: 1,
				allowBlank: true
			},
			
			{	_fld: 'category_id',
				ref: '_category_id',
				name: 'category_id',
				fieldLabel: this.ln.category,
				anchor: '100%',
				width: 200,
				xtype: 'pc_shop_category_field',
				mode: 'local',
				editable: false,
				forceSelection: true,
				value: '',
				value_text: '',
				allowBlank: true
			}
			
			
			
		];
	},
	get_tbar_buttons: function() {
		var buttons =  [
			this.get_button_for_add(),
			this.get_button_for_edit(),
			this.get_button_for_del()
		];
		if (this.sortable) {
			//buttons.push(this.get_button_for_move_up());
			//buttons.push(this.get_button_for_move_down());
		}
		return buttons;
	}
}); 
//debugger;
