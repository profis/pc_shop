ALTER TABLE  `{prefix}shop_item_attributes`
	ADD `next_attribute_id` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `position`,
	ADD `level` SMALLINT(6) NOT NULL DEFAULT 1 AFTER `next_attribute_id`;

ALTER TABLE  `{prefix}shop_product_prices`
	ADD `items_left` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL AFTER `discount`;

ALTER TABLE `{prefix}shop_product_prices`
	CHANGE `info_1` `info_1` TEXT COLLATE utf8_unicode_ci NOT NULL,
	CHANGE `info_2` `info_2` TEXT COLLATE utf8_unicode_ci NOT NULL,
	CHANGE `info_3` `info_3` TEXT COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE  `{prefix}shop_products`
	ADD `is_not_quantitive` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `mpn`;
