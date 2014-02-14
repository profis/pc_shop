PC.plugin.pc_shop.attribute_categories_crud = Ext.extend(PC.ux.LocalCrud, {
	api_url: 'api/plugin/pc_shop/attributes_categories/',
	api_url_get: 'api/plugin/pc_shop/attributes_categories/get/',
	
	checkable: true,
	
	//reload_after_sync: true,
	
	constructor: function(config) {
		config.bbar = this.get_bbar();
		PC.plugin.pc_shop.attribute_categories_crud.superclass.constructor.call(this, config);
	},
	
	get_store_fields: function() {
		return [
			'names', {name: 'name', mapping: 'names', convert: function(names, n){return PC.utils.extractName(names);}},
			'id', 'checked', 'category_id'
		]; 
	},
	
	get_grid_columns: function() {
		return [
			//dialog.expander,
			{header: this.ln.name, dataIndex: 'name', width: 250},
		];
	},
	
	ajax_sync_success_respone_handler: function(data) {
		PC.plugin.pc_shop.attribute_categories_crud.superclass.ajax_sync_success_respone_handler.call(this, data);
		if (this._window) {
			this._window.close();
		}
	},
	
	get_tbar_buttons: function() {
		var buttons =  [
		];
		return buttons;
	},
	
	
	get_bbar: function() {
	
		return [
			{	text: PC.i18n.save,
				iconCls: 'icon-save',
				ref: '../../_btn_save',

				scope: this,
				handler: this.sync_grid
			}
		];
	}
}); 
//debugger;
