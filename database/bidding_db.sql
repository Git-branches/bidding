-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2025 at 02:11 PM
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
-- Database: `bidding_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bids`
--

CREATE TABLE `bids` (
  `id` int(30) NOT NULL,
  `user_id` int(30) NOT NULL,
  `product_id` int(30) NOT NULL,
  `bid_amount` float NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=bid,2=confirmed,3=cancelled',
  `date_created` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(30) NOT NULL,
  `title` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `title`) VALUES
(1, 'Sports'),
(2, 'Appliances'),
(3, 'Desktop Computers'),
(4, 'Laptop'),
(5, 'Mobile Phone'),
(6, 'Health'),
(10, 'People'),
(11, 'Antique');

-- --------------------------------------------------------

--
-- Table structure for table `clients_bid_tbl`
--

CREATE TABLE `clients_bid_tbl` (
  `id` int(11) NOT NULL,
  `bidToken` int(11) NOT NULL,
  `bidderName` varchar(100) NOT NULL,
  `bidderItem` varchar(100) NOT NULL,
  `itemId` int(11) NOT NULL,
  `itemPrice` double NOT NULL,
  `bidAmount` double NOT NULL,
  `bidStatus` varchar(50) NOT NULL,
  `bidDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `clients_bid_tbl`
--

INSERT INTO `clients_bid_tbl` (`id`, `bidToken`, `bidderName`, `bidderItem`, `itemId`, `itemPrice`, `bidAmount`, `bidStatus`, `bidDate`) VALUES
(1, 257133, '5', 'products/gold-utensils.jpg', 59, 980500, 150000, 'In-progress', '2023-12-04 00:00:00'),
(2, 481766, '5', 'products/note.jpg', 58, 250500, 29435, 'In-progress', '2023-12-04 00:00:00'),
(3, 811008, '5', 'products/kabaw.jpg', 62, 65990, 15935, 'In-progress', '2023-12-04 00:00:00'),
(4, 794201, '5', 'products/sundang.jpg', 54, 459500, 80900, 'In-progress', '2023-12-04 00:00:00'),
(5, 196773, '5', 'products/sungka.jpeg', 61, 95000, 15999, 'In-progress', '2023-12-04 00:00:00'),
(6, 669526, '4', 'products/sundang.jpg', 54, 459500, 117305, 'In-progress', '2023-12-04 00:00:00'),
(7, 315142, '4', 'products/note.jpg', 58, 250500, 42680, 'In-progress', '2023-12-04 00:00:00'),
(8, 561071, '4', 'products/gold-utensils.jpg', 59, 980500, 217500, 'In-progress', '2023-12-04 00:00:00'),
(9, 964032, '4', 'products/sungka.jpeg', 61, 95000, 23198, 'In-progress', '2023-12-04 00:00:00'),
(10, 354500, '4', 'products/kabaw.jpg', 62, 65990, 23105, 'In-progress', '2023-12-04 00:00:00'),
(11, 668939, '6', 'products/sundang.jpg', 54, 459500, 170092, 'In-progress', '2023-12-04 00:00:00'),
(12, 770928, '6', 'products/note.jpg', 58, 250500, 61886, 'In-progress', '2023-12-04 00:00:00'),
(13, 214542, '6', 'products/gold-utensils.jpg', 59, 980500, 457293, 'In-progress', '2023-12-04 00:00:00'),
(14, 491893, '6', 'products/sungka.jpeg', 61, 95000, 70722, 'In-progress', '2023-12-04 00:00:00'),
(15, 981426, '6', 'products/kabaw.jpg', 62, 65990, 48578, 'In-progress', '2023-12-04 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(30) NOT NULL,
  `productOwner` varchar(50) NOT NULL,
  `productStatus` varchar(20) NOT NULL,
  `productName` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `itemDesc` text NOT NULL,
  `bidCount` int(11) NOT NULL,
  `minimumBid` double NOT NULL,
  `productPrice` double NOT NULL,
  `productEnd` datetime NOT NULL,
  `productImgLoc` varchar(255) NOT NULL,
  `datePosted` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `productOwner`, `productStatus`, `productName`, `category`, `itemDesc`, `bidCount`, `minimumBid`, `productPrice`, `productEnd`, `productImgLoc`, `datePosted`) VALUES
(54, '4', 'active', 'Lapu-Lapu Bolo', 'Antique', 'This bolo was accidentally found in the coast of Mactan when a man searching jewelry using his metal detector. People said this one killed Magellan', 0, 80900, 459500, '2023-12-07 00:00:00', 'products/sundang.jpg', '2023-12-01 02:17:00'),
(58, '4', 'active', '1 Peso Note', 'Antique', 'This 1 peso bank note is very rare, it was made way back 1860s when american soldiers were reigning here in the philippines for more than 500 years.', 0, 20300, 250500, '2023-12-08 00:00:00', 'products/note.jpg', '2023-12-01 02:38:00'),
(59, '7', 'active', 'Yamashita Gold', 'Antique', 'These gold utensils are 900 years old, these are originated from japan and brought to the philippines during the world war 2. Yamishita\'s Gold!', 0, 150000, 980500, '2023-12-08 00:00:00', 'uploads/products/6911a3a09ccbf.jpg', '2023-12-01 02:54:00'),
(60, '6', 'in-active', 'Gold Amulet', 'Antique', 'The golden amulet used by the famous chinese actor Jackie Chan in the movie Armageddon. Those who wear this amulet will be granted immortality.', 0, 10000, 100000, '2023-11-30 00:00:00', 'products/amulet.jpg', '2023-12-01 04:57:00'),
(61, '5', 'active', 'Legendary Sungka', 'Sports', 'The is the legendary sungka in the early 1850s. Used 78 times in olympic and never been wear even a scratch. Play like a pro in this one.', 0, 15999, 95000, '2023-12-08 00:00:00', 'uploads/products/6911a3bf55ef3.jpeg', '2023-12-01 01:02:00'),
(62, '5', 'active', 'Carved Water Buffalo', 'Antique', 'Made up of a thousand year old tree, carved by the ancient filipino people. Found by an archeologist Pacquiao when he was doing his training in a cave', 0, 10990, 65990, '2023-12-08 00:00:00', 'uploads/products/6911a36fe1356.jpg', '2023-12-02 02:19:00'),
(63, '8', 'active', 'Iphone 15 PRO MAX', 'Appliances', 'asdasd', 0, 0.02, 111, '2025-11-29 03:59:00', 'uploads/products/prod_69119bfb0f42b.jpg', '2025-11-10 16:02:03');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(30) NOT NULL,
  `name` text NOT NULL,
  `email` varchar(200) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `cover_img` text NOT NULL,
  `about_content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `name`, `email`, `contact`, `cover_img`, `about_content`) VALUES
