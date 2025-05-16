-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 16, 2025 at 01:10 PM
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
-- Database: `Travel`
--

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) NOT NULL,
  `fullName` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `agencyName` varchar(100) NOT NULL,
  `agencyId` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`id`, `fullName`, `email`, `phone`, `agencyName`, `agencyId`, `password`, `created_at`) VALUES
(1, 'Sujal Kunwar', 'sujalkunwar22@gmail.com', '9842756406', 'Sujal Adventure', '0001', 'Ss234@##*', '2025-05-16 05:29:54');

-- --------------------------------------------------------

--
-- Table structure for table `Availability`
--

CREATE TABLE `Availability` (
  `availability_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('available','unavailable','booked') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Availability`
--

INSERT INTO `Availability` (`availability_id`, `listing_id`, `date`, `status`, `created_at`, `updated_at`) VALUES
(2, 1, '2023-05-16', 'available', '2025-05-15 16:10:31', '2025-05-15 16:14:30'),
(3, 1, '2023-05-18', 'available', '2025-05-15 16:13:52', '2025-05-15 16:14:18'),
(4, 1, '2023-05-17', 'available', '2025-05-15 16:13:56', '2025-05-15 16:14:18'),
(5, 1, '2023-05-30', 'available', '2025-05-15 16:14:09', '2025-05-15 16:14:16'),
(6, 1, '2023-05-25', 'available', '2025-05-15 16:14:10', '2025-05-15 16:14:17'),
(7, 1, '2023-05-24', 'available', '2025-05-15 16:14:10', '2025-05-15 16:14:17'),
(8, 1, '2023-05-22', 'available', '2025-05-15 16:14:11', '2025-05-15 16:14:14'),
(9, 1, '2023-05-29', 'available', '2025-05-15 16:14:12', '2025-05-15 16:14:16'),
(10, 1, '2023-05-31', 'available', '2025-05-15 16:14:31', '2025-05-15 16:14:31'),
(11, 1, '2023-05-23', 'available', '2025-05-15 16:14:37', '2025-05-15 16:14:37'),
(12, 1, '2023-05-10', 'available', '2025-05-15 16:17:47', '2025-05-15 16:17:47'),
(13, 1, '2023-05-28', 'available', '2025-05-15 16:17:54', '2025-05-15 16:17:54'),
(14, 1, '2023-05-01', 'available', '2025-05-15 16:29:07', '2025-05-15 16:29:07'),
(15, 1, '2025-05-01', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 16:41:49'),
(16, 1, '2025-05-02', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 16:41:49'),
(17, 1, '2025-05-03', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 20:03:04'),
(18, 1, '2025-05-04', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 20:03:04'),
(19, 1, '2025-05-05', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 20:03:04'),
(20, 1, '2025-05-06', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 20:03:04'),
(21, 1, '2025-05-07', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 16:41:49'),
(22, 1, '2025-05-08', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 20:03:04'),
(23, 1, '2025-05-09', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 16:41:49'),
(24, 1, '2025-05-10', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 16:41:49'),
(25, 1, '2025-05-11', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 20:03:04'),
(26, 1, '2025-05-12', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 16:41:49'),
(27, 1, '2025-05-13', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 16:41:49'),
(28, 1, '2025-05-14', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 16:41:49'),
(29, 1, '2025-05-15', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 16:41:49'),
(30, 1, '2025-05-16', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 16:41:49'),
(31, 1, '2025-05-17', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 16:41:49'),
(32, 1, '2025-05-18', 'unavailable', '2025-05-15 16:40:35', '2025-05-15 16:41:49'),
(33, 1, '2025-05-19', 'unavailable', '2025-05-15 16:40:36', '2025-05-15 16:41:49'),
(34, 1, '2025-05-20', 'unavailable', '2025-05-15 16:40:36', '2025-05-15 20:03:04'),
(35, 1, '2025-05-21', 'unavailable', '2025-05-15 16:40:36', '2025-05-15 16:41:49'),
(36, 1, '2025-05-22', 'available', '2025-05-15 16:40:36', '2025-05-15 16:48:09'),
(37, 1, '2025-05-23', 'unavailable', '2025-05-15 16:40:36', '2025-05-15 16:41:49'),
(38, 1, '2025-05-24', 'unavailable', '2025-05-15 16:40:36', '2025-05-15 16:41:49'),
(39, 1, '2025-05-25', 'available', '2025-05-15 16:40:36', '2025-05-15 16:47:28'),
(40, 1, '2025-05-26', 'unavailable', '2025-05-15 16:40:36', '2025-05-15 16:41:49'),
(41, 1, '2025-05-27', 'unavailable', '2025-05-15 16:40:36', '2025-05-15 16:41:49'),
(42, 1, '2025-05-28', 'unavailable', '2025-05-15 16:40:36', '2025-05-15 16:41:49'),
(43, 1, '2025-05-29', 'unavailable', '2025-05-15 16:40:36', '2025-05-15 16:41:49'),
(44, 1, '2025-05-30', 'unavailable', '2025-05-15 16:40:36', '2025-05-15 16:41:49'),
(45, 1, '2025-05-31', 'unavailable', '2025-05-15 16:40:36', '2025-05-15 20:03:04');

-- --------------------------------------------------------

--
-- Table structure for table `Bookings`
--

CREATE TABLE `Bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_email` varchar(255) NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `number_of_guests` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `booking_status` enum('confirmed','pending','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Bookings`
--

INSERT INTO `Bookings` (`booking_id`, `user_id`, `listing_id`, `guest_name`, `guest_email`, `check_in_date`, `check_out_date`, `number_of_guests`, `total_amount`, `booking_status`, `created_at`) VALUES
(1, 1, 1, '', '', '2025-05-22', '2025-05-27', 2, 17500.00, 'confirmed', '2025-05-15 17:47:57'),
(2, 1, 2, '', '', '2025-05-15', '2025-05-18', 1, 10500.00, 'confirmed', '2025-05-15 17:57:07'),
(3, 1, 2, '', '', '2025-05-16', '2025-05-19', 3, 15000.00, 'pending', '2025-05-15 17:59:48'),
(4, 1, 2, '', '', '2025-05-16', '2025-05-19', 2, 15000.00, 'confirmed', '2025-05-15 17:59:57'),
(5, 1, 1, 'Pratik Sapkota', 'pratik@gmail.com', '2025-05-15', '2025-05-16', 1, 7000.00, 'confirmed', '2025-05-15 18:04:19'),
(6, 1, 3, 'Sujal Kunwar', 'sujalkunwar22@gmail.com', '2025-05-15', '2025-05-22', 1, 29400.00, 'confirmed', '2025-05-15 18:06:09'),
(7, 1, 3, 'Prasid Sunar', 'prasid@gmail.com', '2025-05-19', '2025-05-30', 1, 46200.00, 'confirmed', '2025-05-15 19:52:25');

-- --------------------------------------------------------

--
-- Table structure for table `BusAvailability`
--

CREATE TABLE `BusAvailability` (
  `availability_id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `status` enum('available','unavailable') DEFAULT 'available',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `BusBookings`
--

CREATE TABLE `BusBookings` (
  `booking_id` int(11) NOT NULL,
  `bus_service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `journey_date` date NOT NULL,
  `num_seats` int(11) NOT NULL,
  `seat_numbers` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `booking_status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `BusBookings`
--

INSERT INTO `BusBookings` (`booking_id`, `bus_service_id`, `user_id`, `journey_date`, `num_seats`, `seat_numbers`, `total_amount`, `booking_status`, `created_at`) VALUES
(1, 5, 1, '2025-05-15', 5, '19,18,17,25,26', 2250.00, 'pending', '2025-05-15 18:35:09'),
(2, 5, 1, '2025-05-16', 1, '14', 450.00, 'pending', '2025-05-15 18:36:48'),
(3, 5, 1, '2025-05-16', 4, '15,10,9,13', 1800.00, 'pending', '2025-05-15 18:37:11'),
(4, 5, 1, '2025-05-16', 1, '1', 450.00, 'pending', '2025-05-15 18:38:56'),
(5, 5, 1, '2025-05-16', 1, '2', 450.00, 'pending', '2025-05-15 18:40:54'),
(6, 5, 1, '2025-05-16', 1, '6', 450.00, 'pending', '2025-05-15 18:41:33'),
(7, 5, 1, '2025-05-16', 1, '31', 450.00, 'pending', '2025-05-15 18:43:08'),
(8, 9, 1, '2025-05-26', 1, '7', 610.00, 'confirmed', '2025-05-15 19:37:11'),
(9, 9, 1, '2025-05-27', 1, '4', 610.00, 'confirmed', '2025-05-15 19:53:17');

-- --------------------------------------------------------

--
-- Table structure for table `BusOperators`
--

CREATE TABLE `BusOperators` (
  `operator_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `BusOperators`
--

INSERT INTO `BusOperators` (`operator_id`, `name`, `email`, `password`, `company_name`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, 'Smita Operator', 'smita@example.com', '1234', 'Travel Services', NULL, NULL, '2025-05-15 18:52:34', '2025-05-15 18:52:34'),
(2, 'Sujal Kunwar', 'sujalkunwar@gmail.com', 'sujal123', 'Sujal Travels .Co', NULL, NULL, '2025-05-15 18:54:29', '2025-05-15 18:54:29');

-- --------------------------------------------------------

--
-- Table structure for table `BusSchedules`
--

CREATE TABLE `BusSchedules` (
  `schedule_id` int(11) NOT NULL,
  `bus_service_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `status` enum('available','unavailable') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `BusSchedules`
--

INSERT INTO `BusSchedules` (`schedule_id`, `bus_service_id`, `date`, `status`) VALUES
(1, 9, '2025-05-26', 'available'),
(2, 10, '2025-05-30', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `BusServices`
--

CREATE TABLE `BusServices` (
  `bus_service_id` int(11) NOT NULL,
  `bus_name` varchar(100) NOT NULL,
  `route` varchar(255) NOT NULL,
  `bus_type` varchar(50) NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time NOT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_seats` int(11) NOT NULL,
  `amenities` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `operator_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `BusServices`
--

INSERT INTO `BusServices` (`bus_service_id`, `bus_name`, `route`, `bus_type`, `departure_time`, `arrival_time`, `duration`, `price`, `total_seats`, `amenities`, `image_url`, `created_at`, `updated_at`, `operator_id`) VALUES
(1, 'Sangitam Travels', 'Kathmandu to Pokhara', 'ac', '18:00:00', '03:00:00', '9 hours', 600.00, 40, '[\"wifi\",\"charging\",\"ac\",\"water\"]', NULL, '2025-05-15 18:17:07', '2025-05-15 18:17:07', 1),
(2, 'Shree Sairam Travels', 'Kathmandu to Pokhara', 'deluxe', '20:15:00', '06:00:00', '9 hours 45 mins', 500.00, 35, '[\"wifi\",\"charging\",\"ac\",\"movie\"]', NULL, '2025-05-15 18:17:07', '2025-05-15 18:17:07', 1),
(3, 'Gayatri Travels', 'Pokhara to Kathmandu', 'tourist', '20:30:00', '05:30:00', '9 hours', 700.00, 45, '[\"wifi\",\"charging\",\"ac\",\"water\",\"blanket\"]', NULL, '2025-05-15 18:17:07', '2025-05-15 18:17:07', 1),
(4, 'Swaminarayan Travels', 'Biratnagar to Kathmandu', 'normal', '19:25:00', '06:15:00', '10 hours 50 mins', 900.00, 38, '[\"charging\",\"water\"]', NULL, '2025-05-15 18:17:07', '2025-05-15 18:17:07', 1),
(5, 'Mountain Express', 'Kathmandu to Chitwan', 'ac', '07:00:00', '13:00:00', '6 hours', 450.00, 40, '[\"wifi\",\"charging\",\"ac\",\"movie\"]', NULL, '2025-05-15 18:17:07', '2025-05-15 18:17:07', 1),
(6, 'Nepal Yatayat', 'Pokhara to Butwal', 'deluxe', '09:30:00', '16:30:00', '7 hours', 550.00, 35, '[\"wifi\",\"charging\",\"ac\",\"water\",\"blanket\"]', NULL, '2025-05-15 18:17:07', '2025-05-15 18:17:07', 1),
(7, 'Dhaulagiri Express', 'Butwal to Pokhara', 'tourist', '16:00:00', '23:00:00', '7 hours', 650.00, 42, '[\"wifi\",\"charging\",\"ac\",\"movie\",\"blanket\"]', NULL, '2025-05-15 18:17:07', '2025-05-15 18:17:07', 1),
(8, 'Green Line', 'Dharan to Kathmandu', 'ac', '17:30:00', '05:30:00', '12 hours', 850.00, 36, '[\"wifi\",\"charging\",\"ac\",\"water\",\"blanket\",\"movie\"]', NULL, '2025-05-15 18:17:07', '2025-05-15 18:17:07', 1),
(9, 'Sujal Travels', 'Gorkha to Kathmandu', 'deluxe', '06:00:00', '12:09:00', '6hours', 610.00, 16, '', NULL, '2025-05-15 19:25:32', '2025-05-15 19:36:58', 2),
(10, 'Prasi8d Travels', 'Pokhara To Gorkha', 'ac', '11:00:00', '20:00:00', '9 Hours', 1560.00, 36, 'Wifi, AC, Charging Dock, TV', NULL, '2025-05-15 19:56:46', '2025-05-15 19:56:46', 2);

-- --------------------------------------------------------

--
-- Table structure for table `GuestHouseAmenities`
--

CREATE TABLE `GuestHouseAmenities` (
  `amenity_id` int(11) NOT NULL,
  `guesthouse_id` int(11) DEFAULT NULL,
  `amenity_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `GuestHouses`
--

CREATE TABLE `GuestHouses` (
  `guesthouse_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `capacity` int(11) NOT NULL,
  `amenities` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Listings`
--

CREATE TABLE `Listings` (
  `listing_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `property_name` varchar(255) NOT NULL,
  `property_location` varchar(255) NOT NULL,
  `property_type` varchar(100) NOT NULL,
  `property_description` text NOT NULL,
  `property_amenities` text NOT NULL,
  `nightly_rate` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Listings`
--

INSERT INTO `Listings` (`listing_id`, `owner_id`, `property_name`, `property_location`, `property_type`, `property_description`, `property_amenities`, `nightly_rate`, `created_at`, `image_url`) VALUES
(1, 1, 'Peace Guest House', 'Pokhara, Nepal', 'guesthouse', '**Peace Guesthouse** is a cozy and welcoming place designed for travelers seeking comfort, tranquility, and affordability. Nestled in a peaceful neighborhood, it offers a homely atmosphere with clean rooms, friendly staff, and essential amenities. Whether you\'re a solo traveler, a couple, or a small group, Peace Guesthouse provides a relaxing stay and easy access to local attractions.\n', 'Wifi, SPA, Swimming Pool, Parking', 3500.00, '2025-05-15 11:19:02', 'https://www.telegraph.co.uk/content/dam/Travel/hotels/asia/nepal/the-pavilions-himalayas-pool-p.jpg'),
(2, 1, 'Mountain View Guest House', 'Pokhara, Lakeside', 'Guest House', 'Beautiful guest house with stunning views of the Annapurna range', 'WiFi,Breakfast,Mountain View,Free Parking', 2500.00, '2025-05-15 17:31:41', 'https://www.telegraph.co.uk/content/dam/Travel/hotels/asia/nepal/the-pavilions-himalayas-pool-p.jpg'),
(3, 1, 'Lakeside Retreat', 'Pokhara, Lakeside', 'Guest House', 'Peaceful retreat by Phewa Lake with garden views', 'WiFi,Garden,Lake View,Restaurant', 3000.00, '2025-05-15 17:31:41', 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/10/05/b5/3b/kathmandu-guest-house.jpg?w=500&h=-1&s=1');

-- --------------------------------------------------------

--
-- Table structure for table `Owners`
--

CREATE TABLE `Owners` (
  `owner_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `username` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Owners`
--

INSERT INTO `Owners` (`owner_id`, `full_name`, `email`, `password`, `created_at`, `username`) VALUES
(1, 'Sujal Kunwar', 'sujalkunwar22@gmail.com', '$2y$10$IXG0wI2NbfpTCxouJVO4GeIuXZaLo6MQEtmITXUGavACiMvq1mtz.', '2025-05-15 11:09:56', 'sujal');

-- --------------------------------------------------------

--
-- Table structure for table `Permissions`
--

CREATE TABLE `Permissions` (
  `permission_id` int(11) NOT NULL,
  `permission_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Roles`
--

CREATE TABLE `Roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TransactionMonitoring`
--

CREATE TABLE `TransactionMonitoring` (
  `monitoring_id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `status` enum('completed','failed','pending') NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Transactions`
--

CREATE TABLE `Transactions` (
  `transaction_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('credit_card','wallet','cash') NOT NULL,
  `status` enum('completed','failed','pending') NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Transactions`
--

INSERT INTO `Transactions` (`transaction_id`, `booking_id`, `user_id`, `amount`, `payment_method`, `status`, `transaction_date`) VALUES
(1, 1, NULL, 17500.00, 'credit_card', 'completed', '2025-05-15 17:55:13'),
(2, 2, NULL, 10500.00, 'credit_card', 'completed', '2025-05-15 17:57:37'),
(3, 4, 1, 15000.00, 'credit_card', 'completed', '2025-05-15 18:00:29'),
(4, 5, 1, 7000.00, 'credit_card', 'completed', '2025-05-15 18:05:00'),
(5, 6, 1, 29400.00, 'credit_card', 'completed', '2025-05-15 18:06:48'),
(6, 7, 1, 46200.00, 'credit_card', 'completed', '2025-05-15 19:52:48');

-- --------------------------------------------------------

--
-- Table structure for table `UserPermissions`
--

CREATE TABLE `UserPermissions` (
  `user_permission_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `permission_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `UserRoles`
--

CREATE TABLE `UserRoles` (
  `user_role_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`user_id`, `full_name`, `email`, `username`, `password`, `phone_number`, `role_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Sujal Kunwar', 'sujal@gmail.com', 'sujal', '$2y$10$MVBEXE4F3hOEclGCUyeYTuVIJHDMCWl8btsbcM00RePfC119to512', NULL, NULL, 'active', '2025-05-14 14:23:55', '2025-05-16 10:55:07'),
(11, 'Prasid Sunar', 'prasid23@gmail.com', 'prasid', '$2y$10$iwcwzfsb60hHctOx1TOG1e.W3Tg4rIUOpYPLGp8MjqaVAdimkNQ4K', NULL, NULL, 'active', '2025-05-14 15:56:00', '2025-05-14 15:56:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `Availability`
--
ALTER TABLE `Availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD UNIQUE KEY `unique_listing_date` (`listing_id`,`date`);

--
-- Indexes for table `Bookings`
--
ALTER TABLE `Bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `listing_id` (`listing_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `BusAvailability`
--
ALTER TABLE `BusAvailability`
  ADD PRIMARY KEY (`availability_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `BusBookings`
--
ALTER TABLE `BusBookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `bus_service_id` (`bus_service_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `BusOperators`
--
ALTER TABLE `BusOperators`
  ADD PRIMARY KEY (`operator_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `BusSchedules`
--
ALTER TABLE `BusSchedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `bus_service_id` (`bus_service_id`);

--
-- Indexes for table `BusServices`
--
ALTER TABLE `BusServices`
  ADD PRIMARY KEY (`bus_service_id`);

--
-- Indexes for table `GuestHouseAmenities`
--
ALTER TABLE `GuestHouseAmenities`
  ADD PRIMARY KEY (`amenity_id`),
  ADD KEY `guesthouse_id` (`guesthouse_id`);

--
-- Indexes for table `GuestHouses`
--
ALTER TABLE `GuestHouses`
  ADD PRIMARY KEY (`guesthouse_id`);

--
-- Indexes for table `Listings`
--
ALTER TABLE `Listings`
  ADD PRIMARY KEY (`listing_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `Owners`
--
ALTER TABLE `Owners`
  ADD PRIMARY KEY (`owner_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `Permissions`
--
ALTER TABLE `Permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `Roles`
--
ALTER TABLE `Roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `TransactionMonitoring`
--
ALTER TABLE `TransactionMonitoring`
  ADD PRIMARY KEY (`monitoring_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `Transactions`
--
ALTER TABLE `Transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `UserPermissions`
--
ALTER TABLE `UserPermissions`
  ADD PRIMARY KEY (`user_permission_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `UserRoles`
--
ALTER TABLE `UserRoles`
  ADD PRIMARY KEY (`user_role_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Availability`
--
ALTER TABLE `Availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `Bookings`
--
ALTER TABLE `Bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `BusAvailability`
--
ALTER TABLE `BusAvailability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `BusBookings`
--
ALTER TABLE `BusBookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `BusOperators`
--
ALTER TABLE `BusOperators`
  MODIFY `operator_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `BusSchedules`
--
ALTER TABLE `BusSchedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `BusServices`
--
ALTER TABLE `BusServices`
  MODIFY `bus_service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `GuestHouseAmenities`
--
ALTER TABLE `GuestHouseAmenities`
  MODIFY `amenity_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `GuestHouses`
--
ALTER TABLE `GuestHouses`
  MODIFY `guesthouse_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Listings`
--
ALTER TABLE `Listings`
  MODIFY `listing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Owners`
--
ALTER TABLE `Owners`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Permissions`
--
ALTER TABLE `Permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Roles`
--
ALTER TABLE `Roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TransactionMonitoring`
--
ALTER TABLE `TransactionMonitoring`
  MODIFY `monitoring_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Transactions`
--
ALTER TABLE `Transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `UserPermissions`
--
ALTER TABLE `UserPermissions`
  MODIFY `user_permission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `UserRoles`
--
ALTER TABLE `UserRoles`
  MODIFY `user_role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Availability`
--
ALTER TABLE `Availability`
  ADD CONSTRAINT `Availability_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `Listings` (`listing_id`) ON DELETE CASCADE;

--
-- Constraints for table `Bookings`
--
ALTER TABLE `Bookings`
  ADD CONSTRAINT `Bookings_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `Listings` (`listing_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`);

--
-- Constraints for table `BusAvailability`
--
ALTER TABLE `BusAvailability`
  ADD CONSTRAINT `BusAvailability_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `BusSchedules` (`schedule_id`);

--
-- Constraints for table `BusBookings`
--
ALTER TABLE `BusBookings`
  ADD CONSTRAINT `BusBookings_ibfk_1` FOREIGN KEY (`bus_service_id`) REFERENCES `BusServices` (`bus_service_id`),
  ADD CONSTRAINT `BusBookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`);

--
-- Constraints for table `BusSchedules`
--
ALTER TABLE `BusSchedules`
  ADD CONSTRAINT `BusSchedules_ibfk_1` FOREIGN KEY (`bus_service_id`) REFERENCES `BusServices` (`bus_service_id`);

--
-- Constraints for table `GuestHouseAmenities`
--
ALTER TABLE `GuestHouseAmenities`
  ADD CONSTRAINT `GuestHouseAmenities_ibfk_1` FOREIGN KEY (`guesthouse_id`) REFERENCES `GuestHouses` (`guesthouse_id`);

--
-- Constraints for table `Listings`
--
ALTER TABLE `Listings`
  ADD CONSTRAINT `Listings_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `Owners` (`owner_id`) ON DELETE CASCADE;

--
-- Constraints for table `TransactionMonitoring`
--
ALTER TABLE `TransactionMonitoring`
  ADD CONSTRAINT `TransactionMonitoring_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `Transactions` (`transaction_id`);

--
-- Constraints for table `Transactions`
--
ALTER TABLE `Transactions`
  ADD CONSTRAINT `Transactions_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `Bookings` (`booking_id`),
  ADD CONSTRAINT `Transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`);

--
-- Constraints for table `UserPermissions`
--
ALTER TABLE `UserPermissions`
  ADD CONSTRAINT `UserPermissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`),
  ADD CONSTRAINT `UserPermissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `Permissions` (`permission_id`);

--
-- Constraints for table `UserRoles`
--
ALTER TABLE `UserRoles`
  ADD CONSTRAINT `UserRoles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`),
  ADD CONSTRAINT `UserRoles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `Roles` (`role_id`);

--
-- Constraints for table `Users`
--
ALTER TABLE `Users`
  ADD CONSTRAINT `Users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `Roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
