<?php

class PC_shop_import_admin_api extends PC_shop_admin_api {
	
	/**
	 * Access to category is being checked in all api methods
	 */
	protected function _before_action() {
		
	}
	
	protected function _after_action_success() {
		//if (!in_array($this->_method, array('default_action'))) {
			$this->_init_data_change_hook();
		//}
	}
	
	public function confirm() {
			$this->_out['success'] = false;
				//import parsed data
				$categoryId = v($_POST['categoryId']);
				$this->_check_category_access($categoryId);
				$ln = v($_POST['ln'], 'ru');
				$this->shop = $this->core->Get_object('PC_shop_manager');
				$category = $this->shop->categories->Get($categoryId);
				if (!$category) {
					$this->_out['error'] = 'Invalid category ID';
					return;
				}
				$d = json_decode(v($_POST['data'], '{}'), true);
				if (!$d) {
					$this->_out['error'] = 'Invalid data specified for import';
					return;
				}
				if (!count($d)) {
					$this->_out['error'] = 'No items found in import data';
					return;
				}
				//hide all products in this category
				$query = "UPDATE {$this->cfg['db']['prefix']}shop_products SET flags=flags^? WHERE category_id=? and flags&?=?";
				$r = $this->db->prepare($query);
				$query_params = array(PC_shop_products::PF_PUBLISHED, $categoryId, PC_shop_products::PF_PUBLISHED, PC_shop_products::PF_PUBLISHED);
				$s = $r->execute($query_params);
				
				$total_hidden = $r->rowCount();
				
				//prepare statements
				$publish_query = "UPDATE {$this->cfg['db']['prefix']}shop_products SET flags=flags|? WHERE id=?";
				$rPublish = $this->db->prepare($publish_query);
				//$rPublish->execute(array(PC_shop_products::PF_PUBLISHED, $productId));
				//select all items' attributes in this category
				$shopSite = $this->core->Get_object('PC_shop_site');
				$this->site->Identify();
				$query_2 = "SELECT p.id productId, a.attribute_id attrId, a.id itemAttrId FROM {$this->cfg['db']['prefix']}shop_products p JOIN {$this->cfg['db']['prefix']}shop_item_attributes a ON a.item_id=p.id and a.flags&?=? WHERE p.category_id=?";
				$rProductAttributes = $this->db->prepare($query_2);
				$query_params_2 = array(PC_shop_attributes::ITEM_IS_PRODUCT, PC_shop_attributes::ITEM_IS_PRODUCT, $categoryId);
				$s = $rProductAttributes->execute($query_params_2);
				
				if (!$s) {
					$this->_out['error'] = 'Error while selecting item attributes';
					
					/*
					//show all products in this category
					$query = "UPDATE {$cfg['db']['prefix']}shop_products SET flags=flags^? WHERE category_id=? and flags&?=?";
					$r = $db->prepare($query);
					$query_params = array(PC_shop_products::PF_PUBLISHED, $categoryId, PC_shop_products::PF_PUBLISHED, PC_shop_products::PF_PUBLISHED);
					$s = $r->execute($query_params);
					*/					
					
					return;
				}
				$itemAttrMap = array();
				while ($dd = $rProductAttributes->fetch()) {
					if (!isset($itemAttrMap[$dd['productId']])) $itemAttrMap[$dd['productId']] = array();
					$itemAttrMap[$dd['productId']]['attrIds'][] = $dd['attrId'];
					$itemAttrMap[$dd['productId']]['itemAttrIds'][] = $dd['itemAttrId'];
				}
				unset($dd);
				//update items
				$items_imported = 0;
				$items_inserted = 0;
				$items_updated = 0;
				foreach ($d as $item) {
					if (!is_array($item['fields'])) {
						continue;
					}
					if (!count($item['fields'])) {
						continue;
					}
					$id = array_shift($item['fields']);
					$product_id = $this->shop->products->Exists_in_content('name', $id, $ln, $categoryId);
					if (!$product_id) {
						continue;
					}
					//insert/update attributes
					$total_attributes = 0;
					$total_edited_attributes = 0;
					$total_assigned_attributes = 0;
					$edited_attributes = array();
					$assigned_attributes = array();
					foreach ($item['attributes'] as $attrId=>$attrValue) {
						$total_attributes++;
						$updateMode = false;
						if (isset($itemAttrMap[$product_id])) {
							$itemAttrId = array_search($attrId, $itemAttrMap[$product_id]['attrIds']);
							if ($itemAttrId !== false) { //{ if (in_array($attrId, $itemAttrMap[$id])) {
								$itemAttrId = $itemAttrMap[$product_id]['itemAttrIds'][$itemAttrId];
								$updateMode = true;
							}
						}
						if ($updateMode) {
							//echo "Update\n";
							$edited_attributes[$attrId] = $attrValue;
							$this->shop->attributes->Edit_for_item($itemAttrId, null, $attrValue);
							$total_edited_attributes++;
						}
						else {
							//echo "Create\n";
							$assigned_attributes[$attrId] = $attrValue;
							$this->shop->attributes->Assign_to_item($product_id, PC_shop_attributes::ITEM_IS_PRODUCT, $attrId, null, $attrValue);
							$total_assigned_attributes++;
						}
					}
					
					//publish product
					$publish_query_params = array(PC_shop_products::PF_PUBLISHED, $product_id);
					$rPublish->execute($publish_query_params);
					$items_imported++;
				}
				
				
				if ($this->core->Count_hooks('plugin/pc_shop/save/product')) {
					$this->_out['success'] = true;
					$this->_out['data'] = array();
					$hook_object = false;
					$this->core->Init_hooks('plugin/pc_shop/save/product', array(
						'success'=> &$this->_out['success'],
						'category' => $categoryId,
						'out'=> &$this->_out,
						'hook_object' => &$hook_object,
					));
				}
				$this->_out['success'] = true;
				$this->_out['imported'] = $items_imported;
				//$this->_out['inserted'] = $items_inserted;
				//$this->_out['updated'] = $items_updated;
				
				
				
	}
	
