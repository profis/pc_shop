Ext.ns('PC.plugin.'+ CurrentlyParsing);
var Plugin = PC.plugin[CurrentlyParsing];

Plugin.productsPerPage = 5;
Plugin.URL = PC.plugins.GetUrl(CurrentlyParsing);
Plugin.api = {
	Public: PC.global.BASE_URL + '/api/plugin/'+ CurrentlyParsing +'/',
	Admin: PC.global.BASE_URL + PC.global.ADMIN_DIR + '/api/plugin/'+ CurrentlyParsing +'/'
};


Plugin.editorId = {
	Product: PC.editors.FormatID(CurrentlyParsing, 'product'),
	Category: PC.editors.FormatID(CurrentlyParsing, 'category')
}
Plugin.ParseID = function(id) {
	var types = ['category', 'product'];
	for (var a=0; types[a] != undefined; a++) {
		if (new RegExp('^('+ CurrentlyParsing +'\/)?'+ types[a] +'\/').test(id)) {
			var params = {type: types[a]};
			var cutFrom = types[a].length + 1;
			if (new RegExp('^'+ CurrentlyParsing +'\/').test(id)) cutFrom += CurrentlyParsing.length + 1;
			params.id = id.substring(cutFrom);
			return params;
		}
	}
	return false;
}
Plugin.FormatId = function(type, id) {
	return CurrentlyParsing +'/'+ type +'/'+ id;
}
Plugin.tree = {
	actions: {
		CreateCategory: new Ext.Action({
			text: 'New subcategory',
			icon: 'images/add.gif',
			handler: function() {
				//create inactive node
				var n = PC.tree.menus.current_node;
				var treeId = 'new-'+ new Date().getTime();
				var id = CurrentlyParsing +'/category/'+ treeId;
				var parent_id = Plugin.ParseID(n.attributes.id);
				if (parent_id.type == 'category') parent_id = parent_id.id;
				else parent_id = 0;
				var data = {
					id: treeId,
					_unsaved: true,
					parent_id: parent_id,
					draggable: false,
					allowDrop: false,
					_empty: true,
					_names: {},
					pc_shop_products_count: 0
				};
				var afterAppend = function(node) {
					if (node) {
						node.select();
						PC.editors.Load({
							id: id, 
							data: data
						}, true);
					}
				}
				node = PC.tree.Append(n, data, afterAppend);
				//open node
				//on save - activate node
			}
		}),
		DeleteCategory: new Ext.Action({
			text: 'Delete',
			icon: 'images/delete.gif',
			handler: function() {
				var n = PC.tree.menus.current_node;
				if (n.attributes.pc_shop_products_count > 0) {
					Ext.Msg.show({
						title: PC.i18n.attention,
						msg: 'You can`t delete category that has products inside.',
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
											msg: 'Error deleting category.'+ (errMsg!=undefined?' '+errMsg:''),
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
			text: 'New product',
			icon: PC.plugins.GetUrl('pc_shop') +'product.png',
			handler: function() {
				//create new product in this category
				//create inactive node
				var n = PC.tree.menus.current_node;
				var treeId = 'new-'+ new Date().getTime();
				var id = CurrentlyParsing +'/product/'+ treeId;
				/*var category_id = Plugin.ParseID(n.attributes.id);
				if (category_id.type == 'category') category_id = category_id.id;
				else category_id = 0;*/
				var data = {
					id: treeId,
					_unsaved: true,
					draggable: false,
					allowDrop: false,
					leaf: true,
					icon: PC.plugins.GetUrl('pc_shop') +'product.png',
					_names: {}
				};
				var afterAppend = function(node) {
					if (node) {
						node.select();
						PC.editors.Load({
							id: id, 
							data: data
						}, true);
					}
				}
				node = PC.tree.Append(n, data, afterAppend);
			}
		}),
		DeleteProduct: new Ext.Action({
			text: 'Delete',
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
											msg: 'Error deleting product.'+ (errMsg!=undefined?' '+errMsg:''),
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
		id: 'pc_tree_menu_'+ CurrentlyParsing +'_shop',
		items: [
			Plugin.tree.actions.CreateCategory,
			'-',
			PC.tree.actions.Rename,
			PC.tree.actions.Properties
		]
	}),
	category: new Ext.menu.Menu({
		id: 'pc_tree_menu_'+ CurrentlyParsing +'_category',
		items: [
			Plugin.tree.actions.CreateProduct,
			'-',
			Plugin.tree.actions.CreateCategory,
			'-',
			Plugin.tree.actions.DeleteCategory
		]
	}),
	product: new Ext.menu.Menu({
		id: 'pc_tree_menu_'+ CurrentlyParsing +'_product',
		items: [
			Plugin.tree.actions.CreateProduct,
			'-',
			Plugin.tree.actions.DeleteProduct
		]
	})
}
//begiu svirties laisvumas + fiksatorius
PC.hooks.Register('core/tree/menu/'+ CurrentlyParsing, function(params){
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
PC.hooks.Register('core/editors/identify/'+ CurrentlyParsing, function(params){
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
			+'<tpl if="!is_attachment"><div style="clear:both;padding:10px 0 6px 0;margin-bottom:5px;border-bottom:1px solid #888;"><h2><img style="vertical-align:-2px;" src="images/image.png" alt="" /> Main images</h2></div></tpl>'
			+'<tpl if="is_attachment"><div style="clear:both;padding:10px 0 6px 0;margin-bottom:5px;border-bottom:1px solid #888;"><h2><img style="vertical-align:-2px;" src="images/page_white_text.png" alt="" /> Attachments</h2></div></tpl>'
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
PC.editors.Register(CurrentlyParsing, 'category', function(){
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
		title: 'Media',
		items: View,
		tbar: [
			{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save},
			'-',
			{	icon: 'images/add.png', text: 'Add image',
				handler: function(){
					var params = {
						save_fn: Plugin.media.GallerySave,
						saveFnType: 'main',
						callee: 'image'
					};
					PC.dialog.gallery.show(params);
				}
			},
			{	icon: 'images/page_white_text.png', text: 'Add attachment',
				handler: function(){
					var params = {
						save_fn: Plugin.media.GallerySave,
						saveFnType: 'attachment'
					};
					PC.dialog.gallery.show(params);
				}
			},
			{	icon: 'images/link_break.png',
				text: 'Unlink',
				handler: function(){
					var media = PC.editors.Get()._media;
					var records = media.getSelectedRecords();
					media.store.remove(records);
				}
			},
			'-',
			{icon: 'images/delete.png', text: 'Delete'}
		]
	});
	return {
		xtype: 'tabpanel',
		bodyCssClass: 'x-border-layout-ct',
		activeTab: 0,
		tbar: new PC.ux.LanguageBar(),
		//deferredRender: true,
		items: [
			{	title: 'Information',
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
			{	title: 'Properties',
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
					{ref: '_external_id', fieldLabel: 'External ID', xtype: 'textfield'},
					{ref: '_discount', fieldLabel: 'Discount', xtype: 'numberfield'},
					{ref: '_percentage_discount', fieldLabel: 'Discount, %', xtype: 'numberfield'},
					{ref: '_active', fieldLabel: 'Active', xtype: 'checkbox'}
				]
			},
			{	title: 'Description',
				ref: '_description',
				xtype: 'container',
				layout: 'fit',
				items: [{
					xtype: 'profis_tinymce',
					ref: '_description'
				}]
			},
			ViewPanel/*,
			{	title: 'Attributes',
				id: Plugin.editorId.Category +'_tab_attributes',
				//layout: 'form',
				//bodyCssClass: 'x-border-layout-ct',
				padding: 6,
				//labelWidth: 200,
				//labelAlign: 'right',
				/*defaults: {
					anchor: '100%'
				},* /
				border: false,
				tbar: [{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save}]
			}*/
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
				editor._description._description,
				editor._properties._discount,
				editor._properties._percentage_discount,
				editor._properties._active,
				editor._properties._external_id
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
		},
		Load: function(editor, data, ln, freshLoad, callback) {
			if (data != undefined) {
				data = data.data;
				var media = PC.editors.Get()._media;
				//resources
				if (data.resources != undefined) {
					var mediaStore = media.store;
					media.is_attachment = null;
					mediaStore.loadData(data.resources);
					media.refresh();
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
				editor._properties._active.setValue(data.active);
			}
			if (typeof callback == 'function') callback();
		},
		Store: function(editor, data, callback) {
			//save editor data to data store (this.Data = );
			var d = data;
			d.external_id = editor._properties._external_id.getValue();
			d.discount = editor._properties._discount.getValue();
			d.percentage_discount = editor._properties._percentage_discount.getValue();
			d.active = editor._properties._active.getValue();
			
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
		Save: function() {
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
						n.attributes._names = names;
						PC.tree.component.localizeNode(n);
						PC.editors.Fill({
							id: json.id,
							data: json.data
						}, null, true);
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
PC.editors.Register(CurrentlyParsing, 'product', function(){
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
		title: 'Media',
		items: View,
		tbar: [
			{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save},
			'-',
			{	icon: 'images/add.png', text: 'Add image',
				handler: function(){
					var params = {
						save_fn: Plugin.media.GallerySave,
						saveFnType: 'main',
						callee: 'image'
					};
					PC.dialog.gallery.show(params);
				}
			},
			{	icon: 'images/page_white_text.png', text: 'Add attachment',
				handler: function(){
					var params = {
						save_fn: Plugin.media.GallerySave,
						saveFnType: 'attachment'
					};
					PC.dialog.gallery.show(params);
				}
			},
			{	icon: 'images/link_break.png',
				text: 'Unlink',
				handler: function(){
					var media = PC.editors.Get()._media;
					var records = media.getSelectedRecords();
					media.store.remove(records);
				}
			},
			'-',
			{icon: 'images/delete.png', text: 'Delete'}
		]
	});
	return {
		xtype: 'tabpanel',
		bodyCssClass: 'x-border-layout-ct',
		activeTab: 0,
		tbar: new PC.ux.LanguageBar(),
		//deferredRender: true,
		items: [
			{	title: 'Information',
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
			{	title: 'Properties',
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
					{ref: '_manufacturer', fieldLabel: 'Manufacturer', xtype: 'textfield'},
					{ref: '_mpn', fieldLabel: 'Manufacturer product number (MPN)', xtype: 'textfield'},
					{ref: '_quantity', fieldLabel: 'Quantity', xtype: 'numberfield'},
					{ref: '_warranty', fieldLabel: 'Warranty', xtype: 'numberfield'},
					{ref: '_price', fieldLabel: 'Price', xtype: 'numberfield'},
					{ref: '_discount', fieldLabel: 'Discount', xtype: 'numberfield'},
					{ref: '_percentage_discount', fieldLabel: 'Discount, %', xtype: 'numberfield'},
					{ref: '_active', fieldLabel: 'Active', xtype: 'checkbox'}
				]
			},
			{	title: 'Description',
				ref: '_description',
				xtype: 'container',
				layout: 'fit',
				items: [{
					xtype: 'profis_tinymce',
					ref: '_description'
				}]
			},
			{	title: 'Short description',
				ref: '_short_description',
				xtype: 'container',
				layout: 'fit',
				items: [{
					xtype: 'profis_tinymce',
					ref: '_short_description'
				}]
			},
			ViewPanel/*,
			{	title: 'Attributes',
				id: Plugin.editorId.Product +'_tab_attributes',
				//layout: 'form',
				//bodyCssClass: 'x-border-layout-ct',
				padding: 6,
				//labelWidth: 200,
				//labelAlign: 'right',
				/*defaults: {
					anchor: '100%'
				},* /
				border: false,
				tbar: [{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save}]
			}*/
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
				editor._information._title,
				editor._information._description,
				editor._information._keywords,
				editor._properties._manufacturer,
				editor._properties._mpn,
				editor._properties._price,
				editor._properties._discount,
				editor._properties._percentage_discount,
				editor._properties._warranty,
				editor._properties._quantity,
				editor._properties._active
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
		},
		Load: function(editor, data, ln, freshLoad, callback) {
			if (data != undefined) {
				data = data.data;
				//resources
				var media = PC.editors.Get()._media;
				if (data.resources != undefined) {
					var mediaStore = media.store;
					media.is_attachment = null;
					mediaStore.loadData(data.resources);
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
				editor._properties._manufacturer.setValue(data.manufacturer);
				editor._properties._mpn.setValue(data.mpn);
				editor._properties._price.setValue(data.price);
				editor._properties._discount.setValue(data.discount);
				editor._properties._percentage_discount.setValue(data.percentage_discount);
				editor._properties._warranty.setValue(data.warranty);
				editor._properties._quantity.setValue(data.quantity);
				editor._properties._active.setValue(data.active);
			}
			if (typeof callback == 'function') callback();
		},
		Store: function(editor, data, callback) {
			//save editor data to data store (this.Data = );
			var d = data;
			
			d.manufacturer = editor._properties._manufacturer.getValue();
			d.mpn = editor._properties._mpn.getValue();
			d.price = editor._properties._price.getValue();
			d.discount = editor._properties._discount.getValue();
			d.percentage_discount = editor._properties._percentage_discount.getValue();
			d.quantity = editor._properties._quantity.getValue();
			d.warranty = editor._properties._warranty.getValue();
			d.active = editor._properties._active.getValue();
			
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
		Save: function() {
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
						n.attributes._names = names;
						PC.tree.component.localizeNode(n);
						PC.editors.Fill({
							id: json.id,
							data: json.data
						}, null, true);
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
			/*node.datepicker = new Ext.DatePickerProfis({
				renderTo: pagingEl,
				format: 'Y-m-d',
				cls: 'x-profis-datepicker',
				handler: function(picker, date, callback){
					var formattedDate = date.format('Y-m-d');
					var statusEl = Ext.get(pagingBlockId +'-status');
					statusEl.update('<img style="vertical-align:-2px;margin-right:3px" src="images/calendar.gif" alt="" />'+ formattedDate);
					tree.addLoaderParam(module_name, 'date', formattedDate);
					node.reload();
					node.attributes.dateFilter = formattedDate;
					if (typeof callback == 'function') callback();
				},
				listeners: {
					afterrender: function(picker){
						picker.el.dom.style.marginBottom = '5px';
						//get list of dates that has news
						Plugin.Refresh_enabled_dates(picker, ctrl, node.attributes.id);
						//create filter status element
						var filterStatusEl = document.createElement('div');
						filterStatusEl.setAttribute('id', pagingBlockId +'-status');
						filterStatusEl.innerHTML = '<img style="vertical-align:-2px;margin-right:3px" src="images/calendar.gif" alt="" />'+ ln.no_date;
						pagingEl.appendChild(filterStatusEl);
						var filterStatusExtEl = Ext.get(filterStatusEl);
						filterStatusExtEl.setVisibilityMode(Ext.Element.DISPLAY);
						if (!node.childNodes.length) filterStatusExtEl.hide();
						/* //create 'remove date filter' button
						Ext.get(pagingEl).child('.x-date-bottom');* /
					}
				}
			});*/
			/*node.addListener('append', function(tree, node, appended, index){
				Plugin.Refresh_status_element(
					Ext.get(pagingBlockId +'-status'),
					node.childNodes.length
				);
			});
			node.addListener('remove', function(tree, node, removed){
				Plugin.Refresh_status_element(
					Ext.get(pagingBlockId +'-status'),
					node.childNodes.length
				);
			});*/
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
	if (ctrl !== CurrentlyParsing) {
		var id = this.ParseID(n.id);
		if (id === false) return false;
		if (id.type != 'category') return false;
	}
	return true;
}

PC.hooks.Register('tree.load', function(params){
	var ln = PC.i18n.mod[CurrentlyParsing];
	var tree = params.tree;
	var n = params.node;
	if (!Plugin.IsValidPagingContainer(n)) return false;
	if (n.attributes.pc_shop_products_count == undefined) return false;
	if (n.attributes.pc_shop_products_count < Plugin.productsPerPage) return false;
	Plugin.RenderPaging(n);
});

PC.hooks.Register('tree.beforeload', function(params){
	var ln = PC.i18n.mod[CurrentlyParsing];
	var tree = params.tree;
	var n = params.node;
	if (!Plugin.IsValidPagingContainer(n)) return false;
	if (n.attributes.pc_shop_products_count == undefined) return false;
	if (n.attributes.pc_shop_products_count < Plugin.productsPerPage) return false;
	var limit = Plugin.productsPerPage;
	var start = 0;
	if (n.paging != undefined) start = n.pagingStore.lastOptions.params.start;
	var page = Math.ceil((start + limit) / limit);
	tree.addLoaderParam(CurrentlyParsing, 'page', page);
	tree.addLoaderParam(CurrentlyParsing, 'perPage', limit);
});