-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2025 at 04:31 PM
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
-- Table structure for table `iss_persons`
--

CREATE TABLE `iss_persons` (
  `id` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pwd_hash` varchar(255) NOT NULL,
  `pwd_salt` varchar(255) NOT NULL,
  `admin` varchar(255) NOT NULL,
  `verified` int(11) NOT NULL,
  `verify_token` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `iss_persons`
--

INSERT INTO `iss_persons` (`id`, `fname`, `lname`, `mobile`, `email`, `pwd_hash`, `pwd_salt`, `admin`, `verified`, `verify_token`) VALUES
(1, 'George', 'Corser', '', '', '', '', '', 0, 0),
(2, 'Emily', 'Dombrowski', '9897810655', 'emily@dombrowski.edu', '5d41402abc4b2a76b9719d911017c592', '#dy4', 'Y', 0, 0),
(5, 'Emilyy', 'Dombrowskii', '', 'emdombro@svsu.edu', '5d41402abc4b2a76b9719d911017c592', '3aba8ed15a2a0188', '0', 0, 84);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `iss_persons`
--
ALTER TABLE `iss_persons`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `iss_persons`
--
ALTER TABLE `iss_persons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
