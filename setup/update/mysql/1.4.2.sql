CREATE TABLE `{prefix}shop_currency_rates` (
  `c_id` int(11) unsigned NOT NULL,
  `rate` decimal(10,4) unsigned NOT NULL,
  UNIQUE KEY `c_id` (`c_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `{prefix}shop_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pkey` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(10,2) unsigned NOT NULL,
  `c_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pkey` (`pkey`,`price`,`c_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `{prefix}shop_attribute_contents` ADD CONSTRAINT `attribute_id_3` UNIQUE(`attribute_id`, `ln`);

ALTER TABLE `{prefix}shop_product_prices`
	ADD `id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
	CHANGE `quantity` `quantity` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	ADD `c_id` INT(11) UNSIGNED NOT NULL DEFAULT '0';
	ADD PRIMARY KEY (`id`),
	DROP INDEX `product_id`,
	ADD CONSTRAINT `product_id` UNIQUE (`product_id`, `quantity`, `c_id`);
 
INSERT IGNORE INTO `{prefix}variables` (`vkey`, `controller`, `site`, `ln`, `value`) VALUES 
('description', 'pc_shop', 0, 'lt', 'Aprašymas'),
('description', 'pc_shop', 0, 'en', 'Description'),
('description', 'pc_shop', 0, 'ru', 'Описание'),

('details', 'pc_shop', 0, 'lt', 'Plačiau'),
('details', 'pc_shop', 0, 'en', 'View details'),
('details', 'pc_shop', 0, 'ru', 'Подробнее')б

('attribute', 'pc_shop', 0, 'lt', 'Parametras'),
('attribute', 'pc_shop', 0, 'en', 'Parameter'),
('attribute', 'pc_shop', 0, 'ru', 'Параметр'),

('attributes', 'pc_shop', 0, 'lt', 'Parametrai'),
('attributes', 'pc_shop', 0, 'en', 'Parameters'),
('attributes', 'pc_shop', 0, 'ru', 'Параметры'),

('order', 'pc_shop', 0, 'en', 'Order'),
('order', 'pc_shop', 0, 'ru', 'Заказ'),
('order', 'pc_shop', 0, 'lt', 'Užsakymas');

UPDATE `{prefix}variables` SET `value`='Valiuta' WHERE `vkey`='currency' AND `controller`='pc_shop' AND `ln`='lt' AND `value`='Lt';
UPDATE `{prefix}variables` SET `value`='Currency' WHERE `vkey`='currency' AND `controller`='pc_shop' AND `ln`='en' AND `value`='Lt';
UPDATE `{prefix}variables` SET `value`='Валюта' WHERE `vkey`='currency' AND `controller`='pc_shop' AND `ln`='ru' AND `value`='Лт';
