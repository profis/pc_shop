CREATE TABLE `{prefix}shop_category_product_filter_contents` (
  `filter_id` smallint(5) unsigned NOT NULL,
  `ln` varchar(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `filter_id` (`filter_id`,`ln`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `{prefix}shop_currencies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `country_name` varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `country_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE `{prefix}shop_currency_contents` (
  `currency_id` int(11) unsigned NOT NULL,
  `ln` varchar(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `currency_id` (`currency_id`,`ln`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `{prefix}shop_category_product_filters`
	ADD `input_type` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL AFTER `attribute`,
	ADD `filter_type` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `input_type`,
	ADD `position` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `disabled`,
	DROP INDEX `category_id`,
	ADD CONSTRAINT `category_id` UNIQUE (`category_id`, `attribute`, `filter_type`);

UPDATE `{prefix}variables` SET `vkey`='order_phone' WHERE `vkey`='order_tel' AND `controller`='pc_shop';
