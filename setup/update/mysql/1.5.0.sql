CREATE TABLE `{prefix}shop_coupons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `is_for_hot` tinyint(1) NOT NULL DEFAULT '0',
  `time_from` datetime NOT NULL,
  `time_to` datetime NOT NULL,
  `category_id` smallint(5) NOT NULL,
  `use_limit` smallint(8) NOT NULL DEFAULT '1',
  `used` smallint(8) NOT NULL DEFAULT '0',
  `percentage_discount` decimal(5,2) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `{prefix}shop_order_items`
	ADD `attributes` TINYTEXT COLLATE utf8_unicode_ci NOT NULL AFTER `quantity`;

ALTER TABLE `{prefix}shop_order_items` DROP INDEX `order_id`;
ALTER TABLE `{prefix}shop_order_items` ADD CONSTRAINT `order_id` UNIQUE(`order_id`, `product_id`, `attributes`(14));

ALTER TABLE `{prefix}shop_orders`
	ADD `token` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL AFTER `data`,
	ADD `transaction_id` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL AFTER `token`,
	ADD `coupon_id` INT(10) NOT NULL AFTER `comment`,
	ADD `discount` DECIMAL(10,2) UNSIGNED NOT NULL AFTER `total_price`;

ALTER TABLE `{prefix}shop_prices` DROP INDEX `pkey`;
ALTER TABLE `{prefix}shop_prices` ADD CONSTRAINT `pkey` UNIQUE(`pkey`, `c_id`);

ALTER TABLE `{prefix}shop_product_contents`
	ADD `custom_name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL AFTER `name`,
	CHANGE `seo_keywords` `seo_keywords` TEXT COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE `{prefix}shop_product_prices`
	ADD `price_diff` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `price`,
	ADD `discount` DECIMAL(10,2) UNSIGNED DEFAULT NULL AFTER `price_diff`,
	ADD `attribute_id` INT(10) NOT NULL DEFAULT 0 AFTER `c_id`,
	ADD `attribute_value_id` INT(10) NOT NULL DEFAULT 0 AFTER `attribute_id`,
	ADD `attribute_item_id` INT(10) NOT NULL DEFAULT 0 AFTER `attribute_value_id`,
	ADD `info_1` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL AFTER `attribute_item_id`,
	ADD `info_2` VARCHAR(40) COLLATE utf8_unicode_ci NOT NULL AFTER `info_1`,
	ADD `info_3` VARCHAR(80) COLLATE utf8_unicode_ci NOT NULL AFTER `info_2`;

ALTER TABLE `{prefix}shop_product_prices` DROP INDEX `product_id`;
ALTER TABLE `{prefix}shop_product_prices` ADD CONSTRAINT `product_id` UNIQUE(`product_id`,`quantity`,`c_id`,`attribute_id`,`attribute_value_id`,`attribute_item_id`);

ALTER TABLE `{prefix}shop_products`
	CHANGE `created_on` `created_on` INT(10) UNSIGNED DEFAULT NULL AFTER `hot_from`,
	ADD `info_1` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL AFTER `state`,
	ADD `info_2` VARCHAR(80) COLLATE utf8_unicode_ci NOT NULL AFTER `info_1`,
	ADD `info_3` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL AFTER `info_2`;