	public function initialize_object() {
		$id = v($_POST['id']);
		$this->_check_category_access($id);
		$this->shop = $this->core->Get_object('PC_shop_manager');
		$category = $this->shop->categories->Get($id);
		if ($category) {
			//count items in category
			$totalItems = $this->shop->products->Count($id);
			if ($totalItems === 0) {
				$this->site->Identify();
				$params = array('includeName'=> true);
				$category['attributes'] = $this->shop->attributes->Get_for_item($id, PC_shop_attributes::ITEM_IS_CATEGORY, $params);
				if ($category['attributes'] !== false) {
					$this->_out['success'] = true;
					if (count($category['attributes'])) {
						$sections = array();
						foreach ($category['attributes'] as $attr) {
							if (preg_match("#^Секция ([1-9]+)$#", $attr['name'], $m)) {
								$sections[$m[1]] = $attr['value'];
							}
						}
						//ksort($sections);
						$totalFlats = 0;
						$flat_int_number = 0;
						$all_flats = array();
						foreach ($sections as $a => $section_value) {
							$section_custom_columns = array();
							if (strpos($section_value, '[')) {
								list($section_data_1, $section_data_2) = explode('[', $section_value);
								$section_data_1 = trim($section_data_1);
								$section_data_2 = trim($section_data_2, ']');
								$section_data_2 = trim($section_data_2);
								$section_custom_columns = PC_utils::string_to_array($section_data_2);

								$section_value = $section_data_1;
							}

							$sectionData = explode('/', $section_value);
							if (!$sectionData) break;
							//echo 'Section '.$a.': '; print_pre($sectionData);
							for ($index_floor = 1; $index_floor <= $sectionData[0]; $index_floor++) {
								for ($index_col = 1; $index_col <= $sectionData[1]; $index_col++) {
									$custom_part = '';
									if (!isset($section_custom_columns[$index_col])) {
										$flat_int_number++;
									}
									else {
										$custom_part = $section_custom_columns[$index_col];
									}
									$all_flats[] = $flat_int_number . $custom_part;
								}
							}
							$totalFlats += $sectionData[0] * $sectionData[1];
						}
						/*
						for ($a=1; isset($sections[$a]); $a++) {
							$sectionData = explode('/', $sections[$a]);
							if (!$sectionData) break;
							//echo 'Section '.$a.': '; print_pre($sectionData);
							$totalFlats += $sectionData[0] * $sectionData[1];
						}
						*/
						/*
						for ($a=1; $a<=$totalFlats; $a++) {
							$params = array();
							$data =  array('category_id'=> $id, 'contents'=> array(), 'published'=> false);
							$data['contents'][$this->site->ln] = array('name'=> $a);
							$this->shop->products->Create($id, $a, $data, $params);
						}
						*/
					   foreach ($all_flats as $pos => $a) {
							$params = array();
							$data =  array('category_id'=> $id, 'contents'=> array(), 'published'=> false);
							$data['contents'][$this->site->ln] = array('name'=> $a);
							$this->shop->products->Create($id, $pos + 1, $data, $params);
						}

						$this->_out['success'] = true;
						$this->_out['productsCount'] = $this->shop->products->Count($id);
					}
				}
				else $this->_out['error'] = 'Cannot get category attributes';
			}
			else if ($totalItems === false) {
				$this->_out['error'] = 'Database error';
			}
			else $this->_out['error'] = 'There`s some items inside that category.';
		}
	}
	
