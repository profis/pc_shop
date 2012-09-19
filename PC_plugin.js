var Plugin = Ext.ns('PC.plugin.'+ CurrentlyParsing);
Plugin.Name = CurrentlyParsing;

Plugin.productsPerPage = 5;
Plugin.URL = PC.plugins.GetUrl(Plugin.Name);
Plugin.api = {
	Public: PC.global.BASE_URL + '/api/plugin/'+ Plugin.Name +'/',
	Admin: PC.global.BASE_URL + PC.global.ADMIN_DIR + '/api/plugin/'+ Plugin.Name +'/'
};


PC.utils.localize('mod.'+ Plugin.Name, {
	en: {
		new_subcategory: 'New subcategory',
		new_product: 'New item',
		main_images: 'Main images',
		attachments: 'Attachments',
		media: 'Media',
		add_image: 'Add image',
		add_attachment: 'Add attachment',
		unlink: 'Unlink',
		info: 'Information',
		properties: 'Properties',
		external_id: 'External ID',
		discount: 'Discount',
		attributes: 'Attributes',
		manufacturer: 'Manufacturer',
		mpn: 'Manufacturer product number (MPN)',
		quantity: 'Quantity',
		warranty: 'Warranty',
		price: 'Price',
		short_description: 'Short description',
		msg: {
			error_products_inside: 'You can`t delete category that has items inside.',
			error_deleting_category: 'Error deleting category',
			error_deleting_product: 'Error deleting item.'
		},
		addAttribute: 'Add attribute'
	},
	lt: {
		new_subcategory: 'Nauja subkategorija',
		new_product: 'Nauja prekė',
		main_images: 'Pagrindinės nuotraukos',
		attachments: 'Prisegti failai',
		media: 'Medija',
		add_image: 'Pridėti nuotrauką',
		add_attachment: 'Prisegti failą',
		unlink: 'Išimti',
		info: 'Informacija',
		properties: 'Nustatymai',
		external_id: 'Išorinis ID kodas',
		discount: 'Nuolaida',
		attributes: 'Atributai',
		manufacturer: 'Gamintojas',
		mpn: 'Gamintojo kodas (MPN)',
		quantity: 'Kiekis',
		warranty: 'Garantija',
		price: 'Kaina',
		short_description: 'Trumpas aprašymas',
		msg: {
			error_products_inside: 'Jūs negalite ištrinti kategorijos, kurioje yra prekių.',
			error_deleting_category: 'Nepavyko ištrinti kategorijos',
			error_deleting_product: 'Nepavyko ištrinti prekės'
		},
		addAttribute: 'Add attribute'
    },
	ru: {
		new_subcategory: 'Новая субкатегория',
		new_product: 'Новый товар',
		main_images: 'Основное изображение',
		attachments: 'Прикрепленные файлы',
		media: 'Медиа',
		add_image: 'Добавить изображение',
		add_attachment: 'Добавить файл',
		unlink: 'Отсоединить',
		info: 'Информация',
		properties: 'Свойства',
		external_id: 'Внешний ID код',
		discount: 'Скидка',
		attributes: 'Атрибуты',
		manufacturer: 'Производитель',
		mpn: 'Производственный код (MPN)',
		quantity: 'Кол-во',
		warranty: 'Гарантия',
		price: 'Цена',
		short_description: 'Короткое описание',
		msg: {
			error_products_inside: 'Вы не можете удалить категорию, в которой есть товары',
			error_deleting_category: 'Ошибка при удалении категории',
			error_deleting_product: 'Ошибка при удалении товара'
		},
		addAttribute: 'Add attribute'
	}
});

Plugin.ln = PC.i18n.mod.pc_shop;

