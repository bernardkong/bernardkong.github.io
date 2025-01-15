-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 15, 2025 at 11:00 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hospital_referral`
--

-- --------------------------------------------------------

--
-- Table structure for table `clinics`
--

CREATE TABLE `clinics` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `clinics`
--

INSERT INTO `clinics` (`id`, `name`, `address`, `phone`, `email`, `is_active`, `created_at`) VALUES
(1, 'wtc mediclinic', 'wtc', '60126719897', 'kesc@qmed.asia', 1, '2024-12-16 11:03:05'),
(2, 'myclinic', 'myclinic', '01287878787', 'kasd@asad.xoa', 1, '2024-12-16 11:03:50'),
(3, 'Klinik IDCC', 'IDCC, Shah Alam', '0127896789', 'klinikidcc@gmail.com', 1, '2024-12-17 08:13:09');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Cardiology', 'Specializes in diagnosing and treating heart conditions and cardiovascular diseases.', 'active', '2024-12-16 09:09:24', '2024-12-16 09:09:24'),
(2, 'Neurology', 'Focuses on disorders of the nervous system, including brain and spinal cord.', 'active', '2024-12-16 09:09:24', '2024-12-16 09:09:24'),
(3, 'Orthopedics', 'Deals with conditions involving the musculoskeletal system.', 'active', '2024-12-16 09:09:24', '2024-12-16 09:09:24'),
(4, 'Pediatrics', 'Provides medical care for infants, children, and adolescents.', 'active', '2024-12-16 09:09:24', '2024-12-16 09:09:24'),
(5, 'Oncology', 'Specializes in the diagnosis and treatment of cancer.', 'active', '2024-12-16 09:09:24', '2024-12-16 09:09:24'),
(6, 'Emergency Medicine', 'Provides immediate medical attention for acute illnesses and injuries.', 'active', '2024-12-16 09:09:24', '2024-12-16 09:09:24'),
(7, 'Internal Medicine', 'Deals with the prevention, diagnosis, and treatment of adult diseases.', 'active', '2024-12-16 09:09:24', '2024-12-16 09:09:24'),
(8, 'Dermatology', 'Focuses on conditions affecting the skin, hair, and nails.', 'active', '2024-12-16 09:09:24', '2024-12-16 09:09:24'),
(9, 'Psychiatry', 'Specializes in the diagnosis, treatment, and prevention of mental disorders.', 'active', '2024-12-16 09:09:24', '2024-12-16 09:09:24'),
(11, 'Urology', 'Specializing with the diagnosis and treatment of disorders of the urinary tract in both men and women, as well as the male reproductive system.', 'active', '2024-12-17 04:35:22', '2024-12-17 04:35:22'),
(12, 'Obstetrics and Gynaecology', 'Maternity care deals with the care and support for expectant mothers and their families.', 'active', '2024-12-17 04:40:51', '2024-12-17 04:40:51'),
(13, 'Ophthalmology', 'It is the area of medicine dealing with the diagnosis and treatment of disorders of the eye.', 'active', '2024-12-17 04:41:07', '2024-12-17 04:41:07'),
(14, 'ENT, Head & Neck', 'Deal with the diagnoses, management and treatment of a wide range of diseases and conditions of the ear, nose and throat in both children and adults.', 'active', '2024-12-17 04:43:02', '2024-12-17 04:43:02'),
(15, 'Breast Surgery', 'Specializes in diagnosing and treating various breast conditions, including breast cancer.', 'active', '2024-12-17 04:52:27', '2024-12-17 04:52:27'),
(16, 'General Surgery', 'Experts in surgery and the managements of patients needing these procedures.', 'active', '2024-12-17 05:00:46', '2024-12-17 05:00:46'),
(17, 'Rheumatology', 'Deals with the investigation, diagnosis and treatment of joint, bone and connective tissue disorders such as arthritis and chronic back pain.', 'active', '2024-12-17 05:12:46', '2024-12-17 05:12:46'),
(18, 'Gastroenterology', 'Deals with gastroenterology procedures', 'active', '2024-12-17 08:07:12', '2024-12-17 08:07:12');

-- --------------------------------------------------------

--
-- Table structure for table `gp_doctors`
--

CREATE TABLE `gp_doctors` (
  `id` int NOT NULL,
  `gp_name` varchar(255) NOT NULL,
  `login` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `certification` varchar(255) NOT NULL,
  `mmc_no` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `clinic_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gp_doctors`
--

INSERT INTO `gp_doctors` (`id`, `gp_name`, `login`, `password`, `phone`, `certification`, `mmc_no`, `is_active`, `clinic_id`, `created_at`, `updated_at`) VALUES
(1, 'dr tai', 'gpdrtai', '88888888', '1231', 'Fammed', '57183', 1, 1, '2024-12-16 11:22:56', '2024-12-18 10:47:12'),
(2, 'Dr Elon Tai', 'drelon', '88888888', '+60126719897', 'GP', '101575', 1, 2, '2024-12-16 15:04:05', '2024-12-16 15:04:05'),
(3, 'Dr.Claire', 'claire', '123', '0124567890', 'Fam Med', '67890', 1, 3, '2024-12-17 08:13:42', '2024-12-17 08:13:42'),
(4, 'Dr. Bernard', 'bernard@gmail.com', 'Admin123!', '0164567890', 'Fam Med', '117897', 1, 1, '2024-12-17 08:16:31', '2024-12-17 08:16:31'),
(5, 'Dr.Haranee', 'haranee@gmail.com', 'Admin123!', '0167895678', 'Dermatology', '88888', 1, 3, '2024-12-17 08:17:08', '2024-12-17 08:17:08'),
(6, 'Dr Eric', 'dreric', 'Admin123!', '0168219785', 'Interventional Radiology', '59711', 1, 1, '2024-12-26 05:58:07', '2024-12-26 05:58:07');

-- --------------------------------------------------------

--
-- Table structure for table `gp_programs`
--

CREATE TABLE `gp_programs` (
  `program_id` int NOT NULL,
  `program_title` varchar(255) NOT NULL,
  `program_description` text,
  `program_link` varchar(255) DEFAULT NULL,
  `program_image` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gp_programs`
--

INSERT INTO `gp_programs` (`program_id`, `program_title`, `program_description`, `program_link`, `program_image`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'SJMC Cardiology Grand Round', 'Pulmonary embolism: An under recognized problem', 'https://www.youtube.com/watch?v=hYlllLcy3Zc', 'uploads/programs/676257ed22839.png', 1, '2024-12-18 05:04:45', '2024-12-18 05:27:56');

-- --------------------------------------------------------

--
-- Table structure for table `honour_points`
--

