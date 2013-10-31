Ext.namespace('PC.ux');

PC.ux.categoryField = Ext.extend(Ext.form.CompositeField, {
	
	
	constructor: function(config) {
		if (!config) {
			config = {};
		}
		var width = 400;
		var value = 0;
		var value_text = '';
		if (config.width) {
			width = config.width;
		}
		if (config.value) {
			value = config.value;
		}
		if (config.value_text) {
			value_text = config.value_text;
		}
		config.items = [
			{
				xtype: 'hidden',
				ref: '../_category_id',
				value: value
			},

			{	
				fieldLabel: 'Category',
				xtype: 'container',
				layout: 'fit',
				anchor: '100%',
				width: width,
				items: PC.view_factory.get_shortcut_field({
					id: 'pc_shop_import_products_category',
					width: width,
					value: value_text
				}, 
				{
					callback: Ext.createDelegate(function(value, lang, node_id){
						debugger;
						//Ext.getCmp('pc_shop_import_products_category_hidden').setValue(node_id);
					}, this),
					page_selector_params: {
						tree_params : {
							additionalBaseParams : {
								controller: 'pc_shop',
								pc_shop: {
									categories_only: true
								},
								default_controller: 'pc_shop'
							}
						},
						return_type: 'name_path'
					}
				})
			}
			
		];
		PC.ux.categoryField.superclass.constructor.call(this, config);
	},
			
	getValue: function() {
		return '';
		return this.crud.get_store_data(true);
	},
			
	isDirty: function() {
		var dirty = false;
		return dirty;
	}
	
});

Ext.ComponentMgr.registerType('pc_shop_category_field', PC.ux.categoryField);