Plugin.editorId = {
	Product: PC.editors.FormatID(Plugin.Name, 'product'),
	Category: PC.editors.FormatID(Plugin.Name, 'category')
}
Plugin.ParseID = function(id) {
	var types = ['category', 'product'];
	for (var a=0; types[a] != undefined; a++) {
		if (new RegExp('^('+ Plugin.Name +'\/)?'+ types[a] +'\/').test(id)) {
			var params = {type: types[a]};
			var cutFrom = types[a].length + 1;
			if (new RegExp('^'+ Plugin.Name +'\/').test(id)) cutFrom += Plugin.Name.length + 1;
			params.id = id.substring(cutFrom);
			return params;
		}
	}
	return false;
}
Plugin.FormatId = function(type, id) {
	return Plugin.Name +'/'+ type +'/'+ id;
}
Plugin.tree = {
	actions: {
		CreateCategory: new Ext.Action({
			text: Plugin.ln.new_subcategory,
			icon: 'images/add.gif',
			handler: function() {
				var n = PC.tree.menus.current_node;
				var parent_id = Plugin.ParseID(n.attributes.id);
				if (parent_id.type == 'category') parent_id = parent_id.id;
				else parent_id = 0;
				//create category
				var d = {
					id: 0,
					parent_id: parent_id,
					resources: {add: [], remove: []}
				}
				if (parent_id == 0) {
					d.pid = n.attributes.id;
				}
				Ext.Ajax.request({
					url: Plugin.api.Admin +'save/category',
					method: 'POST',
					params: {
						data: Ext.util.JSON.encode(d)
					},
					success: function(result){
						var json = Ext.util.JSON.decode(result.responseText);
						if (json) if (json.success) {
							var newId = Plugin.FormatId('category', json.id);
							var data = {
								id: newId,
								parent_id: parent_id,
								draggable: false,
								allowDrop: false,
								_empty: true,
								_names: {},
								pc_shop_products_count: 0
							};
							node = PC.tree.Append(n, data, function(n){
								node_rename_menu(n, true);
							});
							return true;
						}
						alert('create error');
					},
					failure: function(){
						alert('create error');
					}
				});
				//var treeId = 'new-'+ new Date().getTime();
				//var id = Plugin.Name +'/category/'+ treeId;
				/*var afterAppend = function(node) {
					if (node) {
						node.select();
						PC.editors.Load({
							id: id, 
							data: data
						}, true);
					}
				}*/
				
				//open node
				//on save - activate node
			}
		}),
		DeleteCategory: new Ext.Action({
			text: PC.i18n.del,
			icon: 'images/delete.gif',
			handler: function() {
				var n = PC.tree.menus.current_node;
				if (n.attributes.pc_shop_products_count > 0) {
					Ext.Msg.show({
						title: PC.i18n.attention,
						msg: Plugin.ln.msg.error_products_inside,
						buttons: Ext.MessageBox.OK,
						icon: Ext.MessageBox.WARNING
					});
					return;
				}
				Ext.MessageBox.show({
					title: PC.i18n.msg.title.confirm,
					msg: String.format(PC.i18n.msg.perm_del, 'category "'+n.text+'"'),
					buttons: Ext.MessageBox.YESNO,
					icon: Ext.MessageBox.WARNING,
					fn: function(rslt) {
						switch (rslt) {
							case 'yes':
								var id = Plugin.ParseID(n.id);
								Ext.Ajax.request({
									url: Plugin.api.Admin +'delete/category',
									method: 'POST',
									params: {
										id: id.id
									},
									callback: function(opts, success, rspns) {
										if (success && rspns.responseText) {
											try {
												var data = Ext.decode(rspns.responseText);
												if (data.success) {
													n.remove();
													return; // OK
												}
												else {
													var errMsg = data.error;
												}
											} catch(e) {};
										}
										Ext.MessageBox.show({
											title: PC.i18n.error,
											msg: Plugin.ln.error_deleting_category + (errMsg!=undefined?' '+errMsg:''),
											buttons: Ext.MessageBox.OK,
											icon: Ext.MessageBox.ERROR
										});
									}
								});
								break;
							default: // case 'no':
						}
					}
				});
				return;
			}
		}),
		CreateProduct: new Ext.Action({
			text: Plugin.ln.new_product,
			icon: PC.plugins.GetUrl('pc_shop') +'product.png',
			handler: function() {
				var categoryNode = PC.tree.menus.current_node;
				//category id
				var categoryId = Plugin.ParseID(categoryNode.id);
				if (categoryId.type != 'category') return false;
				var d = {
					id: 0,
					category_id: categoryId.id,
					resources: {add: [], remove: []}
				}
				Ext.Ajax.request({
					url: Plugin.api.Admin +'save/product',
					method: 'POST',
					params: {
						data: Ext.util.JSON.encode(d)
					},
					success: function(result){
						var json = Ext.util.JSON.decode(result.responseText);
						if (json) if (json.success) {
							//var n = PC.tree.node;
							var data = {
								id: Plugin.FormatId('product', json.id),
								category_id: d.category_id,
								draggable: false,
								allowDrop: false,
								leaf: true,
								_names: {},
								icon: PC.plugins.GetUrl('pc_shop') +'product.png'
							};
							node = PC.tree.Append(categoryNode, data, function(n){
								node_rename_menu(n, true);
								n.parentNode.attributes.pc_shop_products_count++;
							});
							//PC.tree.component.localizeNode(n);
							return true;
						}
						alert('create error');
					},
					failure: function(){
						alert('create error');
					}
				});
			}
		}),
		DeleteProduct: new Ext.Action({
			text: PC.i18n.del,
			icon: 'images/delete.gif',
			handler: function() {
				//delete product ant its relatives
				var n = PC.tree.menus.current_node;
				Ext.MessageBox.show({
					title: PC.i18n.msg.title.confirm,
					msg: String.format(PC.i18n.msg.perm_del, 'product "'+n.text+'"'),
					buttons: Ext.MessageBox.YESNO,
					icon: Ext.MessageBox.WARNING,
					fn: function(rslt) {
						switch (rslt) {
							case 'yes':
								var id = Plugin.ParseID(n.id);
								Ext.Ajax.request({
									url: Plugin.api.Admin +'delete/product',
									method: 'POST',
									params: {
										id: id.id
									},
									callback: function(opts, success, rspns) {
										if (success && rspns.responseText) {
											try {
												var data = Ext.decode(rspns.responseText);
												if (data.success) {
													n.parentNode.attributes.pc_shop_products_count--;
													n.remove();
													return; // OK
												}
												else {
													var errMsg = data.error;
												}
											} catch(e) {};
										}
										Ext.MessageBox.show({
											title: PC.i18n.error,
											msg: Plugin.ln.error_deleting_product + (errMsg!=undefined?' '+errMsg:''),
											buttons: Ext.MessageBox.OK,
											icon: Ext.MessageBox.ERROR
										});
									}
								});
								break;
							default: // case 'no':
						}
					}
				});
				return;
			}
		})
	}
}
Plugin.tree.menus = {
	shop: new Ext.menu.Menu({
		id: 'pc_tree_menu_'+ Plugin.Name +'_shop',
		items: [
			Plugin.tree.actions.CreateCategory,
			'-',
			PC.tree.actions.Rename,
			PC.tree.actions.Properties
		]
	}),
	category: new Ext.menu.Menu({
		id: 'pc_tree_menu_'+ Plugin.Name +'_category',
		items: [
			Plugin.tree.actions.CreateProduct,
			Plugin.tree.actions.CreateCategory,
			'-',
			PC.tree.actions.Rename,
			'-',
			Plugin.tree.actions.DeleteCategory
		]
	}),
	product: new Ext.menu.Menu({
		id: 'pc_tree_menu_'+ Plugin.Name +'_product',
		items: [
			Plugin.tree.actions.CreateProduct,
			'-',
			PC.tree.actions.Rename,
			'-',
			Plugin.tree.actions.DeleteProduct
		]
	})
}
PC.hooks.Register('core/tree/menu/'+ Plugin.Name, function(params){
	var id = Plugin.ParseID(params.node.id);
	if (id === false) {
		params.menu = Plugin.tree.menus.shop;
	}
	else if (id.type == 'category') {
		params.menu = Plugin.tree.menus.category;
	}
	else if (id.type == 'product') {
		params.menu = Plugin.tree.menus.product;
	}
});
PC.hooks.Register('core/editors/identify/'+ Plugin.Name, function(params){
	var id = Plugin.ParseID(params.id);
	if (id !== false) {
		params.editor = id.type;
	}
});
PC.hooks.Register('core/editors/unload', function(params){
	if (params.data != undefined) if (params.data.data != undefined) if (params.data.data._unsaved === true) {
		var n = PC.tree.component.getNodeById(params.data.data.id);
		if (n) n.remove();
	}
});
PC.hooks.Register('core/tree/node/renderIcon/'+ Plugin.Name, function(params){
	var id = Plugin.ParseID(params.id);
	if (id) {
		if (id.type == 'product') {
			var n = params.node, attr = n.attributes, icon = 'product';
			if (attr.published != 1) {
				if (attr.published != undefined) icon = 'product_inactive';
			}
			else if (attr.hot == 1) {
				if (attr.nomenu == 1) icon = 'nomenu_hot';
				else icon = 'hot';
			}
			else if (attr.nomenu == 1) icon = 'nomenu';
			params.icon = Plugin.URL +'images/'+ icon +'.png';
		}
	}
});
//category editor
Ext.ns('Plugin.media');
Plugin.media.Store = new Ext.data.ArrayStore({
	//xtype: 'arraystore',
	fields: [
		'resource_id', 'item_id', 'file_id', 'is_category', 'is_attachment',
		'size', 'extension', 'filename', 'path', 'filetype',
		{name: 'short_name', mapping: 7, convert: PC.ux.gallery.files.GetShortName}
	],
	data: [],
	sortInfo: {
		field: 'is_attachment',
		direction: 'ASC'
	},
	listeners: {
		load: function(store, records, options){
			store.commitChanges();
			store._deletedFields = [];
		},
		remove: function(store, record, index){
			if (record.data.resource_id != null) store._deletedFields.push(record);
		}
	},
	_deletedFields: [],
	_getDeletedFields: function(){
		return this._deletedFields;
	},
	_getNewFields: function() {
		var list = [];
		Ext.iterate(this.getRange(), function(rec){
			if (rec.data.resource_id == null) list.push(rec);
		});
		return list;
	}
});
Plugin.media.Template = new Ext.XTemplate(
	'<tpl for=".">'
		//+'<tpl if="alert(filename)"></tpl>'
		//+'<tpl if="alert(is_attachment)"></tpl>'
		+'<tpl if="this.startChanged(is_attachment)">'
			+'<tpl if="!is_attachment"><div style="clear:both;padding:10px 0 6px 0;margin-bottom:5px;border-bottom:1px solid #888;"><h2><img style="vertical-align:-2px;" src="images/image.png" alt="" /> '+ Plugin.ln.main_images +'</h2></div></tpl>'
			+'<tpl if="is_attachment"><div style="clear:both;padding:10px 0 6px 0;margin-bottom:5px;border-bottom:1px solid #888;"><h2><img style="vertical-align:-2px;" src="images/page_white_text.png" alt="" /> '+ Plugin.ln.attachments +'</h2></div></tpl>'
		+'</tpl>'
		//item selector
		+'<div class="thumb-wrap template-icons" id="{id}">'
			//template for images
			+'<tpl if="filetype==\'image\'">'
				+'<div class="thumb">'
					//format image
					+'<img class="drag-img" src="../gallery/'
						+'admin/id/thumbnail/{file_id}'
						//+'{path}thumbnail/{filename}'
						//+'{path}thumbnail/{filename}'
					+'" alt="" title="{filename}" />'
				+'</div>'
			+'</tpl>'
			//template for other files
			+'<tpl if="filetype!=\'image\'">'
				+'<div class="file">'
					+'<tpl if="filetype==\'document\'">'
						+'<tpl if="extension==\'pdf\'">'
							+'<img class="drag-img" src="images/filetypes/File-Pdf-48.png" alt="" title="{filename}" />'
						+'</tpl>'
						+'<tpl if="extension==\'doc\' || extension==\'docx\'">'
							+'<img class="drag-img" src="images/filetypes/Word-48.png" alt="" title="{filename}" />'
						+'</tpl>'
						+'<tpl if="extension==\'xls\' || extension==\'xlsx\'">'
							+'<img class="drag-img" src="images/filetypes/File-Excel-48.png" alt="" title="{filename}" />'
						+'</tpl>'
						+'<tpl if="extension==\'ppt\' || extension==\'pptx\'">'
							+'<img class="drag-img" src="images/filetypes/File-PowerPoint-48.png" alt="" title="{filename}" />'
						+'</tpl>'
						+'<tpl if="extension==\'txt\' || extension==\'cdr\'">'
							+'<img class="drag-img" src="images/filetypes/File-48.png" alt="" title="{filename}" />'
						+'</tpl>'
						+'<tpl if="extension==\'swf\'">'
							+'<img class="drag-img" src="images/filetypes/File_swf-48.png" alt="" title="{filename}" />'
						+'</tpl>'
					+'</tpl>'
					+'<tpl if="filetype==\'audio\'">'
						+'<img class="drag-img" src="images/filetypes/Audio-48.png" alt="" title="{filename}" />'
					+'</tpl>'
					+'<tpl if="filetype==\'video\'">'
						+'<img class="drag-img" src="images/filetypes/Video-48.png" alt="" title="{filename}" />'
					+'</tpl>'
					+'<tpl if="filetype==\'archive\'">'
						+'<img class="drag-img" src="images/filetypes/Zip-48.png" alt="" title="{filename}" />'
					+'</tpl>'
				+'</div>'
			+'</tpl>'
			// shortname of the filename
			+'<span class="gallery-file-title">{short_name}</span>'
		+'</div>'
	+'</tpl>', {
		is_attachment: null,
		startChanged: function(is_attachment) {
			var oldStatus = this.is_attachment;
			//alert('Old status: '+ oldStatus);
			//alert('Is attachment: '+ is_attachment);
			//console.log('-----------------------------------------------------');
			this.is_attachment = is_attachment;
			//check status
			if (is_attachment != oldStatus) return true;
			return false;
		}/*,
		isStartOfAttachments: function(is_attachment) {
			var r = false;
			if (this.is_attachment != true && is_attachment) r = true;
			this.is_attachment = is_attachment;
			return r;
			/*if (this.is_attachment != true || is_attachment) {
				this.is_attachment = is_attachment;
				return true;
			}
			return false;* /
		},
		isStartOfImages: function(is_attachment) {
			var r = false;
			if (this.is_attachment != false && !is_attachment) r = true;
			this.is_attachment = is_attachment;
			return r;
			/*if (this.is_attachment != true || is_attachment) {
				this.is_attachment = is_attachment;
				return true;
			}
			return false;* /
		}*/
	}
);
Plugin.media.GallerySave = function(link, rec, callback, params){
	if (params.saveFnType != undefined) {
		var data = PC.editors.Data.data;
		var isAttachment = false;
		if (params.saveFnType == 'attachment') isAttachment = true;
		else if (params.saveFnType != 'main') {
			callback();
			return;
		}
		var recData = {
			resource_id: null,
			item_id: data.id,
			file_id: rec.data.id,
			is_category: (PC.editors.Current[1] == 'category'),
			is_attachment: isAttachment,
			size: rec.data.size,
			extension: rec.data.extension,
			filename: rec.data.name,
			path: rec.data.path,
			filetype: rec.data.filetype,
			short_name: (rec.data.name.length > 13?rec.data.name.substr(0, 11) +'...':rec.data.name)
		};
		var media = PC.editors.Get()._media;
		var store = media.store;
		var n = new store.recordType(recData);
		store.addSorted(n);
		media.tpl.is_attachment = null;
		media.refresh();
	}
	callback();
}
Plugin.attributes = {};
Plugin.attributes.Store = new Ext.data.JsonStore({
	autoLoad: true,
	url: Plugin.api.Admin +'attributes/getWithValues',
	baseParams: {api: true},
	fields: [
		'id', 'is_category_attribute', 'is_custom', 'is_searchable', 'names', 'values',
		{name: 'name', mapping: 'names', convert: function(names, n){return PC.utils.extractName(names);}},
		{name: 'nameClean', mapping: 'names', convert: function(names, n){return PC.utils.extractName(names, null, {greyOut:false});}}
	],
	idProperty: 'id'
});
Plugin.attributes.ParseAttributeName = function(id, n) {
	var attributeNode = Plugin.attributes.Store.getById(id);
	if (!attributeNode) return;
	return attributeNode.data.name;
};
Plugin.attributes.ParseAttributeValue = function(id, n) {
	if (id == null) {
		if (typeof n.data == 'object') return n.data.value;
		return n.value;
	}
	if (typeof n == 'object') var attributeId = n.attribute_id;
	else var attributeId = n;
	var attributeNode = Plugin.attributes.Store.getById(attributeId);
	if (!attributeNode) return;
	if (attributeNode.data.values[id] == undefined) return;
	return PC.utils.extractName(attributeNode.data.values[id]);
};
Plugin.attributes.ItemStore = new Ext.data.JsonStore({
	url: Plugin.api.Admin +'attributes/getForItem',
	fields: [
		'id', 'item_id', 'attribute_id', 'flags', 'value_id', 'value',
		{name: 'attributeName', mapping: 'attribute_id', convert: Plugin.attributes.ParseAttributeName},
		{name: 'displayValue', mapping: 'value_id', convert: Plugin.attributes.ParseAttributeValue}
	],
	listeners: {
		load: function(store, records, options){
			store.commitChanges();
			store._deletedFields = {};
		},
		remove: function(store, record, index){
			store._deletedFields[record.id] = record;
		}
	},
	_deletedFields: [],
	_getDeletedFields: function(){
		return this._deletedFields;
	},
	_getSaveData: function() {
		//attribute_id, value_id, value
		//item_id
		var list = {save:[], remove:[]};
		
		Ext.iterate(Plugin.attributes.ItemStore.getModifiedRecords(), function(rec){
			if (Plugin.attributes.ItemStore._deletedFields[rec.id] != undefined) return;
			list.save.push({
				id: rec.data.id,
				attribute_id: rec.data.attribute_id,
				value_id: rec.data.value_id,
				value: rec.data.value
			});
		});
		Ext.iterate(Plugin.attributes.ItemStore._deletedFields, function(id, rec){
			if (rec.data.id == 0) return;
			list.remove.push(rec.data.id);
		});
		return list;
	},
	_getAttributeData: function(id) {
		if (typeof id == 'object') {
			if (id.data == undefined) return false;
			if (id.data.attribute_id == undefined) return false;
			var id = id.data.attribute_id;
		}
		return Plugin.attributes.Store.getById(id);
	}
});
Plugin.attributes.Grid = {
	ref: '../attributesGrid',
	border: false,
	store: Plugin.attributes.ItemStore,
	//plugins: dialog.expander, //expander could be used here to identify what effects on price attributes does
	columns: [
		//dialog.expander,
		{header: 'Attribute', dataIndex: 'attributeName', width: 200},
		{header: 'Value', dataIndex: 'displayValue', id: 'pc_shop_item_attribute_value_col'}
	],
	autoExpandColumn: 'pc_shop_item_attribute_value_col',
	_insertRecord: function(rec, cfg) {
		if (typeof cfg != 'object') var cfg = {};
		var grid = (cfg.grid != undefined?cfg.grid:this);
		var ev = (cfg.ev != undefined?cfg.ev:Ext.EventObject);
		var attrRec = grid.store._getAttributeData(rec);
		if (!attrRec) return false;
		var isCustom = parseInt(attrRec.data.is_custom);
		var items = [];
		var initialValue = (isCustom?rec.data.value:rec.data.value_id);
		var initialValueIsSelected = (initialValue != null && initialValue != '');
		if (isCustom) {
			items.push(
				{	fieldLabel: 'Enter value',
					ref: '_value',
					anchor: '100%',
					value: initialValue,
					listeners: {
						change: function(field, value, old) {
							if (value != '') {
								initialValueIsSelected = true;
								w._saveBtn.enable();
							}
							else {
								w._saveBtn.disable();
							}
						},
						specialkey: function(fld, e) {
							if (e.getKey() == e.ENTER) {
								w.Save();
							}
						}
					}
				},
				{	xtype: 'compositefield',
					ref: '_suggestions',
					hidden: true,
					fieldLabel: 'Suggestions',
					items: [
						{xtype: 'textfield', hidden: true},
						{xtype: 'label', ref: '_list', text: '-', style: 'margin-top:3px;padding-bottom:3px;'}
					]
				}
			);
		}
		else {
			var storeData = [];
			Ext.iterate(attrRec.data.values, function(id, value){
				storeData.push([id, PC.utils.extractName(value)]);
			});
			items.push(
				{	fieldLabel: 'Choose value',
					xtype: 'combo',
					ref: '_value_id',
					anchor: '100%',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['id', 'name'],
						idIndex: 0,
						data: storeData
					},
					displayField: 'name',
					valueField: 'id',
					value: rec.data.value_id,
					editable: false,
					forceSelection: true,
					triggerAction: 'all',
					listeners: {
						change: function(field, value, old) {
							if (value != '') {
								initialValueIsSelected = true;
								w._saveBtn.enable();
							}
						},
						specialkey: function(fld, e) {
							if (e.getKey() == e.ENTER) {
								w.Save();
							}
						},
						select: function(field, record, index) {
							field.fireEvent('change', field, record.data.id);
						}
					}
				}
			);
		}
		var windowCfg = {
			title: (isCustom?'Editing':'Choose') +' value for "'+ attrRec.data.nameClean +'"',
			layout: 'form',
			labelWidth: 70,
			labelAlign: 'right',
			padding: '6px 6px 2px',
			defaultType: 'textfield',
			autoScroll: true,
			items: items,
			width: 300,
			modal: true,
			resizable: false,
			pageX: ev.getPageX(),
			pageY: ev.getPageY(),
			Save: function(){
				if (isCustom) {
					rec.set('value', w._value.getValue());
					rec.set('displayValue', Plugin.attributes.ParseAttributeValue(null, rec));
				}
				else {
					var newId = w._value_id.getValue();
					rec.set('value_id', newId);
					rec.set('displayValue', Plugin.attributes.ParseAttributeValue(newId, rec.data.attribute_id));
				}
				w.close();
			},
			buttons: [
				{	ref: '../_saveBtn',
					text: Ext.Msg.buttonText.ok,
					handler: function() {
						w.Save();
					},
					disabled: !initialValueIsSelected
				},
				{	ref: '../_cancelBtn',
					text: Ext.Msg.buttonText.cancel,
					handler: function() {
						if (cfg.isNew) Plugin.attributes.ItemStore.remove(rec);
						w.close();
					}
				}
			],
			listeners: {
				afterrender: function(w){
					if (isCustom) {
						Ext.Ajax.request({
							url: Plugin.api.Admin +'attributes/getSuggestions',
							method: 'POST',
							params: {attributeId: attrRec.data.id},
							success: function(result){
								var data = Ext.util.JSON.decode(result.responseText);
								if (data) {
									if (typeof data == 'object') {
										var options = [];
										Ext.iterate(data, function(i) {
											if (i == rec.data.value) return;
											options.push('<a class="pc_shop_attribute_value_suggestion" href="#">'+ i +'</a>');
										});
										if (options.length) w._suggestions.innerCt._list.setText(options.join(', '), false);
										var els = Ext.query('a.pc_shop_attribute_value_suggestion', w._suggestions.innerCt._list.el.dom);
										if (els.length) {
											Ext.iterate(els, function(el){
												var extEl = Ext.get(el);
												extEl.on('click', function(ev, el){
													var oldValue = w._value.getValue();
													w._value.setValue(el.innerHTML);
													w._value.fireEvent('change', w._value, el.innerHTML, oldValue);
												});
											});
											w._suggestions.show();
											return true;
										}
									}
									return true;
								}
							}
						});
					}
				}
			}
		};
		var w = new Ext.Window(windowCfg);
		w.show();
	},
	listeners: {
		celldblclick: function(grid, rowIndex, columnIndex, ev) {
			if (columnIndex == 1) {
				var rec = Plugin.attributes.ItemStore.getAt(rowIndex);
				grid._insertRecord(rec, {
					grid: grid,
					ev: ev
				});
			}
		}
	},
	tbar: [
		{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save},
		'-',
		{	xtype: 'combo', mode: 'local',
			ref: '../_attribute_id',
			store: Plugin.attributes.Store,
			valueField: 'id',
			displayField: 'nameClean',
			triggerAction: 'all',
			//tpl: '<tpl for="."><div class="x-combo-list-item">{'+ this.displayField +'}</div></tpl>',
			tpl: '<tpl for="."><div class="x-combo-list-item">{[PC.utils.extractName(values.names)]}</div></tpl>',
			editable: false,
			listeners: {
				change: function(field, value, ovalue) {},
				select: function(cb, rec, idx) {
					cb.fireEvent('change', cb, cb.value, cb.originalValue);
				},
				expand: function(field){
					Plugin.attributes.Store.filter('is_category_attribute', (PC.editors.Current[1]=='category'?'1':'0'));
				}
			}
		},
		{	icon: 'images/add.png', text: PC.i18n.add,// text: Plugin.ln.addAttribute,
			handler: function(){
				var attField = PC.editors.Get().attributesGrid._attribute_id;
				var id = attField.getValue();
				var rec = attField.store.getById(id);
				if (!rec) return;
				var gridRec = new Plugin.attributes.ItemStore.recordType({
					id: 0,
					//item_id: rec.data.,
					attribute_id: id,
					value_id: null,
					value: null
				});
				gridRec.set('attributeName', Plugin.attributes.ParseAttributeName(id, gridRec));
				gridRec.markDirty();
				Plugin.attributes.ItemStore.add(gridRec);
				//init edit
				PC.editors.Get().attributesGrid._insertRecord(gridRec, {
					isNew: true
				});
			}
		},
		'-',
		{	icon: 'images/delete.png',
			text: PC.i18n.del,
			handler: function(){
				var grid = PC.editors.Get().attributesGrid;
				var records = grid.selModel.getSelections();
				if (!records.length) return;
				Ext.MessageBox.show({
					title: PC.i18n.msg.title.confirm,
					msg: String.format(PC.i18n.msg.confirm_delete, 'selected attributes'),
					buttons: Ext.MessageBox.YESNO,
					icon: Ext.MessageBox.WARNING,
					fn: function(clicked) {
						if (clicked == 'yes') {
							grid.store.remove(records);
							Ext.Msg.hide();
						}
					}
				});
			}
		}
	]
};
Plugin.attributes.Load = function(type, itemId){
	Plugin.attributes.ItemStore.setBaseParam('type', type);
	Plugin.attributes.ItemStore.setBaseParam('itemId', itemId);
	Plugin.attributes.ItemStore.reload();
}
PC.editors.Register(Plugin.Name, 'category', function(){
	var View = new PC.ux.gallery.files.View({
		ref: '../_media',
		//xtype: 'pc_gallery_files_view',
		store: Plugin.media.Store,
		tpl: Plugin.media.Template,
		border: false,
		style: 'background-color:#fff;'
	});
	var ViewPanel = new Ext.Panel({
		id: Plugin.editorId.Category +'_tab_media',
		title: Plugin.ln.media,
		items: View,
		tbar: [
			{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save},
			'-',
			{	icon: 'images/add.png', text: Plugin.ln.add_image,
				handler: function(){
					var params = {
						save_fn: Plugin.media.GallerySave,
						saveFnType: 'main',
						callee: 'image'
					};
					PC.dialog.gallery.show(params);
				}
			},
			{	icon: 'images/page_white_text.png', text: Plugin.ln.add_attachment,
				handler: function(){
					var params = {
						save_fn: Plugin.media.GallerySave,
						saveFnType: 'attachment'
					};
					PC.dialog.gallery.show(params);
				}
			},
			{	icon: 'images/link_break.png',
				text: Plugin.ln.unlink,
				handler: function(){
					var media = PC.editors.Get()._media;
					var records = media.getSelectedRecords();
					media.store.remove(records);
				}
			},
			'-',
			{icon: 'images/delete.png', text: PC.i18n.del}
		]
	});
	var attributesGrid = new Ext.grid.GridPanel(Plugin.attributes.Grid);
	return {
		xtype: 'tabpanel',
		bodyCssClass: 'x-border-layout-ct',
		activeTab: 0,
		tbar: new PC.ux.LanguageBar(),
		//deferredRender: true,
		items: [
			{	title: Plugin.ln.info,
				ref: '_information',
				layout: 'hbox',
				layoutConfig: {
					align: 'stretch'
				},
				border: false,
				defaults: {
					flex: 1,
					bodyCssClass: 'x-border-layout-ct',
					border: false,
					autoScroll: true
				},
				tbar: [{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save}],
				items: [
					{	style: 'padding:0 6px 6px 6px',
						border: false,
						defaults: {
							border: false
						},
						cls: 'x-panel-mc',
						items: [
							//name
							{	xtype: 'box',
								style: 'padding: 6px',
								html: PC.i18n.name.replace(/\s/, '&nbsp;') + ':'
							},
							{	xtype: 'container',
								//id: 'db_fld_name_container',
								layout: 'fit',
								height: 22,
								items: [{
									ref: '../../_name',
									xtype: 'textfield',
									//xtype: 'profis_multilnfield',
									editorCfg: {
										title: PC.i18n.name
									}
								}]
							},
							//route
							{	xtype: 'box',
								style: 'padding: 6px',
								html: PC.i18n.seo_link.replace(/\s/, '&nbsp;') + ':'
							},
							{	xtype: 'container',
								//id: 'db_fld_route_container',
								layout: 'fit',
								height: 22,
								items: [{
									ref: '../../_route',
									xtype: 'textfield',
									//xtype: 'profis_multilnfield',
									editorCfg: {
										title: PC.i18n.seo_link
									}
								}]
							},
							//route lock
							{	xtype: 'container',
								//id: 'db_fld_route_lock_container',
								layout: 'fit',
								style: 'text-align: right',
								items: [{
									ref: '../../_route_lock',
									xtype: 'checkbox',
									boxLabel: PC.i18n.page.route_lock
								}]
							},
							//title
							{	xtype: 'box',
								style: 'padding: 6px',
								html: PC.i18n.title.replace(/\s/, '&nbsp;') + ':'
							},
							{	xtype: 'container',
								layout: 'fit',
								height: 22,
								items: [{
									ref: '../../_title',
									xtype: 'textfield',
									editorCfg: {
										title: PC.i18n.title
									}
								}]
							},
							//description
							{	xtype: 'box',
								style: 'padding: 6px',
								html: PC.i18n.desc.replace(/\s/, '&nbsp;') + ':'
							},
							{	height: 80,
								xtype: 'container',
								//id: 'db_fld_description_container',
								layout: 'fit',
								items: [{
									ref: '../../_description',
									xtype: 'textarea',
									//xtype: 'profis_multilnfield',
									autoCreate: {
										tag: 'textarea'
									},
									editorCfg: {
										defaultType: 'textarea',
										title: PC.i18n.desc,
										defaults: {
											height: 50
										}
									}
								}]
							},
							//keywords
							{	xtype: 'box',
								style: 'padding: 6px',
								html: PC.i18n.keywords.replace(/\s/, '&nbsp;') + ':'
							},
							{	xtype: 'container',
								height: 80,
								layout: 'fit',
								items: [{
									ref: '../../_keywords',
									xtype: 'textarea',
									autoCreate: {
										tag: 'textarea'
									},
									editorCfg: {
										defaultType: 'textarea',
										title: PC.i18n.keywords,
										defaults: {
											height: 50
										}
									},
									maxLength: 255, //same as in a database, varchar(255)
									listeners: {
										change: function(field, value, old) {
											field.setValue(value.replace(/\n/g, ', '));
										}
									}
								}]
							}
						]
					}
				]
			},
			{	title: Plugin.ln.properties,
				ref: '_properties',
				id: Plugin.editorId.Category +'_tab_properties',
				layout: 'form',
				bodyCssClass: 'x-border-layout-ct',
				padding: 6,
				labelWidth: 200,
				labelAlign: 'right',
				defaults: {
					anchor: '100%'
				},
				border: false,
				tbar: [{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save}],
				items: [
					{ref: '_external_id', fieldLabel: Plugin.ln.external_id, xtype: 'textfield'},
					{ref: '_discount', fieldLabel: Plugin.ln.discount, xtype: 'numberfield'},
					{ref: '_percentage_discount', fieldLabel: Plugin.ln.discount +', %', xtype: 'numberfield'},
					{ref: '_hot', fieldLabel: PC.i18n.page.hot, xtype: 'checkbox'},
					{ref: '_nomenu', fieldLabel: PC.i18n.page.nomenu, xtype: 'checkbox'},
					{ref: '_published', fieldLabel: PC.i18n.page.published, xtype: 'checkbox'}
				]
			},
			{	title: PC.i18n.desc,
				ref: '_description',
				xtype: 'container',
				layout: 'fit',
				items: [{
					xtype: 'profis_tinymce',
					ref: '_description'
				}]
			},
			ViewPanel,
			{	title: Plugin.ln.attributes,
				id: Plugin.editorId.Category +'_tab_attributes',
				xtype: 'container',
				layout: 'fit',
				items: [attributesGrid],
				border: false,
				tbar: [{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save}]
			}
		],
		listeners: {
			beforetabchange: function(panel, tab, currentTab) {
				var tabId = tab.getId();
				if (tabId == Plugin.editorId.Category +'_tab_properties'
					|| tabId == Plugin.editorId.Category +'_tab_media'
					|| tabId == Plugin.editorId.Category +'_tab_attributes') {
					panel.getTopToolbar().hide();
				}
				else panel.getTopToolbar().show();
				panel.doLayout();
			},
			show: function(panel){
				panel.getTopToolbar().Reload();
			}
		},
		Clear: function(editor) {
			var fields = [
				editor._information._name,
				editor._information._description,
				editor._information._keywords,
				editor._information._route,
				editor._information._route_lock,
				editor._description._description,
				editor._properties._discount,
				editor._properties._percentage_discount,
				editor._properties._external_id,
				editor._properties._hot,
				editor._properties._nomenu,
				editor._properties._published
			];
			for (var a=0; fields[a] != undefined; a++) {
				fields[a].setValue('');
			}
			var description = editor._description._description;
			if (description.ed != undefined) {
				description.setValue('');
				description.ed.undoManager.clear();
				description.ed.isNotDirty = 1;
			}
			Plugin.attributes.ItemStore.clearData();
			//Plugin.attributes.Store.clearFilter();
		},
		Load: function(editor, data, ln, freshLoad, callback) {
			if (data != undefined) {
				if (freshLoad) {
					var idData = Plugin.ParseID(data.id);
					if (idData) Plugin.attributes.Load(idData.type, idData.id);
					else Plugin.attributes.ItemStore.reload();
					Plugin.attributes.Store.filter('is_category_attribute', '1');
				}
				data = data.data;
				var media = PC.editors.Get()._media;
				//resources
				if (data.resources != undefined) {
					var mediaStore = media.store;
					media.is_attachment = null;
					mediaStore.loadData(data.resources);
					if (media.rendered) media.refresh();
				}
				//contents
				if (data.contents != undefined) if (typeof data.contents[ln] == 'object') {
					editor._information._name.setValue(data.contents[ln].name);
					editor._information._title.setValue(data.contents[ln].seo_title);
					editor._information._description.setValue(data.contents[ln].seo_description);
					editor._information._keywords.setValue(data.contents[ln].seo_keywords);
					editor._information._route.setValue(data.contents[ln].route);
					var description = editor._description._description;
					description.setValue(data.contents[ln].description);
					if (description.ed != undefined) {
						description.ed.undoManager.clear();
						description.ed.isNotDirty = 1;
					}
				}
				editor._properties._external_id.setValue(data.external_id);
				editor._properties._discount.setValue(data.discount);
				editor._properties._percentage_discount.setValue(data.percentage_discount);
				editor._properties._hot.setValue(data.hot);
				editor._properties._nomenu.setValue(data.nomenu);
				editor._properties._published.setValue(data.published);
				editor._information._route_lock.setValue(data.route_lock);
			}
			if (typeof callback == 'function') callback();
		},
		Store: function(editor, data, callback) {
			//save editor data to data store (this.Data = );
			var d = data;
			d.external_id = editor._properties._external_id.getValue();
			d.discount = editor._properties._discount.getValue();
			d.percentage_discount = editor._properties._percentage_discount.getValue();
			d.hot = editor._properties._hot.getValue();
			d.nomenu = editor._properties._nomenu.getValue();
			d.published = editor._properties._published.getValue();
			d.route_lock = editor._information._route_lock.getValue();
			
			var ln = PC.global.ln;
			if (typeof d.contents != 'object') d.contents = {};
			if (d.contents[ln] == undefined) d.contents[ln] = {};
			var c = d.contents[ln];
			c.name = editor._information._name.getValue();
			c.route = editor._information._route.getValue();
			c.seo_title = editor._information._title.getValue();
			c.seo_description = editor._information._description.getValue();
			c.seo_keywords = editor._information._keywords.getValue();
			var description = editor._description._description;
			if (description.rendered) c.description = description.getValue();
			
			if (typeof callback == 'function') callback(true);
		},
		Save: function(callback) {
			var d = PC.editors.Data.data;
			var ed = PC.editors.Get();
			//parse resources
			var resources = {add: [], remove: []};
			Ext.iterate(ed._media.store._getNewFields(), function(i){
				resources.add.push({
					id: i.data.file_id,
					is_attachment: i.data.is_attachment
				});
			});
			Ext.iterate(ed._media.store._getDeletedFields(), function(i){
				resources.remove.push({
					id: i.data.file_id,
					is_attachment: i.data.is_attachment
				});
			});
			d.resources = resources;
			delete d.loader;
			//save attributes
			d.attributes = Plugin.attributes.ItemStore._getSaveData();
			//save data
			Ext.Ajax.request({
				url: Plugin.api.Admin +'save/category',
				method: 'POST',
				params: {
					data: Ext.util.JSON.encode(d)
				},
				success: function(result){
					var json = Ext.util.JSON.decode(result.responseText);
					if (json) if (json.success) {
						var n = PC.tree.node;
						if (d._unsaved) {
							n.setId(Plugin.FormatId('category', json.id));
							n.attributes._unsaved = false;
						}
						var names = {};
						Ext.iterate(json.data.contents, function(ln, cData){
							if (cData.name != undefined) names[ln] = cData.name;
						});
						n.attributes.hot = json.data.hot;
						n.attributes.nomenu = json.data.nomenu;
						n.attributes.published = json.data.published;
						n.attributes._names = names;
						PC.editors.Fill({
							id: json.id,
							data: json.data
						}, null, true);
						if (typeof callback == 'function') callback();
						return true;
					}
					alert('save error');
				},
				failure: function(){
					alert('save error');
				}
			});
		},
		IsDirty: function() {
			return false;
		}
	};
});
//product editor
PC.editors.Register(Plugin.Name, 'product', function(){
	var View = new PC.ux.gallery.files.View({
		ref: '../_media',
		//xtype: 'pc_gallery_files_view',
		store: Plugin.media.Store,
		tpl: Plugin.media.Template,
		border: false,
		style: 'background-color:#fff;'
	});
	var ViewPanel = new Ext.Panel({
		id: Plugin.editorId.Product +'_tab_media',
		title: Plugin.ln.media,
		items: View,
		tbar: [
			{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save},
			'-',
			{	icon: 'images/add.png', text: Plugin.ln.add_image,
				handler: function(){
					var params = {
						save_fn: Plugin.media.GallerySave,
						saveFnType: 'main',
						callee: 'image'
					};
					PC.dialog.gallery.show(params);
				}
			},
			{	icon: 'images/page_white_text.png', text: Plugin.ln.add_attachment,
				handler: function(){
					var params = {
						save_fn: Plugin.media.GallerySave,
						saveFnType: 'attachment'
					};
					PC.dialog.gallery.show(params);
				}
			},
			{	icon: 'images/link_break.png',
				text: Plugin.ln.unlink,
				handler: function(){
					var media = PC.editors.Get()._media;
					var records = media.getSelectedRecords();
					media.store.remove(records);
				}
			},
			'-',
			{icon: 'images/delete.png', text: PC.i18n.del}
		]
	});
	var attributesGrid = new Ext.grid.GridPanel(Plugin.attributes.Grid);
	return {
		xtype: 'tabpanel',
		bodyCssClass: 'x-border-layout-ct',
		activeTab: 0,
		tbar: new PC.ux.LanguageBar(),
		//deferredRender: true,
		items: [
			{	title: Plugin.ln.info,
				ref: '_information',
				layout: 'hbox',
				layoutConfig: {
					align: 'stretch'
				},
				border: false,
				defaults: {
					flex: 1,
					bodyCssClass: 'x-border-layout-ct',
					border: false,
					autoScroll: true
				},
				tbar: [{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save}],
				items: [
					{	style: 'padding:0 6px 6px 6px',
						border: false,
						defaults: {
							border: false
						},
						cls: 'x-panel-mc',
						items: [
							//name
							{	xtype: 'box',
								style: 'padding: 6px',
								html: PC.i18n.name.replace(/\s/, '&nbsp;') + ':'
							},
							{	xtype: 'container',
								//id: 'db_fld_name_container',
								layout: 'fit',
								height: 22,
								items: [{
									ref: '../../_name',
									xtype: 'textfield',
									//xtype: 'profis_multilnfield',
									editorCfg: {
										title: PC.i18n.name
									}
								}]
							},
							//route
							{	xtype: 'box',
								style: 'padding: 6px',
								html: PC.i18n.seo_link.replace(/\s/, '&nbsp;') + ':'
							},
							{	xtype: 'container',
								//id: 'db_fld_route_container',
								layout: 'fit',
								height: 22,
								items: [{
									ref: '../../_route',
									xtype: 'textfield',
									//xtype: 'profis_multilnfield',
									editorCfg: {
										title: PC.i18n.seo_link
									}
								}]
							},
							//route lock
							{	xtype: 'container',
								//id: 'db_fld_route_lock_container',
								layout: 'fit',
								style: 'text-align: right',
								items: [{
									ref: '../../_route_lock',
									xtype: 'checkbox',
									boxLabel: PC.i18n.page.route_lock
								}]
							},
							//title
							{	xtype: 'box',
								style: 'padding: 6px',
								html: PC.i18n.title.replace(/\s/, '&nbsp;') + ':'
							},
							{	xtype: 'container',
								layout: 'fit',
								height: 22,
								items: [{
									ref: '../../_title',
									xtype: 'textfield',
									editorCfg: {
										title: PC.i18n.title
									}
								}]
							},
							//description
							{	xtype: 'box',
								style: 'padding: 6px',
								html: PC.i18n.desc.replace(/\s/, '&nbsp;') + ':'
							},
							{	height: 80,
								xtype: 'container',
								//id: 'db_fld_description_container',
								layout: 'fit',
								items: [{
									ref: '../../_description',
									xtype: 'textarea',
									//xtype: 'profis_multilnfield',
									autoCreate: {
										tag: 'textarea'
									},
									editorCfg: {
										defaultType: 'textarea',
										title: PC.i18n.desc,
										defaults: {
											height: 50
										}
									}
								}]
							},
							//keywords
							{	xtype: 'box',
								style: 'padding: 6px',
								html: PC.i18n.keywords.replace(/\s/, '&nbsp;') + ':'
							},
							{	xtype: 'container',
								height: 80,
								layout: 'fit',
								items: [{
									ref: '../../_keywords',
									xtype: 'textarea',
									autoCreate: {
										tag: 'textarea'
									},
									editorCfg: {
										defaultType: 'textarea',
										title: PC.i18n.keywords,
										defaults: {
											height: 50
										}
									},
									maxLength: 255, //same as in a database, varchar(255)
									listeners: {
										change: function(field, value, old) {
											field.setValue(value.replace(/\n/g, ', '));
										}
									}
								}]
							}
						]
					}
				]
			},
			{	title: Plugin.ln.properties,
				ref: '_properties',
				id: Plugin.editorId.Product +'_tab_properties',
				layout: 'form',
				bodyCssClass: 'x-border-layout-ct',
				padding: 6,
				labelWidth: 200,
				labelAlign: 'right',
				defaults: {
					anchor: '100%'
				},
				border: false,
				tbar: [{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save}],
				items: [
					{ref: '_external_id', fieldLabel: Plugin.ln.external_id, xtype: 'textfield'},
					{ref: '_manufacturer', fieldLabel: Plugin.ln.manufacturer, xtype: 'textfield'},
					{ref: '_mpn', fieldLabel: Plugin.ln.mpn, xtype: 'textfield'},
					{ref: '_quantity', fieldLabel: Plugin.ln.quantity, xtype: 'numberfield'},
					{ref: '_warranty', fieldLabel: Plugin.ln.warranty, xtype: 'numberfield'},
					{ref: '_price', fieldLabel: Plugin.ln.price, xtype: 'numberfield'},
					{ref: '_discount', fieldLabel: Plugin.ln.discount, xtype: 'numberfield'},
					{ref: '_percentage_discount', fieldLabel: Plugin.ln.discount +', %', xtype: 'numberfield'},
					{ref: '_hot', fieldLabel: PC.i18n.page.hot, xtype: 'checkbox'},
					{ref: '_nomenu', fieldLabel: PC.i18n.page.nomenu, xtype: 'checkbox'},
					{ref: '_published', fieldLabel: PC.i18n.page.published, xtype: 'checkbox'}
				]
			},
			{	title: PC.i18n.desc,
				ref: '_description',
				xtype: 'container',
				layout: 'fit',
				items: [{
					xtype: 'profis_tinymce',
					ref: '_description'
				}]
			},
			{	title: Plugin.ln.short_description,
				ref: '_short_description',
				xtype: 'container',
				layout: 'fit',
				items: [{
					xtype: 'profis_tinymce',
					ref: '_short_description'
				}]
			},
			ViewPanel,
			{	title: Plugin.ln.attributes,
				id: Plugin.editorId.Product +'_tab_attributes',
				xtype: 'container',
				layout: 'fit',
				items: [attributesGrid],
				border: false,
				tbar: [{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save}]
			}
		],
		listeners: {
			beforetabchange: function(panel, tab, currentTab) {
				var tabId = tab.getId();
				if (tabId == Plugin.editorId.Product +'_tab_properties'
					|| tabId == Plugin.editorId.Product +'_tab_media'
					|| tabId == Plugin.editorId.Product +'_tab_attributes') {
					panel.getTopToolbar().hide();
				}
				else panel.getTopToolbar().show();
				//panel.getTopToolbar().doLayout();
			}
		},
		Clear: function(editor) {
			var fields = [
				editor._information._name,
				editor._information._route,
				editor._information._route_lock,
				editor._information._title,
				editor._information._description,
				editor._information._keywords,
				editor._properties._external_id,
				editor._properties._manufacturer,
				editor._properties._mpn,
				editor._properties._price,
				editor._properties._discount,
				editor._properties._percentage_discount,
				editor._properties._warranty,
				editor._properties._quantity,
				editor._properties._hot,
				editor._properties._nomenu,
				editor._properties._published
			];
			for (var a=0; fields[a] != undefined; a++) {
				fields[a].setValue('');
			}
			var description = editor._description._description;
			if (description.ed != undefined) {
				description.setValue('');
				description.ed.undoManager.clear();
				description.ed.isNotDirty = 1;
			}
			var shortDescription = editor._short_description._short_description;
			if (shortDescription.ed != undefined) {
				shortDescription.setValue('');
				shortDescription.ed.undoManager.clear();
				shortDescription.ed.isNotDirty = 1;
			}
			Plugin.attributes.ItemStore.clearData();
			Plugin.attributes.Store.clearFilter();
		},
		Load: function(editor, data, ln, freshLoad, callback) {
			if (data != undefined) {
				if (freshLoad) {
					var idData = Plugin.ParseID(data.id);
					if (idData) Plugin.attributes.Load(idData.type, idData.id);
					else Plugin.attributes.ItemStore.reload();
					Plugin.attributes.Store.filter('is_category_attribute', '0');
				}
				data = data.data;
				//resources
				var media = PC.editors.Get()._media;
				if (data.resources != undefined) {
					var mediaStore = media.store;
					media.is_attachment = null;
					mediaStore.loadData(data.resources);
					if (media.rendered) media.refresh();
				}
				//contents
				if (data.contents != undefined) if (typeof data.contents[ln] == 'object') {
					editor._information._name.setValue(data.contents[ln].name);
					editor._information._route.setValue(data.contents[ln].route);
					editor._information._title.setValue(data.contents[ln].seo_title);
					editor._information._description.setValue(data.contents[ln].seo_description);
					editor._information._keywords.setValue(data.contents[ln].seo_keywords);
					var description = editor._description._description;
					description.setValue(data.contents[ln].description);
					if (description.ed != undefined) {
						description.ed.undoManager.clear();
						description.ed.isNotDirty = 1;
					}
					var shortDescription = editor._short_description._short_description;
					shortDescription.setValue(data.contents[ln].short_description);
					if (shortDescription.ed != undefined) {
						shortDescription.ed.undoManager.clear();
						shortDescription.ed.isNotDirty = 1;
					}
				}
				editor._properties._external_id.setValue(data.external_id);
				editor._properties._manufacturer.setValue(data.manufacturer);
				editor._properties._mpn.setValue(data.mpn);
				editor._properties._price.setValue(data.price);
				editor._properties._discount.setValue(data.discount);
				editor._properties._percentage_discount.setValue(data.percentage_discount);
				editor._properties._warranty.setValue(data.warranty);
				editor._properties._quantity.setValue(data.quantity);
				editor._properties._hot.setValue(data.hot);
				editor._properties._nomenu.setValue(data.nomenu);
				editor._properties._published.setValue(data.published);
				editor._information._route_lock.setValue(data.route_lock);
			}
			if (typeof callback == 'function') callback();
		},
		Store: function(editor, data, callback) {
			//save editor data to data store (this.Data = );
			var d = data;
			
			d.external_id = editor._properties._external_id.getValue();
			d.manufacturer = editor._properties._manufacturer.getValue();
			d.mpn = editor._properties._mpn.getValue();
			d.price = editor._properties._price.getValue();
			d.discount = editor._properties._discount.getValue();
			d.percentage_discount = editor._properties._percentage_discount.getValue();
			d.quantity = editor._properties._quantity.getValue();
			d.warranty = editor._properties._warranty.getValue();
			d.hot = editor._properties._hot.getValue();
			d.nomenu = editor._properties._nomenu.getValue();
			d.published = editor._properties._published.getValue();
			d.route_lock = editor._information._route_lock.getValue();
			
			var ln = PC.global.ln;
			if (typeof d.contents != 'object') d.contents = {};
			if (d.contents[ln] == undefined) d.contents[ln] = {};
			var c = d.contents[ln];
			c.name = editor._information._name.getValue();
			c.route = editor._information._route.getValue();
			c.seo_title = editor._information._title.getValue();
			c.seo_description = editor._information._description.getValue();
			c.seo_keywords = editor._information._keywords.getValue();
			var description = editor._description._description;
			if (description.rendered) c.description = description.getValue();
			var shortDescription = editor._short_description._short_description;
			if (shortDescription.rendered) c.short_description = shortDescription.getValue();
			
			
			if (typeof callback == 'function') callback(true);
		},
		Save: function(callback) {
			var d = PC.editors.Data.data;
			var ed = PC.editors.Get();
			//parse resources
			var resources = {add: [], remove: []};
			Ext.iterate(ed._media.store._getNewFields(), function(i){
				resources.add.push({
					id: i.data.file_id,
					is_attachment: i.data.is_attachment
				});
			});
			Ext.iterate(ed._media.store._getDeletedFields(), function(i){
				resources.remove.push({
					id: i.data.file_id,
					is_attachment: i.data.is_attachment
				});
			});
			d.resources = resources;
			delete d.loader;
			//category id
			var categoryId = Plugin.ParseID(PC.tree.node.parentNode.id);
			if (categoryId.type != 'category') return false;
			d.category_id = categoryId.id;
			//save data
			Ext.Ajax.request({
				url: Plugin.api.Admin +'save/product',
				method: 'POST',
				params: {
					data: Ext.util.JSON.encode(d)
				},
				success: function(result){
					var json = Ext.util.JSON.decode(result.responseText);
					if (json) if (json.success) {
						var n = PC.tree.node;
						if (d._unsaved) {
							n.setId(Plugin.FormatId('product', json.id));
							n.attributes._unsaved = false;
						}
						var names = {};
						Ext.iterate(json.data.contents, function(ln, cData){
							if (cData.name != undefined) names[ln] = cData.name;
						});
						n.attributes.hot = json.data.hot;
						n.attributes.nomenu = json.data.nomenu;
						n.attributes.published = json.data.published;
						n.attributes._names = names;
						PC.editors.Fill({
							id: json.id,
							data: json.data
						}, null, true);
						if (typeof callback == 'function') callback();
						return true;
					}
					alert('save error');
				},
				failure: function(){
					alert('save error');
				}
			});
		}
	};
});