	public function delete_products_attributes() {
			$this->_out['success'] = false;
				//import parsed data
				$categoryId = v($_POST['id']);
				$this->_check_category_access($categoryId);

				$query_params = array();
				$query_params[] = $categoryId;
				
				$shop = $this->core->Get_object('PC_shop_manager');
				$flags_cond = $this->db->get_flag_query_condition(PC_shop_attributes::ITEM_IS_PRODUCT, $query_params);
				
				$query = "DELETE FROM {$this->cfg['db']['prefix']}shop_item_attributes
					where item_id in (SELECT id FROM {$this->cfg['db']['prefix']}shop_products where category_id = ?)
					and $flags_cond";
				
				$r = $this->db->prepare($query);
				if ($s = $r->execute($query_params)) {
					$this->_out['success'] = true;
					$this->_out['deleted_attributes'] = $r->rowCount();
					//Hide all products in this category:
					$query_2_params = array($categoryId);
					
					//$flags_cond = $db->get_flag_query_condition(PC_shop_products::PF_PUBLISHED, $query_2_params);
					
					//$update_query = "UPDATE {$cfg['db']['prefix']}shop_products SET flags=flags^? WHERE category_id=? and $flags_cond";

					$delete_query = "DELETE FROM {$this->cfg['db']['prefix']}shop_products WHERE category_id=?";

					$r_hide = $this->db->prepare($delete_query);
					$s_hide = $r_hide->execute($query_2_params);
					if ($s_hide) {
						$this->_out['deleted_products'] = $r_hide->rowCount();
					}
					
					if ($this->core->Count_hooks('plugin/pc_shop/save/product')) {
						$this->_out['success'] = true;
						$this->_out['data'] = array();
						$hook_object = false;
						$this->core->Init_hooks('plugin/pc_shop/save/product', array(
							'success'=> &$this->_out['success'],
							'category' => $categoryId,
							'out'=> &$this->_out,
							'hook_object' => &$hook_object,
						));
					}
					
				}
	}
	
