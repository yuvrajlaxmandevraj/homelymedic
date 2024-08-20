-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 20, 2024 at 07:21 PM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `homelymedic`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(50) NOT NULL,
  `type` varchar(32) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `area` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `alternate_mobile` varchar(20) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `city_id` int(20) NOT NULL DEFAULT 0,
  `city` varchar(252) NOT NULL,
  `landmark` varchar(128) DEFAULT NULL,
  `state` varchar(200) DEFAULT NULL,
  `country` varchar(200) DEFAULT NULL,
  `lattitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `partner_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `is_saved_for_later` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `service_id`, `qty`, `is_saved_for_later`, `created_at`, `updated_at`) VALUES
(8, 4, 1, 1, 0, '2024-03-07 06:02:35', '2024-03-07 06:02:35');

-- --------------------------------------------------------

--
-- Table structure for table `cash_collection`
--

CREATE TABLE `cash_collection` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `commison` int(11) NOT NULL,
  `status` text NOT NULL,
  `partner_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `order_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(50) NOT NULL DEFAULT 0,
  `name` varchar(1024) NOT NULL,
  `image` text NOT NULL,
  `slug` varchar(1024) NOT NULL,
  `admin_commission` double NOT NULL COMMENT 'global admin commission for all partners',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0 - deactive | 1 - active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL,
  `dark_color` varchar(255) NOT NULL,
  `light_color` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`, `image`, `slug`, `admin_commission`, `status`, `created_at`, `updated_at`, `dark_color`, `light_color`) VALUES
(1, 0, 'Basic Healthcare Services', 'basic-needs.png', '', 0, 1, '2024-01-03 06:31:46', '0000-00-00 00:00:00', '#2a2c3e', '#ffffff'),
(2, 0, 'Specialized Nursing Services', 'nurse.png', '', 0, 1, '2024-01-03 06:34:26', '0000-00-00 00:00:00', '#2a2c3e', '#ffffff'),
(3, 0, 'Maternal and Child Health', 'ultrasound.png', '', 0, 1, '2024-01-03 06:46:01', '0000-00-00 00:00:00', '#2a2c3e', '#ffffff'),
(4, 0, 'Rehabilitation Services', 'rehabilitation.png', '', 0, 1, '2024-01-03 06:46:26', '0000-00-00 00:00:00', '#2a2c3e', '#ffffff');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `name` mediumtext NOT NULL,
  `latitude` varchar(120) DEFAULT NULL,
  `longitude` varchar(120) DEFAULT NULL,
  `delivery_charge_method` varchar(30) DEFAULT NULL,
  `fixed_charge` int(11) NOT NULL DEFAULT 0,
  `per_km_charge` int(11) NOT NULL DEFAULT 0,
  `range_wise_charges` text DEFAULT NULL,
  `time_to_travel` int(11) NOT NULL DEFAULT 0,
  `geolocation_type` varchar(30) DEFAULT NULL COMMENT 'not used in current',
  `radius` varchar(512) DEFAULT '0' COMMENT 'not used in current',
  `boundary_points` text DEFAULT NULL COMMENT 'not used in current',
  `max_deliverable_distance` int(10) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `country_codes`
--

CREATE TABLE `country_codes` (
  `name` text NOT NULL,
  `code` text NOT NULL,
  `created_at` date DEFAULT NULL,
  `id` int(11) NOT NULL,
  `is_default` int(11) NOT NULL DEFAULT 0,
  `updated_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `country_codes`
--

