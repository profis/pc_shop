if (!PC_plugin_dialog_pc_shop.tabs) {
	PC_plugin_dialog_pc_shop.tabs = {};
}

PC.plugin.pc_shop.crud_coupons = Ext.extend(PC.ux.crud, {
	api_url: 'api/plugin/pc_shop/coupons/',
	
	store_admin_ln: true,
	
	reload_after_save: true,
	
	no_ln_fields: true,
	
	add_window_width: 350,
	edit_window_width: 550,
	
	get_store_fields: function() {
		return [
			'time_from', 'time_to', 'category_id', 'category_name',
			'id', 'code', 'is_for_hot', 'use_limit', 'used', 'percentage_discount', 'prices_key'
		];
	},
	
	get_grid_columns: function() {
		return [
			//dialog.expander,
			{header: this.ln.code, dataIndex: 'code'},
			{header: this.ln.time_from, dataIndex: 'time_from', width: 150},
			{header: this.ln.time_to, dataIndex: 'time_to', width: 150},
			{header: this.ln.use_limit, dataIndex: 'use_limit'},
			{header: this.ln.category, dataIndex: 'category_name'},
			{header: 'Category id', dataIndex: 'category_id'},
			{header: this.ln.is_for_hot, dataIndex: 'is_for_hot', renderer: this._render_cell_yes_no},
			{header: this.ln.discount + ' %', dataIndex: 'percentage_discount'},
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
				allowBlank: false
			},
			
			{	_fld: 'time_from',
				ref: '_time_from',
				name: 'time_from',
				fieldLabel: this.ln.time_from,
				anchor: '100%',
				xtype: 'pc_date_time_field',
				mode: 'local',
				editable: false,
				forceSelection: true
			},
			
			{	_fld: 'time_to',
				ref: '_time_to',
				name: 'time_to',
				fieldLabel: this.ln.time_to,
				anchor: '100%',
				xtype: 'pc_date_time_field',
				mode: 'local',
				editable: false,
				forceSelection: true
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
			},
			
			{	_fld: 'percentage_discount',
				ref: '_percentage_discount',
				name: 'percentage_discount',
				fieldLabel: this.ln.discount + ' %',
				anchor: '100%',
				xtype: 'numberfield',
				mode: 'local',
				editable: false,
				forceSelection: true,
				allowBlank: true
			}
		];
	},
			
	get_empty_edit_form_fields: function() {
		var fields = PC.plugin.pc_shop.crud_coupons.superclass.get_empty_edit_form_fields.call(this);

		var i;
		var categoryText = '';
		if( this.selected_id ) {
			var items = this.store.data.items;
			for( i in items ) {
				if( items[i].id == this.selected_id ) {
					categoryText = items[i].data.category_name;
					break;
				}
			}
		}

		for( i in fields ) {
			if( fields[i].name == 'category_id' ) {
				fields[i].value_text = categoryText;
				break;
			}
		}

		fields.push({
			_fld: 'prices_key',
			ref: '_prices_key',
			//name: 'prices',
			fieldLabel: 'Prices',
			_do_not_save: true,
			xtype: 'pc_shop_price_field'
		});
		return fields;
	},			
			
	get_tbar_buttons: function() {
		var buttons =  [
			this.get_button_for_add(),
			this.get_button_for_edit(),
			this.get_button_for_refresh(),
			this.get_button_for_del()
		];
		if (this.sortable) {
			//buttons.push(this.get_button_for_move_up());
			//buttons.push(this.get_button_for_move_down());
		}
		return buttons;
	},
	
	_after_update: function() {
		this.form_field_container._prices_key.sync();
	}
	
}); 
//debugger;