	public function export_products() {
			$this->_out['success'] = false;
				//import parsed data
				
				$categoryId = v($this->routes->Get(3));
				$this->_check_category_access($categoryId);
				$ln = v($this->routes->Get(4), 'en');
				
				$shopSite = $this->core->Get_object('PC_shop_site');
				$this->site->Identify();
				$query = "SELECT p.id productId, a.attribute_id attrId, a.id itemAttrId, a.value aValue 
					FROM {$this->cfg['db']['prefix']}shop_products p 
					JOIN {$this->cfg['db']['prefix']}shop_item_attributes a ON a.item_id=p.id and a.flags&?=? 
					JOIN {$this->cfg['db']['prefix']}shop_attributes sa ON sa.id = a.attribute_id
					WHERE p.category_id=?
					ORDER by p.id, sa.position
					";
				$rProductAttributes = $this->db->prepare($query);
				$query_params = array(PC_shop_attributes::ITEM_IS_PRODUCT, PC_shop_attributes::ITEM_IS_PRODUCT, $categoryId);
				$s = $rProductAttributes->execute($query_params);
				
				$itemAttrMap = array();
				
				$products_attributes = array();
				while ($dd = $rProductAttributes->fetch()) {
					if (!isset($products_attributes[$dd['productId']])) $products_attributes[$dd['productId']] = array();
					$products_attributes[$dd['productId']][ $dd['attrId']] = $dd['aValue'];
				}

				$attributes_names = array();
				$products_attributes_for_export = array();
				
				$shop = $this->core->Get_object('PC_shop_site');
				$category_data = $shop->categories->Get($categoryId);
				$shop->categories->Load_path($category_data);
				
				$pre_values = array(
					'Город' => '',
					'Район' => '',
					'Метро' => false,
					'Адрес' => '',
				);
				
				
				$i = 1;
				foreach ($pre_values as $key => $value) {
					if (!isset($category_data['path'][$i])) {
						break;
					}
					if ($value !== false) {
						$pre_values[$key] = v($category_data['path'][$i]['name']);
					}
					else {
						unset($pre_values[$key]);
					}
					$i++;
				}
				
				if (!empty($products_attributes)) {
					$array_keys = array_keys($products_attributes);
					$attributes_keys = array_keys($products_attributes[$array_keys[0]]);

					if (empty($attributes_keys)) {
						//break;
					}


					foreach ($attributes_keys as $key => $value) {
						$attributes_names[$value] = '';
					}

					$query_attr_names = "SELECT * FROM pc_shop_attribute_contents
						WHERE attribute_id IN (" . implode(',', $attributes_keys) . ") AND ln = ?";
					$params_attr_names = array($ln);

					$r_attr_names = $this->db->prepare($query_attr_names);
					$s_attr_names = $r_attr_names->execute($params_attr_names);

					$bron_key = false;
					$status_key = false;
					
					if ($s_attr_names) {
						while ($d = $r_attr_names->fetch()) {
							$attributes_names[$d['attribute_id']] = $d['name'];
							if ($d['name'] == PC_shop_excel_reader::ATTR_NAME_BRON) {
								$bron_key = $d['attribute_id'];
							}
							if ($d['name'] == PC_shop_excel_reader::ATTR_NAME_STATUS) {
								$status_key = $d['attribute_id'];
							}
						}
					}


					$attributes_names = array_merge(array_keys($pre_values), $attributes_names);

					$pre_values = array_values($pre_values);
					foreach ($products_attributes as $key => $product_data) {
						$product = array();
						$meta = array();
						foreach ($attributes_keys as $attr_key) {
							$product[$attr_key] = v($product_data[$attr_key], '');
							if ($attr_key == $status_key and $product[$attr_key] == 'не для продажи') {
								$meta['background_color'] = "ccffcc";
							}
							if ($attr_key == $bron_key) {
								if ($product[$attr_key] == 'своя') {
									$meta['background_color'] = "00ccff";
								}
								if ($product[$attr_key] == 'чужая') {
									$meta['background_color'] = "ccffff";
								}
							}
												
						}
						$product = array_merge($pre_values, $product);
						if (!empty($meta)) {
							$product['_meta'] = $meta;
						}
						$products_attributes_for_export[] = $product;
					}
				
				}
				else {
					$attributes_names = array_keys($pre_values);
					$products_attributes_for_export[] = array_values($pre_values);
				}
				
				require_once $this->cfg['path']['admin'] . 'classes/Excel_builder.php';
				$excel_builder = new Excel_builder(sanitize_file_name($category_data['name']) . '_' . $category_data['id'], $this->cfg['path']['libs'] . 'xlsxstreamwriter-1.0.0/');

				$excel_builder->make('Лист1', $attributes_names, $products_attributes_for_export);
				
				$excel_builder->output();
				exit;
	}
	
	protected function _import_set_debug() {
	}
	
	protected function _import_set_arguments() {
		$this->categoryId = v($_POST['categoryId']);
		$this->ln = v($_POST['ln']);
	}
	
	protected function _import_adjust_arguments() {
		$this->ln = 'ru';
	}
	
	protected function _import_check_category() {
		$shop = $this->core->Get_object('PC_shop_manager');
		$totalItems = $shop->products->Count($this->categoryId);
		if (!$totalItems) {
			$this->_out['success'] = false;
			$this->_out['error'] = 'There is no items in this category';
			return;
		}
	}
	
	protected function _import_check_file() {
		if (!isset($_FILES['file'])) {
			$this->_out['error'] = 'No file selected';
			return;
		}
		if ($_FILES['file']['error'] > 0) {
			$this->_out['error'] = 'Error uploading selected file';
			return;
		}
		if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
			$this->_out['error'] = 'Uploaded file was not found (probably server issue)';
			return;
		}
	}
	
	protected function _import() {
		//echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body>';
		if ($this->core->Count_hooks('plugin/pc_shop/import/parse')) {
			$this->_out['success'] = true;
			$this->_out['data'] = array();
			$hook_object = false;
			$this->core->Init_hooks('plugin/pc_shop/import/parse', array(
				//'file'=> dirname(__FILE__)."/Брехово.xls",
				'file'=> $_FILES['file']['tmp_name'],
				'success'=> &$this->_out['success'],
				'data'=> &$this->_out['data'],
				'category' => $this->categoryId,
				'ln' => $this->ln,
				'out'=> &$this->_out,
				'hook_object' => &$hook_object,
			));
		}
		else {
			$this->_out['error'] = 'No default parsing method is currently implemented. You must have separate plugin that does';
		}
	}
	
	
	public function default_action() {
		$this->_import_set_arguments();
		$this->_check_category_access($this->categoryId);
		$this->_import_adjust_arguments();
		
		$this->_import_check_category();
		if (isset($this->_out['error'])) {
			return;
		}
		
		$this->_import_check_file();
		if (isset($this->_out['error'])) {
			return;
		}
		
		$this->_import();
		
	}
	
}

?>
