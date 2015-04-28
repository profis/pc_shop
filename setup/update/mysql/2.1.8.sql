ALTER TABLE `{prefix}shop_attribute_categories`
	CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `{prefix}shop_attributes_categories`
	CHANGE COLUMN `attribute_id` `attribute_id` INT(10) UNSIGNED NOT NULL,
	CHANGE COLUMN `category_id` `category_id` INT(10) UNSIGNED NOT NULL;

DELETE FROM `{prefix}shop_attribute_category_contents` WHERE `attr_category_id` NOT IN (SELECT `id` FROM `{prefix}shop_attribute_categories`);
ALTER TABLE `{prefix}shop_attribute_category_contents`
	ADD CONSTRAINT `fk_sacc_attribute_category` FOREIGN KEY (`attr_category_id`) REFERENCES `{prefix}shop_attribute_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

DELETE FROM `{prefix}shop_attribute_contents` WHERE `attribute_id` NOT IN (SELECT `id` FROM `{prefix}shop_attributes`);
ALTER TABLE `{prefix}shop_attribute_contents` 
	ADD CONSTRAINT `fk_sacn_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `{prefix}shop_attributes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

DELETE FROM `{prefix}shop_attribute_values` WHERE `attribute_id` NOT IN (SELECT `id` FROM `{prefix}shop_attributes`);
ALTER TABLE `{prefix}shop_attribute_values` 
	ADD CONSTRAINT `fk_sav_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `{prefix}shop_attributes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

DELETE FROM `{prefix}shop_attribute_value_contents` WHERE `value_id` NOT IN (SELECT `id` FROM `{prefix}shop_attribute_values`);
ALTER TABLE `{prefix}shop_attribute_value_contents` 
	ADD CONSTRAINT `fk_savc_attribute_value` FOREIGN KEY (`value_id`) REFERENCES `{prefix}shop_attribute_values` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

DELETE FROM `{prefix}shop_attributes_categories` WHERE `attribute_id` NOT IN (SELECT `id` FROM `{prefix}shop_attributes`) OR `category_id` NOT IN (SELECT `id` FROM `{prefix}shop_attribute_categories`);
ALTER TABLE `{prefix}shop_attributes_categories` 
	ADD CONSTRAINT `fk_sacm_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `{prefix}shop_attributes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `fk_sacm_attribute_category` FOREIGN KEY (`category_id`) REFERENCES `{prefix}shop_attribute_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

DELETE FROM `{prefix}shop_item_attributes` WHERE `attribute_id` NOT IN (SELECT `id` FROM `{prefix}shop_attributes`);
UPDATE `{prefix}shop_item_attributes` SET `value_id` = NULL WHERE `value_id` NOT IN (SELECT `id` FROM `{prefix}shop_attribute_values`);
ALTER TABLE `{prefix}shop_item_attributes` 
	ADD INDEX `fk_item_attributes_attributes_idx` (`attribute_id` ASC),
	ADD INDEX `fk_item_attributes_attribute_values_idx` (`value_id` ASC),
	ADD CONSTRAINT `fk_sia_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `{prefix}shop_attributes` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
	ADD CONSTRAINT `fk_sia_attribute_value` FOREIGN KEY (`value_id`) REFERENCES `{prefix}shop_attribute_values` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
