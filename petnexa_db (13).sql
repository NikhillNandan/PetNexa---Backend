-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 06:11 PM
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
-- Database: `petnexa_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_breed_detection`
--

CREATE TABLE `ai_breed_detection` (
  `detection_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `uploaded_image` varchar(255) NOT NULL,
  `predicted_breed` varchar(100) DEFAULT NULL,
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_breed_detection`
--

INSERT INTO `ai_breed_detection` (`detection_id`, `user_id`, `uploaded_image`, `predicted_breed`, `confidence_score`, `created_at`) VALUES
(1, 1, 'uploads/ai/detect_1.jpg', 'Golden Retriever', 95.50, '2026-02-23 14:00:06'),
(2, 2, 'uploads/ai/detect_2.jpg', 'Persian Cat', 92.30, '2026-02-23 14:00:06'),
(3, 3, 'uploads/ai/detect_3.jpg', 'German Shepherd', 97.10, '2026-02-23 14:00:06'),
(4, 1, 'uploads/ai/detect_4.jpg', 'Siamese Cat', 89.80, '2026-02-23 14:00:06'),
(5, 2, 'uploads/ai/detect_5.jpg', 'Labrador Retriever', 93.60, '2026-02-23 14:00:06'),
(6, 3, 'uploads/ai/detect_6.jpg', 'Pomeranian', 88.40, '2026-02-23 14:00:06'),
(7, 1, 'uploads/ai/detect_7.jpg', 'Beagle', 91.20, '2026-02-23 14:00:06'),
(8, 2, 'uploads/ai/detect_8.jpg', 'Maine Coon', 94.70, '2026-02-23 14:00:06'),
(9, 3, 'uploads/ai/detect_9.jpg', 'Shih Tzu', 87.90, '2026-02-23 14:00:06'),
(10, 1, 'uploads/ai/detect_10.jpg', 'Bengal Cat', 96.10, '2026-02-23 14:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `buyer_profiles`
--

CREATE TABLE `buyer_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `preferences` text DEFAULT NULL,
  `upi_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buyer_profiles`
--

INSERT INTO `buyer_profiles` (`profile_id`, `user_id`, `preferences`, `upi_id`) VALUES
(1, 1, NULL, 'ananya@upi'),
(2, 2, NULL, 'rohan@upi'),
(3, 3, NULL, 'priya@upi');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `certificate_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `issued_by` int(11) DEFAULT NULL,
  `certificate_type` enum('VACCINATION','HEALTH','LICENSE') NOT NULL,
  `certificate_file` varchar(255) NOT NULL,
  `issued_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`certificate_id`, `pet_id`, `issued_by`, `certificate_type`, `certificate_file`, `issued_date`, `expiry_date`, `notes`, `created_at`) VALUES
(1, 1, 7, 'VACCINATION', 'uploads/certs/cert_1.pdf', '2025-06-15', '2026-06-15', 'Rabies vaccination complete', '2026-02-23 14:00:06'),
(2, 2, 8, 'HEALTH', 'uploads/certs/cert_2.pdf', '2025-07-20', '2026-07-20', 'General health check passed', '2026-02-23 14:00:06'),
(3, 3, 7, 'VACCINATION', 'uploads/certs/cert_3.pdf', '2025-05-10', '2026-05-10', 'Parvo & Distemper combo', '2026-02-23 14:00:06'),
(4, 4, 8, 'HEALTH', 'uploads/certs/cert_4.pdf', '2025-08-01', '2026-08-01', 'FIV/FeLV negative', '2026-02-23 14:00:06'),
(5, 5, 7, 'VACCINATION', 'uploads/certs/cert_5.pdf', '2025-09-12', '2026-09-12', 'DHPP vaccination', '2026-02-23 14:00:06'),
(6, 6, 8, 'LICENSE', 'uploads/certs/cert_6.pdf', '2025-04-05', '2027-04-05', 'Municipal pet license', '2026-02-23 14:00:06'),
(7, 7, 7, 'VACCINATION', 'uploads/certs/cert_7.pdf', '2025-10-18', '2026-10-18', 'Rabies + Bordetella', '2026-02-23 14:00:06'),
(8, 8, 8, 'HEALTH', 'uploads/certs/cert_8.pdf', '2025-11-22', '2026-11-22', 'Deworming completed', '2026-02-23 14:00:06'),
(9, 9, 7, 'LICENSE', 'uploads/certs/cert_9.pdf', '2025-03-30', '2027-03-30', 'City registration license', '2026-02-23 14:00:06'),
(10, 10, 8, 'VACCINATION', 'uploads/certs/cert_10.pdf', '2025-12-01', '2026-12-01', 'FVRCP vaccine for cats', '2026-02-23 14:00:06'),
(11, 7, 7, 'HEALTH', 'uploads/certificates/health certificate_cert_5_1771865612.pdf', '2026-02-23', NULL, 'Appointment ID: 5', '2026-02-23 16:53:32'),
(12, 7, 7, 'HEALTH', '', '2026-02-23', NULL, 'Type: Health Certificate\nIssued: 2026-02-23\nValidity: 1\nDoctor ID: 7\nPet ID: 7\nRemarks: none', '2026-02-23 16:53:32'),
(13, 7, 7, 'HEALTH', 'uploads/certificates/health certificate_cert_5_1771865775.pdf', '2026-02-23', NULL, 'Appointment ID: 5', '2026-02-23 16:56:15'),
(14, 7, 7, 'HEALTH', '', '2026-02-23', NULL, 'Type: Health Certificate\nIssued: 2026-02-23\nValidity: 1\nDoctor ID: 7\nPet ID: 7\nRemarks: none', '2026-02-23 16:56:15');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_text` text DEFAULT NULL,
  `message_type` enum('text','image','file') DEFAULT 'text',
  `media_url` varchar(500) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_delivered` tinyint(1) DEFAULT 1,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`message_id`, `sender_id`, `receiver_id`, `message_text`, `message_type`, `media_url`, `file_name`, `is_read`, `is_delivered`, `sent_at`, `timestamp`) VALUES
(1, 1, 4, 'Hi, is Bruno still available?', 'text', NULL, NULL, 1, 1, '2026-02-23 14:00:06', '2026-02-23 19:30:06'),
(2, 4, 1, 'Yes! Bruno is available. Would you like to visit?', 'text', NULL, NULL, 1, 1, '2026-02-23 14:00:06', '2026-02-23 19:30:06'),
(3, 1, 4, 'Can you share more photos?', 'text', NULL, NULL, 1, 1, '2026-02-23 14:00:06', '2026-02-23 19:30:06'),
(4, 2, 5, 'What is the price for Max?', 'text', NULL, NULL, 1, 1, '2026-02-23 14:00:06', '2026-02-23 19:30:06'),
(5, 5, 2, 'Max is priced at Rs 13000. Negotiable.', 'text', NULL, NULL, 1, 1, '2026-02-23 14:00:06', '2026-02-23 19:30:06'),
(6, 3, 6, 'Is Charlie vaccinated?', 'text', NULL, NULL, 1, 1, '2026-02-23 14:00:06', '2026-02-23 19:30:06'),
(7, 6, 3, 'Yes, fully vaccinated with certificate.', 'text', NULL, NULL, 0, 1, '2026-02-23 14:00:06', '2026-02-23 19:30:06'),
(8, 2, 4, 'Can I visit to see Rocky this weekend?', 'text', NULL, NULL, 0, 1, '2026-02-23 14:00:06', '2026-02-23 19:30:06'),
(9, 1, 5, 'Do you deliver pets to Anna Nagar?', 'text', NULL, NULL, 0, 1, '2026-02-23 14:00:06', '2026-02-23 19:30:06'),
(10, 5, 1, 'Yes we can arrange delivery within Chennai.', 'text', NULL, NULL, 1, 1, '2026-02-23 14:00:06', '2026-02-23 19:30:06');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_appointments`
--

CREATE TABLE `doctor_appointments` (
  `appointment_id` int(11) NOT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `booking_time` varchar(20) DEFAULT NULL,
  `service_name` varchar(255) DEFAULT NULL,
  `visit_type` varchar(20) DEFAULT 'clinic',
  `consultation_status` enum('BOOKED','COMPLETED','CANCELLED') DEFAULT 'BOOKED',
  `treatment_notes` text DEFAULT NULL,
  `treatment_charge` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_appointments`
--

INSERT INTO `doctor_appointments` (`appointment_id`, `pet_id`, `doctor_id`, `buyer_id`, `appointment_date`, `booking_time`, `service_name`, `visit_type`, `consultation_status`, `treatment_notes`, `treatment_charge`, `created_at`) VALUES
(1, 1, 7, 1, '2026-02-25 10:00:00', '10:00 AM', 'General Checkup', 'clinic', 'CANCELLED', '', 500.00, '2026-02-23 14:00:06'),
(2, 2, 8, 1, '2026-02-25 11:30:00', '11:30 AM', 'Wellness Exam', 'clinic', 'BOOKED', NULL, 600.00, '2026-02-23 14:00:06'),
(3, 3, 7, 2, '2026-02-26 09:00:00', '09:00 AM', 'Surgery Consult', 'clinic', 'CANCELLED', '', 1500.00, '2026-02-23 14:00:06'),
(4, 5, 8, 2, '2026-02-26 14:00:00', '02:00 PM', 'Deworming', 'home', 'BOOKED', NULL, 400.00, '2026-02-23 14:00:06'),
(5, 7, 7, 3, '2026-02-27 10:30:00', '10:30 AM', 'Vaccination', 'clinic', 'COMPLETED', NULL, 800.00, '2026-02-23 14:00:06'),
(6, 1, 7, 1, '2026-01-15 10:00:00', '10:00 AM', 'Dental Cleaning', 'clinic', 'COMPLETED', NULL, 2000.00, '2026-02-23 14:00:06'),
(7, 4, 8, 2, '2026-01-20 15:00:00', '03:00 PM', 'Skin Treatment', 'clinic', 'COMPLETED', NULL, 1200.00, '2026-02-23 14:00:06'),
(8, 6, 7, 3, '2026-01-25 11:00:00', '11:00 AM', 'Emergency Care', 'home', 'COMPLETED', NULL, 3000.00, '2026-02-23 14:00:06'),
(9, 8, 8, 1, '2026-02-01 09:30:00', '09:30 AM', 'Blood Work', 'clinic', 'COMPLETED', NULL, 1000.00, '2026-02-23 14:00:06'),
(10, 5, 7, 3, '2026-02-10 16:00:00', '04:00 PM', 'General Checkup', 'home', 'CANCELLED', NULL, 500.00, '2026-02-23 14:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_profiles`
--

CREATE TABLE `doctor_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `experience` int(11) DEFAULT NULL,
  `hospital` varchar(255) DEFAULT NULL,
  `languages` varchar(255) DEFAULT NULL,
  `upi_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_profiles`
--

INSERT INTO `doctor_profiles` (`profile_id`, `user_id`, `qualification`, `specialization`, `experience`, `hospital`, `languages`, `upi_id`) VALUES
(1, 7, 'MVSc', 'Surgery', 7, 'Chennai Vet Hospital', 'English, Tamil, Hindi', 'ramesh@upi'),
(2, 8, 'BVSc', 'General Practice', 4, 'Guindy Animal Clinic', 'English, Tamil', 'meera@upi');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_services`
--

CREATE TABLE `doctor_services` (
  `service_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_services`
--

INSERT INTO `doctor_services` (`service_id`, `doctor_id`, `service_name`, `description`, `price`, `duration`, `created_at`) VALUES
(1, 7, 'General Checkup', 'Complete physical examination', 500.00, '30 min', '2026-02-23 14:00:06'),
(2, 7, 'Vaccination', 'Core vaccines for dogs and cats', 800.00, '20 min', '2026-02-23 14:00:06'),
(3, 7, 'Surgery Consult', 'Pre-surgical evaluation', 1500.00, '45 min', '2026-02-23 14:00:06'),
(4, 7, 'Dental Cleaning', 'Professional teeth cleaning', 2000.00, '60 min', '2026-02-23 14:00:06'),
(5, 7, 'Emergency Care', 'Urgent medical attention', 3000.00, '60 min', '2026-02-23 14:00:06'),
(6, 8, 'Wellness Exam', 'Annual wellness screening', 600.00, '30 min', '2026-02-23 14:00:06'),
(7, 8, 'Deworming', 'Internal parasite treatment', 400.00, '15 min', '2026-02-23 14:00:06'),
(8, 8, 'Skin Treatment', 'Dermatological exam and treatment', 1200.00, '40 min', '2026-02-23 14:00:06'),
(9, 8, 'X-Ray', 'Digital radiography', 1800.00, '30 min', '2026-02-23 14:00:06'),
(10, 8, 'Blood Work', 'Complete blood panel analysis', 1000.00, '20 min', '2026-02-23 14:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'system' COMMENT 'appointment, order, booking, chat, certificate, review, system',
  `reference_id` int(11) DEFAULT NULL COMMENT 'ID of related entity',
  `data` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `type`, `reference_id`, `data`, `is_read`, `created_at`) VALUES
(1, 1, 'Appointment Confirmed', 'Your appointment with Dr. Ramesh on Feb 25 is confirmed.', 'appointment', 1, NULL, 1, '2026-02-23 14:00:06'),
(2, 2, 'Booking Confirmed', 'Your spa booking at Pawsome Spa is confirmed.', 'booking', 2, NULL, 0, '2026-02-23 14:00:06'),
(3, 7, 'New Appointment', 'Ananya has booked a General Checkup on Feb 25.', 'appointment', 1, NULL, 1, '2026-02-23 14:00:06'),
(4, 9, 'New Spa Booking', 'Rohan has booked Grooming Deluxe at your spa.', 'booking', 2, NULL, 0, '2026-02-23 14:00:06'),
(5, 4, 'New Message', 'You have a new message from Ananya about Bruno.', 'chat', 1, NULL, 1, '2026-02-23 14:00:06'),
(6, 1, 'Pet Listed', 'Simba (Bengal) has been newly listed by Vikram Pets.', 'pet', 10, NULL, 1, '2026-02-23 14:00:06'),
(7, 3, 'Payment Success', 'Payment of Rs 800 for Charlie grooming is successful.', 'payment', 5, NULL, 0, '2026-02-23 14:00:06'),
(8, 8, 'Appointment Cancelled', 'Priya cancelled the General Checkup appointment.', 'appointment', 10, NULL, 0, '2026-02-23 14:00:06'),
(9, 10, 'Spa Review', 'Ananya left a 5 star review for Happy Tails Spa.', 'review', 1, NULL, 0, '2026-02-23 14:00:06'),
(10, 2, 'Welcome!', 'Welcome to Petnexa! Explore pets, doctors, and spas nearby.', 'system', NULL, NULL, 1, '2026-02-23 14:00:06'),
(11, 3, 'Certificate Issued', 'A health certificate has been issued for your pet. You can download it now.', 'certificate', 12, NULL, 0, '2026-02-23 16:53:32'),
(12, 3, 'Certificate Issued', 'A health certificate has been issued for your pet. You can download it now.', 'certificate', 14, NULL, 0, '2026-02-23 16:56:15'),
(13, 1, 'Appointment Update', 'Your appointment status has been updated to: CANCELLED', 'appointment', 1, NULL, 0, '2026-02-23 17:07:24'),
(14, 2, 'Appointment Update', 'Your appointment status has been updated to: CANCELLED', 'appointment', 3, NULL, 0, '2026-02-23 17:07:40');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `pet_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `pet_name` varchar(100) DEFAULT NULL,
  `species` varchar(50) DEFAULT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('MALE','FEMALE') DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `availability_status` enum('AVAILABLE','RESERVED','SOLD') DEFAULT 'AVAILABLE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`pet_id`, `seller_id`, `pet_name`, `species`, `breed`, `age`, `gender`, `color`, `price`, `description`, `availability_status`, `created_at`) VALUES
(1, 4, 'Bruno', 'Dog', 'Golden Retriever', 2, 'MALE', 'Golden', 15000.00, 'Friendly and playful golden retriever', 'AVAILABLE', '2026-02-23 14:00:06'),
(2, 4, 'Whiskers', 'Cat', 'Persian', 1, 'FEMALE', 'White', 12000.00, 'Beautiful long-haired Persian cat', 'AVAILABLE', '2026-02-23 14:00:06'),
(3, 4, 'Rocky', 'Dog', 'German Shepherd', 3, 'MALE', 'Black & Tan', 20000.00, 'Well-trained GSD, great guard dog', 'AVAILABLE', '2026-02-23 14:00:06'),
(4, 5, 'Luna', 'Cat', 'Siamese', 2, 'FEMALE', 'Cream', 10000.00, 'Elegant Siamese with blue eyes', 'AVAILABLE', '2026-02-23 14:00:06'),
(5, 5, 'Max', 'Dog', 'Labrador Retriever', 1, 'MALE', 'Chocolate', 13000.00, 'Energetic and loving Lab puppy', 'AVAILABLE', '2026-02-23 14:00:06'),
(6, 5, 'Coco', 'Dog', 'Pomeranian', 2, 'FEMALE', 'Orange', 8000.00, 'Cute and fluffy Pomeranian', 'RESERVED', '2026-02-23 14:00:06'),
(7, 6, 'Charlie', 'Dog', 'Beagle', 1, 'MALE', 'Tricolor', 11000.00, 'Curious and merry Beagle puppy', 'AVAILABLE', '2026-02-23 14:00:06'),
(8, 6, 'Bella', 'Cat', 'Maine Coon', 3, 'FEMALE', 'Tabby', 18000.00, 'Gentle giant, very affectionate', 'AVAILABLE', '2026-02-23 14:00:06'),
(9, 6, 'Buddy', 'Dog', 'Shih Tzu', 2, 'MALE', 'White & Brown', 9000.00, 'Adorable lap dog, great companion', 'SOLD', '2026-02-23 14:00:06'),
(10, 4, 'Simba', 'Cat', 'Bengal', 1, 'MALE', 'Spotted', 22000.00, 'Exotic Bengal cat with wild markings', 'AVAILABLE', '2026-02-23 14:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `pet_images`
--

CREATE TABLE `pet_images` (
  `image_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pet_images`
--

INSERT INTO `pet_images` (`image_id`, `pet_id`, `image_url`) VALUES
(1, 1, 'uploads/pets/golden_retriever.jpg'),
(2, 2, 'uploads/pets/persian_cat.jpg'),
(3, 3, 'uploads/pets/german_shepherd.jpg'),
(4, 4, 'uploads/pets/siamese_cat.jpg'),
(5, 5, 'uploads/pets/labrador.jpg'),
(6, 6, 'uploads/pets/pomeranian.jpg'),
(7, 7, 'uploads/pets/beagle.jpg'),
(8, 8, 'uploads/pets/maine_coon.jpg'),
(9, 9, 'uploads/pets/shih_tzu.jpg'),
(10, 10, 'uploads/pets/bengal_cat.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `pet_transactions`
--

CREATE TABLE `pet_transactions` (
  `transaction_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('PENDING','SUCCESS','FAILED','BOOKED','CONFIRMED','REJECTED') DEFAULT 'BOOKED',
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pet_transactions`
--

INSERT INTO `pet_transactions` (`transaction_id`, `pet_id`, `buyer_id`, `seller_id`, `amount`, `payment_method`, `payment_status`, `transaction_date`) VALUES
(1, 9, 2, 6, 9000.00, 'UPI', 'SUCCESS', '2026-02-23 14:00:06'),
(2, 1, 1, 4, 15000.00, 'UPI', 'PENDING', '2026-02-23 14:00:06'),
(3, 4, 2, 5, 10000.00, 'UPI', 'SUCCESS', '2026-02-23 14:00:06'),
(4, 5, 3, 5, 13000.00, 'Cash', 'SUCCESS', '2026-02-23 14:00:06'),
(5, 7, 3, 6, 11000.00, 'UPI', 'BOOKED', '2026-02-23 14:00:06'),
(6, 2, 1, 4, 12000.00, 'UPI', 'CONFIRMED', '2026-02-23 14:00:06'),
(7, 3, 2, 4, 20000.00, 'Cash', 'PENDING', '2026-02-23 14:00:06'),
(8, 6, 1, 5, 8000.00, 'UPI', 'SUCCESS', '2026-02-23 14:00:06'),
(9, 8, 3, 6, 18000.00, 'UPI', 'BOOKED', '2026-02-23 14:00:06'),
(10, 10, 2, 4, 22000.00, 'Cash', 'PENDING', '2026-02-23 14:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `target_user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `reviewer_id`, `target_user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 4, 5, 'Excellent pet shop! Bruno is healthy and playful.', '2026-02-23 14:00:06'),
(2, 2, 5, 4, 'Good service. Max was well taken care of.', '2026-02-23 14:00:06'),
(3, 3, 6, 5, 'Charlie is adorable! Very transparent seller.', '2026-02-23 14:00:06'),
(4, 1, 7, 5, 'Dr. Ramesh is very knowledgeable and caring.', '2026-02-23 14:00:06'),
(5, 2, 8, 4, 'Dr. Meera explained everything clearly.', '2026-02-23 14:00:06'),
(6, 3, 7, 5, 'Emergency care was prompt. Thank you doctor!', '2026-02-23 14:00:06'),
(7, 1, 5, 4, 'Lakshmi has a great collection of pets.', '2026-02-23 14:00:06'),
(8, 2, 4, 5, 'Rocky is a stunning dog. Vikram is trustworthy.', '2026-02-23 14:00:06'),
(9, 3, 8, 4, 'Skin treatment worked wonders for Luna.', '2026-02-23 14:00:06'),
(10, 1, 6, 3, 'Decent experience but delivery was slow.', '2026-02-23 14:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `saved_pets`
--

CREATE TABLE `saved_pets` (
  `saved_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saved_pets`
--

INSERT INTO `saved_pets` (`saved_id`, `buyer_id`, `pet_id`, `saved_at`) VALUES
(1, 1, 1, '2026-02-23 14:00:06'),
(2, 1, 3, '2026-02-23 14:00:06'),
(3, 1, 10, '2026-02-23 14:00:06'),
(4, 2, 2, '2026-02-23 14:00:06'),
(5, 2, 5, '2026-02-23 14:00:06'),
(6, 2, 7, '2026-02-23 14:00:06'),
(7, 3, 4, '2026-02-23 14:00:06'),
(8, 3, 6, '2026-02-23 14:00:06'),
(9, 3, 8, '2026-02-23 14:00:06'),
(10, 3, 9, '2026-02-23 14:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `seller_profiles`
--

CREATE TABLE `seller_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shop_name` varchar(255) DEFAULT NULL,
  `seller_type` enum('INDIVIDUAL','SHOP') DEFAULT 'INDIVIDUAL',
  `upi_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seller_profiles`
--

INSERT INTO `seller_profiles` (`profile_id`, `user_id`, `shop_name`, `seller_type`, `upi_id`) VALUES
(1, 4, 'Vikram Pet Shop', 'SHOP', 'vikram@upi'),
(2, 5, 'Lakshmi Pet World', 'SHOP', 'lakshmi@upi'),
(3, 6, '', 'INDIVIDUAL', 'karthik@upi');

-- --------------------------------------------------------

--
-- Table structure for table `spa_bookings`
--

CREATE TABLE `spa_bookings` (
  `booking_id` int(11) NOT NULL,
  `spa_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_date` datetime NOT NULL,
  `booking_time` time DEFAULT '10:00:00',
  `status` varchar(20) DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `pet_name` varchar(100) DEFAULT 'Unknown',
  `pet_type` varchar(50) DEFAULT 'Dog',
  `booking_status` enum('BOOKED','COMPLETED','CANCELLED') DEFAULT 'BOOKED',
  `payment_status` enum('PENDING','SUCCESS','FAILED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spa_bookings`
--

INSERT INTO `spa_bookings` (`booking_id`, `spa_id`, `pet_id`, `service_id`, `buyer_id`, `user_id`, `booking_date`, `booking_time`, `status`, `total_amount`, `pet_name`, `pet_type`, `booking_status`, `payment_status`, `created_at`) VALUES
(1, 1, 1, 1, 1, 1, '2026-02-25 10:00:00', '10:00:00', 'pending', 500.00, 'Bruno', 'Dog', 'BOOKED', 'PENDING', '2026-02-23 14:00:06'),
(2, 1, 3, 2, 2, 2, '2026-02-25 11:00:00', '11:00:00', 'pending', 1200.00, 'Rocky', 'Dog', 'BOOKED', 'PENDING', '2026-02-23 14:00:06'),
(3, 2, 2, 6, 1, 1, '2026-02-26 09:00:00', '09:00:00', 'pending', 400.00, 'Whiskers', 'Cat', 'BOOKED', 'PENDING', '2026-02-23 14:00:06'),
(4, 2, 5, 7, 3, 3, '2026-02-26 14:00:00', '14:00:00', 'pending', 900.00, 'Max', 'Dog', 'BOOKED', 'PENDING', '2026-02-23 14:00:06'),
(5, 1, 7, 4, 3, 3, '2026-02-27 10:00:00', '10:00:00', 'confirmed', 800.00, 'Charlie', 'Dog', 'BOOKED', 'SUCCESS', '2026-02-23 14:00:06'),
(6, 1, 1, 1, 1, 1, '2026-01-15 10:00:00', '10:00:00', 'completed', 500.00, 'Bruno', 'Dog', 'COMPLETED', 'SUCCESS', '2026-02-23 14:00:06'),
(7, 2, 4, 8, 2, 2, '2026-01-20 15:00:00', '15:00:00', 'completed', 600.00, 'Luna', 'Cat', 'COMPLETED', 'SUCCESS', '2026-02-23 14:00:06'),
(8, 1, 6, 5, 2, 2, '2026-01-25 11:00:00', '11:00:00', 'completed', 700.00, 'Coco', 'Dog', 'COMPLETED', 'SUCCESS', '2026-02-23 14:00:06'),
(9, 2, 8, 10, 1, 1, '2026-02-01 09:00:00', '09:00:00', 'completed', 1800.00, 'Bella', 'Cat', 'COMPLETED', 'SUCCESS', '2026-02-23 14:00:06'),
(10, 1, 5, 3, 3, 3, '2026-02-10 16:00:00', '16:00:00', 'cancelled', 300.00, 'Max', 'Dog', 'CANCELLED', 'FAILED', '2026-02-23 14:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `spa_profiles`
--

CREATE TABLE `spa_profiles` (
  `spa_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `spa_name` varchar(255) DEFAULT NULL,
  `services_offered` text DEFAULT NULL,
  `upi_id` varchar(100) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 0.0,
  `total_reviews` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spa_profiles`
--

INSERT INTO `spa_profiles` (`spa_id`, `user_id`, `spa_name`, `services_offered`, `upi_id`, `rating`, `total_reviews`) VALUES
(1, 9, 'Pawsome Spa', 'Bathing, Grooming, Hair Cut, Nail Trimming', 'suresh@upi', 4.5, 12),
(2, 10, 'Happy Tails Spa', 'Bathing, De-shedding, Teeth Cleaning, Flea Treatment', 'deepa@upi', 4.2, 8);

-- --------------------------------------------------------

--
-- Table structure for table `spa_reviews`
--

CREATE TABLE `spa_reviews` (
  `review_id` int(11) NOT NULL,
  `spa_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spa_reviews`
--

INSERT INTO `spa_reviews` (`review_id`, `spa_id`, `user_id`, `rating`, `review_text`, `created_at`) VALUES
(1, 1, 1, 5, 'Bruno loved the bath! Came back smelling amazing.', '2026-02-23 14:00:06'),
(2, 1, 2, 4, 'Rocky grooming was excellent. Took a bit long.', '2026-02-23 14:00:06'),
(3, 1, 3, 5, 'Great service and friendly staff at Pawsome Spa.', '2026-02-23 14:00:06'),
(4, 2, 1, 4, 'Whiskers was handled gently. Good cat spa!', '2026-02-23 14:00:06'),
(5, 2, 2, 5, 'Luna enjoyed the de-shedding treatment.', '2026-02-23 14:00:06'),
(6, 2, 3, 4, 'Teeth cleaning was thorough. Will visit again.', '2026-02-23 14:00:06'),
(7, 1, 1, 5, 'Nail trimming was quick and painless for Bruno.', '2026-02-23 14:00:06'),
(8, 1, 2, 3, 'Had to wait 20 mins past appointment time.', '2026-02-23 14:00:06'),
(9, 2, 3, 5, 'Spa package was worth every rupee!', '2026-02-23 14:00:06'),
(10, 2, 1, 4, 'Ear cleaning was gentle. Staff is professional.', '2026-02-23 14:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `spa_services`
--

CREATE TABLE `spa_services` (
  `service_id` int(11) NOT NULL,
  `spa_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT 60,
  `duration` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spa_services`
--

INSERT INTO `spa_services` (`service_id`, `spa_id`, `service_name`, `description`, `duration_minutes`, `duration`, `price`, `created_at`) VALUES
(1, 1, 'Full Bath', 'Complete bathing with shampoo & conditioner', 45, '45 min', 500.00, '2026-02-23 14:00:06'),
(2, 1, 'Grooming Deluxe', 'Full body grooming with styling', 90, '90 min', 1200.00, '2026-02-23 14:00:06'),
(3, 1, 'Nail Trimming', 'Safe nail clipping and filing', 20, '20 min', 300.00, '2026-02-23 14:00:06'),
(4, 1, 'Hair Cut', 'Breed-specific hair styling', 60, '60 min', 800.00, '2026-02-23 14:00:06'),
(5, 1, 'Flea Treatment', 'Anti-flea bath and prevention spray', 40, '40 min', 700.00, '2026-02-23 14:00:06'),
(6, 2, 'Basic Bath', 'Quick bath with premium shampoo', 30, '30 min', 400.00, '2026-02-23 14:00:06'),
(7, 2, 'De-shedding', 'Deep coat de-shedding treatment', 60, '60 min', 900.00, '2026-02-23 14:00:06'),
(8, 2, 'Teeth Cleaning', 'Oral hygiene and breath freshening', 30, '30 min', 600.00, '2026-02-23 14:00:06'),
(9, 2, 'Ear Cleaning', 'Gentle ear cleaning and inspection', 15, '15 min', 250.00, '2026-02-23 14:00:06'),
(10, 2, 'Spa Package', 'Bath + grooming + nail trim combo', 120, '120 min', 1800.00, '2026-02-23 14:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `typing_status`
--

CREATE TABLE `typing_status` (
  `user_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `is_typing` tinyint(1) DEFAULT 0,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `typing_status`
--

INSERT INTO `typing_status` (`user_id`, `recipient_id`, `is_typing`, `updated_at`) VALUES
(1, 4, 0, '2026-02-23 19:30:06'),
(1, 5, 0, '2026-02-23 19:30:06'),
(2, 4, 0, '2026-02-23 19:30:06'),
(2, 5, 0, '2026-02-23 19:30:06'),
(3, 6, 0, '2026-02-23 19:30:06'),
(4, 1, 0, '2026-02-23 19:30:06'),
(4, 2, 0, '2026-02-23 19:30:06'),
(5, 1, 0, '2026-02-23 19:30:06'),
(5, 2, 0, '2026-02-23 19:30:06'),
(6, 3, 0, '2026-02-23 19:30:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('BUYER','SELLER','DOCTOR','SPA_OWNER','ADMIN') NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `is_online` tinyint(1) DEFAULT 0,
  `last_seen` datetime DEFAULT NULL,
  `fcm_token` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_otp` varchar(6) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `latitude` double DEFAULT 0,
  `longitude` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `profile_image`, `address`, `city`, `state`, `pincode`, `is_verified`, `is_online`, `last_seen`, `fcm_token`, `created_at`, `reset_otp`, `otp_expires_at`, `latitude`, `longitude`) VALUES
(1, 'Ananya Sharma', 'ananya@test.com', '9876543210', 'BuyerPass1', 'BUYER', 'uploads/roles/buyer/user_1.jpg', '12, Anna Nagar, Chennai', NULL, NULL, NULL, 0, 0, NULL, 'dagLO5pxSuK-49r8sYPKME:APA91bFhJqomluX_8YGS1T_893jA3itXouHK5K8BBIvf3c9MuUtRK6JkezUm22XIC4D6yGpUBU_V_cxLciKfJOEDQTjDxw6Gk-L_mkWJRRmW8bEL359sETc', '2026-02-23 14:00:06', NULL, NULL, 12.7955, 79.9558),
(2, 'Rohan Mehta', 'rohan@test.com', '9876543211', 'BuyerPass2', 'BUYER', 'uploads/roles/buyer/user_2.jpg', '45, T. Nagar, Chennai', NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-02-23 14:00:06', NULL, NULL, 12.8831, 80.1),
(3, 'Priya Krishnan', 'priya@test.com', '9876543212', 'BuyerPass3', 'BUYER', 'uploads/roles/buyer/user_3.jpg', '78, Adyar, Chennai', NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-02-23 14:00:06', NULL, NULL, 12.7746, 80.0849),
(4, 'Vikram Pets', 'vikram@test.com', '9876543213', 'SellerPass1', 'SELLER', 'uploads/roles/seller/user_4.jpg', '23, Porur, Chennai', NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-02-23 14:00:06', NULL, NULL, 12.8709, 79.9889),
(5, 'Lakshmi Animals', 'lakshmi@test.com', '9876543214', 'SellerPass2', 'SELLER', 'uploads/roles/seller/user_5.jpg', '45, MG Road, Bangalore', NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-02-23 14:00:06', NULL, NULL, 12.9716, 77.5946),
(6, 'Karthik Store', 'karthik@test.com', '9876543215', 'SellerPass3', 'SELLER', 'uploads/roles/seller/user_6.jpg', '23, Bandra West, Mumbai', NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-02-23 14:00:06', NULL, NULL, 19.076, 72.8777),
(7, 'Dr. Ramesh Kumar', 'ramesh@test.com', '9876543216', 'DoctorPass1', 'DOCTOR', 'uploads/roles/doctor/user_7.jpg', '34, Vadapalani, Chennai', NULL, NULL, NULL, 0, 0, NULL, 'dagLO5pxSuK-49r8sYPKME:APA91bFhJqomluX_8YGS1T_893jA3itXouHK5K8BBIvf3c9MuUtRK6JkezUm22XIC4D6yGpUBU_V_cxLciKfJOEDQTjDxw6Gk-L_mkWJRRmW8bEL359sETc', '2026-02-23 14:00:06', NULL, NULL, 12.8268, 80.0066),
(8, 'Dr. Meera Nair', 'meera@test.com', '9876543217', 'DoctorPass2', 'DOCTOR', 'uploads/roles/doctor/user_8.jpg', '67, Marina Beach Road, Chennai', NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-02-23 14:00:06', NULL, NULL, 13.0827, 80.2707),
(9, 'Suresh Iyer', 'suresh@test.com', '9876543218', 'SpaPass1', 'SPA_OWNER', 'uploads/roles/spa_owner/user_9.jpg', '90, Nungambakkam, Chennai', NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-02-23 14:00:06', NULL, NULL, 12.7852, 80.0556),
(10, 'Deepa Rajan', 'deepa@test.com', '9876543219', 'SpaPass2', 'SPA_OWNER', 'uploads/roles/spa_owner/user_10.jpg', '11, RS Puram, Coimbatore', NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-02-23 14:00:06', NULL, NULL, 11.0168, 76.9558);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_breed_detection`
--
ALTER TABLE `ai_breed_detection`
  ADD PRIMARY KEY (`detection_id`),
  ADD KEY `fk_ai_user` (`user_id`);

--
-- Indexes for table `buyer_profiles`
--
ALTER TABLE `buyer_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `fk_buyer_user` (`user_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`certificate_id`),
  ADD KEY `fk_certificate_pet` (`pet_id`),
  ADD KEY `fk_certificate_doctor` (`issued_by`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `fk_chat_sender` (`sender_id`),
  ADD KEY `fk_chat_receiver` (`receiver_id`),
  ADD KEY `idx_conversation` (`sender_id`,`receiver_id`,`timestamp`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_unread` (`receiver_id`,`is_read`),
  ADD KEY `idx_latest_message` (`sender_id`,`receiver_id`,`timestamp`),
  ADD KEY `idx_unread_count` (`receiver_id`,`is_read`,`timestamp`);

--
-- Indexes for table `doctor_appointments`
--
ALTER TABLE `doctor_appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `fk_appointment_pet` (`pet_id`),
  ADD KEY `fk_appointment_doctor` (`doctor_id`),
  ADD KEY `fk_appointment_buyer` (`buyer_id`);

--
-- Indexes for table `doctor_profiles`
--
ALTER TABLE `doctor_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `fk_doctor_user` (`user_id`);

--
-- Indexes for table `doctor_services`
--
ALTER TABLE `doctor_services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `fk_doctor_service_owner` (`doctor_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_notification_user` (`user_id`),
  ADD KEY `idx_user_unread` (`user_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`pet_id`),
  ADD KEY `fk_pet_seller` (`seller_id`),
  ADD KEY `idx_pet_species` (`species`),
  ADD KEY `idx_pet_breed` (`breed`);

--
-- Indexes for table `pet_images`
--
ALTER TABLE `pet_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `fk_pet_image` (`pet_id`);

--
-- Indexes for table `pet_transactions`
--
ALTER TABLE `pet_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `fk_transaction_pet` (`pet_id`),
  ADD KEY `fk_transaction_buyer` (`buyer_id`),
  ADD KEY `fk_transaction_seller` (`seller_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `fk_review_reviewer` (`reviewer_id`),
  ADD KEY `fk_review_target` (`target_user_id`);

--
-- Indexes for table `saved_pets`
--
ALTER TABLE `saved_pets`
  ADD PRIMARY KEY (`saved_id`),
  ADD UNIQUE KEY `unique_saved_pet` (`buyer_id`,`pet_id`),
  ADD KEY `idx_buyer_saved` (`buyer_id`),
  ADD KEY `idx_pet_saved` (`pet_id`);

--
-- Indexes for table `seller_profiles`
--
ALTER TABLE `seller_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `fk_seller_user` (`user_id`);

--
-- Indexes for table `spa_bookings`
--
ALTER TABLE `spa_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_spa_booking_pet` (`pet_id`),
  ADD KEY `fk_spa_booking_service` (`service_id`),
  ADD KEY `fk_spa_booking_buyer` (`buyer_id`),
  ADD KEY `fk_booking_spa` (`spa_id`);

--
-- Indexes for table `spa_profiles`
--
ALTER TABLE `spa_profiles`
  ADD PRIMARY KEY (`spa_id`),
  ADD KEY `fk_spa_user` (`user_id`);

--
-- Indexes for table `spa_reviews`
--
ALTER TABLE `spa_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `fk_review_spa` (`spa_id`),
  ADD KEY `fk_review_user` (`user_id`);

--
-- Indexes for table `spa_services`
--
ALTER TABLE `spa_services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `fk_spa_owner` (`spa_id`);

--
-- Indexes for table `typing_status`
--
ALTER TABLE `typing_status`
  ADD PRIMARY KEY (`user_id`,`recipient_id`),
  ADD KEY `fk_typing_user` (`user_id`),
  ADD KEY `fk_typing_recipient` (`recipient_id`),
  ADD KEY `idx_updated` (`updated_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_breed_detection`
--
ALTER TABLE `ai_breed_detection`
  MODIFY `detection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `buyer_profiles`
--
ALTER TABLE `buyer_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `certificate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `doctor_appointments`
--
ALTER TABLE `doctor_appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `doctor_profiles`
--
ALTER TABLE `doctor_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `doctor_services`
--
ALTER TABLE `doctor_services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `pet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pet_images`
--
ALTER TABLE `pet_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pet_transactions`
--
ALTER TABLE `pet_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `saved_pets`
--
ALTER TABLE `saved_pets`
  MODIFY `saved_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `seller_profiles`
--
ALTER TABLE `seller_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `spa_bookings`
--
ALTER TABLE `spa_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `spa_profiles`
--
ALTER TABLE `spa_profiles`
  MODIFY `spa_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `spa_reviews`
--
ALTER TABLE `spa_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `spa_services`
--
ALTER TABLE `spa_services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_breed_detection`
--
ALTER TABLE `ai_breed_detection`
  ADD CONSTRAINT `fk_ai_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `buyer_profiles`
--
ALTER TABLE `buyer_profiles`
  ADD CONSTRAINT `fk_buyer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `fk_certificate_doctor` FOREIGN KEY (`issued_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_certificate_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `fk_chat_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chat_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_appointments`
--
ALTER TABLE `doctor_appointments`
  ADD CONSTRAINT `fk_appointment_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_appointment_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_appointment_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE SET NULL;

--
-- Constraints for table `doctor_profiles`
--
ALTER TABLE `doctor_profiles`
  ADD CONSTRAINT `fk_doctor_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_services`
--
ALTER TABLE `doctor_services`
  ADD CONSTRAINT `fk_doctor_service_owner` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `fk_pet_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `pet_images`
--
ALTER TABLE `pet_images`
  ADD CONSTRAINT `fk_pet_image` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE CASCADE;

--
-- Constraints for table `pet_transactions`
--
ALTER TABLE `pet_transactions`
  ADD CONSTRAINT `fk_transaction_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_transaction_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`),
  ADD CONSTRAINT `fk_transaction_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_review_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_review_target` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `saved_pets`
--
ALTER TABLE `saved_pets`
  ADD CONSTRAINT `fk_saved_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_saved_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE CASCADE;

--
-- Constraints for table `seller_profiles`
--
ALTER TABLE `seller_profiles`
  ADD CONSTRAINT `fk_seller_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `spa_bookings`
--
ALTER TABLE `spa_bookings`
  ADD CONSTRAINT `fk_booking_spa` FOREIGN KEY (`spa_id`) REFERENCES `spa_profiles` (`spa_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_spa_booking_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_spa_booking_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`),
  ADD CONSTRAINT `fk_spa_booking_service` FOREIGN KEY (`service_id`) REFERENCES `spa_services` (`service_id`);

--
-- Constraints for table `spa_profiles`
--
ALTER TABLE `spa_profiles`
  ADD CONSTRAINT `fk_spa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `spa_reviews`
--
ALTER TABLE `spa_reviews`
  ADD CONSTRAINT `fk_review_spa` FOREIGN KEY (`spa_id`) REFERENCES `spa_profiles` (`spa_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `spa_services`
--
ALTER TABLE `spa_services`
  ADD CONSTRAINT `fk_spa_owner` FOREIGN KEY (`spa_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `typing_status`
--
ALTER TABLE `typing_status`
  ADD CONSTRAINT `fk_typing_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_typing_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
