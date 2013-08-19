PC.utils.localize('mod.pc_shop.category_product_filters', {
	lt: {
		title: 'Produktų filtrai',
		name: 'Pavadinimas',
		attribute: 'Atributas',
		input_type: 'Lauko tipas',
		filter_type: 'Filtro tipas',
		disabled: 'Išjungtas'		

	},
	en: {
		title: 'Product filters',
		name: 'Name',
		attribute: 'Attribute',
		input_type: 'Input type',
		disabled: 'Disabled',
		filter_type: 'Filter type',

	},
	ru: {
		title: 'Фильтры товаров',
		name: 'Название',
		attribute: 'Атрибут',
		input_type: 'Тип ввода',
		disabled: 'Disabled',
		filter_type: 'Тип фильтра',
	}
});

Plugin_pc_shop_category_product_filters_crud = Ext.extend(PC.ux.crud, {
	api_url: 'api/plugin/pc_shop/category_product_filters/',
	
	grid_id: 'Plugin_pc_shop_category_product_filters_crud_grid',
	
	auto_load: false,
	
	//no_ln_fields: true,
	
	per_page: 20,
	
	sortable: true,
	
	filter_types: {
		0: ' = ',
		1: ' >= ',
		2: ' <= '
	},
	
	get_store: function() {
		var store = Plugin_pc_shop_category_product_filters_crud.superclass.get_store.call(this);
		return store;
	},
	
	get_store_fields: function() {
		return [
			'names', {name: 'name', mapping: 'names', convert: function(names, n){return PC.utils.extractName(names);}},
			'id', 'attribute', 'input_type', 'filter_type'
		];
	},
	
	_render_attribute: function(value, metaData, record, rowIndex, colIndex, store) {
		var attr_record = PC.plugin.pc_shop.attributes.Store.getById(value);
		if (attr_record) {
			return PC.utils.extractName(attr_record.data.names);
		}
		return value;
	},
			
	_render_filter_type: function(value, metaData, record, rowIndex, colIndex, store) {
		if (this.filter_types[value]) {
			return this.filter_types[value];
		}
		return value;
	},
	
	get_grid_columns: function() {
		return [
			{header: this.ln.name, dataIndex: 'name'},
			{header: this.ln.attribute, dataIndex: 'attribute', width: 150,
				renderer: Ext.createDelegate(this._render_attribute, this)
			},
			{
				header: this.ln.filter_type, 
				dataIndex: 'filter_type', 
				renderer: Ext.createDelegate(this._render_filter_type, this)
			},
			{
				header: this.ln.input_type, 
				dataIndex: 'input_type'
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
			},
			/*
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
			/*
			{	_fld: 'filter_type',
				fieldLabel: this.ln.filter_type,
				anchor: '100%',
				xtype: 'combo',
				emptyText: ' = ',
				//mode: 'local',
				store: {
					xtype: 'arraystore',
					fields: ['value', 'label'],
					idIndex: 0,
					data: PC.utils.getComboArrayFromObject(this.filter_types)
				},
				valueField: 'value',
				displayField: 'label',
				triggerAction: 'all',
				//tpl: '<tpl for="."><div class="x-combo-list-item">{'+ this.displayField +'}</div></tpl>',
				//tpl: '<tpl for="."><div class="x-combo-list-item">{[PC.utils.extractName(values.names)]}</div></tpl>',
				forceSelection: true,
				editable: false
			}*/
			{	
				_fld: 'filter_type',
				fieldLabel: this.ln.filter_type,
				anchor: '100%',
				xtype:'textfield'
			},
			{	
				_fld: 'input_type',
				fieldLabel: this.ln.input_type,
				anchor: '100%',
				xtype:'textfield'
			}
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