CREATE TABLE `honour_points` (
  `id` int NOT NULL,
  `gp_id` int NOT NULL,
  `points` int DEFAULT '0',
  `level` varchar(50) DEFAULT 'Bronze',
  `total_referrals` int DEFAULT '0',
  `successful_referrals` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `honour_points`
--

INSERT INTO `honour_points` (`id`, `gp_id`, `points`, `level`, `total_referrals`, `successful_referrals`, `created_at`, `updated_at`) VALUES
(1, 1, 10, 'Bronze', 10, 0, '2024-12-16 16:45:49', '2024-12-18 16:47:44'),
(2, 2, 4, 'Bronze', 4, 0, '2024-12-16 17:25:53', '2024-12-17 07:32:58'),
(3, 5, 2, 'Bronze', 2, 0, '2024-12-17 10:54:07', '2024-12-18 13:12:26'),
(4, 4, 1, 'Bronze', 1, 0, '2024-12-19 06:35:41', '2024-12-19 06:35:41');

-- --------------------------------------------------------

--
-- Table structure for table `hospital_settings`
--

CREATE TABLE `hospital_settings` (
  `id` int NOT NULL,
  `hospital_name` varchar(255) NOT NULL,
  `hospital_address` text NOT NULL,
  `hospital_phone` varchar(50) NOT NULL,
  `hospital_email` varchar(255) NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hospital_logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `hospital_settings`
--

INSERT INTO `hospital_settings` (`id`, `hospital_name`, `hospital_address`, `hospital_phone`, `hospital_email`, `updated_at`, `hospital_logo`) VALUES
(1, ' Subang Jaya Medical Centre', 'No. 1, Jalan SS 12/1A, Ss 12, 47500 Subang Jaya, Selangor,', ' 03-5639112', 'healthcare@asia1health.com', '2024-12-17 06:40:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `ic_number` varchar(14) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `name`, `date_of_birth`, `ic_number`, `contact_number`, `email`, `address`, `created_at`) VALUES
(1, 'Ahmad bin Abdullah', '1980-05-15', '800515-14-5543', '012-345-6789', 'ahmad.abdullah@email.com', 'No 123, Jalan Merdeka 1/3, Taman Merdeka, 43650 Bandar Baru Bangi, Selangor', '2024-12-16 13:31:11'),
(2, 'Siti Nurhayati binti Ibrahim', '1992-08-22', '920822-10-5566', '019-876-5432', 'siti.nur@email.com', 'No 45, Lorong Sentosa 2, Taman Sentosa, 14000 Bukit Mertajam, Penang', '2024-12-16 13:31:11'),
(3, 'Muhammad Hafiz bin Ismail', '1975-03-10', '750310-08-5789', '013-777-8899', 'hafiz.ismail@email.com', 'No 67, Jalan Harmoni 3, Taman Harmoni, 81300 Skudai, Johor', '2024-12-16 13:31:11'),
(4, 'Nurul Aina binti Hassan', '1988-11-30', '881130-06-5432', '017-555-4444', 'nurul.aina@email.com', 'No 89, Jalan Mawar 5, Taman Mawar, 25200 Kuantan, Pahang', '2024-12-16 13:31:11'),
(5, 'Lee Wei Ming', '1995-07-18', '950718-04-5673', '016-222-3333', 'weiming.lee@email.com', 'No 12, Jalan Kota 2/5, Taman Kota, 41000 Klang, Selangor', '2024-12-16 13:31:11'),
(6, 'Tan Mei Ling', '1983-01-25', '830125-14-5889', '014-999-8888', 'meiling.tan@email.com', 'No 34, Jalan Indah 7, Taman Indah, 88300 Kota Kinabalu, Sabah', '2024-12-16 13:31:11'),
(7, 'Raj Kumar a/l Subramaniam', '1970-09-05', '700905-08-5231', '012-888-7777', 'raj.kumar@email.com', 'No 56, Jalan Damai 3, Taman Damai, 31350 Ipoh, Perak', '2024-12-16 13:31:11'),
(8, 'Nur Fatimah binti Zainuddin', '1990-04-12', '900412-01-5445', '019-333-2222', 'fatimah.z@email.com', 'No 78, Lorong Bahagia 4, Taman Bahagia, 93100 Kuching, Sarawak', '2024-12-16 13:31:11'),
(9, 'Wong Kai Xin', '1978-12-08', '781208-12-5667', '017-444-5555', 'kaixin.wong@email.com', 'No 90, Jalan Setia 6, Taman Setia, 20400 Kuala Terengganu, Terengganu', '2024-12-16 13:31:11'),
(10, 'Aisha binti Mohamed', '1987-06-20', '870620-14-5221', '013-666-7777', 'aisha.mohamed@email.com', 'No 123, Jalan Perdana 8, Taman Perdana, 05100 Alor Setar, Kedah', '2024-12-16 13:31:11'),
(12, 'Tai tzyy jiun', '1987-06-24', '870624-23-5043', '12-6719 879', 'asdasd@asdjkah.com', 'asdad', '2024-12-16 14:30:07'),
(13, 'sim hui xin ', '1987-06-24', '870624-20-0111', '12-8123 781', 'lkasjdkl@kasjdklj.con', 'askldj', '2024-12-16 14:37:34'),
(14, 'Tai tzyy jiun', '1987-06-24', '870624-52-7465', '12-8172 837', '123@aslkdj.com', 'asdasd', '2024-12-16 14:45:13'),
(15, 'alksdjkla', '1987-06-24', '870624-23-5045', '12-1098 237', 'asd@123.com', 'asdasd', '2024-12-16 15:50:31'),
(17, 'asdasd', '1987-06-22', '870622-35-0444', '+60126719897', 'klasdj@m.com', 'asd', '2024-12-16 15:55:15'),
(19, 'asdjklj', '1987-06-24', '870624-12-1212', '+601267142323', 'asklj@alksjd.com', 'asd', '2024-12-16 16:54:53'),
(25, 'asdkj', '1987-06-24', '870624-23-5023', '12-3123 123', 'asdlk@m.com', 'asd', '2024-12-16 17:25:53'),
(26, 'asdask', '1987-06-24', '870624-23-3504', '12-3981 273', 'kajsdklj@aksdjl.com', 'asd', '2024-12-16 17:26:16'),
(27, 'asdasd', '1987-06-24', '870624-22-2222', '+601231231233', '123123@asd.com', 'asd', '2024-12-16 18:07:28'),
(28, 'Kev lim', '1987-06-22', '870622-12-1212', '+601231231892', 'klasjdii', 'asda', '2024-12-16 18:10:52'),
(29, 'dfgh', '2024-12-02', '345634-56-', '+60345345324', 'dfgh', 'sdfgb', '2024-12-16 19:53:46'),
(30, 'Ali bin Ahmad', '1969-05-04', '690504-30-5678', '+60122334567', 'alibinahmad11@gmail.com', 'adsfadfds', '2024-12-17 03:27:36'),
(31, 'Elon', '2000-01-01', '000101-30-1010', '+60143456789', 'elon@gmail.com', 'Seremban', '2024-12-17 07:13:39'),
(32, 'asdas', '1987-06-24', '870624-23-2212', '12-5454 545', 'a2@asd.com', 'asdasd', '2024-12-17 07:21:52'),
(33, 'asd', '1987-06-24', '870624-25-2525', '12-6178 787', 'aasd@kalsjdlk.com', 'asdasd', '2024-12-17 07:32:58'),
(35, 'Bernard', '2000-04-27', '000427-07-0631', '+60164192345', 'bernard@gmail.com', 'KL', '2024-12-17 08:11:47'),
(36, 'haranee', '1997-03-14', '970314-10-6578', '+60162341203', 'haranee@qmed.asia', 'Desa Green Service Apartments, Jalan Desa Bakti, Taman Desa, 58000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', '2024-12-17 10:59:39'),
(48, 'asdkjk', '1987-06-24', '870624-23-5403', '+60126719897', 'asd@masid.com', 'asd', '2024-12-17 19:51:46'),
(49, 'tai tzkajsd jaksdjk', '1987-06-24', '870624235043', '0126719897', 'akl@123.com', 'aksjdlkj', '2024-12-17 19:57:14'),
(50, 'asdlkka;sdkl', '1987-06-24', '870624-23-5043', '0126719897', 'aklsjdlk@123.com', 'asd', '2024-12-17 20:00:39'),
(51, 'asdad', '1987-06-24', '870624-23-5043', '01267198989', 'asd@asd.com', 'asdasd', '2024-12-17 20:08:29'),
(52, 'asdasd', '1987-06-24', '870624-23-5043', '0123123198023', 'kajsldk@asas.com', 'asdasd', '2024-12-17 20:39:59'),
(53, 'Bob', '2001-02-18', 'A789609', '+60149098877', 'bob@gmail.com', 'Australia', '2024-12-18 07:47:50'),
(55, 'huixintest', '1987-06-24', '870624-23-5403', '+601267189712', 'asd@miishda.com', 'asdasd', '2024-12-18 11:20:11'),
(56, 'Lisa', '2000-12-30', 'K134567', '+601122334455', 'lisa@gmai.com', 'USA', '2024-12-18 13:12:26'),
(57, 'PAUL', '1987-06-24', '870624-23-5245', '+601278787223', 'asd@asd.omc', 'asd', '2024-12-18 16:40:04'),
(58, 'I am groot', '1987-06-24', '870624-54-5454', '+601267187878', 'as@asd.ocm', 'asd', '2024-12-18 16:47:44'),
(59, 'Sora', '1971-02-19', 'E898989', '+60148887766', 'sora@gmail.com', '', '2024-12-19 06:35:41');

-- --------------------------------------------------------

--
-- Table structure for table `points_history`
--

CREATE TABLE `points_history` (
  `id` int NOT NULL,
  `gp_id` int NOT NULL,
  `referral_id` int NOT NULL,
  `points` int NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `referring_gp_id` int NOT NULL,
  `department_id` int NOT NULL,
  `priority_level` enum('routine','urgent','emergency') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected','completed','no_show') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `specialist_id` int DEFAULT NULL,
  `referral_letter_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path to stored referral letter file',
  `payment_mode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `clinical_history` text COLLATE utf8mb4_unicode_ci,
  `diagnosis` text COLLATE utf8mb4_unicode_ci,
  `investigation_results` text COLLATE utf8mb4_unicode_ci,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `no_show_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `patient_id`, `referring_gp_id`, `department_id`, `priority_level`, `status`, `created_at`, `updated_at`, `specialist_id`, `referral_letter_path`, `payment_mode`, `clinical_history`, `diagnosis`, `investigation_results`, `remarks`, `no_show_date`) VALUES
(1, 12, 1, 1, 'routine', 'approved', '2024-12-16 14:30:07', '2024-12-17 18:17:55', 2, NULL, '', NULL, NULL, NULL, NULL, NULL),
(2, 13, 1, 5, 'routine', 'pending', '2024-12-16 14:37:34', '2024-12-16 15:41:38', 3, NULL, '', NULL, NULL, NULL, NULL, NULL),
(3, 14, 1, 7, 'routine', 'completed', '2024-12-16 14:45:13', '2024-12-17 18:18:18', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(4, 15, 1, 7, 'routine', 'pending', '2024-12-16 15:50:31', '2024-12-16 15:50:31', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(5, 17, 2, 7, 'routine', 'pending', '2024-12-16 15:55:15', '2024-12-16 15:55:15', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(6, 19, 2, 7, 'routine', 'pending', '2024-12-16 16:54:53', '2024-12-16 16:54:53', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(10, 25, 2, 1, 'routine', 'rejected', '2024-12-16 17:25:53', '2024-12-18 04:34:10', 6, NULL, '', NULL, NULL, NULL, NULL, NULL),
(11, 26, 1, 1, 'routine', 'pending', '2024-12-16 17:26:16', '2024-12-16 17:26:16', 6, NULL, '', NULL, NULL, NULL, NULL, NULL),
(12, 27, 1, 7, 'routine', 'pending', '2024-12-16 18:07:28', '2024-12-16 18:07:28', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(13, 28, 2, 7, 'routine', 'pending', '2024-12-16 18:10:52', '2024-12-16 18:10:52', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(14, 29, 1, 7, 'urgent', 'rejected', '2024-12-16 19:53:46', '2024-12-18 04:43:08', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(15, 30, 1, 9, 'routine', 'pending', '2024-12-17 03:27:36', '2024-12-17 03:27:36', 10, NULL, '', NULL, NULL, NULL, NULL, NULL),
(16, 31, 1, 13, 'routine', 'pending', '2024-12-17 07:13:39', '2024-12-17 07:13:39', 14, NULL, '', NULL, NULL, NULL, NULL, NULL),
(17, 32, 2, 15, 'routine', 'approved', '2024-12-17 07:21:52', '2024-12-17 18:51:58', 17, NULL, '', NULL, NULL, NULL, NULL, NULL),
(18, 33, 2, 1, 'routine', 'approved', '2024-12-17 07:32:58', '2024-12-17 18:21:10', 6, NULL, '', NULL, NULL, NULL, NULL, NULL),
(19, 35, 1, 8, 'routine', 'approved', '2024-12-17 08:11:47', '2024-12-17 18:21:08', 9, NULL, '', NULL, NULL, NULL, NULL, NULL),
(20, 36, 5, 16, 'urgent', 'approved', '2024-12-17 10:59:39', '2024-12-17 18:20:56', 18, NULL, '', NULL, NULL, NULL, NULL, NULL),
(21, 12, 1, 1, 'routine', 'approved', '2024-12-16 06:30:07', '2024-12-17 10:17:55', 2, NULL, '', NULL, NULL, NULL, NULL, NULL),
(22, 13, 1, 5, 'routine', 'pending', '2024-12-16 06:37:34', '2024-12-16 07:41:38', 3, NULL, '', NULL, NULL, NULL, NULL, NULL),
(23, 14, 1, 7, 'routine', 'completed', '2024-12-16 06:45:13', '2024-12-17 10:18:18', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(24, 15, 1, 7, 'routine', 'pending', '2024-12-16 07:50:31', '2024-12-16 07:50:31', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(25, 17, 2, 7, 'routine', 'pending', '2024-12-16 07:55:15', '2024-12-16 07:55:15', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(26, 19, 2, 7, 'routine', 'pending', '2024-12-16 08:54:53', '2024-12-16 08:54:53', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(27, 25, 2, 1, 'routine', 'pending', '2024-12-16 09:25:53', '2024-12-16 09:25:53', 6, NULL, '', NULL, NULL, NULL, NULL, NULL),
(28, 26, 1, 1, 'routine', 'pending', '2024-12-16 09:26:16', '2024-12-16 09:26:16', 6, NULL, '', NULL, NULL, NULL, NULL, NULL),
(29, 27, 1, 7, 'routine', 'rejected', '2024-12-16 10:07:28', '2024-12-18 04:43:04', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(30, 28, 2, 7, 'routine', 'pending', '2024-12-16 10:10:52', '2024-12-16 10:10:52', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(31, 29, 1, 7, 'urgent', 'pending', '2024-12-16 11:53:46', '2024-12-16 11:53:46', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(32, 30, 1, 9, 'routine', 'pending', '2024-12-16 19:27:36', '2024-12-16 19:27:36', 10, NULL, '', NULL, NULL, NULL, NULL, NULL),
(33, 31, 1, 13, 'routine', 'rejected', '2024-12-16 23:13:39', '2024-12-18 07:43:00', 14, NULL, '', NULL, NULL, NULL, NULL, NULL),
(34, 32, 2, 15, 'routine', 'approved', '2024-12-16 23:21:52', '2024-12-17 10:51:58', 17, NULL, '', NULL, NULL, NULL, NULL, NULL),
(35, 33, 2, 1, 'routine', 'approved', '2024-12-16 23:32:58', '2024-12-17 10:21:10', 6, NULL, '', NULL, NULL, NULL, NULL, NULL),
(36, 35, 1, 8, 'routine', 'approved', '2024-12-17 00:11:47', '2024-12-17 10:21:08', 9, NULL, '', NULL, NULL, NULL, NULL, NULL),
(37, 36, 5, 16, 'urgent', 'approved', '2024-12-17 02:59:39', '2024-12-17 10:20:56', 18, NULL, '', NULL, NULL, NULL, NULL, NULL),
(38, 48, 1, 1, 'routine', 'pending', '2024-12-17 19:51:46', '2024-12-17 19:51:46', 6, NULL, '', NULL, NULL, NULL, NULL, NULL),
(39, 49, 1, 15, 'routine', 'rejected', '2024-12-17 19:57:14', '2024-12-18 04:43:15', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(40, 50, 1, 15, 'routine', 'rejected', '2024-12-17 20:00:39', '2024-12-18 02:01:22', 8, NULL, '', NULL, NULL, NULL, NULL, NULL),
(41, 51, 1, 5, 'routine', 'rejected', '2024-12-17 20:08:29', '2024-12-18 01:26:25', 5, NULL, '', NULL, NULL, NULL, NULL, NULL),
(42, 52, 1, 5, 'routine', 'completed', '2024-12-17 20:39:59', '2024-12-18 01:59:28', 5, NULL, '', NULL, NULL, NULL, NULL, NULL),
(43, 53, 1, 2, 'urgent', 'no_show', '2024-12-18 07:47:50', '2024-12-18 15:37:46', 2, NULL, '', NULL, NULL, NULL, NULL, NULL),
(44, 55, 1, 1, 'routine', 'pending', '2024-12-18 11:20:11', '2024-12-18 11:20:11', 6, 'uploads/referral_letters/ref_6762afeb73f15.docx', '', NULL, NULL, NULL, NULL, NULL),
(45, 56, 5, 15, 'routine', 'pending', '2024-12-18 13:12:26', '2024-12-18 13:12:26', 17, 'uploads/referral_letters/ref_6762ca3a34995.pdf', '', NULL, NULL, NULL, NULL, NULL),
(46, 57, 1, 1, 'routine', 'rejected', '2024-12-18 16:40:04', '2024-12-18 16:46:15', 22, NULL, 'self_pay', '', '', '', 'The slots is full', NULL),
(47, 58, 1, 1, 'routine', 'completed', '2024-12-18 16:47:44', '2024-12-18 16:49:28', 22, NULL, 'self_pay', '', 'heart attack', '', 'asdsad', NULL),
(48, 59, 4, 1, 'routine', 'completed', '2024-12-19 06:35:41', '2024-12-19 06:47:10', 22, NULL, 'insurance', 'Chest pain', 'TRO ACS', 'ECG ischemic changes II, III', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `referral_clinical_feedback`
--

CREATE TABLE `referral_clinical_feedback` (
  `id` int NOT NULL,
  `referral_id` int NOT NULL,
  `diagnosis` text,
  `physical_findings` text,
  `investigation` text,
  `further_plan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `referral_clinical_feedback`
--

INSERT INTO `referral_clinical_feedback` (`id`, `referral_id`, `diagnosis`, `physical_findings`, `investigation`, `further_plan`, `created_at`, `updated_at`) VALUES
(1, 47, 'Musculoskeletal pain', 'BP 120/80, PR:80 regular', 'ECG: no ischemic changes', 'to monitor if there is any new chest pain episode', '2024-12-18 17:29:19', '2024-12-19 06:25:17'),
(2, 48, 'IHD', 'BP normal', 'Echo normal', 'Cardiprin 100mg OD', '2024-12-19 06:47:45', '2024-12-19 06:47:45');

-- --------------------------------------------------------

--
-- Table structure for table `specialists`
--

CREATE TABLE `specialists` (
  `id` int NOT NULL,
  `nsr_number` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `department_id` int DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `profile` text,
  `picture` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `specialists`
--

INSERT INTO `specialists` (`id`, `nsr_number`, `name`, `department_id`, `specialization`, `email`, `phone`, `status`, `created_at`, `updated_at`, `profile`, `picture`, `password`) VALUES
(2, 'NSR/137389', 'Dr Azizi bin Abu Bakar', 2, 'Neurological Surgery', 'drazizi@hospital.com', '+60193171818', 'active', '2024-12-16 09:11:18', '2024-12-17 06:49:13', '<p>Qualification:</p><p>MBBS (UM), MS Surgery (UKM), Fellowships in Vascular &amp; Pediatric Neurosurgery</p><p>Languages:</p><p>English, Bahasa Melayu, Javanese</p><h3><strong>Procedure Focus:</strong></h3><ul><li>Arteriovenous Malformation (AVM) Repair</li><li>Brain Bypass (Stroke)</li><li>Congenital Malformation (Brain &amp; Spine)</li><li>Craniofacial Reconstruction</li><li>Fast-track Surgery</li></ul><h3><strong>Procedure Performed:</strong></h3><ul><li>Brain and Spine Tumour Removal (Biopsy, Excision)</li><li>Brain Revascularisation (Brain Bypass - Direct and Indirect)</li><li>Carotid Endarterectomy</li><li>Cranial and Craniofacial Reconstruction</li><li>CSF Diversion (Endoscopic, Shunt)</li><li>Epilepsy Lesion Removal, Vagal Nerve Stimulator Insertion</li><li>Fastrack Neurosurgery</li><li>Repair of Brain Aneurysm (Clipping, Trapping)</li><li>Repair of Brain and Spine AVM</li><li>Repair of CSF Leak</li><li>Repair of Spina Bifida Aperta and Occulta</li><li>Release of Tethered Spinal Cord</li><li>Surgery for Degenerative Spine</li><li>Surgery for Spasticity (Selective Dorsal Rhizotomy, Baclofen Pump)</li><li>Surgery for Trigeminal Neuralgia, Hemifacial Spasm</li></ul><h3><strong>Conditions Treated:</strong></h3><ul><li>Brain Abnormality at Birth and Adulthood</li><li>Brain Attack (Stroke, TIA, Brain Blood Clot, Carotid Artery Narrowing, Moyamoya Disease and Moyamoya-like Syndrome)</li><li>Brain Water Disturbances (Hydrocephalus, Syringomyelia)</li><li>Diseases of Peripheral Nerves</li><li>Epilepsy, Trigeminal Neuralgia, Hemifacial Spasm</li><li>Spina Bifida (Open Spina Bifida at Birth, Lipomeningocele, Split Spinal Cord, Tethered Spinal Cord)</li><li>Spinal Degeneration</li><li>Traumatic Injury of Brain and Spine</li><li>Tumour of Brain, Nerve and Spine (Primary and Metastatic)</li><li>Vascular Malformations of Brain and Spine (AVM, Aneurysm, Cavernoma, Fistula)</li></ul><h3><strong>Awards:</strong></h3><ul><li>Excellent Service Awards 2005, 2007 and 2010</li></ul><h3><strong>Post Graduate Qualifications:</strong></h3><ul><li>Master of Surgery (UKM)</li><li>Fellowships in Vascular &amp; Pediatric Neurosurgery &nbsp;</li><li>Neurosurgery Fellowship (Australia)</li></ul><h3><strong>Medical School:</strong></h3><ul><li>MBBS (UM)</li></ul>', 'uploads/specialists/67611ee9485bd.png', NULL),
(3, 'NSR/12347', 'Dr Ng Wuey Min', 3, 'Sports Surgery, Arthroscopy, Orthopaedic Surgery', 'ng.wueymin@hospital.com', '+60193171818', 'active', '2024-12-16 09:11:18', '2024-12-17 06:58:39', '<p>Qualification:</p><p>MBBS (Mal), Master in Orthoapedic (UM)</p><p>Languages:</p><p>English, Bahasa Melayu, Chinese (Mandarin, Cantonese)</p><h3>Procedure Focus:</h3><ul><li>Orthopaedic Trauma Knee Surgery: arthroscopic knee ligament reconstruction, meniscal repair or reconstruction, cartilage repair, uni compartment and total knee replacement, knee realignment surgery&nbsp;</li><li>Patella Surgery: patellar realignment surgery, MPFL repair and reconstruction. Trochloeplasty&nbsp;</li><li>Ankle: surgery: ankle scope, ligament repair and reconstruction. Achilles’ tendon repair and reconstruction.&nbsp;</li><li>Hip Surgery: Hip scope&nbsp;</li><li>Shoulder Surgery: Arthroscopic surgery. Shoulder Stabilisation surgery. Rotator cuff repair and Superior Capsule reconstruction. Shoulder Replacement&nbsp;</li><li>Elbow Surgery: arthroscopic surgery, ligament repair and reconstruction. &nbsp;</li></ul><h3>Condition Treated:</h3><ul><li>Hip, Knee, Ankle, Shoulder, Elbow related condition &nbsp;</li></ul><h3>Memberships &amp; Associations:</h3><ul><li>Malaysia Orthopaedic Association</li><li>Malaysia Arthroscopic Society&nbsp;</li><li>CSAMM Member&nbsp;</li><li>APOA HUL Founder Member&nbsp;</li><li>APKASS Founder Member&nbsp;</li><li>ESSKA Member</li></ul><h3>Post Graduate Qualifications:</h3><ul><li>Universiti Malaya</li></ul><h3>Clinical Training:</h3><ul><li>AO Advanced Trauma Training (USA)&nbsp;</li><li>Shoulder Fellowship in Korea&nbsp;</li><li>Shoulder &amp; Elbow Fellowship in Japan &nbsp;</li></ul><h3>Awards:</h3><ul><li>Travelling Fellowship (Aus &amp; NZ)</li></ul><h3>Special Privileges According to Hospital:</h3><ul><li>Orthopaedic trauma&nbsp;</li><li>Sport injury &amp; arthroscopic surgery &nbsp;</li></ul><h3>Medical School:</h3><p>MBBS, &nbsp;University Malaya &nbsp;<br>&nbsp;</p>', 'uploads/specialists/6761211f0e0c6.jpg', NULL),
(4, 'NSR/124428', 'Dr Chan Lee Lee', 4, 'Paediatric Haematology - Oncology', 'chan.leelee@hospital.com', '+60193171818', 'active', '2024-12-16 09:11:18', '2024-12-17 06:51:35', '<p>Qualification:</p><p>MBBS (UM), DCH (London), MRCP (UK), FRCP (Edin)</p><p>Languages:</p><p>English, Bahasa Melayu, Chinese (Mandarin, Cantonese, Hokkien)</p><h2>Profile</h2><p>&nbsp;</p><h3>Procedure Focus:</h3><ul><li>Bone marrow examination</li><li>Chemotherapy</li><li>Bone marrow transplantations</li></ul><h3>Condition Treated:</h3><ul><li>Paediatric blood and cancer disorders</li></ul><h3>Memberships &amp; Associations:</h3><ul><li>MPA</li></ul><h3>Post Graduate Qualifications:</h3><ul><li>DCH(London), MRCP (UK), FRCP(Edin).</li></ul><h3>Clinical Training:</h3><ul><li>General paediatrics</li><li>Paediatric haematology/oncology</li></ul><h3>Medical School:</h3><p>University of Malaya</p><h3>Media / Publications:</h3><ul><li><a href=\"https://subangjayamedicalcentre.com/blog-content/reviewing-the-attitudes-towards-child-cancers\">Reviewing the attitudes towards child cancers</a><br>&nbsp;</li></ul>', 'uploads/specialists/67611f77ee6d2.jpg', NULL),
(5, 'NSR/12349', 'Dr Matin Mellor Abdullah', 5, 'Oncology', 'matin.mellor@hospital.com', '+60193171818', 'active', '2024-12-16 09:11:18', '2024-12-17 06:57:37', '<p>Qualification:</p><p>FFRRCS (Ireland) AM (Mal)</p><p>Languages:</p><p>English, Bahasa Melayu, Bidayuh</p><h3>Procedure Focus:</h3><ul><li>Cancer treatment, Image guided radiation therapy (IGRT)</li><li>Intensity modulated radiation therapy (IMRT)</li><li>Radiation therapy, Stereotactic radiation therapy (SRT)</li></ul><h3>Condition Treated:</h3><p>Breast cancer, Cancer, Terminal illnesses, Tumors</p><h3>Post Graduate Clinical Training:</h3><ul><li>House Officer Training in Obstetric &amp; Gynaecology - Universiti Hospital, Kuala Lumpur</li><li>Internal Medicine, Universiti Hospital, Kuala Lumpur</li><li>Clinical Oncology &amp; Radiotherapy Training in Hospital Kuala Lumpur, Malaysia</li><li>Clinical Oncology Training in Queen Elizabeth Hospital, Birmingham, UK 1992-1996</li><li>Lecturer and Specialist in Radiotherapy and Oncology, University Hospital Kuala Lumpur 1996-1999</li><li>Yayasan Sarawak Scholarship to read Medicine in Universiti Malaya, Kuala Lumpur</li><li>Brachytherapy Attachment- Daniel den Hoed Cancer Centre, Rotterdam, Holland</li><li>Stereotactic Radiotherapy Training - Prince of Wales Hospital, Sydney, Australia</li></ul><h3>Board Certification:</h3><p>Ireland, FFRRCS (Ireland) 1994Malaysia, AM (Mal)</p><h3>Medical School:</h3><p>MBBS (Mal), University Malaya 1984&nbsp;</p><h3>Media / Publications:</h3><ul><li><a href=\"https://www.bfm.my/podcast/the-bigger-picture/health-and-living/how-lung-cancer-treatment-has-changed\">How Lung Cancer Treatment Has Changed - BFM Podcast</a></li><li><a href=\"https://easily.sinchew.com.my/node/2754/%E9%BC%BB%E5%92%BD%E7%99%8C-%E5%A4%A7%E9%A9%AC%E5%8D%8E%E8%A3%94%E5%85%A8%E7%90%83%E5%B1%85%E5%86%A0\">Nasopharyngeal cancer ranks first among Malaysian Chinese&nbsp;</a><br>&nbsp;</li></ul>', 'uploads/specialists/676120e1f0c09.jpg', NULL),
(6, 'NSR/125395', 'Dr Ahmad Nizar Jamaluddin', 1, 'Cardiac ', 'ahmad.nizar@hospital.com', '+60193171818', 'active', '2024-12-16 09:11:18', '2024-12-17 06:48:34', '<p>Qualification:</p><p>MD (UKM), MRCP (UK), FACC (USA)</p><p>Languages:</p><p>English, Bahasa Melayu</p><h3>Core Speciality:</h3><p>Electrophysiology</p><h3>Procedure Focus:</h3><ul><li>Angioplasty</li><li>CT Coronary Angiogram</li><li>CT Coronary Angiography</li><li>Cardiac Electrophysiology</li><li>Cardiac Pacing</li><li>Cardiac Resynchronization Therapy (CRT)</li><li>Catheter Ablation</li><li>Dual Chamber Cardiac Pacing</li><li>Implantable Cardioverter Defibrillator (ICD) Therapy</li></ul><h3>Condition Treated:</h3><p>Atrial Fibrillation (AF), Cardiac Arrest, Coronary Heart Disease (CHD), Heart Disorders</p><h3>Memberships &amp; Associations:</h3><ul><li>Council Member, National Heart Association of Malaysia, 1992-2010</li><li>Chairperson, Cardiology Credential Committee Academy of Medicine Malaysia, 2006-2009</li><li>President, National Heart Association of Malaysia, 2004</li></ul><h3>Post Graduate Clinical Training:</h3><ul><li>Fellow, American College of Cardiology, 2004,</li><li>Cardiology Fellowship, Institute Jantung Negara Kuala Lumpur, 1991 - 94,</li><li>MRCP (UK), Member, Royal College of Physicians of the United Kingdom, 1991,</li><li>Fellow, National Heart Association of Malaysia,</li><li>Fellow, Asean College of Cardiology</li></ul><h3>Board Certification:</h3><p>Cardiology, American College of Cardiology (ACC), United States, 2004, Royal College of Physicians, United Kingdom, MRCP (UK)</p><h3>Awards:</h3><p>Young Investigator Award, 2nd National Heart Association Scientific Meeting , 1996, Young Investigator Award, 9th Asean Congress of Cardiology , 1992, Asian Development Bank Scholarship for Cardiology , 1992</p><h3>Medical School:</h3><p>M.D. University Kebangsaan, Malaysia, 1986&nbsp;<br>&nbsp;</p>', 'uploads/specialists/67611ec258f80.jpg', NULL),
(8, 'NSR/123848', 'Dr A. Raveendhran', 7, 'Internal Medicine', 'a.raveendhran@hospital.com', '+60193171818', 'active', '2024-12-16 09:11:18', '2024-12-17 06:47:12', '<p>Qualification:</p><p>MBBS (Mal), MRCP (UK), MRCP (Ire), Dip Genito-Urinary Med (Lond)</p><p>Languages:</p><p>English, Bahasa Melayu, Tamil</p><h3>Condition Treated:</h3><p>Family Illnesses and Diseases, Lifestyle Diseases</p><h3>Memberships &amp; Associations:</h3><p>Life Member, Malaysian Medical Association</p><h3>Post Graduate Clinical Training:</h3><ul><li>Advance Course in Infectious Diseases, Singapore, 1992</li><li>Diplomate in Genito-Urinary Medicine, Society of Apothecaries of London, 1988</li><li>Postgraduate Course in Sexually Transmitted Diseases, Central Middlesex Hospital London and University College Hospital London, 1987</li><li>Postgraduate Venereology Trainee/Fellow, Central Middlesex Hospital London and University College Hospital London, 1987</li><li>Advance Course in Tuberculosis, National Tuberculosis Centre, Kuala Lumpur, 1986</li><li>Advance Course in Medicine, Whittington Hospital, London, 1984-85</li><li>Postgraduate Course in Neurology, Institute of Neurology, Queen Square, London, 1984,</li><li>Honorary Senior House Officer and Research Fellow, Medical Department, King\'s College Hospital and Medical School, London, 1983 - 85</li></ul><h3>Board Certification:</h3><p>Internal Medicine - Ireland, 1985, Internal Medicine - United Kingdom, 1985</p><h3>Medical School:</h3><p>MBBS, University of Malaya, Malaysia, 1977&nbsp;</p>', 'uploads/specialists/67611e70401a6.jpg', NULL),
(9, 'NSR/128313', 'Dr Ch\'ng Chin Chwen', 8, 'Dermatology', 'chinchwen@hospital.com', '+60193171818', 'active', '2024-12-16 09:11:18', '2024-12-17 06:50:39', '<p>Qualification:</p><p>MBBS (UM), MRCP (UK), AdvM Derm (UKM), FAADV</p><p>Languages:</p><p>English, Bahasa Melayu, Chinese (Mandarin, Cantonese, Hokkien)</p><h3>Procedure Focus:</h3><ul><li>Allergic Testing</li><li>Acne Extractions</li><li>Aesthetic Enhancement (Botulinum Toxin and Filler Injection)</li><li>Chemical Peeling</li><li>Cryosurgery</li><li>Electrosurgery</li><li>Excisions of lumps and bumps</li><li>Intralesional Injections</li><li>Laser Treatment (Face &amp; Body)</li><li>Scar Reduction Procedures</li></ul><h3>Condition Treated:</h3><ul><li>Acne &amp; Rosacea</li><li>Eczema and Allergy</li><li>Hair and Nail Disorder</li><li>Hyperhidrosis</li><li>Psoriasis</li><li>Scar &amp; Keloids</li><li>Skin Growths and Cancer</li><li>Skin Infections</li><li>Skin Pigmentary Disorder</li><li>Urticaria</li></ul><h3>Memberships &amp; Associations:</h3><ul><li>Malaysian Medical Association (Life Member Since 2006)</li><li>Dermatological Society of Malaysia (Member Since 2015)</li><li>Debra Malaysia, (Honorary Treasurer 2013 - 2023)</li><li>Academy of Medicine (Member Since 2016)</li><li>Dermatology Specialty Subcommittee for National Specialist Register Malaysia, (Member 2015-2018)</li><li>Malaysian Urticaria Expert Group (Advisor 2017)</li><li>Malaysian Clinical Practice Guideline for Management of Atopic Eczema (Development Group Member, 2016-2018</li><li>External Viva Examiner for MSc (Healthy Aging Medical Aesthetic and Regenerative Medicine) UCSI, 2017-2018</li><li>Viva Examiner for Letter of Credentialing and Privileging of Aesthetic Medical Practice for Chapter 1, 2023 - 2024</li></ul><h3>Post Graduate Qualifications:</h3><ul><li>Membership of Royal College of Physicians (London)</li><li>Advanced Master in Dermatology (UKM)&nbsp;</li><li>Fellowship in Dermatologic Surgery (Mahidol University)</li></ul><h3>Clinical Training:</h3><ul><li>General Medicine Training, Ministry of Health, 2006-2008</li><li>Internal Medicine Training, Ministry of Health, 2008-2010</li><li>Clinical Dermatology, University Malaya, 2010-2011</li><li>Advanced Master In Dermatology, University Kebangsaan Malaysia, 2011-2014</li><li>Fellowship In Dermatologic Laser Surgery, Mahidol University, 2017</li></ul><h3>Awards:</h3><ul><li>Alumni Association of King Edward VII College of Medicine and The Faculty of Medicine, Universiti Malaya and Singapore, 2006</li><li>Book Prize from Malaysian General Medical College, 2006</li><li>Gold Medal of Dr S.G Rajahram, UM</li><li>Gold Medal and Book Prize from Penang Medical Practitioners Society</li><li>Overall Outstanding Medical Student of The Year, University Malaya, 2006</li></ul><h3>Medical School:</h3><p>University Malaya</p><h3>Media / Publications:</h3><ul><li><a href=\"https://www.instagram.com/cccskindoc/?hl=en\">Dr Ch\'ng Chin Chwen\'s Instagram</a></li><li><a href=\"https://subangjayamedicalcentre.com/blog-content/dr-chng-chin-chwens-article\">全身多汗很尴尬影响生活和人际关系 | Chinese Article</a></li><li><a href=\"https://rzcognizance.com/article/%E8%AF%AF%E8%A7%A3%E6%9C%80%E6%B7%B1%E7%9A%84%E9%93%B6%E5%B1%91%E7%97%85\">误解最深的银屑病 | rzcognizance.com | Chinese Article</a></li><li><a href=\"https://www.chinapress.com.my/20210115/%E3%80%90%E9%A1%BE%E5%90%8D%E6%80%9D%E5%8C%BB%E3%80%91%E4%B8%8D%E4%BB%85%E6%98%AF%E7%9A%AE%E8%82%A4%E7%96%BE%E7%97%85-%E7%89%9B%E7%9A%AE%E7%99%A3%E5%9B%B0%E6%89%B0%E8%BA%AB%E5%BF%83\">(顾名思医) 不仅是皮肤疾病 牛皮癣困扰身心 | chinapress.com.my | Chinese Article</a></li><li><a href=\"https://feminine.com.my/info/%e7%99%bd%e8%89%b2%e5%bc%ba%e4%ba%ba1-%e5%ba%84%e6%b2%81%e7%ba%af%c2%b7%e7%be%8e%e4%b8%bd%e6%95%91%e6%98%9f/\">[白色强人1] 庄沁纯·美丽救星 | feminine.com.my | Chinese Article</a></li><li><a href=\"https://feminine.com.my/my-wellness/health-care/%E4%BF%9D%E5%81%A5%E4%B8%93%E9%A2%98-%E5%8B%BF%E8%BD%BB%E8%A7%86%E7%9A%AE%E8%82%A4%E4%B8%8A%E7%9A%84%E4%B8%80%E9%A2%97%E7%97%A3-%E5%AE%83%E9%9A%8F%E6%97%B6%E4%BC%9A%E7%99%8C%E5%8F%98\">(保健专题) 勿轻视皮肤上的一颗痣 它随时会癌变! | feminine.com.my | Chinese Article</a></li><li><a href=\"https://easily.sinchew.com.my/node/840/%E5%8D%B3%E4%BD%BF%E4%B8%8D%E9%9D%92%E6%98%A5-%E4%B9%9F%E4%BC%9A%E9%95%BF%E7%97%98%E7%97%98-%E4%BD%8E%E7%B3%96%E9%A5%AE%E9%A3%9F%E5%8A%A9%E9%99%A4%E7%97%98\">即使不青春 也会长痘痘 低糖饮食助除痘 | easily.sinchew.com.my | Chinese Article</a></li><li><a href=\"https://c.cari.com.my/portal.php?mod=view&amp;aid=101598\">癣和湿疹你分得出吗？搞错症用错药恐会恶化 | C.Cari.com.my | Chinese Article</a></li><li><a href=\"https://fb.watch/vTSDy5VnCI/\">牛皮癣会引发可怕并发症 | 988 FM&nbsp;</a></li><li><a href=\"https://fb.watch/vTSD-K2TX3/\">一颗痣也会癌变 | 988 FM</a></li><li><a href=\"https://fb.watch/vTSFl8IQfr/\">湿疹反反复复，怎么办？ | 988 FM</a></li><li><a href=\"https://fb.watch/vTSGsiZ1C6/\">曾经让人听了就怕，误解也大的麻风病，消失了吗？| 988 FM</a></li></ul>', 'uploads/specialists/67611f3f55572.png', NULL),
(10, 'NSR/127235', 'Dr Toh Chin Lee', 9, 'Child & Adolescent Psychiatry', 'toh.chinlee@hospital.com', '+60193171818', 'active', '2024-12-16 09:11:18', '2024-12-17 07:07:27', '<p>Qualification:</p><p>MBBS (UM), MPM (UM), FCP (Australia)</p><p>Languages:</p><p>English, Bahasa Melayu, Chinese (Cantonese, Hokkien)</p><h3>Core Speciality:</h3><p>Child and Adolescent Psychiatry</p><h3>Procedure Focus:</h3><ul><li>CBT</li><li>Family Therapy</li><li>Group Therapy</li><li>Play Therapy</li><li>Psychotherapy</li></ul><h3>Condition Treated:</h3><ul><li>Autism Spectrum Disorder</li><li>Attention Deficit Hyperactivity Disorder</li><li>Anxiety Disorder</li><li>Bipolar Disorder</li><li>Conduct Disorder</li><li>Dysthymia</li><li>Major Depression</li><li>OCD</li><li>Phobias</li><li>Schizophrenia</li><li>Separation Anxiety</li></ul><h3>Memberships &amp; Associations:</h3><ul><li>Malaysian Psychiatric Association</li><li>Malaysian Child And Adolescent Psychiatry Association</li><li>Asian Society Of Child And Adolescent Psychiatry And Allied Profession</li><li>Persatuan Kebajikan Pendidikan Psikiatri Hospital Selayang</li></ul><h3>Post Graduate Clinical Training:</h3><ul><li>University Hospital, Kuala Lumpur</li><li>Kuala Lumpur Hospital</li><li>Royal Children’s Hospital, Melbourne</li></ul><h3>Medical School:</h3><p>Not Relevant</p><h3>Awards:</h3><p>Kesatria Mangku Negara</p>', 'uploads/specialists/6761232f2fb8c.jpg', NULL),
(12, 'NSR/12333', 'Dr Mohd Rusdi Abdullah', 3, 'Sports Surgery, Arthroscopy, Orthopaedic Surgery', 'rusdi@gmail.com', '+60193171818', 'active', '2024-12-17 04:06:00', '2024-12-17 06:58:10', '<h3>Procedure Focus:</h3><ul><li>Sports injury treatment &amp; surgery</li><li>Trauma treatment &amp; surgery</li><li>Arthroscopy &amp; Minimal invasive surgery</li><li>Fracture treatment &amp; fixation</li><li>Injection into joints and soft tissue</li><li>Knee ACL, PCL, &amp; Multiligaments Surgeries</li><li>Meniscus &amp; Cartilage Procedures</li><li>Shoulder stabilization, Rotator Cuff Repair &amp; Subacromial Decompression</li><li>Joints Stabilization Procedures</li><li>Soft Tissues, Muscle, Tendons &amp; Ligaments Procedures</li><li>Radiofrequency Ablation (RFA)&nbsp;</li><li>Joint Replacement Surgery</li><li>Wound Management</li><li>Removal of Cyst</li><li>Tendonitis &amp; Ligament sprained treatment</li></ul><h3>Condition Treated:</h3><ul><li>Sports injuries</li><li>Trauma, fracture and dislocation</li><li>Bone injuries &amp; disorders</li><li>Joint injuries &amp; disorders</li><li>Knee Anterior Cruciate Ligament (ACL) &amp; Multiligaments injuries</li><li>Meniscus &amp; Cartilage injuries</li><li>Shoulder instability, rotator cuff injury &amp; impingement</li><li>Muscle, Ligament, Tendon injuries &amp; disorders</li><li>Osteoarthritis</li><li>Cystic Lesion</li></ul><h3>Memberships &amp; Associations:</h3><ul><li>Malaysian Orthopedic Association (MOA)</li><li>Malaysian Arthroscopy Society (MAS)</li><li>International Society of Arthroscopy, Knee Surgery and Orthopedic Sports Medicine (ISAKOS)</li><li>European Society for Sports Traumatology, Knee Surgery &amp; Arthroscopy (ESSKA)</li><li>Asia Pacific Knee, Arthroscopy &amp; Sports Medicine Society (APKASS)</li><li>AO trauma</li></ul><h3>Post Graduate Qualifications:</h3><ul><li>Masters in Orthopedic Surgery, Universiti Kebangsaan Malaysia (UKM)</li><li>Fellowship Training in Arthroscopy &amp; Sports Surgery (MOH)</li><li>Fellowship in Orthopedic Sports Medicine, Technical University of Munich (TUM), Germany</li></ul><h3>Clinical Training:</h3><ul><li>Fellowship in Arthroscopy &amp; Knee Surgery, Instituto Ortopedico Rizzoli, Bologna, Italy</li><li>Fellowship in AO Trauma Surgery, BIDMC- Harvard Medical School, Massachusetts, USA</li><li>AO Trauma / Reconstruction Course, Gold Coast, Australia</li><li>AO Trauma Advanced Principles of Fracture Management, Singapore</li><li>Shoulder Arthroscopy Course, Birmingham, United Kingdom</li><li>Rotator Cuff Masterclass, Liverpool, United Kingdom</li><li>Hip Arthroscopy Attachment, Munich, Germany</li><li>Upper Extremity Surgeon Bioskill Course, San Diego, USA</li><li>Primary &amp; Revision Total Knee Arthroplasty, Antwerp, Belgium</li><li>Primary Total Knee &amp; Hip Replacement, Hongkong</li><li>Hyalofast &amp; Cartilage Masterclass, Poland</li><li>Advanced Trauma Life Support (ATLS)</li><li>Certified Medical Impairment Assessor (CMIA) NIOSH</li><li>Fellow in Arthroscopy &amp; Sports Injury Unit, Hospital Kuala Lumpur</li></ul><h3>Special Privileges According to Hospital:&nbsp;</h3><ul><li>Multi ligament reconstruction surgery</li></ul><h3>Awards:</h3><ul><li>Masters of Orthopedic Surgery Best Student Award 2011, UKM</li><li>Civil Service Excellence Award 2016, Ministry of Health Malaysia</li></ul><h3>Medical School:</h3><p>MBBS, International Islamic University Malaysia (IIUM), 2002</p><h3>Media / Publications:</h3><ul><li><a href=\"https://subangjayamedicalcentre.com/blog-content/maintaining-a-solid-internal-structure\">Maintaining a solid internal structure</a></li><li><a href=\"https://subangjayamedicalcentre.com/blog-content/my-knee-is-locked\">My knee is locked!</a></li><li><a href=\"https://subangjayamedicalcentre.com/blog-content/when-the-damage-is-done-to-your-cartilage\">When the damage is done to your cartilage</a></li></ul>', 'uploads/specialists/676121025f051.jpg', NULL),
(13, 'NSR/125239', 'Dr Bala Sundaram Mariappan', 11, 'Urology', 'bala.sundaram@hospital.com', '+60193171818', 'active', '2024-12-17 04:39:25', '2024-12-17 06:49:49', '<p>Qualification:</p><p>MBBS(UM), AM (Mal), M. Med (Surgery) (Singapore), FRCS (Edin), FRCS (Glasg), FRCS (Urol) (Glasg)</p><p>Languages:</p><p>English, Bahasa Melayu, Tamil</p><h3>Procedure Focus:</h3><ul><li>Laser Stone Surgery</li><li>Laparoscopic Surgery</li><li>Pediatric Urological Surgery</li><li>Prostate Surgery</li><li>Robotic Surgery</li><li>Ureteroscopy</li><li>Endourology (PCNL, RIRS)</li><li>Urological Cancer Surgery</li><li>Reconstructive Urological Surgery</li><li>Urinary Incontinence Surgery</li></ul><h3>Condition Treated:</h3><p>Urinary infection, Male infertility, Female Urology, Paediatric Urology, Prostate Diseases, Sexual Dysfunction, Urinary Stone, Urinary Incontinence, Urological Cancer</p><h3>Memberships &amp; Associations:</h3><ul><li>Malaysian Urological Association</li><li>Malaysian Medical Association - Life Member</li><li>Fellow, Royal College of Surgeon, Edinburgh, UK</li><li>Academic Member of Malaysia</li><li>European Association of Urology - Member</li><li>American Urology Association - Member</li><li>Fellow, Royal College of Glasgow, UK</li></ul><h3>Post Graduate Clinical Training:</h3><ul><li>Trainee in General Surgery, Hospital Alor Star, 1992-1995</li><li>Clinical Specialist in General Surgery and Urology, Hospital Alor Star, 1996-1998</li><li>Clinical Specialist in Urology, Hospital Sultanah Aminah, Johor Bahru, 1998-1999</li><li>Clinical Specialist in Urology, Hospital Kuala Lumpur, 2000</li><li>Senior Registrar and Fellow, University of Flinders, Australia, 2001-2002</li><li>Consultant Urologist, Hospital Kuala Lumpur, 2002</li><li>Training in da Vinci Robotic Surgical System, California, USA, 2004</li><li>Head of Department, and Consultant Urologist, Hospital Sultanah Aminah, Johor Bahru , 2002 - 2007</li></ul><h3>Awards:</h3><p>Excellence in Public Service Award, Ministry of Health, Malaysia , 1998, Excellence in Public Service Award, Ministry of Health, Malaysia, 2002</p><h3>Medical School:</h3><p>University of Malaya, Malaysia, 1991, National University of Singapore (M. Med Surgery), Singapore, 1996</p>', 'uploads/specialists/67611f0d60e41.jpg', NULL),
(14, 'NSR/129087', 'Dr Norazah Abdul Rahman', 13, 'Ophthalmology, Paediatrics Ophthalmology & Strabismus Surgery', 'norazah@hospital.com', '+6019171818', 'active', '2024-12-17 04:46:12', '2024-12-17 06:59:11', '<p>Qualification:</p><p>MBBS (UM), MMed (Ophth) (UM), Fellowship in Paediatric Ophthalmology (MOH)</p><p>Languages:</p><p>English, Bahasa Malaysia</p><h3>Core Speciality:</h3><p>Ophthalmology</p><h3>Procedure Focus:</h3><ul><li>Adult Cataract Surgery with Intraocular Implant (Monofocal, Multifocal, Toric And Iris Fixated Lens)</li><li>Femtosecond Assisted Cataract Surgery</li><li>Pediatric Cataract Surgery with and without Intraocular Implant</li><li>Pediatric Glaucoma Surgery, Glaucoma Drainage Device</li><li>Pterygium Excision</li><li>Strabismus Surgery</li><li>Laser Procedure (Diabetic Laser Treatment, YAG Laser, TSCPC)</li><li>Intravitreal Injection</li></ul><h3>Condition Treated:</h3><ul><li>External Eye Diseases, (Infection, Chalazion, Styes, Dry Eye)</li><li>Cataract, Trauma, Diabetic Eye Disease, Armd</li><li>Squint, Retinopathy of Prematurity, Vision Screening</li><li>Pediatric External Eye Diseases and Allergy Eye</li><li>Pediatric Cataract and Amblyopia Treatment</li><li>Refractive Errors and Myopia Control</li></ul><h3>Memberships &amp; Associations:</h3><ul><li>Malaysian Medical Council (MMC)</li><li>National Specialist Register (NSR)</li><li>Malaysian Society of Ophthalmology (MSO)</li><li>World Society of Pediatric Ophthalmology and Strabismus (WSPOS)</li></ul><h3>Post Graduate Clinical Training:</h3><ul><li>Master of Ophthalmology (UM) Hospital Kuala Lumpur, 2004-2006</li><li>Master of Ophthalmology (UM) 2006-2009</li><li>Pediatric Ophthalmology subspecialty/ Fellowship Training, Hospital Kuala Lumpur 2011-2013</li><li>Pediatric Ophthalmology subspecialty/ Fellowship Training, Hospital Selayang 2013-2014</li></ul><h3>Medical School:</h3><ul><li>Faculty of Medicine (University Malaya)</li><li>Masters in Ophthalmology, University of Malaya (2009)</li><li>Fellowship in Pediatric Ophthalmology (Malaysia Ministry of Health)</li></ul><h3>Media / Publications:</h3><ul><li><a href=\"https://fb.watch/8HCeg2Ahc2/\">The effect of lockdown on children\'s eyes</a></li><li><a href=\"https://aradamansaramedicalcentre.com/blog-content/debunking-the-myths-surrounding-lasik\">Debunking the myths surrounding Lasik</a></li><li><a href=\"https://www.thestar.com.my/metro/metro-news/2019/09/07/strabismus#.XuCWdFikm2Y.email=0A=0A---\">Strabismus</a></li><li><a href=\"https://www.thestar.com.my/news/nation/2019/11/18/aiming-for-better-vision-and-health#.XuCW5dRKz-M.email\">Aiming for better vision and health</a></li><li><a href=\"https://www.thestar.com.my/metro/metro-news/2019/11/25/vision-health-in-sharp-focus#.XuCXj5V80Xw.email\">Vision health in sharp focust</a></li><li><a href=\"https://www.bfm.my/podcast/the-bigger-picture/health-and-living/caring-for-childrens-eyes#.XuCa7VqslXU.whatsapp\">BFM health and living potcast</a></li><li><a href=\"https://www.bfm.my/podcast/the-bigger-picture/health-and-living/caring-for-childrens-eyes#.XuCa7VqslXU.whatsapp\">Control children’s screen time during MCO to maintain good eye health</a></li><li><a href=\"https://subangjayamedicalcentre.com/blog-content/world-sight-day\">World Sight Day</a></li><li><a href=\"https://subangjayamedicalcentre.com/blog-content/ways-to-prevent-spread-of-conjunctivitis\">Health Insights: Ways to prevent spread of Conjunctivitis</a></li><li><a href=\"https://www.aradamansaramedicalcentre.com/blog-content/childrens-vision-what-parents-need-to-know\">Health Insights: Are My Kids Seeing Normally?</a></li><li><a href=\"https://www.aradamansaramedicalcentre.com/blog-content/what-to-expect-when-you-bring-your-child-for-examination\">Health Insights: What To Expect when you bring your child for examination?</a></li><li><a href=\"https://www.aradamansaramedicalcentre.com/blog-content/easing-your-child-into-wearing-spectacles\">Health Insights: The Fantastic Four Eyes Easing your child into wearing spectacles</a></li><li><a href=\"https://www.aradamansaramedicalcentre.com/blog-content/how-gadgets-digital-screens-are-harming-your-childs-eyes\">Health Insights: How Gadgets &amp; Digital Screens Are Harming your Child’s Eyes</a></li><li><a href=\"https://parkcitymedicalcentre.com/blog-content/bagaimana-gajet--skrin-digital-merosakkan-mata-anak-anda\">Health Insights: BAGAIMANA Gajet &amp; Skrin Digital Merosakkan Mata Anak Anda</a></li><li><a href=\"https://www.aradamansaramedicalcentre.com/blog-content/maintaining-good-eye-health-in-the-digital-age\">Maintaining good eye health in the digital age</a></li><li><a href=\"https://sinarplus.sinarharian.com.my/lifestyle/lensa-mata-jadi-keruh-berawan-bukan-sahaja-warga-emas-bayi-juga-boleh-dilahir-dengan-katarak/\">Cloudy Lens: Not Only The Elderly, Babies Can Also Be Born With Cataract</a></li><li><a href=\"https://www.thestar.com.my/news/nation/2024/04/03/screen-time-leads-to-spectacles-time\">Screen time leads to spectacles time | TheStar News</a></li></ul>', 'uploads/specialists/6761213ef40eb.png', NULL),
(15, 'NSR/130955', 'Dr Pauline Pue Leng Boi', 12, 'Obstetrics and Gynaecology, Gynaecology (General), Gynaecology (Urogynaecology)', 'pauline.pue@hospital.com', '+60193171818', 'active', '2024-12-17 04:48:45', '2024-12-17 06:59:42', '<p>Qualification:</p><p>MD (USM), MMED O&amp;G (USM), FRCOG (UK), Fellowship Urogynaecology (Taiwan)</p><p>Languages:</p><p>English, Bahasa Melayu, Chinese (Hokkien), Thai</p><h3>Condition Treated:</h3><ul><li>Antenatal Care: Excluding high-risk pregnancies, vaginal and operative deliveries</li><li>General Gynaecology: Cervical problems, Endometriosis, Fibroids, Menstrual problems, Ovarian cysts, Polycystic Ovarian Syndrome (PCOS)</li><li>Urogynaecology: Pelvic Organ Prolapse, Urinary Incontinence, Urinary Tract Infection</li></ul><h3>Procedures Performed:</h3><ul><li>Caesarean Section</li><li>Colposcopy</li><li>Cone Biopsy</li><li>Cystectomy</li><li>Hysterectomy</li><li>Hysteroscopy</li><li>Laparoscopic Surgeries</li><li>Minimal Invasive Incontinence Surgery</li><li>Myomectomy</li><li>Pelvic Floor Reconstruction</li><li>Vault Suspension</li><li>Vaginal Delivery</li><li>Vaginal Surgery</li></ul><h3>Memberships &amp; Associations:</h3><ul><li>Fellow of Royal College O&amp;G (UK),</li><li>Member of International Urogynaecology Association</li><li>Member of Malaysian O&amp;G Society</li><li>Member of Malaysian Urogyanecology Society.&nbsp;</li></ul><h3>Clinical Training:</h3><ul><li>Seberang Jaya Hospital, &nbsp;Kulim Hospital, Penang General Hospital (1999-2004)</li><li>Hospital Universiti Sains Malaysia (2005-2006)&nbsp;</li><li>Batu Pahat Hospital, Hospital Serdang ( 2007- 2011)&nbsp;</li><li>Chang Gung Memorial Hospital, Taiwan (2012)&nbsp;</li><li>Penang General Hospital, Kuala Lumpur General Hospital (2013-2015)<br>&nbsp;</li></ul><h3>Post-Graduate Qualifications:</h3><ul><li>Master of Medicine in Obstetrics and Gynaecology (USM),&nbsp;</li><li>Fellow of Royal College of Obstetricians and Gynaecologist (UK),&nbsp;</li><li>Fellowship in Urogynaecology (Taiwan)&nbsp;&nbsp;</li></ul><h3>Medical School:</h3><ul><li>Universiti Sains Malaysia (USM)</li></ul><h3>Awards:</h3><ul><li>Excellence Service Award ( Ministry of Health) 2008&nbsp;</li></ul><h3>Media / Publications:</h3><ul><li><a href=\"https://subangjayamedicalcentre.com/blog-content/when-common-is-not-necessarily-normal\">When common is not necessarily normal | <i>The Star</i></a></li><li><a href=\"https://pubmed.ncbi.nlm.nih.gov/24245849/\">Strangulated Small Bowel 14 Years After Abdominal Sacrocolpopexy | From <i>Journal of Obstetrics and Gynaecology Research</i></a></li><li><a href=\"https://pubmed.ncbi.nlm.nih.gov/23430075/\">Vaginal Vascular Malformation Mimicking Pelvic Organ Prolapse Requiring Serial Embolizations</a> <a href=\"https://pubmed.ncbi.nlm.nih.gov/23430075/\"><i>| From International Urogynecology Journal&nbsp;</i></a></li><li><a href=\"https://pubmed.ncbi.nlm.nih.gov/26700103/\">Risk Factors for Failure of Repeat Midurethral Sling Surgery for Recurrent or Persistent Stress Urinary Incontinence | From <i>International Urogynecology Journal</i></a></li><li><a href=\"https://pubmed.ncbi.nlm.nih.gov/25808989/\">Long-Term Outcome of Native Tissue Reconstructive Vaginal Surgery for Advanced Pelvic Organ Prolapse at 86 Months: Hysterectomy Versus Hysteropexy | <i>From Journal of Obstetrics and Gynaecology Research</i></a></li><li><a href=\"https://pubmed.ncbi.nlm.nih.gov/32898974/\">Mid Urethral Slings for the Treatment of Urodynamic Stress Incontinence in Overweight and Obese Women: Surgical Outcomes and Preoperative Predictors of Failure | From Journal of Urology</a></li></ul>', 'uploads/specialists/6761215e127b3.jpg', NULL),
(16, 'NSR/131674', 'Dr Shifa Bt Zulkifli', 14, 'Otorhinolaryngology-ENT, Head & Neck Surgery, Paediatrics ENT, Head & Neck', 'shifa@hospital.com', '+60193171818', 'active', '2024-12-17 04:50:34', '2024-12-17 07:00:12', '<p>Qualification:</p><p>MBBS (IIUM), MMED ORL-HNS (USM), FELLOWSHIP PAEDIATRIC OTORHINOLARYNGOLOGY (MALAYSIA), CMIA (NIOSH)</p><p>Languages:</p><p>English, Bahasa Melayu</p><h3>Procedure Focus:</h3><ul><li>Foreign Body and Wax Removal&nbsp;</li><li>Tonsil and Adenoid Surgery</li><li>Ear Ventilation Tube (Grommet)</li><li>Ear Drum Repair ( Myringoplasty)</li><li>Head &amp; Neck Mass, Sinus Surgery</li><li>Endoscopic Sinus Surgery</li><li>Septal and Turbinate Surgery</li><li>Laser Surgery</li><li>Endoscopic and Open Airway Surgery&nbsp;</li><li>Mastoid Surgery</li><li>Bone Anchored Hearing Implants</li><li>Sleep and Snoring Surgery&nbsp;</li><li>Tracheostomy</li><li>Salivary Gland Surgery</li><li>Ex-Utero Intrapartum Treatment (Neonate)</li></ul><h3>Condition Treated:</h3><ul><li>Congenital ENT Diseases&nbsp;</li><li>Noisy Breathing In Children&nbsp;</li><li>Adult and Children ENT Diseases&nbsp;</li><li>Snoring and Sleep Apnea&nbsp;</li><li>Hearing, Balance and Tinnitus&nbsp;</li><li>Nose Allergy, Sinusitis and Polyps&nbsp;</li><li>Ear Infection&nbsp;</li><li>Tonsillitis and Sore Throat&nbsp;</li><li>Voice and Swallowing Disorder&nbsp;</li><li>Airway Disorder</li></ul><h3>Post Graduate Qualifications:</h3><ul><li>Master In Medicine Otorhinolaryngology- Head and Neck Surgery (USM).</li><li>Fellowship in Paediatric Otorhinolaryngology-Head and Neck (MALAYSIA).</li><li>Clinical Attachment in Rhinology, Kyung Hee University Medical Centre, Seoul, South Korea.</li><li>Visiting Fellow/Physician Great Ormond Street Hospital for Children, London, UK.</li><li>Visiting Fellow/Physician Cincinnati Children Hospital Medical Centre, Ohio, USA.</li><li>Certified Medical Impairment Assessor (CMIA-NIOSH)</li></ul><h3>Clinical Training:</h3><ul><li>Otorhinolaryngologist- Head &amp;Neck Surgeon Selayang and Sungai Buloh Hospital ( 2013-2016</li><li>ORL-HNS Surgeon and Pediatric ENT Fellow in UKMMC, UMMC, Serdang Hospital, HTAR Klang, HSB Alor Setar (2016-2018)</li><li>Head of Department &amp; Consultant ORL -HNS and Pediatric, Hospital Tunku Azizah, Kuala Lumpur</li></ul><h3>Awards:</h3><ul><li>Excellence Service Awards, Ministry of Health Malaysia 2011, 2020.</li></ul><h3>Special Privileges According to Hospital:</h3><ul><li>General ENT-H&amp;N Surgery</li><li>Paediatric ENT-H&amp;N Surgery</li></ul><h3>Medical School:</h3><p>Bachelor of Medicine and Bachelor of Surgery (MBBS) (IIUM)</p><h3>Media &amp; Publications:</h3><ul><li><a href=\"https://www.facebook.com/watch/live/?ref=watch_permalink&amp;v=227455321979435\">Pendengaran Berkait Rapat Dengan Pendengaran Kanak-Kanak</a></li><li><a href=\"https://www.cureus.com/articles/50167-managing-a-complicated-acute-otomastoiditis-at-day-four-of-life\">Managing a Complicated Acute Otomastoiditis at Day Four of Life</a></li><li><a href=\"https://link.springer.com/article/10.1007%2Fs12070-021-02851-y\">Ex Utero Intrapartum Treatment (EXIT) Into the World: A Multidisciplinary Approach and Outcomes in a Malaysia Tertiary Centre</a></li></ul>', 'uploads/specialists/6761217ce0733.jpg', NULL),
(17, 'NSR/135211', 'Dr Daphne Anthonysamy', 15, 'Breast Surgery, Endocrine Surgery', 'daphne@hospital.com', '+60193171818', 'active', '2024-12-17 04:53:56', '2024-12-17 06:55:10', '<p>Qualification:</p><p>M.D (UPM), M.Surg (UKM), Subspeciality training in breast &amp; endocrine surgery (KKM)</p><p>Languages:</p><p>English, Bahasa Melayu</p><h3>Procedure Focus:</h3><ul><li>Breast Cancer Surgery</li><li>Breast Conserving surgery / Oncoplasty (Breast Remodelling)</li><li>Breast Lump Excision</li><li>Mastectomy, skin/nipple sparing mastectomy with immediate reconstruction (implant/latissimus dorsi flap / pedicle TRAM flap</li><li>Contralateral reduction Mammoplasty</li><li>Sentinel lymph node biopsy/ Axillary Clearance</li><li>Hook wire localisation &amp; wide local excision</li><li>Thyroidectomy (Total/Hemi) with intra operative nerve monitoring</li><li>Parathyroidectomy</li><li>Neck Dissection</li></ul><h3>Memberships &amp; Associations:</h3><ul><li>Academy of Medicine</li><li>Breast Chapter, College of Surgeons, Malaysia</li><li>Asia Pacific Society of Thyroid Surgery</li></ul><h3>Post Graduate Clinical Training:</h3><ul><li>Master in Surgery - HKL</li><li>General Surgery - HKL, HTAR, Klang</li><li>Breast &amp; Endocrine Surgery - HKL, Putrajaya Hospital</li><li>Fellowship training in Oncoplastic Breast Surgery - AIIMS, New Delhi</li><li>Fellowship training in Oncoplastic Breast Surgery, Sirirai Hospital, Bangkok</li></ul><h3>Awards:</h3><ul><li>FFM Bhd Award for Surgery, 2002</li><li>Ethicon Prize CSAMM AGM / ASM, 2010</li><li>Anugerah Perkhidmatan Cemerlang KKM, 2013</li></ul><h3>Media / Publications:</h3><ul><li><a href=\"https://www.subangjayamedicalcentre.com/blog-content/reconstruction-is-an-option\">Reconstruction is an option</a></li><li><a href=\"https://subangjayamedicalcentre.com/blog-content/self-examination-is-self-care\">Self-Examination Is Self-Care</a></li></ul>', 'uploads/specialists/6761204e2e400.jpg', NULL),
(18, 'NSR/133580', 'Dr Khong Tak Loon', 16, 'Colorectal Surgery, General Surgery', 'khong.takloon@hospital.com', '+60193171818', 'active', '2024-12-17 05:02:25', '2024-12-17 06:56:58', '<p>Qualification:</p><p>CCT (UK), FRCS (Eng), MD Res (Lon), MSc (Lon), MBChB (Edin)</p><p>Languages:</p><p>English, Bahasa Melayu, Chinese (Mandarin, Cantonese)</p><h3>Procedure Focus:</h3><ul><li>Colorectal Surgery</li><li>General Surgery</li></ul><h3>Procedures Performed:</h3><ul><li>Anal fistula surgery</li><li>Colonoscopy</li><li>Endoanal and endorectal ultrasound</li><li>Haemorrhoid surgery</li><li>Laparoscopic colon and rectal resection</li><li>Upper gastrointestinal endoscopy</li></ul><h3>Condition Treated:</h3><ul><li>Anal fissure</li><li>Anal fistula</li><li>Colon and rectal cancer</li><li>Colonic and rectal polyps</li><li>Diverticular disease,</li><li>Haemorrhoids</li><li>Inflammatory bowel disease</li><li>Pilonidal sinus</li><li>Rectal prolapse</li></ul><h3>Memberships &amp; Associations:</h3><ul><li>Australian Health Practitioner Regulation Agency</li><li>Fellow of the Royal College of Surgeons England</li><li>Malaysian Society of Colorectal Surgeons</li></ul><h3>Clinical Training:</h3><ul><li>Fellowship in laparoscopic and endoluminal surgery with Australian Colorectal Endosurgery Brisbane, Australia</li><li>Higher Surgical Training - Health Education Yorkshire, England</li></ul><h3>Post-Graduate Qualifications:</h3><ul><li>Completion of Certificate of Training (UK)</li><li>Doctorate in Clinical Medical Research (Imperial College, London)&nbsp;</li><li>Fellow of the Royal College of Surgeons England</li><li>Masters in Surgical Science (Imperial College, London)</li></ul><h3>Medical School:</h3><ul><li>MBChB (University of Edinburgh)</li></ul><h3>Awards:</h3><ul><li>Alan Edwards Award</li><li>Alexander Simpson Award</li></ul>', 'uploads/specialists/676120ba6a8e8.png', NULL),
(19, 'NSR/128645', 'Dr Heselynn Hussein', 17, 'Internal Medicine & Rheumatology', 'heselynn@hospital.com', '+60193171818', 'active', '2024-12-17 05:14:41', '2024-12-17 06:56:32', '<p>Qualification:</p><p>MBBCh (Wales) MRCP (UK)</p><p>Languages:</p><p>English, Bahasa Melayu</p><h3>Core Speciality:</h3><p>Internal Medicine &amp; Rheumatology</p><h3>Procedure Focus:</h3><p>Intra-Articular Injection</p><h3>Condition Treated:</h3><p>Connective Tissue Diseases, Gout, Osteoarthritis, Osteoporosis, Psoriatic Arthritis, Rheumatism, Rheumatoid Arthritis, Spondyloarthritis, Systemic Lupus Erythematosus (Sle)</p><h3>Memberships &amp; Associations:</h3><ul><li>Immediate Past President Malaysian Society of Rheumatology</li><li>Medical Advisor Persatuan SLE Malaysia</li></ul><h3>Post Graduate Clinical Training:</h3><ul><li>Fellow in Rheumatology MRCP (London)</li><li>Postgraduate Certificate in Herbal Medicine, Napier University, Edinburgh Hospital</li><li>University Kebangsaan Malaysia</li><li>University Hospital Birmingham, UK</li></ul><h3>Awards:</h3><p>Excellent Service Award, Ministry of Health Malaysia, 1994, 2001, 2008</p><h3>Medical School:</h3><p>University of Wales College of Medicine, Cardiff&nbsp;</p>', 'uploads/specialists/676120a064312.jpg', NULL),
(21, 'NSR/124730', 'Dr C K Ranjeev Prabhakeran', 18, 'Gastroenterology and Hepatology', 'ranjeev@hospital.com', '+60193171818', 'active', '2024-12-17 08:09:05', '2024-12-17 08:09:05', '<p>Qualification:</p><p>MBBS (Mal), MRCP (UK), FRCP (Glasg), AM (Mal)</p><p>Languages:</p><p>English, Bahasa Melayu, Tamil</p><h3>Procedure Focus:</h3><ul><li>Endoscopic Retrograde Cholangiopancreatography (ERCP)</li><li>Oesophageal Stenting, Oesophago Gastric Duodeno Scopy (OGDS)</li><li>Oesophago-Gastrectomy</li><li>Polypectomy</li></ul><h3>Condition Treated:</h3><p>Pancreatic Diseases</p><h3>Memberships &amp; Associations:</h3><ul><li>Member, Academy of Medicine Malaysia</li><li>Life Member, Malaysian Medical Association</li><li>Life Member, Malaysian Society of Gastroenterology and Hepatology</li></ul><h3>Post Graduate Clinical Training:</h3><ul><li>Registrar, Department of Medicine, Seremban General Hospital, 1992-1994</li><li>Clinical Specialist, Seremban General Hospital, 1994-1996</li><li>MRCP (UK), Member, Royal College of Physicians of United Kingdom, 1994</li><li>Consultant Physician in Internal Medicine with Special interest in Gastroenterology and Hepatology, University Hospital, Kuala Lumpur, 1996-2002</li><li>FRCP (Glasg), Fellow, Royal College of Physicians of Glasgow, 2003</li></ul><h3>Board Certification:</h3><p>Royal College of Physicians, United Kingdom, MRCP (UK) 1994</p><h3>Medical School:</h3><p>MBBS, University of Malaya, Malaysia, 1989&nbsp;</p>', 'uploads/specialists/676131a1d3d3b.jpg', NULL),
(22, 'NSR/123123', 'Dr SIM specialist', 1, 'requesting new features', 'huixinnoob@qmed.asia', '0126788787', 'active', '2024-12-18 16:14:46', '2024-12-18 16:14:46', '', NULL, '88888888');

-- --------------------------------------------------------

--
-- Table structure for table `specialist_schedule`
--

CREATE TABLE `specialist_schedule` (
  `id` int NOT NULL,
  `specialist_id` int NOT NULL,
  `day_of_week` int NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `specialist_schedule`
--

INSERT INTO `specialist_schedule` (`id`, `specialist_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `created_at`, `updated_at`) VALUES
(38, 8, 1, '09:00:00', '13:00:00', 1, '2024-12-18 07:21:47', '2024-12-18 07:21:47'),
(39, 8, 2, '09:00:00', '13:00:00', 1, '2024-12-18 07:21:47', '2024-12-18 07:21:47'),
(40, 8, 3, '09:00:00', '13:00:00', 1, '2024-12-18 07:21:47', '2024-12-18 07:21:47'),
(41, 8, 4, '09:00:00', '13:00:00', 1, '2024-12-18 07:21:47', '2024-12-18 07:21:47'),
(42, 8, 5, '09:00:00', '13:00:00', 1, '2024-12-18 07:21:47', '2024-12-18 07:21:47'),
(43, 8, 6, '09:00:00', '13:00:00', 1, '2024-12-18 07:21:47', '2024-12-18 07:21:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `clinic_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `clinic_id`) VALUES
(4, 'drtai', '88888888', 'doctor', '2024-12-16 08:58:19', NULL),
(5, 'drida', '88888888', 'doctor', '2024-12-16 08:58:19', NULL),
(6, 'bernard', '88888888', 'doctor', '2024-12-16 08:58:19', NULL),
(7, 'haranee', '88888888', 'doctor', '2024-12-16 08:58:19', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clinics`
--
ALTER TABLE `clinics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gp_doctors`
--
ALTER TABLE `gp_doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `mmc_no` (`mmc_no`),
  ADD KEY `idx_clinic` (`clinic_id`);

--
-- Indexes for table `gp_programs`
--
ALTER TABLE `gp_programs`
  ADD PRIMARY KEY (`program_id`);

--
-- Indexes for table `honour_points`
--
ALTER TABLE `honour_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gp_id` (`gp_id`);

--
-- Indexes for table `hospital_settings`
--
ALTER TABLE `hospital_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `points_history`
--
ALTER TABLE `points_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gp_id` (`gp_id`),
  ADD KEY `referral_id` (`referral_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `referring_gp_id` (`referring_gp_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `specialist_id` (`specialist_id`);

--
-- Indexes for table `referral_clinical_feedback`
--
ALTER TABLE `referral_clinical_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referral_id` (`referral_id`);

--
-- Indexes for table `specialists`
--
ALTER TABLE `specialists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nsr_number` (`nsr_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `specialist_schedule`
--
ALTER TABLE `specialist_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `specialist_id` (`specialist_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `clinic_id` (`clinic_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clinics`
--
ALTER TABLE `clinics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `gp_doctors`
--
ALTER TABLE `gp_doctors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `gp_programs`
--
ALTER TABLE `gp_programs`
  MODIFY `program_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `honour_points`
--
ALTER TABLE `honour_points`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `hospital_settings`
--
ALTER TABLE `hospital_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `points_history`
--
ALTER TABLE `points_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `referral_clinical_feedback`
--
ALTER TABLE `referral_clinical_feedback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `specialists`
--
ALTER TABLE `specialists`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `specialist_schedule`
--
ALTER TABLE `specialist_schedule`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `gp_doctors`
--
ALTER TABLE `gp_doctors`
  ADD CONSTRAINT `gp_doctors_ibfk_1` FOREIGN KEY (`clinic_id`) REFERENCES `clinics` (`id`);

--
-- Constraints for table `honour_points`
--
ALTER TABLE `honour_points`
  ADD CONSTRAINT `honour_points_ibfk_1` FOREIGN KEY (`gp_id`) REFERENCES `gp_doctors` (`id`);

--
-- Constraints for table `points_history`
--
ALTER TABLE `points_history`
  ADD CONSTRAINT `points_history_ibfk_1` FOREIGN KEY (`gp_id`) REFERENCES `gp_doctors` (`id`),
  ADD CONSTRAINT `points_history_ibfk_2` FOREIGN KEY (`referral_id`) REFERENCES `referrals` (`id`);

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`referring_gp_id`) REFERENCES `gp_doctors` (`id`),
  ADD CONSTRAINT `referrals_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `referrals_ibfk_4` FOREIGN KEY (`specialist_id`) REFERENCES `specialists` (`id`);

--
-- Constraints for table `referral_clinical_feedback`
--
ALTER TABLE `referral_clinical_feedback`
  ADD CONSTRAINT `referral_clinical_feedback_ibfk_1` FOREIGN KEY (`referral_id`) REFERENCES `referrals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `specialists`
--
ALTER TABLE `specialists`
  ADD CONSTRAINT `specialists_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `specialist_schedule`
--
ALTER TABLE `specialist_schedule`
  ADD CONSTRAINT `specialist_schedule_ibfk_1` FOREIGN KEY (`specialist_id`) REFERENCES `specialists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`clinic_id`) REFERENCES `clinics` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