Plugin.RenderPaging = function(node, productsCount) {
	if (node == undefined) return false;
	if (productsCount == undefined) var productsCount = node.attributes.pc_shop_products_count;
	//create anchor element for paging block
	var pagingBlockId = node.ui.elNode.id +'-paging';
	//do not render another copy of datepicker for this node if there is already renedered one
	if (Ext.get(pagingBlockId)) return false;
	//node must be expanded!
	var afterExpand = function(node) {
		var pagingEl = document.createElement('div');
		pagingEl.setAttribute('id', pagingBlockId);
		pagingEl.style.marginBottom = '3px';
		pagingEl.style.marginRight = '5px';
		//insert anchor to the right place in the dom
		//var ctElement = Ext.get(node.getUI().ctNode);
		var ctElement = Ext.get(node.getUI().elNode);
		//ctElement.dom.style.border = '1px solid #D2DCEB';
		//ctElement.dom.style.background = '#fcfcfc';
		ctElement.dom.appendChild(pagingEl);
		//ctElement.insertFirst(pagingEl); //Ext.get(selNode.getUI().elNode.nextSibling).insertFirst
		//create datepicker and render to its anchor
		setTimeout(function(){
			//render datepicker
			node.pagingData = {
				total: productsCount,
				products: []
			};
			for (var a=1; a <= productsCount; a++) {
				node.pagingData.products.push([a]);
			}
			node.pagingStore = new Ext.ux.data.PagingArrayStore({
				node: node,
				fields: ['id'],
				idProperty: 'id',
				data: node.pagingData,
				lastOptions: {params: {start: 0, limit: Plugin.productsPerPage}},
				root: 'products',
				listeners: {
					datachanged: function(store){
						store.node.reload();
					}
				}
			});
			node.paging = new Ext.PagingToolbar({
				renderTo: pagingEl,
				pageSize: Plugin.productsPerPage,
				store: node.pagingStore
			});
			pagingEl.style.marginLeft = node.getDepth()*16 +'px';
			pagingEl.style.marginTop = '2px';
			node.addListener('move', function(tree, node, oldParent, newParent, index){
				//node.datepicker.setPosition(node.getDepth()*16+16, 5);
				pagingEl.style.marginLeft = node.getDepth()*16 +'px';
				pagingEl.style.marginTop = '5px';
			});
		}, 70);
	}
	if (!node.expanded) {
		if (!node.isExpandable()) return false;
		node.expand(false, true, afterExpand, this);
	}
	else afterExpand(node);
}

