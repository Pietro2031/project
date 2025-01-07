-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 07, 2025 at 02:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--
CREATE DATABASE IF NOT EXISTS `project` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `project`;

-- --------------------------------------------------------

--
-- Table structure for table `addons`
--

DROP TABLE IF EXISTS `addons`;
CREATE TABLE `addons` (
  `id` int(11) NOT NULL,
  `addon_name` varchar(255) NOT NULL,
  `addon_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addons`
--

INSERT INTO `addons` (`id`, `addon_name`, `addon_price`, `created_at`) VALUES
(1, 'Extra Shot of Espresso', 15.00, '2025-01-04 13:20:01'),
(2, 'Oat Milk', 5.00, '2025-01-04 13:20:01'),
(3, 'Caramel Syrup', 10.00, '2025-01-04 13:20:01'),
(4, 'Whipped Cream', 5.00, '2025-01-04 13:20:01');

-- --------------------------------------------------------

--
-- Table structure for table `admin_account`
--

DROP TABLE IF EXISTS `admin_account`;
CREATE TABLE `admin_account` (
  `username` varchar(255) NOT NULL,
  `passwords` varchar(255) NOT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `Fname` varchar(255) NOT NULL,
  `Lname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `addresss` varchar(255) NOT NULL,
  `contactNum` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_account`
--

INSERT INTO `admin_account` (`username`, `passwords`, `profile_picture`, `Fname`, `Lname`, `email`, `addresss`, `contactNum`) VALUES
('admin', '123', 'admin/674b4c74e1258_wall3.jpg', 'John Peter', 'Maravilla', 'peterbeans385@gmail.com', 'Catacte, Bustos, Bulcan', '09602558220');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `size` enum('S','M','L') NOT NULL,
  `addons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`addons`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `size`, `addons`, `created_at`, `updated_at`) VALUES
(94, 2, 3, 2, 'L', '[\"flavor-1\"]', '2025-01-07 04:41:55', '2025-01-07 06:59:55'),
(95, 2, 1, 1, 'L', '[\"flavor-2\",\"flavor-3\",\"topping-2\",\"topping-3\",\"topping-4\"]', '2025-01-07 04:42:26', '2025-01-07 04:42:26');

-- --------------------------------------------------------

--
-- Table structure for table `coffee_base`
--

DROP TABLE IF EXISTS `coffee_base`;
CREATE TABLE `coffee_base` (
  `id` int(11) NOT NULL,
  `base_name` varchar(50) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `img` varchar(255) NOT NULL,
  `price` double(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coffee_base`
--

INSERT INTO `coffee_base` (`id`, `base_name`, `quantity`, `img`, `price`) VALUES
(1, 'Espresso', 976, 'img/espresso.png', 70.00),
(2, 'Cold Brew', 998, 'img/cold_brew.png', 80.00),
(3, 'Blended Latte', 999, 'img/blended_latte.png', 40.00),
(4, 'French Press', 961, 'img/cold_brew.png', 50.00),
(5, 'Test', 9, 'uploads/espresso.png', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `coffee_category`
--

DROP TABLE IF EXISTS `coffee_category`;
CREATE TABLE `coffee_category` (
  `id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `category_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coffee_category`
--

INSERT INTO `coffee_category` (`id`, `category_name`, `category_image`, `created_at`) VALUES
(1, 'Espresso', 'uploads/category/cup.png', '2024-12-31 06:17:06'),
(2, 'Latte', 'uploads/category/espresso.png', '2024-12-31 06:17:06'),
(3, 'Cappuccino', 'uploads/category/espresso.png', '2024-12-31 06:17:06'),
(4, 'Americano', 'uploads/category/espresso.png', '2024-12-31 06:17:06');

-- --------------------------------------------------------

--
-- Table structure for table `coffee_flavors`
--

DROP TABLE IF EXISTS `coffee_flavors`;
CREATE TABLE `coffee_flavors` (
  `id` int(11) NOT NULL,
  `flavor_name` varchar(50) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coffee_flavors`
--

INSERT INTO `coffee_flavors` (`id`, `flavor_name`, `quantity`, `price`, `img`) VALUES
(1, 'Vanilla', 972, 10.00, 'uploads/flavor_vanilla.png'),
(2, 'Caramel', -7, 15.00, 'img/flavor_caramel.png'),
(3, 'Hazelnut', 984, 20.00, 'img/flavor_hazelnut.png');

-- --------------------------------------------------------

--
-- Table structure for table `coffee_products`
--

DROP TABLE IF EXISTS `coffee_products`;
CREATE TABLE `coffee_products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `product_description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_sales` int(11) NOT NULL,
  `drink_bases` int(11) NOT NULL,
  `flavor_id` int(11) DEFAULT NULL,
  `toppings_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coffee_products`
--

INSERT INTO `coffee_products` (`id`, `product_name`, `category_id`, `product_image`, `product_description`, `price`, `total_sales`, `drink_bases`, `flavor_id`, `toppings_id`) VALUES
(1, 'Cold Brew', 1, 'uploads/iced_americano.webp', 'A refreshing cold brew coffee with smooth and bold flavors.', 80.50, 73, 4, 1, 3),
(2, 'Pumpkin Spice Cream', 1, 'img/americano.webp', 'A creamy, pumpkin spice-infused beverage perfect for the fall season.', 125.25, 23, 2, 1, 3),
(3, 'Salted Caramel Cold Brew', 1, 'img/Salted Caramel Cold Brew.webp', 'A delicious cold brew with a hint of salted caramel for a sweet and savory experience.', 120.00, 8, 1, 2, 3),
(4, 'Vanilla Sweet Cream', 1, 'img/Vanilla Sweet Cream.webp', 'A rich, smooth vanilla cream added to cold brew coffee for a sweet indulgence.', 100.00, 9, 1, 1, 3),
(5, 'Caramel Macchiato', 2, 'img/Caramel Macchiato.webp', 'A classic caramel macchiato with rich espresso and a touch of creamy caramel syrup.', 105.00, 8, 1, 2, 2),
(6, 'Latte', 2, 'img/latte.webp', 'A smooth and creamy latte made with espresso and steamed milk.', 95.00, 3, 1, 1, 3),
(7, 'Cappuccino', 2, 'img/Cappuccino.webp', 'A classic cappuccino with a balanced combination of espresso, steamed milk, and foam.', 90.00, 0, 3, 1, 3),
(8, 'Espresso', 3, 'img/Espresso.webp', 'A strong espresso shot for a quick pick-me-up.', 75.00, 0, 1, 1, 3),
(9, 'Americano', 3, 'img/americano.webp', 'A rich americano made by diluting espresso with hot water for a bold coffee flavor.', 70.25, 2, 1, 1, 3),
(10, 'Iced Coffee', 1, 'img/Iced Coffee.webp', 'Iced coffee brewed and served over ice, perfect for hot days.', 80.50, 17, 2, 1, 3),
(11, 'Iced Latte', 1, 'img/iced_latte.webp', 'A chilled version of the classic latte, served over ice for a refreshing treat.', 110.00, 0, 1, 1, 3),
(12, 'Iced Americano', 3, 'img/iced_americano.webp', 'A refreshing americano served over ice, perfect for hot weather.', 85.00, 0, 1, 1, 3),
(13, 'Blended Latte', 2, 'img/blended_latte.webp', 'A creamy and smooth latte blended with ice, creating a rich and frothy drink.', 125.00, 4, 1, 1, 2),
(21, 'dsad', 1, 'uploads/almond.png', '', 0.00, 0, 3, 2, 4),
(22, 'dsadasdas', 3, 'uploads/Espresso.webp', '', 11.00, 0, 2, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `coffee_toppings`
--

DROP TABLE IF EXISTS `coffee_toppings`;
CREATE TABLE `coffee_toppings` (
  `id` int(11) NOT NULL,
  `topping_name` varchar(50) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coffee_toppings`
--

INSERT INTO `coffee_toppings` (`id`, `topping_name`, `quantity`, `price`, `img`) VALUES
(1, 'Whipped Cream', 993, 10.00, 'uploads/whip_cream.png'),
(2, 'Cinnamon Powder', 985, 15.00, 'uploads/cinnamon_powder.png'),
(3, 'Chocolate Drizzle', 982, 10.00, 'img/chocolate_drizzle.png'),
(4, 'Caramel Drizzle', 978, 5.00, 'img/caramel_drizzle.png');

-- --------------------------------------------------------

--
-- Table structure for table `cup_size`
--

DROP TABLE IF EXISTS `cup_size`;
CREATE TABLE `cup_size` (
  `id` int(11) NOT NULL,
  `size` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cup_size`
--

INSERT INTO `cup_size` (`id`, `size`, `quantity`, `price`, `img`) VALUES
(1, 'S', 999, 0.00, 'uploads/cup.png'),
(2, 'M', 0, 10.00, 'uploads/cup.png'),
(3, 'L', 17, 20.00, 'uploads/cup.png');

-- --------------------------------------------------------

--
-- Table structure for table `custom_drink`
--

DROP TABLE IF EXISTS `custom_drink`;
CREATE TABLE `custom_drink` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `base` varchar(255) NOT NULL,
  `ingredients` text NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `custom_drink`
--

INSERT INTO `custom_drink` (`id`, `customer_id`, `base`, `ingredients`, `total_price`, `payment_method`, `order_date`) VALUES
(5, 0, '', 'Caramel, Chocolate Drizzle, Whipped Cream', 140.00, 'GCash', '2025-01-06 18:23:58'),
(6, 0, '', 'Hazelnut, Whipped Cream, Chocolate Drizzle, Caramel Drizzle', 195.00, 'Debit Card', '2025-01-07 13:52:34');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

DROP TABLE IF EXISTS `ingredients`;
CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `category` enum('classics','fruity','unexpected','toppings') NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `name`, `image`, `category`, `price`) VALUES
(1, 'Almond', 'img/almond.png', 'classics', 25.00),
(2, 'Caramel', 'img/caramel.png', 'classics', 30.00),
(3, 'Chocolate', 'img/chocolate.png', 'classics', 35.00),
(4, 'Cinnamon Bark', 'img/cinnamon.png', 'unexpected', 20.00),
(5, 'Coconut', 'img/coconut.png', 'unexpected', 15.00),
(6, 'Hazelnut', 'img/hazelnut.png', 'classics', 30.00),
(7, 'Peppermint', 'img/peppermint.png', 'fruity', 25.00),
(8, 'Honey', 'img/honey.png', 'unexpected', 40.00),
(11, 'Cinnamon powder', 'img/cinnamon_powder.png', 'toppings', 5.00),
(12, 'Graham cracker crumbs', 'img/graham_cracker_crumbs.png', 'toppings', 7.00),
(13, 'Oreos mixed in', 'img/oreos_mixed.png', 'toppings', 10.00),
(14, 'Caramel drizzle', 'img/caramel_drizzle.png', 'toppings', 8.00),
(15, 'Chocolate drizzle', 'img/chocolate_drizzle.png', 'toppings', 8.00),
(16, 'Sprinkles', 'img/sprinkles.png', 'toppings', 5.00),
(17, 'Swimming gummy worm', 'img/swimming_gummy_worm.png', 'toppings', 6.00),
(18, 'Whipped cream', 'img/whipped_cream.png', 'toppings', 12.00),
(19, 'Sugar in the Raw packet', 'img/sugar_in_the_raw.png', 'toppings', 3.00),
(20, 'Stevia packet', 'img/stevia_packet.png', 'toppings', 3.50),
(21, 'Splenda packet', 'img/splenda_packet.png', 'toppings', 3.50);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `order_quantity` int(11) NOT NULL,
  `product_ids` text NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `size` varchar(10) DEFAULT NULL,
  `base_price` decimal(10,2) DEFAULT 0.00,
  `addon_price` decimal(10,2) DEFAULT 0.00,
  `payment_method` varchar(255) NOT NULL,
  `flavor` varchar(255) DEFAULT NULL,
  `toppings` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `total_amount`, `order_quantity`, `product_ids`, `status`, `size`, `base_price`, `addon_price`, `payment_method`, `flavor`, `toppings`, `created_at`) VALUES
(161, 0, '2024-12-31 22:41:05', 155.00, 1, '3', 1, NULL, 0.00, 0.00, 'gcash', NULL, NULL, '2025-01-06 22:41:05'),
(162, 0, '2025-01-06 22:41:16', 115.50, 1, '1', 1, NULL, 0.00, 0.00, 'gcash', NULL, NULL, '2025-01-06 22:41:16'),
(163, 0, '2025-01-06 22:41:42', 465.00, 3, '3,3', 1, NULL, 0.00, 0.00, 'gcash', NULL, NULL, '2025-01-06 22:41:42'),
(164, 0, '2025-01-06 22:42:55', 240.00, 2, '12', 1, NULL, 0.00, 0.00, 'gcash', NULL, NULL, '2025-01-06 22:42:55'),
(165, 0, '2025-01-06 22:43:49', 112.00, 2, '22', 1, NULL, 0.00, 0.00, 'gcash', NULL, NULL, '2025-01-06 22:43:49'),
(166, 0, '2025-01-06 22:44:34', 290.00, 2, '4', 1, NULL, 0.00, 0.00, 'gcash', NULL, NULL, '2024-12-29 22:44:34'),
(167, 0, '2025-01-06 22:45:11', 185.25, 1, '2', 1, NULL, 0.00, 0.00, 'gcash', NULL, NULL, '2025-01-06 22:45:11'),
(168, 0, '2025-01-06 22:46:17', 305.00, 2, '3,5', 1, NULL, 0.00, 0.00, 'gcash', NULL, NULL, '2025-01-06 22:46:17'),
(170, 0, '2025-01-06 22:49:54', 100.50, 1, '1', 1, NULL, 0.00, 0.00, 'admin', NULL, NULL, '2023-12-27 22:49:54'),
(171, 0, '2025-01-06 22:54:57', 1441.00, 14, '6,4,5,8,7,11,12,21,22,13,21,8,8,8', 1, NULL, 0.00, 0.00, 'pay on conter', NULL, NULL, '2025-01-06 22:54:57'),
(174, 2, '2025-01-07 07:10:56', 300.00, 2, '3', 1, 'L', 240.00, 20.00, 'cash', 'Vanilla', NULL, '2025-01-07 07:10:56'),
(175, 2, '2024-12-27 07:28:26', 300.00, 2, '3', 2, 'L', 240.00, 20.00, 'cash', 'Vanilla', NULL, '2025-01-07 07:28:26'),
(176, 0, '2024-12-25 04:42:28', 875.75, 5, '1,3,5,5,2', 1, NULL, 0.00, 0.00, 'pay on conter', NULL, NULL, '2024-12-25 04:42:28'),
(177, 2, '2025-01-07 11:43:58', 465.50, 3, '3,1', 0, 'L,L', 320.50, 85.00, 'credit_card', 'Caramel', 'Cinnamon Powder, Chocolate Drizzle, Caramel Drizzle', '2025-01-07 11:43:58');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
CREATE TABLE `payment` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_mode` enum('GCash','Debit Card','Pay on the Counter') NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `slideshow`
--

DROP TABLE IF EXISTS `slideshow`;
CREATE TABLE `slideshow` (
  `id` int(11) NOT NULL,
  `slideshow_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `slideshow`
--

INSERT INTO `slideshow` (`id`, `slideshow_path`) VALUES
(1, 'slideshow/slide3.png'),
(2, 'slideshow/slide2.png'),
(3, 'slideshow/slide1.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `theme`
--

DROP TABLE IF EXISTS `theme`;
CREATE TABLE `theme` (
  `id` int(255) NOT NULL,
  `primary_color` varchar(255) NOT NULL,
  `secondary_color` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `font_color` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `theme`
--

INSERT INTO `theme` (`id`, `primary_color`, `secondary_color`, `logo`, `font_color`) VALUES
(1, '#fff', '#C9C9A6', 'uploads/logos/logo3.png', '#9e9b76');

-- --------------------------------------------------------

--
-- Table structure for table `user_account`
--

DROP TABLE IF EXISTS `user_account`;
CREATE TABLE `user_account` (
  `id` int(11) NOT NULL,
  `userName` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `passwords` varchar(255) NOT NULL,
  `statuss` varchar(255) NOT NULL,
  `attempt` int(255) NOT NULL,
  `Addresss` varchar(255) NOT NULL,
  `ContactNum` varchar(255) NOT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `Fname` varchar(255) NOT NULL,
  `Lname` varchar(255) NOT NULL,
  `addresss2` varchar(255) NOT NULL,
  `verified` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`id`, `userName`, `email`, `passwords`, `statuss`, `attempt`, `Addresss`, `ContactNum`, `profile_picture`, `Fname`, `Lname`, `addresss2`, `verified`) VALUES
(0, 'Peter Beans', 'Peter.Beans@gmail.com', '123', '', 0, 'Bulacan', '09911180759', 'uploads/6777dcac301e0_1735113987052.png', 'Peter', 'Beans', 'baliwag', 'verified'),
(1, 'peter', 'maravillapeter123@gmail.com', '123', '', 4, 'bustos', '09602558220', 'uploads/675f9d1ac54b1_wall3.jpg', 'John Peter', 'maravilla', 'baliwag', 'verified'),
(2, 'gray', 'lance.musngi@gmail.com', '123', '', 0, 'Bulacan', '09911180759', 'uploads/6777dcac301e0_1735113987052.png', 'Lance', 'Musngi', 'baliwag', 'verified');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addons`
--
ALTER TABLE `addons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_account`
--
ALTER TABLE `admin_account`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `coffee_base`
--
ALTER TABLE `coffee_base`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `base_name` (`base_name`);

--
-- Indexes for table `coffee_category`
--
ALTER TABLE `coffee_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coffee_flavors`
--
ALTER TABLE `coffee_flavors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `flavor_name` (`flavor_name`);

--
-- Indexes for table `coffee_products`
--
ALTER TABLE `coffee_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `coffee_toppings`
--
ALTER TABLE `coffee_toppings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `topping_name` (`topping_name`);

--
-- Indexes for table `cup_size`
--
ALTER TABLE `cup_size`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `custom_drink`
--
ALTER TABLE `custom_drink`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `slideshow`
--
ALTER TABLE `slideshow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `theme`
--
ALTER TABLE `theme`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userName` (`userName`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addons`
--
ALTER TABLE `addons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `coffee_base`
--
ALTER TABLE `coffee_base`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `coffee_category`
--
ALTER TABLE `coffee_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `coffee_flavors`
--
ALTER TABLE `coffee_flavors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `coffee_products`
--
ALTER TABLE `coffee_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `coffee_toppings`
--
ALTER TABLE `coffee_toppings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cup_size`
--
ALTER TABLE `cup_size`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `custom_drink`
--
ALTER TABLE `custom_drink`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=179;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `slideshow`
--
ALTER TABLE `slideshow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `theme`
--
ALTER TABLE `theme`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_account`
--
ALTER TABLE `user_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `coffee_products` (`id`);

--
-- Constraints for table `coffee_products`
--
ALTER TABLE `coffee_products`
  ADD CONSTRAINT `coffee_products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `coffee_category` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
