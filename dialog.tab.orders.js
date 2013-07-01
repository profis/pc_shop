if (!PC_plugin_dialog_pc_shop.tabs) {
	PC_plugin_dialog_pc_shop.tabs = {};
}


PC.plugin.pc_shop.crud_orders = Ext.extend(PC.ux.right_side_view_crud, {
	api_url: 'api/plugin/pc_shop/orders/',
	store_admin_ln: true,
	
	no_ln_fields: true,
	
	per_page: 10,
	
	row_editing: true,
	
	get_store_fields: function() {
		return [
			'id', 'address', 'comment', 'date', 'email', 'name', 'phone', 'user_id', 'items', 'data', 'total_price', 'status', 'is_paid', 'payment_option', 'delivery_option', 'delivery_price', 'cod_price',
			{name: 'dateFormatted', mapping: 'date', convert: this.format_time_to_date}
			//{name: 'is_paid_icon', mapping: 'is_paid', convert: dialog.is_paid_icon}
			//{name: 'status_icon', mapping: 'status', convert: dialog.Get_status_icon}
		]
	},
	
	get_store: function() {
		var store =PC.plugin.pc_shop.crud_orders.superclass.get_store.call(this);
		this.store.setDefaultSort('date', 'desc');
		return store;
	},
	
	_render_cell_yes_no: function(value, metaData, record, rowIndex, colIndex, store) {
		if (value == 1) {
			return PC.i18n.yes;
		}
		return PC.i18n.no;
	},
	
	_render_cell_status_label: function(value, metaData, record, rowIndex, colIndex, store) {
		if (this.ln.status_labels[value]) {
			return this.ln.status_labels[value];
		}
		return value;
	},
	
	_render_cell_payment_option: function(value, metaData, record, rowIndex, colIndex, store) {
		if (this.ln.payment_option_labels[value]) {
			return this.ln.payment_option_labels[value];
		}
		return value;
	},
	
	_render_cell_delivery_option: function(value, metaData, record, rowIndex, colIndex, store) {
		if (this.ln.delivery_option_labels[value]) {
			return this.ln.delivery_option_labels[value];
		}
		return value;
	},
	
	get_grid_columns: function() {
		return [
			//uzsakovas, adresas, data, prekiu skaicius, suma
			//dialog.selModel,
			//dialog.expander,
			{header: this.ln.order_info.id, dataIndex: 'id',  width: 60, sortable: true},
			{header: this.ln.order_info.date, dataIndex: 'dateFormatted', width: 100, sortable: true},
			{header: this.ln.order_info.name, dataIndex: 'name', width: 150, sortable: true},
			//{header: 'Address', dataIndex: 'address', width: 180},
			//{header: 'Phone', dataIndex: 'phone', width: 90},
			//{header: 'Comment', dataIndex: 'comment'},
			{
				header: this.ln.order_info.is_paid, 
				dataIndex: 'is_paid', 
				width: 70, 
				sortable: true,
				renderer: this._render_cell_yes_no,
				editor: {
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['is_paid_id', 'is_paid_name'],
						idIndex: 0,
						data: [[0, PC.i18n.no], [1, PC.i18n.yes]]
					},
					displayField: 'is_paid_name',
					valueField: 'is_paid_id',
					editable: false,
					forceSelection: true,
					triggerAction: 'all'
				}
			},
            //{header: 'Prekės', dataIndex: '_author', width: 180, css: 'font-weight:bold'},
            //{id: 'pc_shop_comment_column', header: ln.comment, dataIndex: 'comment'},
            //{header: 'Statusas', dataIndex: 'status_icon', width: 80}
			{
				header: this.ln.order_info.status, 
				dataIndex: 'status', 
				width_: 90,
				sortable: true,
				renderer: Ext.createDelegate(this._render_cell_status_label, this)
			},
			{
				header: this.ln.order_info.payment, 
				dataIndex: 'payment_option', 
				width_: 90,
				sortable: true,
				renderer: Ext.createDelegate(this._render_cell_payment_option, this)
			},
			{
				header: this.ln.order_info.delivery, 
				dataIndex: 'delivery_option', 
				width_: 90, 
				sortable: true,
				renderer: Ext.createDelegate(this._render_cell_delivery_option, this)
			}
        ]
	},
	
	get_add_form_fields: function(edit_mode) {
		var allow_blank_if_edit = false;
		if (edit_mode) {
			allow_blank_if_edit = true;
		}
		return [
			{	
				_fld: 'is_paid',
				ref: '_is_paid',
				name: 'is_paid',
				fieldLabel: this.ln.order_info.is_paid,
				xtype: 'combo',
				mode: 'local',
				store: {
					xtype: 'arraystore',
					fields: ['is_paid_id', 'is_paid_name'],
					idIndex: 0,
					data: [[0, PC.i18n.no], [1, PC.i18n.yes]]
				},
				displayField: 'is_paid_name',
				valueField: 'is_paid_id',
				editable: false,
				forceSelection: true,
				triggerAction: 'all'
			},
			
			{
				_fld: 'status',
				ref: '_status',
				name: 'status',
				fieldLabel: this.ln.order_info.status,
				xtype: 'combo',
				mode: 'local',
				store: {
					xtype: 'arraystore',
					fields: ['status_id', 'status_label'],
					idIndex: 0,
					data: PC.utils.getComboArrayFromObject(this.ln.status_labels)
				},
				displayField: 'status_id',
				valueField: 'status_label',
				editable: false,
				forceSelection: true,
				triggerAction: 'all'
			}
			
		];
	},
	
	get_tbar_filters: function() {
		this.tbar_filter_refs = [
			'_order_id', '_date_from', '_date_to', '_search_phrase'
		];
		return [
			{xtype:'tbtext', text: this.ln.search_label + ':', style:'margin:0 2px;'},
			{	
				xtype:'tbtext',
				text: this.ln.search_id,
				style:'margin:0 2px;'
			},
			{	
				ref: '../_order_id',
				xtype:'textfield',
				_filter_name: 'order_id',
				width: 55
			},
			{xtype:'tbtext', text: this.ln.show_from, style:'margin:0 2px;'},
			{	
				ref: '../_date_from',
				xtype:'datefield',
				_filter_name: 'date_from',
				width: 80,
				value: '',
				initial_value: '',
				maxValue: new Date()
			},
			{xtype:'tbtext', text: this.ln.to, style:'margin:0 2px;'},
			{	ref: '../_date_to',
				_filter_name: 'date_to',
				xtype:'datefield',
				width: 80,
				value: '',
				initial_value: '',
				maxValue: new Date()
			},
			{	xtype:'tbtext',
				text: this.ln.with_phrase,
				style:'margin:0 2px;'
			},
			{	ref: '../_search_phrase',
				_filter_name: 'search_phrase',
				xtype:'textfield',
				width: 80
			}
		];
	},
	
	get_view_xtemplate_empty: function() {
		return [
			'<strong>' + this.ln.order_info.choose_order + ':</strong>'
		]
	},
	
	get_view_xtemplate: function() {
		return [
			'<b>' + this.ln.order_info.buyer_info + ':</b><br />',
			'' + this.ln.order_info.name + ': <i>{name}</i><br />',
			'' + this.ln.order_info.phone + ': <i>{phone}</i><br />',
			'' + this.ln.order_info.email + ': <i>{email}</i><br />',
			'' + this.ln.order_info.address + ': <i>{address}</i><br />',
			//------
			'<br /><b>' + this.ln.order_info.additional_info + ':</b><br />',
			'<tpl for="data">', //should be table
				'<tpl if="!value==\'\'">',
					'{name} - {value} <br />',
				'</tpl>',
			'</tpl>',//------
			'<br />',
			'<tpl if="this.non_empty_array(items)">',
				'<b>' + this.ln.order_info.items + ':</b><br />',
				'<tpl for="items">', //should be table
					'{#}. {name} ' + this.ln.order_info.quantity + ': {quantity} - ' + this.ln.order_info.price_for_each + ': {price}<br />',
					//'{#}. {name} - {short_description} - ' + ln.order_info.quantity + ': {quantity} - ' + ln.order_info.price_for_each + ': {price}<br />',
				'</tpl>',
			'</tpl>',
			//------
			'<tpl if="!comment==\'\'">',
				'<br /><b>' + this.ln.order_info.comment + ': </b><br /><i>{comment}</i><br />',
			'</tpl>',
			'<tpl if="delivery_price &gt; 0">',
				'<br /><b>', this.ln.order_info.delivery_price, '</b> - {delivery_price} <br />',
			'</tpl>',
			'<tpl if="cod_price &gt; 0">',
				'<br /><b>', this.ln.order_info.cod_price, '</b> - {cod_price} <br />',
			'</tpl>',
			'<br /><b>' + this.ln.order_info.total_price + ':</b> {total_price}'
		];
	}

}); 
//debugger;