(1, 'Simple Online Bidding System', 'info@sample.comm', '+6948 8542 623', '1603344720_1602738120_pngtree-purple-hd-business-banner-image_5493.jpg', '&lt;p style=&quot;text-align: center; background: transparent; position: relative;&quot;&gt;&lt;span style=&quot;color: rgb(0, 0, 0); font-family: &amp;quot;Open Sans&amp;quot;, Arial, sans-serif; font-weight: 400; text-align: justify;&quot;&gt;&amp;nbsp;is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&rsquo;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.&lt;/span&gt;&lt;br&gt;&lt;/p&gt;&lt;p style=&quot;text-align: center; background: transparent; position: relative;&quot;&gt;&lt;br&gt;&lt;/p&gt;&lt;p style=&quot;text-align: center; background: transparent; position: relative;&quot;&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;&lt;/p&gt;');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(30) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `userMoney` varchar(100) NOT NULL,
  `amountRcvble` varchar(100) NOT NULL,
  `password` text NOT NULL,
  `email` varchar(200) NOT NULL,
  `contact` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `userType` tinyint(4) NOT NULL,
  `securityWord` varchar(50) NOT NULL,
  `profilePic` varchar(100) NOT NULL,
  `isLogin` smallint(6) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `avatar` varchar(255) DEFAULT '../assets/img/default-avatar.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstName`, `lastName`, `userMoney`, `amountRcvble`, `password`, `email`, `contact`, `address`, `userType`, `securityWord`, `profilePic`, `isLogin`, `date_created`, `created_at`, `avatar`) VALUES
(1, 'Delpressdy', 'Maglinte', 'NzE5OTg3MzE5OTk5ODk=', 'MTAwMDAw', '123', 'press@mail.com', '09704463789', 'Kentucky 16th street, Taguig', 1, 'pussycat', 'profilepics/press.gif', 18, '2023-11-27 00:00:00', '2025-11-10 15:17:59', '../assets/img/default-avatar.png'),
(5, 'Jingky', 'Pacquiao', 'Njc5NzMw', 'MTAwMDAw', 'jingjing', 'jingjing@mail.com', '09686445968', 'Long lost city of Gensan', 2, 'packyyy', 'profilepics/jingjing.jpg', 9, '2023-12-04 00:00:00', '2025-11-10 15:17:59', '../assets/img/default-avatar.png'),
(6, 'Ricardo', 'Dalisay', 'MTYzNDI4', 'MTAwMDAw', 'cardo', 'cardo@mail.com', '09077376569', 'Tandang Sora, Tondo, Metro Manila', 2, 'hayopka', 'profilepics/cardo.jpg', 4, '2023-12-04 00:00:00', '2025-11-10 15:17:59', '../assets/img/default-avatar.png'),
(7, 'Kulot', 'Kulot', 'MTAwMDAw', 'MA==', '123123', 'ejromero@gmail.com', '09103443488', 'General Santos City', 2, 'qwe', '', 0, '2025-11-10 14:11:52', '2025-11-10 15:17:59', '../assets/img/default-avatar.png'),
(8, 'ej', 'romero', 'MTAwMDAw', 'MA==', 'ejromero', 'ejromero294@gmail.com', '09929216022', 'gensan', 2, 'wewss', 'profilepics/pacman.jpg', 0, '2025-11-10 14:39:43', '2025-11-10 15:17:59', ''),
(9, 'ronel', 'policarpio', 'MTAwMDAw', 'MA==', 'ronel123', 'ronel@gmail.com', '09103443488', 'tupi', 2, 'bading', 'profilepics/pressdy.jpg', 0, '2025-11-10 20:56:38', '2025-11-10 20:56:38', '../assets/img/default-avatar.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bids`
--
ALTER TABLE `bids`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients_bid_tbl`
--
ALTER TABLE `clients_bid_tbl`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bids`
--
ALTER TABLE `bids`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `clients_bid_tbl`
--
ALTER TABLE `clients_bid_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
