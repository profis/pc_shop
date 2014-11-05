ALTER TABLE `{prefix}shop_products` 
	ADD COLUMN `weight` DECIMAL(9,3) UNSIGNED NULL DEFAULT NULL AFTER `info_3`,
	ADD COLUMN `volume` DECIMAL(18,9) UNSIGNED NULL DEFAULT NULL AFTER `weight`,
	ADD COLUMN `length` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `volume`,
	ADD COLUMN `width` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `length`,
	ADD COLUMN `height` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `width`;

ALTER TABLE `{prefix}shop_product_prices` 
	ADD COLUMN `weight` DECIMAL(9,3) UNSIGNED NULL DEFAULT NULL AFTER `info_3`,
	ADD COLUMN `volume` DECIMAL(18,9) UNSIGNED NULL DEFAULT NULL AFTER `weight`,
	ADD COLUMN `length` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `volume`,
	ADD COLUMN `width` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `length`,
	ADD COLUMN `height` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `width`;

ALTER TABLE `{prefix}shop_orders`
	DROP INDEX `id`,
	ADD PRIMARY KEY (`id`);

INSERT IGNORE INTO `{prefix}variables` (`vkey`, `controller`, `site`, `ln`, `value`) VALUES
('order_total_exceeds_cod_limit', 'pc_shop', 0, 'lt', 'Užsakymo suma viršija maksimalią sumą, kurią galima sumokėti kurjeriui pristačius užsakymą.'),
('order_total_exceeds_cod_limit', 'pc_shop', 0, 'en', 'Order total exceeds "cash on delivery" limit.'),
('order_total_exceeds_cod_limit', 'pc_shop', 0, 'ru', 'Сумма заказа превышает максимальную сумму, которую можно оплатить курьеру по доставке заказа.');
