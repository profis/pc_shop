PC.utils.localize('mod.pc_shop.category_product_filters', {
	lt: {
		title: 'Produktų filtrai',
		attribute: 'Atributas',
		disabled: 'Išjungtas'

	},
	en: {
		title: 'Product filters',
		attribute: 'Attribute',
		disabled: 'Disabled'

	},
	ru: {
		title: 'Фильтры товаров',
		attribute: 'Атрибут',
		disabled: 'Disabled'
	}
});

Plugin_pc_shop_category_product_filters_crud = Ext.extend(PC.ux.crud, {
	api_url: 'api/plugin/pc_shop/category_product_filters/',
	
	grid_id: 'Plugin_pc_shop_category_product_filters_crud_grid',
	
	auto_load: false,
	
	no_ln_fields: true,
	
	per_page: 20,
	
	get_store: function() {
		var store = Plugin_pc_shop_category_product_filters_crud.superclass.get_store.call(this);
		return store;
	},
	
	get_store_fields: function() {
		return [
				'id', 'attribute'
		];
	},
	
	_render_attribute: function(value, metaData, record, rowIndex, colIndex, store) {
		var attr_record = PC.plugin.pc_shop.attributes.Store.getById(value);
		if (attr_record) {
			return PC.utils.extractName(attr_record.data.names);
		}
		return value;
	},
	
	get_grid_columns: function() {
		return [
			//dialog.expander,
			{header: this.ln.attribute, dataIndex: 'attribute', width: 150,
				renderer: Ext.createDelegate(this._render_attribute, this)
			}
		];
	},
	
	get_add_form_fields: function() {
		return [
			{	_fld: 'attribute',
				fieldLabel: this.ln.attribute,
				anchor: '100%',
				xtype: 'combo',
				emptyText: ' -- ',
				//mode: 'local',
				store: PC.plugin.pc_shop.attributes.Store,
				valueField: 'id',
				displayField: 'nameClean',
				triggerAction: 'all',
				//tpl: '<tpl for="."><div class="x-combo-list-item">{'+ this.displayField +'}</div></tpl>',
				tpl: '<tpl for="."><div class="x-combo-list-item">{[PC.utils.extractName(values.names)]}</div></tpl>',
				editable: false
			}/*,
			
			{	_fld: 'disabled',
				fieldLabel: this.ln.disabled,
				anchor: '100%',
				xtype: 'datefield',
				format: 'Y-m-d',
				_get_raw_value: true,
				mode: 'local',
				editable: false,
				forceSelection: true,
				value: ''
			}*/
		];
	}
}); 
//debugger;

PC.hooks.Register('plugin/pc_shop/add_tab_for_category', function(params) {
	params.tabs.push(new Plugin_pc_shop_category_product_filters_crud({
		pc_no_ln: true,
		ln: PC.i18n.mod.pc_shop.category_product_filters
	}));
});


PC.hooks.Register('plugin/pc_shop/load_tab_panel_for_category', function(params) {
	var grid = Ext.getCmp('Plugin_pc_shop_category_product_filters_crud_grid');
	if (grid) {
		grid.store.setBaseParam('category_id', params.itemId);
		grid.store.setBaseParam('ln', PC.global.ln);
	
		grid.store.url = grid.pc_crud.api_url +'get/' + params.itemId + '/' + PC.global.ln;
		grid.pc_crud.base_params = {
			category_id: params.itemId
		}
		grid.store.proxy.setUrl(grid.store.url);
		grid.store.proxy.url = grid.store.url;
		grid.store.reload();
	}
});
