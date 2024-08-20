CREATE TABLE `country_codes` (`name` text NOT NULL,`code` text NOT NULL,`created_at` date DEFAULT NULL,`id` int(11) NOT NULL AUTO_INCREMENT,`is_default` int(11) NOT NULL DEFAULT 0,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE `country_codes` ADD `updated_at` DATE NULL AFTER `is_default`;
INSERT INTO `country_codes` (`name`, `code`, `created_at`, `id`, `is_default`) VALUES ('India', '+91', NULL, '1', '1');
ALTER TABLE `orders` ADD `promocode_id` INT NULL AFTER `order_longitude`;