INSERT INTO `country_codes` (`name`, `code`, `created_at`, `id`, `is_default`, `updated_at`) VALUES
('India', '+91', NULL, 1, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `delete_general_notification`
--

CREATE TABLE `delete_general_notification` (
  `id` int(50) NOT NULL,
  `user_id` int(50) NOT NULL,
  `notification_id` int(50) NOT NULL,
  `is_readed` tinyint(50) NOT NULL,
  `is_deleted` tinytext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `question` mediumtext DEFAULT NULL,
  `answer` mediumtext DEFAULT NULL,
  `status` char(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Administrator'),
(2, 'members', 'Customers'),
(3, 'partners', 'Service Providing Partners');

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `language` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `is_rtl` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `language`, `code`, `is_rtl`, `created_at`) VALUES
(1, 'english', 'en', 0, '2021-12-25 10:37:11');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `login` varchar(100) NOT NULL,
  `time` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2021-12-02-124048', 'App\\Database\\Migrations\\AddProducts', 'default', 'App', 1669892046, 1),
(2, '2021-12-03-040835', 'App\\Database\\Migrations\\Test', 'default', 'App', 1669892046, 1),
(3, '2022-12-01-114504', 'App\\Database\\Migrations\\users_tokens', 'default', 'App', 1669900113, 2),
(5, '2023-01-25-052312', 'App\\Database\\Migrations\\Test', 'default', 'App', 1708162302, 3);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(128) NOT NULL,
  `message` varchar(512) NOT NULL,
  `type` varchar(12) NOT NULL,
  `type_id` varchar(512) NOT NULL DEFAULT '0',
  `image` varchar(128) DEFAULT NULL,
  `order_id` int(50) DEFAULT NULL,
  `user_id` int(50) DEFAULT NULL,
  `is_readed` tinyint(1) NOT NULL,
  `notification_type` varchar(50) DEFAULT NULL,
  `date_sent` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `target` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `type` varchar(128) NOT NULL,
  `type_id` int(11) NOT NULL,
  `image` varchar(128) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 - Deactive | 1 - Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `partner_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL DEFAULT 0,
  `city` varchar(252) NOT NULL,
  `total` double NOT NULL,
  `visiting_charges` double NOT NULL DEFAULT 0,
  `promo_code` varchar(64) NOT NULL,
  `promo_discount` double NOT NULL,
  `final_total` double NOT NULL,
  `payment_method` varchar(64) NOT NULL,
  `admin_earnings` double NOT NULL,
  `partner_earnings` double NOT NULL,
  `is_commission_settled` tinyint(1) NOT NULL COMMENT '0: Not settled\r\n1: Settled\r\n',
  `address_id` int(11) NOT NULL,
  `address` varchar(2048) NOT NULL,
  `date_of_service` date NOT NULL,
  `starting_time` time NOT NULL,
  `ending_time` time NOT NULL,
  `duration` varchar(64) NOT NULL COMMENT 'in minutes',
  `status` varchar(64) NOT NULL COMMENT '0. awaiting\r\n1. confirmed\r\n2. rescheduled\r\n3. cancelled\r\n4. completed',
  `remarks` varchar(2048) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL,
  `payment_status` text DEFAULT NULL,
  `otp` int(11) DEFAULT NULL,
  `work_started_proof` text DEFAULT NULL,
  `work_completed_proof` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `order_latitude` text DEFAULT NULL,
  `order_longitude` text DEFAULT NULL,
  `promocode_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `order_services`
--

CREATE TABLE `order_services` (
  `id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `service_title` text NOT NULL,
  `tax_percentage` double NOT NULL,
  `tax_amount` double NOT NULL,
  `price` double NOT NULL,
  `discount_price` double NOT NULL,
  `quantity` double NOT NULL,
  `sub_total` double NOT NULL COMMENT 'price X quantity',
  `status` varchar(64) NOT NULL COMMENT '0. awaiting \r\n1. confirmed \r\n2. rescheduled \r\n3. cancelled \r\n4. completed	',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `partner_details`
--

CREATE TABLE `partner_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `partner_id` int(11) NOT NULL COMMENT 'user_id',
  `company_name` varchar(1024) DEFAULT NULL,
  `about` varchar(4096) NOT NULL,
  `national_id` varchar(1024) DEFAULT NULL,
  `address` varchar(1024) DEFAULT NULL,
  `banner` longtext NOT NULL,
  `address_id` varchar(1024) DEFAULT NULL,
  `passport` varchar(1024) DEFAULT NULL,
  `tax_name` varchar(100) DEFAULT NULL,
  `tax_number` varchar(64) DEFAULT NULL,
  `bank_name` varchar(256) DEFAULT NULL,
  `account_number` varchar(256) NOT NULL,
  `account_name` varchar(512) DEFAULT NULL,
  `bank_code` varchar(256) DEFAULT NULL,
  `swift_code` varchar(256) DEFAULT NULL,
  `advance_booking_days` int(11) NOT NULL DEFAULT 0,
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 - individual | 1 - organization ',
  `number_of_members` int(11) NOT NULL,
  `admin_commission` text NOT NULL COMMENT '[ {"category_id" : "commission"},{...} ]',
  `visiting_charges` int(20) NOT NULL,
  `is_approved` tinyint(1) NOT NULL COMMENT '0. Not approved\r\n1. Approved\r\n7. Trashed',
  `service_range` double DEFAULT NULL,
  `ratings` double NOT NULL DEFAULT 0,
  `number_of_ratings` double NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL,
  `other_images` text NOT NULL,
  `long_description` longtext NOT NULL,
  `at_store` int(11) DEFAULT NULL,
  `at_doorstep` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `partner_details`
--

INSERT INTO `partner_details` (`id`, `partner_id`, `company_name`, `about`, `national_id`, `address`, `banner`, `address_id`, `passport`, `tax_name`, `tax_number`, `bank_name`, `account_number`, `account_name`, `bank_code`, `swift_code`, `advance_booking_days`, `type`, `number_of_members`, `admin_commission`, `visiting_charges`, `is_approved`, `service_range`, `ratings`, `number_of_ratings`, `created_at`, `updated_at`, `other_images`, `long_description`, `at_store`, `at_doorstep`) VALUES
(1, 2, 'DivEcho Health', 'High rated professional', 'public/backend/assets/national_id/logo.jpeg', 'jhotwara, jaipur', 'public/backend/assets/banner/pexels-rdne-stock-project-8313180.jpg', 'public/backend/assets/address_id/logo.jpeg', 'public/backend/assets/passport/logo.jpeg', 'IFSC0000', '1234567899', 'SBI', '1234567899999999', 'SBI', 'IFSC0000', 'QWERTY123', 0, 1, 1, '20', 500, 1, NULL, 0, 0, '2024-01-03 07:00:29', '0000-00-00 00:00:00', '', '<p>High rated professional&nbsp;</p>', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `partner_subscriptions`
--

CREATE TABLE `partner_subscriptions` (
  `subscription_id` text NOT NULL,
  `status` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_payment` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `partner_id` int(11) NOT NULL,
  `purchase_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `duration` text NOT NULL,
  `price` text NOT NULL,
  `discount_price` text NOT NULL,
  `publish` text DEFAULT NULL,
  `order_type` text NOT NULL,
  `max_order_limit` text DEFAULT NULL,
  `service_type` text NOT NULL,
  `max_service_limit` text DEFAULT NULL,
  `tax_type` text NOT NULL,
  `tax_id` text NOT NULL,
  `is_commision` text NOT NULL,
  `commission_threshold` text DEFAULT NULL,
  `commission_percentage` text DEFAULT NULL,
  `transaction_id` text DEFAULT NULL,
  `tax_percentage` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `partner_subscriptions`
--

INSERT INTO `partner_subscriptions` (`subscription_id`, `status`, `created_at`, `updated_at`, `is_payment`, `id`, `partner_id`, `purchase_date`, `expiry_date`, `name`, `description`, `duration`, `price`, `discount_price`, `publish`, `order_type`, `max_order_limit`, `service_type`, `max_service_limit`, `tax_type`, `tax_id`, `is_commision`, `commission_threshold`, `commission_percentage`, `transaction_id`, `tax_percentage`) VALUES
('6', 'active', '2024-01-18 13:20:03', '2024-01-18 13:20:03', 1, 7, 2, '2024-01-18', '2024-01-18', 'Subscribe now', 'for verification', 'unlimited', '100', '90', '1', 'unlimited', '0', 'limited', '0', 'included', '', 'yes', '20', '20', '0', 0);

-- --------------------------------------------------------

--
-- Table structure for table `partner_timings`
--

CREATE TABLE `partner_timings` (
  `id` int(11) NOT NULL,
  `partner_id` int(11) NOT NULL,
  `day` varchar(20) DEFAULT NULL,
  `opening_time` time NOT NULL,
  `closing_time` time NOT NULL,
  `is_open` tinyint(2) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `partner_timings`
--

INSERT INTO `partner_timings` (`id`, `partner_id`, `day`, `opening_time`, `closing_time`, `is_open`, `created_at`, `updated_at`) VALUES
(1, 2, 'monday', '00:00:00', '00:00:00', 1, '2024-01-03 07:00:29', '2024-01-03 07:00:29'),
(2, 2, 'tuesday', '00:00:00', '00:00:00', 1, '2024-01-03 07:00:29', '2024-01-03 07:00:29'),
(3, 2, 'wednesday', '00:00:00', '00:00:00', 1, '2024-01-03 07:00:29', '2024-01-03 07:00:29'),
(4, 2, 'thursday', '00:00:00', '00:00:00', 1, '2024-01-03 07:00:29', '2024-01-03 07:00:29'),
(5, 2, 'friday', '00:00:00', '00:00:00', 1, '2024-01-03 07:00:29', '2024-01-03 07:00:29'),
(6, 2, 'saturday', '00:00:00', '00:00:00', 1, '2024-01-03 07:00:29', '2024-01-03 07:00:29'),
(7, 2, 'sunday', '00:00:00', '00:00:00', 1, '2024-01-03 07:00:29', '2024-01-03 07:00:29');

-- --------------------------------------------------------

--
-- Table structure for table `payment_request`
--

CREATE TABLE `payment_request` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` varchar(56) NOT NULL COMMENT 'partner | customer',
  `payment_address` varchar(1024) NOT NULL,
  `amount` double NOT NULL,
  `remarks` varchar(512) DEFAULT NULL,
  `status` tinyint(2) NOT NULL DEFAULT 0 COMMENT '0-pending | 1- approved|2-rejected',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int(11) NOT NULL,
  `partner_id` int(11) NOT NULL,
  `promo_code` varchar(28) NOT NULL,
  `message` varchar(512) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `no_of_users` int(11) DEFAULT NULL,
  `minimum_order_amount` double DEFAULT NULL,
  `discount` double DEFAULT NULL,
  `discount_type` varchar(32) DEFAULT NULL,
  `max_discount_amount` double DEFAULT NULL,
  `repeat_usage` tinyint(4) NOT NULL,
  `no_of_repeat_usage` int(11) DEFAULT NULL,
  `image` varchar(256) DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `partner_id`, `promo_code`, `message`, `start_date`, `end_date`, `no_of_users`, `minimum_order_amount`, `discount`, `discount_type`, `max_discount_amount`, `repeat_usage`, `no_of_repeat_usage`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 0, 'NEWUSER', 'only for first 100 new users', '2024-01-03 00:00:00', '2025-10-31 00:00:00', 100, 100, 10, 'percentage', 50, 1, 1, 'public/uploads/promocodes/pexels-karolina-grabowska-5625108.jpg', 1, '2024-01-03 07:38:22', '2024-01-03 07:38:22');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(1024) NOT NULL,
  `section_type` varchar(1024) NOT NULL,
  `category_ids` varchar(255) DEFAULT NULL,
  `partners_ids` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `status` text NOT NULL,
  `limit` text NOT NULL,
  `rank` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `title`, `section_type`, `category_ids`, `partners_ids`, `created_at`, `updated_at`, `status`, `limit`, `rank`) VALUES
(1, 'Top Rated Provider', 'top_rated_partner', NULL, NULL, '2024-01-03 13:39:52', '2024-01-18 16:42:54', '0', '10', 1),
(2, 'New Top Rated', 'top_rated_partner', NULL, NULL, '2024-01-18 16:33:04', '2024-02-17 16:30:46', '0', '1', 2),
(3, 'Top Rated', 'partners', NULL, '2', '2024-02-03 11:23:07', '2024-02-03 12:01:46', '0', '', 3),
(4, 'Services', 'categories', '4,3,2,1', NULL, '2024-02-03 11:50:09', '2024-02-03 11:50:09', '1', '', 4);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'partner_id',
  `category_id` int(11) NOT NULL,
  `tax_type` varchar(20) NOT NULL DEFAULT ' included',
  `tax_id` int(11) NOT NULL DEFAULT 0,
  `tax` double DEFAULT NULL,
  `title` varchar(2048) NOT NULL,
  `slug` varchar(2048) NOT NULL,
  `description` text NOT NULL,
  `tags` text NOT NULL,
  `image` varchar(512) DEFAULT NULL,
  `price` double NOT NULL,
  `discounted_price` double NOT NULL DEFAULT 0,
  `number_of_members_required` int(11) NOT NULL DEFAULT 1 COMMENT 'No of members required to perform service',
  `duration` varchar(128) NOT NULL COMMENT 'in minutes',
  `rating` double NOT NULL DEFAULT 0 COMMENT 'Average rating',
  `number_of_ratings` double NOT NULL DEFAULT 0,
  `on_site_allowed` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 - not allowed | 1 - allowed',
  `is_cancelable` tinyint(1) NOT NULL DEFAULT 0,
  `cancelable_till` varchar(200) NOT NULL,
  `max_quantity_allowed` int(11) NOT NULL DEFAULT 0 COMMENT '0 - unlimited | number - limited qty',
  `is_pay_later_allowed` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 - not allowed | 1 - allowed',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 - deactive | 1 - active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `long_description` longtext NOT NULL,
  `other_images` text NOT NULL,
  `files` text NOT NULL,
  `faqs` text NOT NULL,
  `at_store` text DEFAULT NULL,
  `at_doorstep` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `user_id`, `category_id`, `tax_type`, `tax_id`, `tax`, `title`, `slug`, `description`, `tags`, `image`, `price`, `discounted_price`, `number_of_members_required`, `duration`, `rating`, `number_of_ratings`, `on_site_allowed`, `is_cancelable`, `cancelable_till`, `max_quantity_allowed`, `is_pay_later_allowed`, `status`, `created_at`, `updated_at`, `long_description`, `other_images`, `files`, `faqs`, `at_store`, `at_doorstep`) VALUES
(1, 2, 4, 'excluded', 2, NULL, 'Glucose Monitoring', '', 'Glucose Monitoring', 'Glucose', 'public/uploads/services/1704265678_4b935c1735a6d8929a4d.jpg', 100, 50, 1, '30', 0, 0, 0, 1, '30', 1, 1, 1, '2024-01-03 07:07:58', '2024-02-03 09:47:00', '<p>Glucose Monitoring</p>', '', '', '[[\"\",\"\"]]', '1', '1');

-- --------------------------------------------------------

--
-- Table structure for table `services_ratings`
--

CREATE TABLE `services_ratings` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `rating` double NOT NULL,
  `comment` varchar(4096) DEFAULT NULL,
  `images` text DEFAULT NULL COMMENT 'multiple images( comma separated )',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `variable` varchar(35) NOT NULL,
  `value` mediumtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `variable`, `value`, `created_at`, `updated_at`) VALUES
(1, 'test', '{\"val\" : \"this\"}', '2022-04-21 05:59:17', '0000-00-00 00:00:00'),
(2, 'languages', '{\"ar-XA\":\"Arabic [Switzerland]\",\"bn-IN\":\"Bengali [India]\",\"en-GB\":\"English [United Kingdom]\",\"fr-CA\":\"French [Canada]\",\"en-US\":\"English [United States of America]\",\"es-ES\":\"Spanish \\/ Castilian [Spain]\",\"fi-FI\":\"Finnish [Finland]\",\"gu-IN\":\"Gujarati [India]\",\"ja-JP\":\"Japanese (ja) [Japan]\",\"kn-IN\":\"Kannada [India]\",\"ml-IN\":\"Malayalam [India]\",\"sv-SE\":\"Swedish [Sweden]\",\"ta-IN\":\"Tamil [India]\",\"tr-TR\":\"Turkish [Turkey]\",\"ms-MY\":\"Malay [Malaysia]\",\"pa-IN\":\"Punjabi [India]\",\"cs-CZ\":\"Czech [Czech Republic]\",\"de-DE\":\"German [Germany]\",\"en-AU\":\"English [Australia]\",\"en-IN\":\"English [India]\",\"es-US\":\"Spanish \\/ Castilian [United States of America]\",\"fr-FR\":\"French [France, French Republic]\",\"hi-IN\":\"Hindi [India]\",\"id-ID\":\"Indonesian [Indonesia]\",\"it-IT\":\"Italian [Italy]\",\"ko-KR\":\"Korean [Korea]\",\"ru-RU\":\"Russian [Russian Federation]\",\"uk-UA\":\"Ukrainian [Ukraine]\",\"cmn-CN\":\"Mandarin Chinese [China]\",\"cmn-TW\":\"Mandarin Chinese [Taiwan]\",\"da-DK\":\"Danish [Denmark]\",\"el-GR\":\"Greek \\/ Modern [Greece]\",\"fil-PH\":\"Filipino \\/ Pilipino [Philippines]\",\"hu-HU\":\"Hungarian [Hungary]\",\"nb-NO\":\"Norwegian Bokm\\u00e5l [Norway]\",\"nl-BE\":\"Dutch [Belgium]\",\"nl-NL\":\"Dutch [Netherlands the]\",\"pt-PT\":\"Portuguese [Portugal, Portuguese Republic]\",\"sk-SK\":\"Slovak [Slovakia (Slovak Republic)]\",\"vi-VN\":\"Vietnamese [Vietnam]\",\"pl-PL\":\"Polish [Poland]\",\"pt-BR\":\"Portuguese [Brazil]\",\"ca-ES\":\"Catalan; Valencian [Spain]\",\"yue-HK\":\"Yue Chinese [Hong Kong]\",\"af-ZA\":\"Afrikaans [South Africa]\",\"bg-BG\":\"Bulgarian [Bulgaria]\",\"lv-LV\":\"Latvian [Latvia]\",\"ro-RO\":\"Romanian \\/ Moldavian \\/ Moldovan [Romania]\",\"sr-RS\":\"Serbian [Serbia]\",\"th-TH\":\"Thai [Thailand]\",\"te-IN\":\"Telugu [India]\",\"is-IS\":\"Icelandic [Iceland]\",\"cy-GB\":\"Welsh [United Kingdom]\",\"en-GB-WLS\":\"English [united kingdom]\",\"es-MX\":\"Spanish \\/ Castilian [Mexico]\",\"en-NZ\":\"English [New Zealand]\",\"en-ZA\":\"English [South Africa]\",\"ar-EG\":\"Arabic [Egypt]\",\"ar-SA\":\"Arabic [Saudi Arabia]\",\"de-AT\":\"German [Austria]\",\"de-CH\":\"German [Switzerland, Swiss Confederation]\",\"en-CA\":\"English [Canada]\",\"en-HK\":\"English [Hong Kong]\",\"en-IE\":\"English [Ireland]\",\"en-PH\":\"English [Philippines]\",\"en-SG\":\"English [Singapore]\",\"es-AR\":\"Spanish \\/ Castilian [Argentina]\",\"es-CO\":\"Spanish \\/ Castilian [Colombia]\",\"et-EE\":\"Estonian [Estonia]\",\"fr-BE\":\"French [Belgium]\",\"fr-CH\":\"French [Switzerland, Swiss Confederation]\",\"ga-IE\":\"Irish [Ireland]\",\"he-IL\":\"Hebrew (modern) [Israel]\",\"hr-HR\":\"Croatian [Croatia]\",\"lt-LT\":\"Lithuanian [Lithuania]\",\"mr-IN\":\"Marathi [India]\",\"mt-MT\":\"Maltese [Malta]\",\"sl-SI\":\"Slovene [Slovenia]\",\"sw-KE\":\"Swahili [Kenya]\",\"ur-PK\":\"Urdu [Pakistan]\",\"zh-CN\":\"Chinese [China]\",\"zh-HK\":\"Chinese [Hong Kong]\",\"zh-TW\":\"Chinese [Taiwan]\",\"es-LA\":\"Spanish \\/ Castilian [Lao]\",\"ar-MS\":\"Arabic [Montserrat]\"}', '2022-04-21 05:59:17', '0000-00-00 00:00:00'),
(13, 'payment_gateways_settings', '{\"razorpayApiStatus\":\"disable\",\"razorpay_mode\":\"test\",\"razorpay_currency\":\"INR\",\"paypal_client_key\":\"1235\",\"paypal_currency_code\":\"USD\",\"paypal_secret_key\":\"1235\",\"paypal_business_email\":\"test@test.com\",\"paypal_mode\":\"sandbox\",\"razorpay_secret\":\"9Lp5asdwq32dsewsdwewfasdfDW9VnB\",\"razorpay_key\":\"3ewrrzp_test_9pXj3rasdfasdfrc\",\"endpoint\":\"https:\\/\\/edemand.espeech.in\\/api\\/webhooks\\/stripe\",\"paystack_status\":\"disable\",\"paystack_mode\":\"test\",\"paystack_currency\":\"NGN\",\"paystack_secret\":\"sk_tes4rfr4rt_c8eb6777f1asdfasdfe4werfda3f8599fa5d0f\",\"paystack_key\":\"pk_test_578fec834wedserd6ee2dsdfg06d6afdbc91\",\"stripe_status\":\"enable\",\"stripe_mode\":\"test\",\"stripe_currency\":\"INR\",\"stripe_publishable_key\":\"pk_test_51LERZeewrfddesfd4dssaSCisdfgsdf2IqfiHZyvbVM6b4R7ofokfYk6HSSmv4KsdfgiSizEswdffPhSC4SZC00M9HLWZZl\",\"stripe_webhook_secret_key\":\"whsec_defrdvcfdcdVPL2OK6Sp2jmsdfg9qgJmgU6Gcv\",\"stripe_secret_key\":\"sk_test_51LERZescT6ngl88bSZzN4SHqH58CFnjkcx87ydsnb65KJ7eEQKSzniJTXgVNXFQPXuKfu9pAOYVMOe6UedfdfE2q7hY5J400qllsvrye\"}', '2022-11-28 13:03:05', '0000-00-00 00:00:00'),
(15, 'terms_conditions', '{\"terms_conditions\":\"<p>Partner Terms and Conditions Here<\\/p>\"}', '2022-04-29 06:48:00', '0000-00-00 00:00:00'),
(16, 'privacy_policy', '{\"privacy_policy\":\"<h1>Privacy Policy for WRTeam<\\/h1>\\r\\n<p>At eDemand Provider, accessible from https:\\/\\/edemand.erestro.me\\/partner\\/login, one of our main priorities is the privacy of our visitors. This Privacy Policy document contains types of information that is collected and recorded by eDemand Provider and how we use it.<\\/p>\\r\\n<p>If you have additional questions or require more information about our Privacy Policy, do not hesitate to contact us.<\\/p>\\r\\n<p>This Privacy Policy applies only to our online activities and is valid for visitors to our website with regards to the information that they shared and\\/or collect in eDemand Provider. This policy is not applicable to any information collected offline or via channels other than this website.<\\/p>\\r\\n<p>Consent<\\/p>\\r\\n<p>By using our website, you hereby consent to our Privacy Policy and agree to its terms.<\\/p>\\r\\n<h2>Information we collect<\\/h2>\\r\\n<p>The personal information that you are asked to provide, and the reasons why you are asked to provide it, will be made clear to you at the point we ask you to provide your personal information.<\\/p>\\r\\n<p>If you contact us directly, we may receive additional information about you such as your name, email address, phone number, the contents of the message and\\/or attachments you may send us, and any other information you may choose to provide.<\\/p>\\r\\n<p>When you register for an Account, we may ask for your contact information, including items such as name, company name, address, email address, and telephone number.<\\/p>\\r\\n<h2>How we use your information<\\/h2>\\r\\n<p>We use the information we collect in various ways, including to:<\\/p>\\r\\n<ul>\\r\\n<li>Provide, operate, and maintain our website<\\/li>\\r\\n<li>Improve, personalize, and expand our website<\\/li>\\r\\n<li>Understand and analyze how you use our website<\\/li>\\r\\n<li>Develop new products, services, features, and functionality<\\/li>\\r\\n<li>Communicate with you, either directly or through one of our partners, including for customer service, to provide you with updates and other information relating to the website, and for marketing and promotional purposes<\\/li>\\r\\n<li>Send you emails<\\/li>\\r\\n<li>Find and prevent fraud<\\/li>\\r\\n<\\/ul>\\r\\n<h2>Log Files<\\/h2>\\r\\n<p>eDemand Provider follows a standard procedure of using log files. These files log visitors when they visit websites. All hosting companies do this and a part of hosting services\' analytics. The information collected by log files include internet protocol (IP) addresses, browser type, Internet Service Provider (ISP), date and time stamp, referring\\/exit pages, and possibly the number of clicks. These are not linked to any information that is personally identifiable. The purpose of the information is for analyzing trends, administering the site, tracking users\' movement on the website, and gathering demographic information.<\\/p>\\r\\n<h2>Advertising Partners Privacy Policies<\\/h2>\\r\\n<p>You may consult this list to find the Privacy Policy for each of the advertising partners of eDemand Provider.<\\/p>\\r\\n<p>Third-party ad servers or ad networks uses technologies like cookies, JavaScript, or Web Beacons that are used in their respective advertisements and links that appear on eDemand Provider, which are sent directly to users\' browser. They automatically receive your IP address when this occurs. These technologies are used to measure the effectiveness of their advertising campaigns and\\/or to personalize the advertising content that you see on websites that you visit.<\\/p>\\r\\n<p>Note that eDemand Provider has no access to or control over these cookies that are used by third-party advertisers.<\\/p>\\r\\n<h2>Third Party Privacy Policies<\\/h2>\\r\\n<p>eDemand Provider\'s Privacy Policy does not apply to other advertisers or websites. Thus, we are advising you to consult the respective Privacy Policies of these third-party ad servers for more detailed information. It may include their practices and instructions about how to opt-out of certain options.<\\/p>\\r\\n<p>You can choose to disable cookies through your individual browser options. To know more detailed information about cookie management with specific web browsers, it can be found at the browsers\' respective websites.<\\/p>\\r\\n<h2>CCPA Privacy Rights (Do Not Sell My Personal Information)<\\/h2>\\r\\n<p>Under the CCPA, among other rights, California consumers have the right to:<\\/p>\\r\\n<p>Request that a business that collects a consumer\'s personal data disclose the categories and specific pieces of personal data that a business has collected about consumers.<\\/p>\\r\\n<p>Request that a business delete any personal data about the consumer that a business has collected.<\\/p>\\r\\n<p>Request that a business that sells a consumer\'s personal data, not sell the consumer\'s personal data.<\\/p>\\r\\n<p>If you make a request, we have one month to respond to you. If you would like to exercise any of these rights, please contact us.<\\/p>\\r\\n<h2>GDPR Data Protection Rights<\\/h2>\\r\\n<p>We would like to make sure you are fully aware of all of your data protection rights. Every user is entitled to the following:<\\/p>\\r\\n<p>The right to access &ndash; You have the right to request copies of your personal data. We may charge you a small fee for this service.<\\/p>\\r\\n<p>The right to rectification &ndash; You have the right to request that we correct any information you believe is inaccurate. You also have the right to request that we complete the information you believe is incomplete.<\\/p>\\r\\n<p>The right to erasure &ndash; You have the right to request that we erase your personal data, under certain conditions.<\\/p>\\r\\n<p>The right to restrict processing &ndash; You have the right to request that we restrict the processing of your personal data, under certain conditions.<\\/p>\\r\\n<p>The right to object to processing &ndash; You have the right to object to our processing of your personal data, under certain conditions.<\\/p>\\r\\n<p>The right to data portability &ndash; You have the right to request that we transfer the data that we have collected to another organization, or directly to you, under certain conditions.<\\/p>\\r\\n<p>If you make a request, we have one month to respond to you. If you would like to exercise any of these rights, please contact us.<\\/p>\\r\\n<h2>Children\'s Information<\\/h2>\\r\\n<p>Another part of our priority is adding protection for children while using the internet. We encourage parents and guardians to observe, participate in, and\\/or monitor and guide their online activity.<\\/p>\\r\\n<p>eDemand Provider does not knowingly collect any Personal Identifiable Information from children under the age of 13. If you think that your child provided this kind of information on our website, we strongly encourage you to contact us immediately and we will do our best efforts to promptly remove such information from our records.<\\/p>\"}', '2022-11-29 09:52:41', '0000-00-00 00:00:00'),
(17, 'about_us', '{\"about_us\":\"<p>this is about us page information<\\/p>\\r\\n<p>&nbsp;<\\/p>\"}', '2022-11-22 06:38:16', '0000-00-00 00:00:00'),
(18, 'general_settings', '{\"company_title\":\"homelyMedic- On Demand Services\",\"support_name\":\"homelyMedic\",\"support_email\":\"info@homelymedic.com\",\"phone\":\"7014080004\",\"system_timezone_gmt\":\"+05:30\",\"system_timezone\":\"Asia\\/Kolkata\",\"max_serviceable_distance\":\"50\",\"country_code\":\"+91\",\"primary_color\":\"#007bff\",\"secondary_color\":\"#fcfcfc\",\"primary_shadow\":\"#ffffff\",\"otp_system\":\"1\",\"booking_auto_cancle_duration\":\"1130\",\"address\":\"<p>#123, Time Square, Bhuj - India<\\/p>\",\"short_description\":\"<p>homelyMedic- On Demand services<\\/p>\",\"copyright_details\":\"<p><strong>Copyright &copy; <\\/strong>2022 homelyMedic. All rights reserved.<\\/p>\",\"support_hours\":\"<p>Enter Support Hours<\\/p>\",\"login_image\":\"\",\"favicon\":\"1704189940_d4cd4596adb28581c76a.jpeg\",\"logo\":\"1704189940_055e22ea3ea8adf92dc4.jpeg\",\"half_logo\":\"1704189940_1f7b1d9f0f40e9aace23.jpeg\",\"partner_favicon\":\"1704189940_dfdccbe7931d12c9fb44.jpeg\",\"partner_logo\":\"1704189940_27936bf1be451444c4ce.jpeg\",\"partner_half_logo\":\"1704189940_41f10cb77b962fa68ede.jpeg\",\"currency\":\"\\u20b9\",\"country_currency_code\":\"INR\",\"decimal_point\":\"0\",\"customer_current_version_android_app\":\"1.0.0\",\"customer_current_version_ios_app\":\"1.0.0\",\"customer_compulsary_update_force_update\":\"0\",\"provider_current_version_android_app\":\"1.0.0\",\"provider_current_version_ios_app\":\"1.0.0\",\"provider_compulsary_update_force_update\":\"0\",\"customer_app_maintenance_schedule_date\":\"2024-01-02 00:00 to 2024-01-02 23:59\",\"message_for_customer_application\":\"\",\"customer_app_maintenance_mode\":\"0\",\"provider_app_maintenance_schedule_date\":\"2024-01-02 00:00 to 2024-01-02 23:59\",\"message_for_provider_application\":\"\",\"provider_app_maintenance_mode\":\"0\",\"provider_location_in_provider_details\":\"1\"}', '2024-01-03 08:16:47', '0000-00-00 00:00:00'),
(19, 'email_settings', '{\"mailProtocol\":\"SMTP\",\"smtpPort\":\"465\",\"smtpHost\":\"smtp.googlemail.com\",\"smtpEncryption\":\"ssl\",\"smtpUsername\":\"rajasthantech.info@gmail.com\",\"mailType\":\"html\",\"smtpPassword\":\"mvezvjkfqdjcjbya\",\"update\":\"Save changes\"}', '2024-01-02 11:09:45', '0000-00-00 00:00:00'),
(21, 'refund_policy', '{\"refund_policy\":\"\"}', '2022-04-21 05:59:17', '0000-00-00 00:00:00'),
(22, 'app_settings', '{\"maintenance_date\":\"2022-11-15\",\"start_time\":\"11:01\",\"end_time\":\"15:03\",\"maintenance_mode\":\"on\"}', '2022-11-01 06:29:54', '0000-00-00 00:00:00'),
(23, 'customer_terms_conditions', '{\"customer_terms_conditions\":\"<p>Customer Terms and Condtions here<\\/p>\"}', '2022-04-29 06:41:44', NULL),
(24, 'customer_privacy_policy', '{\"customer_privacy_policy\":\"<h1>Privacy Policy for eDemand<\\/h1>\\r\\n<p>At https:\\/\\/edemand.espeech.in\\/admin, accessible from https:\\/\\/edemand.espeech.in\\/admin, one of our main priorities is the privacy of our visitors. This Privacy Policy document contains types of information that is collected and recorded by https:\\/\\/edemand.espeech.in\\/admin and how we use it.<\\/p>\\r\\n<p>If you have additional questions or require more information about our Privacy Policy, do not hesitate to contact us.<\\/p>\\r\\n<p>This Privacy Policy applies only to our online activities and is valid for visitors to our website with regards to the information that they shared and\\/or collect in https:\\/\\/edemand.espeech.in\\/admin. This policy is not applicable to any information collected offline or via channels other than this website. Our Privacy Policy was created with the help of the <a href=\\\"https:\\/\\/www.privacypolicygenerator.info\\/\\\">Free Privacy Policy Generator<\\/a>.<\\/p>\\r\\n<h2>Consent<\\/h2>\\r\\n<p>By using our website, you hereby consent to our Privacy Policy and agree to its terms.<\\/p>\\r\\n<h2>Information we collect<\\/h2>\\r\\n<p>The personal information that you are asked to provide, and the reasons why you are asked to provide it, will be made clear to you at the point we ask you to provide your personal information.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>If you contact us directly, we may receive additional information about you such as your name, email address, phone number, the contents of the message and\\/or attachments you may send us, and any other information you may choose to provide.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>When you register for an Account, we may ask for your contact information, including items such as name, company name, address, email address, and telephone number.<\\/p>\\r\\n<h2>How we use your information<\\/h2>\\r\\n<p>We use the information we collect in various ways, including to:<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<ul>\\r\\n<ul>\\r\\n<li>Provide, operate, and maintain our website<\\/li>\\r\\n<\\/ul>\\r\\n<\\/ul>\\r\\n<p>&nbsp;<\\/p>\\r\\n<ul>\\r\\n<ul>\\r\\n<li>Improve, personalize, and expand our website<\\/li>\\r\\n<\\/ul>\\r\\n<\\/ul>\\r\\n<p>&nbsp;<\\/p>\\r\\n<ul>\\r\\n<ul>\\r\\n<li>Understand and analyze how you use our website<\\/li>\\r\\n<\\/ul>\\r\\n<\\/ul>\\r\\n<p>&nbsp;<\\/p>\\r\\n<ul>\\r\\n<ul>\\r\\n<li>Develop new products, services, features, and functionality<\\/li>\\r\\n<\\/ul>\\r\\n<\\/ul>\\r\\n<p>&nbsp;<\\/p>\\r\\n<ul>\\r\\n<ul>\\r\\n<li>Communicate with you, either directly or through one of our partners, including for customer service, to provide you with updates and other information relating to the website, and for marketing and promotional purposes<\\/li>\\r\\n<\\/ul>\\r\\n<\\/ul>\\r\\n<p>&nbsp;<\\/p>\\r\\n<ul>\\r\\n<ul>\\r\\n<li>Send you emails<\\/li>\\r\\n<\\/ul>\\r\\n<\\/ul>\\r\\n<p>&nbsp;<\\/p>\\r\\n<ul>\\r\\n<ul>\\r\\n<li>Find and prevent fraud<\\/li>\\r\\n<\\/ul>\\r\\n<\\/ul>\\r\\n<p>&nbsp;<\\/p>\\r\\n<h2>Log Files<\\/h2>\\r\\n<p>https:\\/\\/edemand.espeech.in\\/admin follows a standard procedure of using log files. These files log visitors when they visit websites. All hosting companies do this and a part of hosting services\' analytics. The information collected by log files include internet protocol (IP) addresses, browser type, Internet Service Provider (ISP), date and time stamp, referring\\/exit pages, and possibly the number of clicks. These are not linked to any information that is personally identifiable. The purpose of the information is for analyzing trends, administering the site, tracking users\' movement on the website, and gathering demographic information.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<h2>Advertising Partners Privacy Policies<\\/h2>\\r\\n<p>You may consult this list to find the Privacy Policy for each of the advertising partners of https:\\/\\/edemand.espeech.in\\/admin.<\\/p>\\r\\n<p>Third-party ad servers or ad networks uses technologies like cookies, JavaScript, or Web Beacons that are used in their respective advertisements and links that appear on https:\\/\\/edemand.espeech.in\\/admin, which are sent directly to users\' browser. They automatically receive your IP address when this occurs. These technologies are used to measure the effectiveness of their advertising campaigns and\\/or to personalize the advertising content that you see on websites that you visit.<\\/p>\\r\\n<p>Note that https:\\/\\/edemand.espeech.in\\/admin has no access to or control over these cookies that are used by third-party advertisers.<\\/p>\\r\\n<h2>Third Party Privacy Policies<\\/h2>\\r\\n<p>https:\\/\\/edemand.espeech.in\\/admin\'s Privacy Policy does not apply to other advertisers or websites. Thus, we are advising you to consult the respective Privacy Policies of these third-party ad servers for more detailed information. It may include their practices and instructions about how to opt-out of certain options.<\\/p>\\r\\n<p>You can choose to disable cookies through your individual browser options. To know more detailed information about cookie management with specific web browsers, it can be found at the browsers\' respective websites.<\\/p>\\r\\n<h2>CCPA Privacy Rights (Do Not Sell My Personal Information)<\\/h2>\\r\\n<p>Under the CCPA, among other rights, California consumers have the right to:<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>Request that a business that collects a consumer\'s personal data disclose the categories and specific pieces of personal data that a business has collected about consumers.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>Request that a business delete any personal data about the consumer that a business has collected.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>Request that a business that sells a consumer\'s personal data, not sell the consumer\'s personal data.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>If you make a request, we have one month to respond to you. If you would like to exercise any of these rights, please contact us.<\\/p>\\r\\n<h2>GDPR Data Protection Rights<\\/h2>\\r\\n<p>We would like to make sure you are fully aware of all of your data protection rights. Every user is entitled to the following:<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>The right to access &ndash; You have the right to request copies of your personal data. We may charge you a small fee for this service.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>The right to rectification &ndash; You have the right to request that we correct any information you believe is inaccurate. You also have the right to request that we complete the information you believe is incomplete.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>The right to erasure &ndash; You have the right to request that we erase your personal data, under certain conditions.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>The right to restrict processing &ndash; You have the right to request that we restrict the processing of your personal data, under certain conditions.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>The right to object to processing &ndash; You have the right to object to our processing of your personal data, under certain conditions.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>The right to data portability &ndash; You have the right to request that we transfer the data that we have collected to another organization, or directly to you, under certain conditions.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>If you make a request, we have one month to respond to you. If you would like to exercise any of these rights, please contact us.<\\/p>\\r\\n<h2>Children\'s Information<\\/h2>\\r\\n<p>Another part of our priority is adding protection for children while using the internet. We encourage parents and guardians to observe, participate in, and\\/or monitor and guide their online activity.<\\/p>\\r\\n<p>https:\\/\\/edemand.espeech.in\\/admin does not knowingly collect any Personal Identifiable Information from children under the age of 13. If you think that your child provided this kind of information on our website, we strongly encourage you to contact us immediately and we will do our best efforts to promptly remove such information from our records.<\\/p>\"}', '2022-11-16 11:52:29', NULL),
(25, 'country_codes_old', '{\r\n  \n  \"countries\": [\n   \r\n  {\n      \"code\": \"+7 840\",\n      \"name\": \"Abkhazia\"\n    },\n \r\n  {\n      \"code\": \"+93\",\n      \"name\": \"Afghanistan\"\n  },\n\r\n  {\n      \"code\": \"+355\",\n      \"name\": \"Albania\"\n    },\n \r\n      {\n      \"code\": \"+213\",\n      \"name\": \"Algeria\"\n    },\n    {\n      \"code\": \"+1 684\",\n      \"name\": \"American Samoa\"\n    },\n    {\n      \"code\": \"+376\",\n      \"name\": \"Andorra\"\n    },\n    {\n      \"code\": \"+244\",\n      \"name\": \"Angola\"\n    },\n    {\n      \"code\": \"+1 264\",\n      \"name\": \"Anguilla\"\n    },\n    {\n      \"code\": \"+1 268\",\n      \"name\": \"Antigua and Barbuda\"\n    },\n    {\n      \"code\": \"+54\",\n      \"name\": \"Argentina\"\n    },\n    {\n      \"code\": \"+374\",\n      \"name\": \"Armenia\"\n    },\n    {\n      \"code\": \"+297\",\n      \"name\": \"Aruba\"\n    },\n    {\n      \"code\": \"+247\",\n      \"name\": \"Ascension\"\n    },\n    {\n      \"code\": \"+61\",\n      \"name\": \"Australia\"\n    },\n    {\n      \"code\": \"+672\",\n      \"name\": \"Australian External Territories\"\n    },\n    {\n      \"code\": \"+43\",\n      \"name\": \"Austria\"\n    },\n    {\n      \"code\": \"+994\",\n      \"name\": \"Azerbaijan\"\n    },\n    {\n      \"code\": \"+1 242\",\n      \"name\": \"Bahamas\"\n    },\n    {\n      \"code\": \"+973\",\n      \"name\": \"Bahrain\"\n    },\n    {\n      \"code\": \"+880\",\n      \"name\": \"Bangladesh\"\n    },\n    {\n      \"code\": \"+1 246\",\n      \"name\": \"Barbados\"\n    },\n    {\n      \"code\": \"+1 268\",\n      \"name\": \"Barbuda\"\n    },\n    {\n      \"code\": \"+375\",\n      \"name\": \"Belarus\"\n    },\n    {\n      \"code\": \"+32\",\n      \"name\": \"Belgium\"\n    },\n    {\n      \"code\": \"+501\",\n      \"name\": \"Belize\"\n    },\n    {\n      \"code\": \"+229\",\n      \"name\": \"Benin\"\n    },\n    {\n      \"code\": \"+1 441\",\n      \"name\": \"Bermuda\"\n    },\n    {\n      \"code\": \"+975\",\n      \"name\": \"Bhutan\"\n    },\n    {\n      \"code\": \"+591\",\n      \"name\": \"Bolivia\"\n    },\n    {\n      \"code\": \"+387\",\n      \"name\": \"Bosnia and Herzegovina\"\n    },\n    {\n      \"code\": \"+267\",\n      \"name\": \"Botswana\"\n    },\n    {\n      \"code\": \"+55\",\n      \"name\": \"Brazil\"\n    },\n    {\n      \"code\": \"+246\",\n      \"name\": \"British Indian Ocean Territory\"\n    },\n    {\n      \"code\": \"+1 284\",\n      \"name\": \"British Virgin Islands\"\n    },\n    {\n      \"code\": \"+673\",\n      \"name\": \"Brunei\"\n    },\n    {\n      \"code\": \"+359\",\n      \"name\": \"Bulgaria\"\n    },\n    {\n      \"code\": \"+226\",\n      \"name\": \"Burkina Faso\"\n    },\n    {\n      \"code\": \"+257\",\n      \"name\": \"Burundi\"\n    },\n    {\n      \"code\": \"+855\",\n      \"name\": \"Cambodia\"\n    },\n    {\n      \"code\": \"+237\",\n      \"name\": \"Cameroon\"\n    },\n    {\n      \"code\": \"+1\",\n      \"name\": \"Canada\"\n    },\n    {\n      \"code\": \"+238\",\n      \"name\": \"Cape Verde\"\n    },\n    {\n      \"code\": \"+ 345\",\n      \"name\": \"Cayman Islands\"\n    },\n    {\n      \"code\": \"+236\",\n      \"name\": \"Central African Republic\"\n    },\n    {\n      \"code\": \"+235\",\n      \"name\": \"Chad\"\n    },\n    {\n      \"code\": \"+56\",\n      \"name\": \"Chile\"\n    },\n    {\n      \"code\": \"+86\",\n      \"name\": \"China\"\n    },\n    {\n      \"code\": \"+61\",\n      \"name\": \"Christmas Island\"\n    },\n    {\n      \"code\": \"+61\",\n      \"name\": \"Cocos-Keeling Islands\"\n    },\n    {\n      \"code\": \"+57\",\n      \"name\": \"Colombia\"\n    },\n    {\n      \"code\": \"+269\",\n      \"name\": \"Comoros\"\n    },\n    {\n      \"code\": \"+242\",\n      \"name\": \"Congo\"\n    },\n    {\n      \"code\": \"+243\",\n      \"name\": \"Congo, Dem. Rep. of (Zaire)\"\n    },\n    {\n      \"code\": \"+682\",\n      \"name\": \"Cook Islands\"\n    },\n    {\n      \"code\": \"+506\",\n      \"name\": \"Costa Rica\"\n    },\n    {\n      \"code\": \"+385\",\n      \"name\": \"Croatia\"\n    },\n    {\n      \"code\": \"+53\",\n      \"name\": \"Cuba\"\n    },\n    {\n      \"code\": \"+599\",\n      \"name\": \"Curacao\"\n    },\n    {\n      \"code\": \"+537\",\n      \"name\": \"Cyprus\"\n    },\n    {\n      \"code\": \"+420\",\n      \"name\": \"Czech Republic\"\n    },\n    {\n      \"code\": \"+45\",\n      \"name\": \"Denmark\"\n    },\n    {\n      \"code\": \"+246\",\n      \"name\": \"Diego Garcia\"\n    },\n    {\n      \"code\": \"+253\",\n      \"name\": \"Djibouti\"\n    },\n    {\n      \"code\": \"+1 767\",\n      \"name\": \"Dominica\"\n    },\n    {\n      \"code\": \"+1 809\",\n      \"name\": \"Dominican Republic\"\n    },\n    {\n      \"code\": \"+670\",\n      \"name\": \"East Timor\"\n    },\n    {\n      \"code\": \"+56\",\n      \"name\": \"Easter Island\"\n    },\n    {\n      \"code\": \"+593\",\n      \"name\": \"Ecuador\"\n    },\n    {\n      \"code\": \"+20\",\n      \"name\": \"Egypt\"\n    },\n    {\n      \"code\": \"+503\",\n      \"name\": \"El Salvador\"\n    },\n    {\n      \"code\": \"+240\",\n      \"name\": \"Equatorial Guinea\"\n    },\n    {\n      \"code\": \"+291\",\n      \"name\": \"Eritrea\"\n    },\n    {\n      \"code\": \"+372\",\n      \"name\": \"Estonia\"\n    },\n    {\n      \"code\": \"+251\",\n      \"name\": \"Ethiopia\"\n    },\n    {\n      \"code\": \"+500\",\n      \"name\": \"Falkland Islands\"\n    },\n    {\n      \"code\": \"+298\",\n      \"name\": \"Faroe Islands\"\n    },\n    {\n      \"code\": \"+679\",\n      \"name\": \"Fiji\"\n    },\n    {\n      \"code\": \"+358\",\n      \"name\": \"Finland\"\n    },\n    {\n      \"code\": \"+33\",\n      \"name\": \"France\"\n    },\n    {\n      \"code\": \"+596\",\n      \"name\": \"French Antilles\"\n    },\n    {\n      \"code\": \"+594\",\n      \"name\": \"French Guiana\"\n    },\n    {\n      \"code\": \"+689\",\n      \"name\": \"French Polynesia\"\n    },\n    {\n      \"code\": \"+241\",\n      \"name\": \"Gabon\"\n    },\n    {\n      \"code\": \"+220\",\n      \"name\": \"Gambia\"\n    },\n    {\n      \"code\": \"+995\",\n      \"name\": \"Georgia\"\n    },\n    {\n      \"code\": \"+49\",\n      \"name\": \"Germany\"\n    },\n    {\n      \"code\": \"+233\",\n      \"name\": \"Ghana\"\n    },\n    {\n      \"code\": \"+350\",\n      \"name\": \"Gibraltar\"\n    },\n    {\n      \"code\": \"+30\",\n      \"name\": \"Greece\"\n    },\n    {\n      \"code\": \"+299\",\n      \"name\": \"Greenland\"\n    },\n    {\n      \"code\": \"+1 473\",\n      \"name\": \"Grenada\"\n    },\n    {\n      \"code\": \"+590\",\n      \"name\": \"Guadeloupe\"\n    },\n    {\n      \"code\": \"+1 671\",\n      \"name\": \"Guam\"\n    },\n    {\n      \"code\": \"+502\",\n      \"name\": \"Guatemala\"\n    },\n    {\n      \"code\": \"+224\",\n      \"name\": \"Guinea\"\n    },\n    {\n      \"code\": \"+245\",\n      \"name\": \"Guinea-Bissau\"\n    },\n    {\n      \"code\": \"+595\",\n      \"name\": \"Guyana\"\n    },\n    {\n      \"code\": \"+509\",\n      \"name\": \"Haiti\"\n    },\n    {\n      \"code\": \"+504\",\n      \"name\": \"Honduras\"\n    },\n    {\n      \"code\": \"+852\",\n      \"name\": \"Hong Kong SAR China\"\n    },\n    {\n      \"code\": \"+36\",\n      \"name\": \"Hungary\"\n    },\n    {\n      \"code\": \"+354\",\n      \"name\": \"Iceland\"\n    },\n    {\n      \"code\": \"+91\",\n      \"name\": \"India\"\n    },\n    {\n      \"code\": \"+62\",\n      \"name\": \"Indonesia\"\n    },\n    {\n      \"code\": \"+98\",\n      \"name\": \"Iran\"\n    },\n    {\n      \"code\": \"+964\",\n      \"name\": \"Iraq\"\n    },\n    {\n      \"code\": \"+353\",\n      \"name\": \"Ireland\"\n    },\n    {\n      \"code\": \"+972\",\n      \"name\": \"Israel\"\n    },\n    {\n      \"code\": \"+39\",\n      \"name\": \"Italy\"\n    },\n    {\n      \"code\": \"+225\",\n      \"name\": \"Ivory Coast\"\n    },\n    {\n      \"code\": \"+1 876\",\n      \"name\": \"Jamaica\"\n    },\n    {\n      \"code\": \"+81\",\n      \"name\": \"Japan\"\n    },\n    {\n      \"code\": \"+962\",\n      \"name\": \"Jordan\"\n    },\n    {\n      \"code\": \"+7 7\",\n      \"name\": \"Kazakhstan\"\n    },\n    {\n      \"code\": \"+254\",\n      \"name\": \"Kenya\"\n    },\n    {\n      \"code\": \"+686\",\n      \"name\": \"Kiribati\"\n    },\n    {\n      \"code\": \"+965\",\n      \"name\": \"Kuwait\"\n    },\n    {\n      \"code\": \"+996\",\n      \"name\": \"Kyrgyzstan\"\n    },\n    {\n      \"code\": \"+856\",\n      \"name\": \"Laos\"\n    },\n    {\n      \"code\": \"+371\",\n      \"name\": \"Latvia\"\n    },\n    {\n      \"code\": \"+961\",\n      \"name\": \"Lebanon\"\n    },\n    {\n      \"code\": \"+266\",\n      \"name\": \"Lesotho\"\n    },\n    {\n      \"code\": \"+231\",\n      \"name\": \"Liberia\"\n    },\n    {\n      \"code\": \"+218\",\n      \"name\": \"Libya\"\n    },\n    {\n      \"code\": \"+423\",\n      \"name\": \"Liechtenstein\"\n    },\n    {\n      \"code\": \"+370\",\n      \"name\": \"Lithuania\"\n    },\n    {\n      \"code\": \"+352\",\n      \"name\": \"Luxembourg\"\n    },\n    {\n      \"code\": \"+853\",\n      \"name\": \"Macau SAR China\"\n    },\n    {\n      \"code\": \"+389\",\n      \"name\": \"Macedonia\"\n    },\n    {\n      \"code\": \"+261\",\n      \"name\": \"Madagascar\"\n    },\n    {\n      \"code\": \"+265\",\n      \"name\": \"Malawi\"\n    },\n    {\n      \"code\": \"+60\",\n      \"name\": \"Malaysia\"\n    },\n    {\n      \"code\": \"+960\",\n      \"name\": \"Maldives\"\n    },\n    {\n      \"code\": \"+223\",\n      \"name\": \"Mali\"\n    },\n    {\n      \"code\": \"+356\",\n      \"name\": \"Malta\"\n    },\n    {\n      \"code\": \"+692\",\n      \"name\": \"Marshall Islands\"\n    },\n    {\n      \"code\": \"+596\",\n      \"name\": \"Martinique\"\n    },\n    {\n      \"code\": \"+222\",\n      \"name\": \"Mauritania\"\n    },\n    {\n      \"code\": \"+230\",\n      \"name\": \"Mauritius\"\n    },\n    {\n      \"code\": \"+262\",\n      \"name\": \"Mayotte\"\n    },\n    {\n      \"code\": \"+52\",\n      \"name\": \"Mexico\"\n    },\n    {\n      \"code\": \"+691\",\n      \"name\": \"Micronesia\"\n    },\n    {\n      \"code\": \"+1 808\",\n      \"name\": \"Midway Island\"\n    },\n    {\n      \"code\": \"+373\",\n      \"name\": \"Moldova\"\n    },\n    {\n      \"code\": \"+377\",\n      \"name\": \"Monaco\"\n    },\n    {\n      \"code\": \"+976\",\n      \"name\": \"Mongolia\"\n    },\n    {\n      \"code\": \"+382\",\n      \"name\": \"Montenegro\"\n    },\n    {\n      \"code\": \"+1664\",\n      \"name\": \"Montserrat\"\n    },\n    {\n      \"code\": \"+212\",\n      \"name\": \"Morocco\"\n    },\n    {\n      \"code\": \"+95\",\n      \"name\": \"Myanmar\"\n    },\n    {\n      \"code\": \"+264\",\n      \"name\": \"Namibia\"\n    },\n    {\n      \"code\": \"+674\",\n      \"name\": \"Nauru\"\n    },\n    {\n      \"code\": \"+977\",\n      \"name\": \"Nepal\"\n    },\n    {\n      \"code\": \"+31\",\n      \"name\": \"Netherlands\"\n    },\n    {\n      \"code\": \"+599\",\n      \"name\": \"Netherlands Antilles\"\n    },\n    {\n      \"code\": \"+1 869\",\n      \"name\": \"Nevis\"\n    },\n    {\n      \"code\": \"+687\",\n      \"name\": \"New Caledonia\"\n    },\n    {\n      \"code\": \"+64\",\n      \"name\": \"New Zealand\"\n    },\n    {\n      \"code\": \"+505\",\n      \"name\": \"Nicaragua\"\n    },\n    {\n      \"code\": \"+227\",\n      \"name\": \"Niger\"\n    },\n    {\n      \"code\": \"+234\",\n      \"name\": \"Nigeria\"\n    },\n    {\n      \"code\": \"+683\",\n      \"name\": \"Niue\"\n    },\n    {\n      \"code\": \"+672\",\n      \"name\": \"Norfolk Island\"\n    },\n    {\n      \"code\": \"+850\",\n      \"name\": \"North Korea\"\n    },\n    {\n      \"code\": \"+1 670\",\n      \"name\": \"Northern Mariana Islands\"\n    },\n    {\n      \"code\": \"+47\",\n      \"name\": \"Norway\"\n    },\n    {\n      \"code\": \"+968\",\n      \"name\": \"Oman\"\n    },\n    {\n      \"code\": \"+92\",\n      \"name\": \"Pakistan\"\n    },\n    {\n      \"code\": \"+680\",\n      \"name\": \"Palau\"\n    },\n    {\n      \"code\": \"+970\",\n      \"name\": \"Palestinian Territory\"\n    },\n    {\n      \"code\": \"+507\",\n      \"name\": \"Panama\"\n    },\n    {\n      \"code\": \"+675\",\n      \"name\": \"Papua New Guinea\"\n    },\n    {\n      \"code\": \"+595\",\n      \"name\": \"Paraguay\"\n    },\n    {\n      \"code\": \"+51\",\n      \"name\": \"Peru\"\n    },\n    {\n      \"code\": \"+63\",\n      \"name\": \"Philippines\"\n    },\n    {\n      \"code\": \"+48\",\n      \"name\": \"Poland\"\n    },\n    {\n      \"code\": \"+351\",\n      \"name\": \"Portugal\"\n    },\n    {\n      \"code\": \"+1 787\",\n      \"name\": \"Puerto Rico\"\n    },\n    {\n      \"code\": \"+974\",\n      \"name\": \"Qatar\"\n    },\n    {\n      \"code\": \"+262\",\n      \"name\": \"Reunion\"\n    },\n    {\n      \"code\": \"+40\",\n      \"name\": \"Romania\"\n    },\n    {\n      \"code\": \"+7\",\n      \"name\": \"Russia\"\n    },\n    {\n      \"code\": \"+250\",\n      \"name\": \"Rwanda\"\n    },\n    {\n      \"code\": \"+685\",\n      \"name\": \"Samoa\"\n    },\n    {\n      \"code\": \"+378\",\n      \"name\": \"San Marino\"\n    },\n    {\n      \"code\": \"+966\",\n      \"name\": \"Saudi Arabia\"\n    },\n    {\n      \"code\": \"+221\",\n      \"name\": \"Senegal\"\n    },\n    {\n      \"code\": \"+381\",\n      \"name\": \"Serbia\"\n    },\n    {\n      \"code\": \"+248\",\n      \"name\": \"Seychelles\"\n    },\n    {\n      \"code\": \"+232\",\n      \"name\": \"Sierra Leone\"\n    },\n    {\n      \"code\": \"+65\",\n      \"name\": \"Singapore\"\n    },\n    {\n      \"code\": \"+421\",\n      \"name\": \"Slovakia\"\n    },\n    {\n      \"code\": \"+386\",\n      \"name\": \"Slovenia\"\n    },\n    {\n      \"code\": \"+677\",\n      \"name\": \"Solomon Islands\"\n    },\n    {\n      \"code\": \"+27\",\n      \"name\": \"South Africa\"\n    },\n    {\n      \"code\": \"+500\",\n      \"name\": \"South Georgia and the South Sandwich Islands\"\n    },\n    {\n      \"code\": \"+82\",\n      \"name\": \"South Korea\"\n    },\n    {\n      \"code\": \"+34\",\n      \"name\": \"Spain\"\n    },\n    {\n      \"code\": \"+94\",\n      \"name\": \"Sri Lanka\"\n    },\n    {\n      \"code\": \"+249\",\n      \"name\": \"Sudan\"\n    },\n    {\n      \"code\": \"+597\",\n      \"name\": \"Suriname\"\n    },\n    {\n      \"code\": \"+268\",\n      \"name\": \"Swaziland\"\n    },\n    {\n      \"code\": \"+46\",\n      \"name\": \"Sweden\"\n    },\n    {\n      \"code\": \"+41\",\n      \"name\": \"Switzerland\"\n    },\n    {\n      \"code\": \"+963\",\n      \"name\": \"Syria\"\n    },\n    {\n      \"code\": \"+886\",\n      \"name\": \"Taiwan\"\n    },\n    {\n      \"code\": \"+992\",\n      \"name\": \"Tajikistan\"\n    },\n    {\n      \"code\": \"+255\",\n      \"name\": \"Tanzania\"\n    },\n    {\n      \"code\": \"+66\",\n      \"name\": \"Thailand\"\n    },\n    {\n      \"code\": \"+670\",\n      \"name\": \"Timor Leste\"\n    },\n    {\n      \"code\": \"+228\",\n      \"name\": \"Togo\"\n    },\n    {\n      \"code\": \"+690\",\n      \"name\": \"Tokelau\"\n    },\n    {\n      \"code\": \"+676\",\n      \"name\": \"Tonga\"\n    },\n    {\n      \"code\": \"+1 868\",\n      \"name\": \"Trinidad and Tobago\"\n    },\n    {\n      \"code\": \"+216\",\n      \"name\": \"Tunisia\"\n    },\n    {\n      \"code\": \"+90\",\n      \"name\": \"Turkey\"\n    },\n    {\n      \"code\": \"+993\",\n      \"name\": \"Turkmenistan\"\n    },\n    {\n      \"code\": \"+1 649\",\n      \"name\": \"Turks and Caicos Islands\"\n    },\n    {\n      \"code\": \"+688\",\n      \"name\": \"Tuvalu\"\n    },\n    {\n      \"code\": \"+1 340\",\n      \"name\": \"U.S. Virgin Islands\"\n    },\n    {\n      \"code\": \"+256\",\n      \"name\": \"Uganda\"\n    },\n    {\n      \"code\": \"+380\",\n      \"name\": \"Ukraine\"\n    },\n    {\n      \"code\": \"+971\",\n      \"name\": \"United Arab Emirates\"\n    },\n    {\n      \"code\": \"+44\",\n      \"name\": \"United Kingdom\"\n    },\n    {\n      \"code\": \"+1\",\n      \"name\": \"United States\"\n    },\n    {\n      \"code\": \"+598\",\n      \"name\": \"Uruguay\"\n    },\n    {\n      \"code\": \"+998\",\n      \"name\": \"Uzbekistan\"\n    },\n    {\n      \"code\": \"+678\",\n      \"name\": \"Vanuatu\"\n    },\n    {\n      \"code\": \"+58\",\n      \"name\": \"Venezuela\"\n    },\n    {\n      \"code\": \"+84\",\n      \"name\": \"Vietnam\"\n    },\n    {\n      \"code\": \"+1 808\",\n      \"name\": \"Wake Island\"\n    },\n    {\n      \"code\": \"+681\",\n      \"name\": \"Wallis and Futuna\"\n    },\n    {\n      \"code\": \"+967\",\n      \"name\": \"Yemen\"\n    },\n    {\n      \"code\": \"+260\",\n      \"name\": \"Zambia\"\n    },\n    {\n      \"code\": \"+255\",\n      \"name\": \"Zanzibar\"\n    },\n    {\n      \"code\": \"+263\",\n      \"name\": \"Zimbabwe\"\n    }\n  ]\n}', '2022-06-06 06:54:27', '2022-06-06 06:48:21'),
(26, 'country_code', '+91', '2022-06-06 07:52:41', '2022-06-06 07:52:26'),
(27, 'api_key_settings', '{\"google_map_api\":\"AIzaSyDiwZs4s9F2M9epXrSvJP9GtRhcjdXNieE\",\"firebase_server_key\":\"AAAAK--BM5A:APA91bFs4VCTsACVFbIoaemWM7F5qL560KOv7m-DsiT53I5P_ad5dfY0Qf4k5jnKaZFiz1mXlNl4Yg2X1z3clJ9l-XBmQODmDrkU6YYZ3s1FI-etoWhQJkux0tgMyVhVTRP39eMe6Uql\"}', '2024-01-03 07:17:47', NULL),
(29, 'range_units', 'kilometers', '2022-08-10 10:37:37', NULL),
(30, 'contact_us', '{\"contact_us\":\"<p>Enter Contact Us.<\\/p>\"}', '2022-11-05 07:53:48', NULL),
(31, 'system_tax_settings', '{\"tax_status\":\"on\",\"tax_name\":\"GST\",\"tax\":\"10\"}', '2022-11-26 06:31:11', NULL),
(32, 'country_codes', '{\r\n  \n \"countries\":[\n \r\n  {\n \"code\": \"+93\",   \n  \"name\": \"Afghanistan\" \n  },\r\n  {\n \"code\": \"+358\",  \n  \"name\": \"Åland Islands\"\n  },\r\n  {\n \"code\": \"+355\",  \n  \"name\": \"Albania\"\n  },\r\n  {\n \"code\": \"+213\",  \n  \"name\": \"Algeria\"\n  },\r\n  {\n \"code\": \"+1 684\",\n  \"name\": \"American Samoa\"\n  },\r\n  {\n \"code\": \"+376\",  \n  \"name\": \"Andorra\"\n  },\r\n  {\n \"code\": \"+244\",  \n  \"name\": \"Angola\"\n  },\r\n  {\n \"code\": \"+1 264\",\n  \"name\": \"Anguilla\"\n  },\r\n  {\n \"code\": \"+672\",  \n  \"name\": \"Antarctica\"\n  },\r\n  {\n \"code\": \"+1 268\",\n  \"name\": \"Antigua and Barbuda\"\n  },\r\n  {\n \"code\": \"+54\",   \n  \"name\": \"Argentina\"\n  },\r\n  {\n \"code\": \"+374\",  \n  \"name\": \"Armenia\"\n  },\r\n  {\n \"code\": \"+297\",  \n  \"name\": \"Aruba\"\n  },\r\n  {\n \"code\": \"+61\",   \n  \"name\": \"Australia\"\n  },\r\n  {\n \"code\": \"+43\",   \n  \"name\": \"Austria\"\n  },\r\n  {\n \"code\": \"+994\",  \n  \"name\": \"Azerbaijan\"\n  },\r\n  {\n \"code\": \"+1 242\",\n  \"name\": \"Bahamas\"\n  },\r\n  {\n \"code\": \"+973\",  \n  \"name\": \"Bahrain\"\n  },\r\n  {\n \"code\": \"+880\",  \n  \"name\": \"Bangladesh\"\n  },\r\n  {\n \"code\": \"+1 246\",\n  \"name\": \"Barbados\"\n  },\r\n  {\n \"code\": \"+375\",  \n  \"name\": \"Belarus\"\n  },\r\n  {\n \"code\": \"+32\",   \n  \"name\": \"Belgium\"\n  },\r\n  {\n \"code\": \"+501\",  \n  \"name\": \"Belize\"\n  },\r\n  {\n \"code\": \"+229\",  \n  \"name\": \"Benin\"\n  },\r\n  {\n \"code\": \"+1 441\",\n  \"name\": \"Bermuda\"\n  },\r\n  {\n \"code\": \"+975\",  \n  \"name\": \"Bhutan\"\n  },\r\n  {\n \"code\": \"+591\",  \n  \"name\": \"Bolivia (Plurinational State of)\"\n  },\r\n  {\n \"code\": \"+599\",  \n  \"name\": \"Bonaire, Sint Eustatius and Saba\"\n  },\r\n  {\n \"code\": \"+387\",  \n  \"name\": \"Bosnia and Herzegovina\"\n  },\r\n  {\n \"code\": \"+267\",  \n  \"name\": \"Botswana\"\n  },\r\n  {\n \"code\": \"+47\",   \n  \"name\": \"Bouvet Island\"\n  },\r\n  {\n \"code\": \"+55\",   \n  \"name\": \"Brazil\"\n  },\r\n  {\n \"code\": \"+246\",  \n  \"name\": \"British Indian Ocean Territory\"\n  },\r\n  {\n \"code\": \"+673\",  \n  \"name\": \"Brunei Darussalam\"\n  },\r\n  {\n \"code\": \"+359\",  \n  \"name\": \"Bulgaria\"\n  },\r\n  {\n \"code\": \"+226\",  \n  \"name\": \"Burkina Faso\"\n  },\r\n  {\n \"code\": \"+257\",  \n  \"name\": \"Burundi\"\n  },\r\n  {\n \"code\": \"+238\",  \n  \"name\": \"Cabo Verde\"\n  },\r\n  {\n \"code\": \"+855\",  \n  \"name\": \"Cambodia\"\n  },\r\n  {\n \"code\": \"+237\",  \n  \"name\": \"Cameroon\"\n  },\r\n  {\n \"code\": \"+1\",    \n  \"name\": \"Canada\"\n  },\r\n  {\n \"code\": \"+1 345\",\n  \"name\": \"Cayman Islands\"\n  },\r\n  {\n \"code\": \"+236\",  \n  \"name\": \"Central African Republic\"\n  },\r\n  {\n \"code\": \"+235\",  \n  \"name\": \"Chad\"\n  },\r\n  {\n \"code\": \"+56\",   \n  \"name\": \"Chile\"\n  },\r\n  {\n \"code\": \"+86\",   \n  \"name\": \"China\"\n  },\r\n  {\n \"code\": \"+61\",   \n  \"name\": \"Christmas Island\"\n  },\r\n  {\n \"code\": \"+61\",   \n  \"name\": \"Cocos (Keeling) Islands\"\n  },\r\n  {\n \"code\": \"+57\",   \n  \"name\": \"Colombia\"\n  },\r\n  {\n \"code\": \"+269\",  \n  \"name\": \"Comoros\"\n  },\r\n  {\n \"code\": \"+242\",  \n  \"name\": \"Congo\"\n  },\r\n  {\n \"code\": \"+243\",  \n  \"name\": \"Congo, Democratic Republic of the\"\n  },\r\n  {\n \"code\": \"+682\",  \n  \"name\": \"Cook Islands\"\n  },\r\n  {\n \"code\": \"+506\",  \n  \"name\": \"Costa Rica\"\n  },\r\n  {\n \"code\": \"+225\",  \n  \"name\": \"Côte d\'Ivoire\"\n  },\r\n  {\n \"code\": \"+385\",  \n  \"name\": \"Croatia\"\n  },\r\n  {\n \"code\": \"+53\",   \n  \"name\": \"Cuba\"\n  },\r\n  {\n \"code\": \"+599\",  \n  \"name\": \"Curaçao\"\n  },\r\n  {\n \"code\": \"+357\",  \n  \"name\": \"Cyprus\"\n  },\r\n  {\n \"code\": \"+420\",  \n  \"name\": \"Czechia\"\n  },\r\n  {\n \"code\": \"+45\",   \n  \"name\": \"Denmark\"\n  },\r\n  {\n \"code\": \"+253\",  \n  \"name\": \"Djibouti\"\n  },\r\n  {\n \"code\": \"+1 767\",\n  \"name\": \"Dominica\"\n  },\r\n  {\n \"code\": \"+1 809\",\n  \"name\": \"Dominican Republic\"\n  },\r\n  {\n \"code\": \"+593\",  \n  \"name\": \"Ecuador\"\n  },\r\n  {\n \"code\": \"+20\",   \n  \"name\": \"Egypt\"\n  },\r\n  {\n \"code\": \"+503\",  \n  \"name\": \"El Salvador\"\n  },\r\n  {\n \"code\": \"+240\",  \n  \"name\": \"Equatorial Guinea\"\n  },\r\n  {\n \"code\": \"+291\",  \n  \"name\": \"Eritrea\"\n  },\r\n  {\n \"code\": \"+372\",  \n  \"name\": \"Estonia\"\n  },\r\n  {\n \"code\": \"+268\",  \n  \"name\": \"Eswatini\"\n  },\r\n  {\n \"code\": \"+251\",  \n  \"name\": \"Ethiopia\"\n  },\r\n  {\n \"code\": \"+500\",  \n  \"name\": \"Falkland Islands (Malvinas)\"\n  },\r\n  {\n \"code\": \"+298\",  \n  \"name\": \"Faroe Islands\"\n  },\r\n  {\n \"code\": \"+679\",  \n  \"name\": \"Fiji\"\n  },\r\n  {\n \"code\": \"+358\",  \n  \"name\": \"Finland\"\n  },\r\n  {\n \"code\": \"+33\",   \n  \"name\": \"France\"\n  },\r\n  {\n \"code\": \"+594\",  \n  \"name\": \"French Guiana\"\n  },\r\n  {\n \"code\": \"+689\",  \n  \"name\": \"French Polynesia\"\n  },\r\n  {\n \"code\": \"+262\",  \n  \"name\": \"French Southern Territories\"\n  },\r\n  {\n \"code\": \"+241\",  \n  \"name\": \"Gabon\"\n  },\r\n  {\n \"code\": \"+220\",  \n  \"name\": \"Gambia\"\n  },\r\n  {\n \"code\": \"+995\",  \n  \"name\": \"Georgia\"\n  },\r\n  {\n \"code\": \"+49\",   \n  \"name\": \"Germany\"\n  },\r\n  {\n \"code\": \"+233\",  \n  \"name\": \"Ghana\"\n  },\r\n  {\n \"code\": \"+350\",  \n  \"name\": \"Gibraltar\"\n  },\r\n  {\n \"code\": \"+30\",   \n  \"name\": \"Greece\"\n  },\r\n  {\n \"code\": \"+299\",  \n  \"name\": \"Greenland\"\n  },\r\n  {\n \"code\": \"+1 473\",\n  \"name\": \"Grenada\"\n  },\r\n  {\n \"code\": \"+590\",  \n  \"name\": \"Guadeloupe\"\n  },\r\n  {\n \"code\": \"+1 671\",\n  \"name\": \"Guam\"\n  },\r\n  {\n \"code\": \"+502\",  \n  \"name\": \"Guatemala\"\n  },\r\n  {\n \"code\": \"+44\",   \n  \"name\": \"Guernsey\"\n  },\r\n  {\n \"code\": \"+224\",  \n  \"name\": \"Guinea\"\n  },\r\n  {\n \"code\": \"+245\",  \n  \"name\": \"Guinea-Bissau\"\n  },\r\n  {\n \"code\": \"+592\",  \n  \"name\": \"Guyana\"\n  },\r\n  {\n \"code\": \"+509\",  \n  \"name\": \"Haiti\"\n  },\r\n  {\n \"code\": \"+672\",  \n  \"name\": \"Heard Island and McDonald Islands\"\n  },\r\n  {\n \"code\": \"+379\",  \n  \"name\": \"Holy See\"\n  },\r\n  {\n \"code\": \"+504\",  \n  \"name\": \"Honduras\"\n  },\r\n  {\n \"code\": \"+852\",  \n  \"name\": \"Hong Kong\"\n  },\r\n  {\n \"code\": \"+36\",   \n  \"name\": \"Hungary\"\n  },\r\n  {\n \"code\": \"+354\",  \n  \"name\": \"Iceland\"\n  },\r\n  {\n \"code\": \"+91\",   \n  \"name\": \"India\"\n  },\r\n  {\n \"code\": \"+62\",   \n  \"name\": \"Indonesia\"\n  },\r\n  {\n \"code\": \"+98\",   \n  \"name\": \"Iran (Islamic Republic of)\"\n  },\r\n  {\n \"code\": \"+964\",  \n  \"name\": \"Iraq\"\n  },\r\n  {\n \"code\": \"+353\",  \n  \"name\": \"Ireland\"\n  },\r\n  {\n \"code\": \"+44\",   \n  \"name\": \"Isle of Man\"\n  },\r\n  {\n \"code\": \"+972\",  \n  \"name\": \"Israel\"\n  },\r\n  {\n \"code\": \"+39\",   \n  \"name\": \"Italy\"\n  },\r\n  {\n \"code\": \"+1\",    \n  \"name\": \"Jamaica\"\n  },\r\n  {\n \"code\": \"+81\",   \n  \"name\": \"Japan\"\n  },\r\n  {\n \"code\": \"+44\",   \n  \"name\": \"Jersey\"\n  },\r\n  {\n \"code\": \"+962\",  \n  \"name\": \"Jordan\"\n  },\r\n  {\n \"code\": \"+7 840\",\n  \"name\": \"Abkhazia\" }\r\n  \n  ]\n}', '2022-06-06 06:54:27', '2022-06-06 06:48:21'),
(33, 'web_settings', '{\"social_media\":[],\"web_title\":\"\",\"web_tagline\":\"\",\"short_description\":\"\",\"playstore_url\":\"\",\"app_section_status\":0,\"applestore_url\":\"\",\"web_logo\":\"1704190468_6ac7d1e3fe59e58e9862.jpeg\",\"web_favicon\":\"1704190468_990fbeed9ba068a90c22.jpeg\",\"web_half_logo\":\"1704190468_4fb373247a8c0d63977f.jpeg\"}', '2024-01-02 10:14:28', NULL),
(34, 'firebase_settings', '{\"apiKey\":\"AIzaSyDiwZs4s9F2M9epXrSvJP9GtRhcjdXNieE\",\"authDomain\":\"homelymedic-f98ec.firebaseapp.com\",\"projectId\":\"homelymedic-f98ec\",\"storageBucket\":\"homelymedic-f98ec.appspot.com\",\"messagingSenderId\":\"188701815696\",\"appId\":\"1:188701815696:web:72b7301106553eaa26b915\",\"measurementId\":\"G-2JPBR89Q98\",\"vapidKey\":\"0\"}', '2023-12-18 03:30:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settlement_history`
--

CREATE TABLE `settlement_history` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `date` date NOT NULL,
  `amount` text NOT NULL,
  `status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` int(11) NOT NULL,
  `type` varchar(128) NOT NULL,
  `type_id` int(11) NOT NULL,
  `image` varchar(128) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 - deactive \r\n1 - active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `type`, `type_id`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'default', 0, 'pexels-polina-tankilevitch-3875197.jpg', 0, '2024-01-03 07:39:55', '2024-02-03 12:29:15'),
(2, 'default', 0, '1704267631.jpg', 0, '2024-01-03 07:40:31', '2024-02-03 12:29:05'),
(3, 'provider', 1, '1705575620.jpeg', 1, '2024-01-18 11:00:20', '2024-01-18 16:30:20');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `duration` text NOT NULL,
  `price` double NOT NULL,
  `discount_price` double NOT NULL,
  `publish` text NOT NULL,
  `order_type` text NOT NULL,
  `max_order_limit` text DEFAULT NULL,
  `service_type` text NOT NULL,
  `max_service_limit` text DEFAULT NULL,
  `tax_type` text NOT NULL,
  `tax_id` text DEFAULT NULL,
  `is_commision` text NOT NULL,
  `commission_threshold` text DEFAULT NULL,
  `commission_percentage` text DEFAULT NULL,
  `status` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `name`, `description`, `duration`, `price`, `discount_price`, `publish`, `order_type`, `max_order_limit`, `service_type`, `max_service_limit`, `tax_type`, `tax_id`, `is_commision`, `commission_threshold`, `commission_percentage`, `status`, `created_at`, `updated_at`) VALUES
(6, 'Subscribe now', 'for verification', 'unlimited', 100, 90, '1', 'unlimited', '0', 'limited', '0', 'included', '', 'yes', '20', '20', '1', '2024-01-03 08:27:47', '2024-01-03 08:27:47');

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

CREATE TABLE `taxes` (
  `id` int(11) NOT NULL,
  `title` varchar(1024) NOT NULL,
  `percentage` double NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0- deactive | 1 - active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `taxes`
--

INSERT INTO `taxes` (`id`, `title`, `percentage`, `status`, `created_at`, `updated_at`) VALUES
(1, 'GST 18%', 18, 1, '2024-01-03 07:05:27', NULL),
(2, 'GST 5%', 5, 1, '2024-01-03 07:05:41', NULL),
(3, 'GST 12%', 12, 1, '2024-01-03 07:05:51', NULL),
(4, 'Gst', 5, 0, '2024-03-07 06:03:45', '2024-03-07 11:34:10');

-- --------------------------------------------------------

--
-- Table structure for table `test`
--

CREATE TABLE `test` (
  `blog_id` int(5) UNSIGNED NOT NULL,
  `blog_title` varchar(100) NOT NULL,
  `blog_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE `themes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `is_default` tinyint(4) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id`, `name`, `slug`, `image`, `is_default`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Retro', 'retro', 'retro.png', 1, 1, '2021-12-03 13:33:03', '2022-08-09 10:20:22');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `ticket_type_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `email` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `status` tinyint(4) DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `id` int(11) NOT NULL,
  `user_type` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `message` longtext CHARACTER SET utf8mb4 DEFAULT NULL,
  `attachments` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_types`
--

CREATE TABLE `ticket_types` (
  `id` int(11) NOT NULL,
  `title` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `transaction_type` varchar(16) NOT NULL,
  `user_id` int(11) NOT NULL,
  `partner_id` int(11) NOT NULL,
  `order_id` varchar(128) DEFAULT NULL,
  `type` varchar(64) DEFAULT NULL,
  `txn_id` varchar(256) DEFAULT NULL,
  `amount` double NOT NULL,
  `status` varchar(12) DEFAULT NULL,
  `currency_code` varchar(5) DEFAULT NULL,
  `message` varchar(128) NOT NULL,
  `transaction_date` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference` text DEFAULT NULL,
  `subscription_id` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `updates`
--

CREATE TABLE `updates` (
  `id` int(20) NOT NULL,
  `version` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `updates`
--

INSERT INTO `updates` (`id`, `version`, `created_at`, `updated_at`) VALUES
(1, '1.0', '2022-11-14 04:55:25', '2022-11-14 04:55:25'),
(2, '1.1.0', '2022-12-01 13:08:33', '2022-12-01 13:08:33'),
(3, '1.2.0', '2022-12-06 13:08:33', '2022-12-06 13:08:33'),
(4, '1.3.0', '2022-12-06 13:08:33', '2022-12-06 13:08:33'),
(5, '1.4.0', '2022-12-06 13:08:33', '2022-12-06 13:08:33'),
(6, '1.5.0', '2022-12-06 13:08:33', '2022-12-06 13:08:33'),
(7, '1.6.0', '2022-12-06 13:08:33', '2022-12-06 13:08:33'),
(8, '1.7.0', '2022-12-06 13:08:33', '2022-12-06 13:08:33'),
(9, '1.8.0', '2022-12-06 13:08:33', '2022-12-06 13:08:33'),
(10, '1.9.0', '2022-12-06 13:08:33', '2022-12-06 13:08:33'),
(11, '2.0.0', '2022-12-06 13:08:33', '2022-12-06 13:08:33'),
(12, '2.1.0', '2022-12-06 13:08:33', '2022-12-06 13:08:33'),
(13, '2.2.0', '2024-02-17 09:31:42', '2024-02-17 09:31:42'),
(14, '2.2.1', '2024-02-17 09:32:11', '2024-02-17 09:32:11');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(250) DEFAULT NULL,
  `balance` double NOT NULL DEFAULT 0,
  `activation_selector` varchar(255) DEFAULT NULL,
  `activation_code` varchar(255) DEFAULT NULL,
  `forgotten_password_selector` varchar(255) DEFAULT NULL,
  `forgotten_password_code` varchar(255) DEFAULT NULL,
  `forgotten_password_time` int(11) UNSIGNED DEFAULT NULL,
  `remember_selector` varchar(255) DEFAULT NULL,
  `remember_code` varchar(255) DEFAULT NULL,
  `created_on` int(11) UNSIGNED NOT NULL,
  `last_login` int(11) UNSIGNED DEFAULT NULL,
  `active` tinyint(1) UNSIGNED DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `country_code` text NOT NULL,
  `fcm_id` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `api_key` text NOT NULL,
  `friends_code` varchar(255) DEFAULT NULL,
  `referral_code` varchar(255) DEFAULT NULL,
  `city_id` int(50) DEFAULT 0,
  `city` varchar(252) DEFAULT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payable_commision` text DEFAULT NULL,
  `strip_id` text DEFAULT NULL,
  `web_fcm_id` text DEFAULT NULL,
  `platform` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `balance`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`, `country_code`, `fcm_id`, `image`, `api_key`, `friends_code`, `referral_code`, `city_id`, `city`, `latitude`, `longitude`, `created_at`, `updated_at`, `payable_commision`, `strip_id`, `web_fcm_id`, `platform`) VALUES
(1, '127.0.0.1', 'divecho', '$2y$12$97XX65/aScY60aDW4wHMM.Z6Cf3Mvw6lY1w7a/.Rtpkuilw13YO8.', 'rajasthantech.info@gmail.com', 2000200, NULL, '', NULL, NULL, NULL, NULL, NULL, 1268889823, 1709972200, 1, 'Admin', 'istrator', 'ADMIN', '7014080004', '', 'eQHx3ANrRLmbdIO7kK8nek:APA91bHuI19SM6qptCWJ3plidwFOhVg2Rg77k4oTuMQ0Xmd521vDBBZKzFX9yLKhe5yEI1SFVvT53Dt8XeIP0j3vxjUtJBj3D7OkgpoSJTHSdznekuew8CL_Ye-MBAjU3ke4lZtgVyI9', 'WhatsApp Image 2023-12-20 at 1.26.37 PM.jpeg', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjkwMDIwMjEsImlzcyI6ImVkZW1hbmQiLCJleHAiOjE3MDA1MzgwMjEsInN1YiI6ImVkZW1hbmRfYXV0aGVudGljYXRpb24iLCJ1c2VyX2lkIjoiMSJ9.bxPMyvDEFrkA1yq2lHhUhACwidQTsoR86te8gofHspM', '45dsrwr', 'MY_CODE', 10, '', '23.2330718', '69.6442306', '2022-05-24 04:44:29', '2022-05-24 04:44:29', NULL, NULL, NULL, NULL),
(2, '', 'Provider', '$2y$10$3mRV/f90q5E1mAXF6nrGTO5PkJA8c9tGW7MqHxpEkh3umGD0UGUcm', 'providerone@divecho.com', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1709792448, 1, NULL, NULL, NULL, '1234567899', '+91', 'cszvmWlaSzq5IMoa3Op9Up:APA91bEpFPaCxSIykCXG3l0CDQB3JqoBJfmonJGReQmP5yksZPI8YFZjgThOvYcCYYNvKHlGfKXxCKlkEWLCoOU59a-rNofqxUAWcsmGujFlLwLYa0k0-kYccLJQqVY8JkGkoHugwTt0', 'public/backend/assets/profile/pexels-anna-shvets-4167542.jpg', '', NULL, NULL, 0, 'jaipur', '26.9476566', '75.7318207', '2024-01-03 07:00:29', '2024-01-03 07:00:29', NULL, NULL, NULL, 'android'),
(4, '', 'naren', '', 'naren@divecho.com', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, NULL, NULL, '8947047070', '+91', 'c68LS6CkSHOar9N7aPdCVD:APA91bGOYPj_MjHtuRLHk81Fmfskp0WtdipR9trhSkWUlOF3urYLiIOuLV9ogj09Vxf5YOeYQMi5ENH8ieQoz862kbgHgadVCMZ2FhfmBY2QcTWmFuBgVKpuwK8-JCLuoRYvkC4C8Ry-', NULL, '', NULL, NULL, 0, NULL, '26.9545943', '75.7455944', '2024-02-26 08:12:11', '2024-02-26 08:12:11', NULL, NULL, NULL, 'android');

-- --------------------------------------------------------

--
-- Table structure for table `users_groups`
--

CREATE TABLE `users_groups` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users_groups`
--

INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES
(1, 1, 1),
(346, 2, 3),
(348, 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `users_tokens`
--

CREATE TABLE `users_tokens` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_tokens`
--

INSERT INTO `users_tokens` (`id`, `user_id`, `token`) VALUES
(1, 3, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MDQyNjkwNjQsImlzcyI6ImVkZW1hbmQiLCJleHAiOjE3MzU4MDUwNjQsInN1YiI6ImVkZW1hbmRfYXV0aGVudGljYXRpb24iLCJ1c2VyX2lkIjoiMyJ9.PPnQKsp27Xqa5LyWzV_-2ZB_pMqwKMXxYtKhF3OSuc8'),
(2, 3, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MDY5NDA3MjAsImlzcyI6ImVkZW1hbmQiLCJleHAiOjE3Mzg0NzY3MjAsInN1YiI6ImVkZW1hbmRfYXV0aGVudGljYXRpb24iLCJ1c2VyX2lkIjoiMyJ9.tzh6tKyPsm1M_DKKtZfPLcS5yNy3tWyxSNbcLsuQV5g'),
(3, 3, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MDY5NDA3MjIsImlzcyI6ImVkZW1hbmQiLCJleHAiOjE3Mzg0NzY3MjIsInN1YiI6ImVkZW1hbmRfYXV0aGVudGljYXRpb24iLCJ1c2VyX2lkIjoiMyJ9.P188Z2JtKPv2tYYvZLlrJaFkCGg89VFqVQ_hgF33KFI'),
(4, 3, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MDY5NTI1MDQsImlzcyI6ImVkZW1hbmQiLCJleHAiOjE3Mzg0ODg1MDQsInN1YiI6ImVkZW1hbmRfYXV0aGVudGljYXRpb24iLCJ1c2VyX2lkIjoiMyJ9.rL3y_l_KRgVriyzIhpJdBrP5mJp9IFF3R-kl-swm9EE'),
(5, 3, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MDY5NTI1MDUsImlzcyI6ImVkZW1hbmQiLCJleHAiOjE3Mzg0ODg1MDUsInN1YiI6ImVkZW1hbmRfYXV0aGVudGljYXRpb24iLCJ1c2VyX2lkIjoiMyJ9.mHT2zjH6XENwsrB22_bD1ZBzYvmmfFTlivdM4ANch_U'),
(6, 3, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MDY5NTM5MzYsImlzcyI6ImVkZW1hbmQiLCJleHAiOjE3Mzg0ODk5MzYsInN1YiI6ImVkZW1hbmRfYXV0aGVudGljYXRpb24iLCJ1c2VyX2lkIjoiMyJ9.Cu__brkCQav_0MdBAYkTOsYHyWVOPW5_vFw7tojaNZU'),
(7, 3, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MDY5NTM5MzYsImlzcyI6ImVkZW1hbmQiLCJleHAiOjE3Mzg0ODk5MzYsInN1YiI6ImVkZW1hbmRfYXV0aGVudGljYXRpb24iLCJ1c2VyX2lkIjoiMyJ9.Cu__brkCQav_0MdBAYkTOsYHyWVOPW5_vFw7tojaNZU'),
(8, 2, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MDgzNTIwNTEsImlzcyI6ImVkZW1hbmQiLCJleHAiOjE3Mzk4ODgwNTEsInN1YiI6ImVkZW1hbmRfYXV0aGVudGljYXRpb24iLCJ1c2VyX2lkIjoiMiJ9.q2qZ6Ga_13-HJduBEJX3H21R6xre3YOtQ1iJcBma9Xg'),
(9, 4, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MDg5MzUxMzEsImlzcyI6ImVkZW1hbmQiLCJleHAiOjE3NDA0NzExMzEsInN1YiI6ImVkZW1hbmRfYXV0aGVudGljYXRpb24iLCJ1c2VyX2lkIjoiNCJ9.lS3Du_uUs4k6Q2IhpcC-X_9v0Dfaof0gwbCuneXBE1Y'),
(10, 2, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MDk3OTIwNDksImlzcyI6ImVkZW1hbmQiLCJleHAiOjE3NDEzMjgwNDksInN1YiI6ImVkZW1hbmRfYXV0aGVudGljYXRpb24iLCJ1c2VyX2lkIjoiMiJ9.J7E1JQ046YYAjo3J2ln7Uk37VlY3ATCuxbI8SDZFO2k'),
(11, 2, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MDk3OTI0NDgsImlzcyI6ImVkZW1hbmQiLCJleHAiOjE3NDEzMjg0NDgsInN1YiI6ImVkZW1hbmRfYXV0aGVudGljYXRpb24iLCJ1c2VyX2lkIjoiMiJ9.WxcuX1lkC-97vHF7Buybo3FT-UvE71rZln8SjDkSieU');

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role` varchar(512) NOT NULL COMMENT '1. super admin\r\n2. admin\r\n3. client',
  `permissions` mediumtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_permissions`
--

INSERT INTO `user_permissions` (`id`, `user_id`, `role`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 1, '1', NULL, '2022-07-21 04:18:12', '2022-08-11 07:36:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partner_id` (`partner_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cash_collection`
--
ALTER TABLE `cash_collection`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `country_codes`
--
ALTER TABLE `country_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delete_general_notification`
--
ALTER TABLE `delete_general_notification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `city_id` (`city_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `address_id` (`address_id`);

--
-- Indexes for table `order_services`
--
ALTER TABLE `order_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`,`service_id`);

--
-- Indexes for table `partner_details`
--
ALTER TABLE `partner_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`partner_id`),
  ADD KEY `address_id` (`address_id`(768));

--
-- Indexes for table `partner_subscriptions`
--
ALTER TABLE `partner_subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `partner_timings`
--
ALTER TABLE `partner_timings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partner_id` (`partner_id`);

--
-- Indexes for table `payment_request`
--
ALTER TABLE `payment_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`partner_id`),
  ADD KEY `partner_id` (`partner_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`category_id`,`tax_id`),
  ADD KEY `tax_id` (`tax_id`),
  ADD KEY `id` (`id`),
  ADD KEY `id_2` (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `tax_id_2` (`tax_id`);

--
-- Indexes for table `services_ratings`
--
ALTER TABLE `services_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`service_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settlement_history`
--
ALTER TABLE `settlement_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`blog_id`);

--
-- Indexes for table `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ticket_types`
--
ALTER TABLE `ticket_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `updates`
--
ALTER TABLE `updates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_activation_selector` (`activation_selector`),
  ADD UNIQUE KEY `uc_forgotten_password_selector` (`forgotten_password_selector`),
  ADD UNIQUE KEY `uc_remember_selector` (`remember_selector`);

--
-- Indexes for table `users_groups`
--
ALTER TABLE `users_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_users_groups` (`user_id`,`group_id`),
  ADD KEY `fk_users_groups_users1_idx` (`user_id`),
  ADD KEY `fk_users_groups_groups1_idx` (`group_id`);

--
-- Indexes for table `users_tokens`
--
ALTER TABLE `users_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cash_collection`
--
ALTER TABLE `cash_collection`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `country_codes`
--
ALTER TABLE `country_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `delete_general_notification`
--
ALTER TABLE `delete_general_notification`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_services`
--
ALTER TABLE `order_services`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `partner_details`
--
ALTER TABLE `partner_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `partner_subscriptions`
--
ALTER TABLE `partner_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `partner_timings`
--
ALTER TABLE `partner_timings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payment_request`
--
ALTER TABLE `payment_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `services_ratings`
--
ALTER TABLE `services_ratings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `settlement_history`
--
ALTER TABLE `settlement_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `test`
--
ALTER TABLE `test`
  MODIFY `blog_id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `themes`
--
ALTER TABLE `themes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_types`
--
ALTER TABLE `ticket_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `updates`
--
ALTER TABLE `updates`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users_groups`
--
ALTER TABLE `users_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=349;

--
-- AUTO_INCREMENT for table `users_tokens`
--
ALTER TABLE `users_tokens`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users_groups`
--
ALTER TABLE `users_groups`
  ADD CONSTRAINT `fk_users_groups_groups1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_users_groups_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