Plugin.IsValidPagingContainer = function(n) {
	var ctrl = n.attributes.controller;
	if (ctrl !== Plugin.Name) {
		var id = this.ParseID(n.id);
		if (id === false) return false;
		if (id.type != 'category') return false;
	}
	return true;
}

PC.hooks.Register('tree.load', function(params){
	var ln = PC.i18n.mod[Plugin.Name];
	var tree = params.tree;
	var n = params.node;
	if (!Plugin.IsValidPagingContainer(n)) return false;
	if (n.attributes.pc_shop_products_count == undefined) return false;
	if (n.attributes.pc_shop_products_count < Plugin.productsPerPage) return false;
	Plugin.RenderPaging(n);
});

PC.hooks.Register('tree.beforeload', function(params){
	var ln = PC.i18n.mod[Plugin.Name];
	var tree = params.tree;
	var n = params.node;
	if (!Plugin.IsValidPagingContainer(n)) return false;
	if (n.attributes.pc_shop_products_count == undefined) return false;
	if (n.attributes.pc_shop_products_count < Plugin.productsPerPage) return false;
	var limit = Plugin.productsPerPage;
	var start = 0;
	if (n.paging != undefined) start = n.pagingStore.lastOptions.params.start;
	var page = Math.ceil((start + limit) / limit);
	tree.addLoaderParam(Plugin.Name, 'page', page);
	tree.addLoaderParam(Plugin.Name, 'perPage', limit);
});