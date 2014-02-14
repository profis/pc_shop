if (!PC_plugin_dialog_pc_shop.tabs) {
	PC_plugin_dialog_pc_shop.tabs = {};
}

/*
PC_shop_attribute_categories_crud = PC.ux.crud;



PC_shop_attribute_categories_crud.prototype.init = function() {
	this.ln = {
		title: 'Attributu kategorijos'
	};
}

*/
//debugger;
//PC.plugin.pc_shop.crud_attribute_categories = Ext.extend(PC.ux.crud);

PC.plugin.pc_shop.crud_delivery_options = Ext.extend(PC.ux.crud, {
	api_url: 'api/plugin/pc_shop/delivery_options/',
	
	//no_ln_fields: true,
	
	sortable: true,
	
	get_store_fields: function() {
		return [
			'names', {name: 'name', mapping: 'names', convert: function(names, n){return PC.utils.extractName(names);}},
			'id', 'code', 'enabled', 'delivery_price', 'no_delivery_price_from', 'cod_price', 'no_cod_price_from'
			
		];
	},
	
	get_grid_columns: function() {
		return [
			//dialog.expander,
			{header: this.ln.name, dataIndex: 'name'},
			{header: this.ln.code, dataIndex: 'code', width: 60},
			{header: this.ln.enabled, dataIndex: 'enabled', renderer: this._render_cell_yes_no, width: 80}//,
			//{header: PC.i18n.mod.pc_shop.config_titles.delivery_price, dataIndex: 'delivery_price'},
			//{header: this.ln.no_delivery_price_from, dataIndex: 'no_delivery_price_from', width: 200},
			//{header: PC.i18n.mod.pc_shop.config_titles.cod_price, dataIndex: 'cod_price', width: 150},
			//{header: this.ln.no_cod_price_from, dataIndex: 'no_cod_price_from', width: 200}
		];
	},
	
	adjust_multiln_params: function(multiln_params) {
		multiln_params.labelWidth = 200;
		multiln_params.window_width = 700;
		multiln_params.height = 550;
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
				required: true,
				allowBlank: false
			},
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
			{	_fld: 'delivery_price',
				ref: '_delivery_price',
				name: 'delivery_price',
				fieldLabel: PC.i18n.mod.pc_shop.config_titles.delivery_price,
				anchor: '100%',
				_do_not_save: true,
				xtype: 'pc_shop_price_field',
				mode: 'local',
				editable: false,
				forceSelection: true,
				//value: 0,
				allowBlank: false,
				height: 150,
			},
			{	_fld: 'no_delivery_price_from',
				ref: '_no_delivery_price_from',
				name: 'no_delivery_price_from',
				fieldLabel: PC.i18n.mod.pc_shop.config_titles.amount_for_free_delivery,
				anchor: '100%',
				_do_not_save: true,
				xtype: 'pc_shop_price_field',
				mode: 'local',
				editable: false,
				forceSelection: true,
				//value: 0,
				allowBlank: false,
				height: 150,
			},
			{	_fld: 'cod_price',
				ref: '_cod_price',
				name: 'cod_price',
				fieldLabel: PC.i18n.mod.pc_shop.config_titles.cod_price,
				anchor: '100%',
				_do_not_save: true,
				xtype: 'pc_shop_price_field',
				mode: 'local',
				editable: false,
				forceSelection: true,
				//value: 0,
				allowBlank: false,
				height: 150,
			},
			{	_fld: 'no_cod_price_from',
				ref: '_no_cod_price_from',
				name: 'no_cod_price_from',
				fieldLabel: PC.i18n.mod.pc_shop.config_titles.amount_for_free_cod,
				anchor: '100%',
				_do_not_save: true,
				xtype: 'pc_shop_price_field',
				mode: 'local',
				editable: false,
				forceSelection: true,
				//value: 0,
				allowBlank: false,
				height: 150,
			}
		];
	},
	
	_after_update: function() {
		this.form_field_container._delivery_price.sync();
		this.form_field_container._no_delivery_price_from.sync();
		this.form_field_container._cod_price.sync();
		this.form_field_container._no_cod_price_from.sync();
	}
	
}); 
//debugger;
