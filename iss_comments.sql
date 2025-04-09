-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2025 at 06:21 PM
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
-- Database: `iss`
--

-- --------------------------------------------------------

--
-- Table structure for table `iss_comments`
--

CREATE TABLE `iss_comments` (
  `id` int(11) NOT NULL,
  `per_id` int(11) NOT NULL,
  `iss_id` int(11) NOT NULL,
  `short_comment` varchar(255) NOT NULL,
  `long_comment` text NOT NULL,
  `posted_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `iss_comments`
--

INSERT INTO `iss_comments` (`id`, `per_id`, `iss_id`, `short_comment`, `long_comment`, `posted_date`) VALUES
(1, 2, 1, 'I need help', 'this is for my job', '2025-03-31'),
(2, 1, 3, 'This issue keeps happening', 'I want to test this first \'uploading\' issue', '2025-04-14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `iss_comments`
--
ALTER TABLE `iss_comments`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `iss_comments`
--
ALTER TABLE `iss_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
