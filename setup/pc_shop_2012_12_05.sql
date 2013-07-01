-- phpMyAdmin SQL Dump
-- version 2.9.1.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Dec 05, 2012 at 10:55 AM
-- Server version: 5.5.28
-- PHP Version: 5.3.10-1ubuntu3.4
-- 
-- Database: `cms4`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_attribute_contents`
-- 

CREATE TABLE `pc_shop_attribute_contents` (
  `attribute_id` int(10) unsigned NOT NULL,
  `ln` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  KEY `attribute_id` (`attribute_id`),
  KEY `attribute_id_2` (`attribute_id`,`ln`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_attribute_value_contents`
-- 

CREATE TABLE `pc_shop_attribute_value_contents` (
  `value_id` int(10) unsigned NOT NULL,
  `ln` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `value_id` (`value_id`,`ln`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_attribute_values`
-- 

CREATE TABLE `pc_shop_attribute_values` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `attribute_id` (`attribute_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=45 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_attributes`
-- 

CREATE TABLE `pc_shop_attributes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_custom` tinyint(1) unsigned NOT NULL,
  `is_searchable` tinyint(1) unsigned NOT NULL,
  `is_category_attribute` tinyint(1) unsigned NOT NULL,
  `position` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=127 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_categories`
-- 

CREATE TABLE `pc_shop_categories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `pid` int(10) unsigned DEFAULT NULL,
  `lft` mediumint(9) NOT NULL,
  `rgt` mediumint(9) NOT NULL,
  `external_id` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `flags` smallint(5) unsigned NOT NULL DEFAULT '1',
  `discount` decimal(10,2) unsigned DEFAULT NULL,
  `percentage_discount` decimal(5,2) unsigned DEFAULT NULL,
  `redirect` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_id` (`external_id`),
  KEY `parent_id` (`parent_id`,`lft`,`rgt`,`flags`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=764 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_category_contents`
-- 

CREATE TABLE `pc_shop_category_contents` (
  `category_id` smallint(5) unsigned NOT NULL,
  `ln` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `custom_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `seo_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seo_description` text COLLATE utf8_unicode_ci NOT NULL,
  `seo_keywords` text COLLATE utf8_unicode_ci NOT NULL,
  `route` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `permalink` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`category_id`,`ln`),
  KEY `route` (`route`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_item_attributes`
-- 

CREATE TABLE `pc_shop_item_attributes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` mediumint(8) unsigned NOT NULL,
  `attribute_id` int(10) unsigned NOT NULL,
  `flags` smallint(5) unsigned NOT NULL,
  `value_id` int(10) unsigned DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15543 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_order_items`
-- 

CREATE TABLE `pc_shop_order_items` (
  `order_id` mediumint(8) unsigned NOT NULL,
  `product_id` mediumint(8) unsigned NOT NULL,
  `quantity` mediumint(8) unsigned NOT NULL,
  `price` decimal(10,2) unsigned NOT NULL,
  UNIQUE KEY `order_id` (`order_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_order_statuses`
-- 

CREATE TABLE `pc_shop_order_statuses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` mediumint(8) unsigned NOT NULL,
  `status` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_orders`
-- 

CREATE TABLE `pc_shop_orders` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `date` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `comment` tinytext COLLATE utf8_unicode_ci,
  `total_price` decimal(10,2) unsigned NOT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_product_categories`
-- 

CREATE TABLE `pc_shop_product_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL,
  `category_id` int(10) NOT NULL,
  `flags` smallint(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_product_contents`
-- 

CREATE TABLE `pc_shop_product_contents` (
  `product_id` mediumint(8) unsigned NOT NULL,
  `ln` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` text COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `seo_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seo_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seo_keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `route` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`product_id`,`ln`),
  KEY `route` (`route`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_product_prices`
-- 

CREATE TABLE `pc_shop_product_prices` (
  `product_id` mediumint(8) unsigned NOT NULL,
  `price` decimal(10,2) unsigned NOT NULL,
  `quantity` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `product_id` (`product_id`,`quantity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_products`
-- 

CREATE TABLE `pc_shop_products` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` smallint(5) unsigned NOT NULL,
  `position` smallint(5) unsigned NOT NULL,
  `external_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `manufacturer_id` smallint(5) unsigned NOT NULL,
  `mpn` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `quantity` mediumint(8) unsigned NOT NULL,
  `flags` smallint(5) unsigned NOT NULL,
  `warranty` tinyint(3) unsigned NOT NULL,
  `discount` decimal(10,2) unsigned DEFAULT NULL,
  `percentage_discount` decimal(5,2) unsigned DEFAULT NULL,
  `price` decimal(10,2) unsigned NOT NULL,
  `import_method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_id` (`external_id`),
  KEY `category_id` (`category_id`,`position`,`manufacturer_id`,`flags`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3421 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_shop_resources`
-- 

CREATE TABLE `pc_shop_resources` (
  `resource_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `position` smallint(5) unsigned NOT NULL DEFAULT '1',
  `item_id` mediumint(8) unsigned NOT NULL,
  `file_id` int(11) NOT NULL,
  `flags` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`resource_id`),
  UNIQUE KEY `item_id` (`item_id`,`file_id`),
  KEY `position` (`position`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=23 ;
