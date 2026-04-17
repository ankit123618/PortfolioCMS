-- phpMyAdmin SQL Dump
-- version 5.2.3-1.fc43
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 17, 2026 at 12:41 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `portfolio`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`) VALUES
(1, 'ankit', '$2y$12$Tu02bywe/45pvaMuDx9Lb.OVChh8085To4MddTjqD7K3R6z3.VDx6');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `slug` varchar(50) DEFAULT NULL,
  `schema` longtext DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `slug`, `schema`, `updated_at`) VALUES
(1, 'home', '{\n    \"page\": \"home\",\n    \"sections\": [\n        {\n            \"type\": \"hero\",\n            \"id\": \"hero\",\n            \"enabled\": true,\n            \"data\": {\n                \"title\": \"Devil\",\n                \"tagline\": \"Software Engineer • Researcher • Educator\\r\\nBuilding technology, knowledge systems, and tools that remove superstition and empower minds.\",\n                \"photo\": \"1775635049_hero.png\"\n            }\n        },\n        {\n            \"type\": \"navigation\",\n            \"id\": \"main-nav\",\n            \"enabled\": true,\n            \"data\": {\n                \"links\": [\n                    {\n                        \"text\": \"About\",\n                        \"href\": \"#about\"\n                    },\n                    {\n                        \"text\": \"Skills\",\n                        \"href\": \"#skills\"\n                    },\n                    {\n                        \"text\": \"Projects\",\n                        \"href\": \"#projects\"\n                    },\n                    {\n                        \"text\": \"Vision\",\n                        \"href\": \"#vision\"\n                    },\n                    {\n                        \"text\": \"Contact\",\n                        \"href\": \"#contact\"\n                    }\n                ]\n            }\n        },\n        {\n            \"type\": \"content\",\n            \"id\": \"about\",\n            \"enabled\": true,\n            \"data\": {\n                \"title\": \"About Me\",\n                \"text\": \"You should describe your work, brand or company here\"\n            }\n        },\n        {\n            \"type\": \"skills\",\n            \"id\": \"skills\",\n            \"enabled\": true,\n            \"data\": {\n                \"title\": \"Skills\",\n                \"items\": [\n                    \"skill 1\",\n                     \"skill 2\",\n                    \"skill3\",\n                    \"Enter your skills here\"\n                ]\n            }\n        },\n        {\n            \"type\": \"projects\",\n            \"id\": \"projects\",\n            \"enabled\": true,\n            \"data\": {\n                \"title\": \"Projects\",\n                \"items\": [\n                    {\n                        \"title\": \"Title1\",\n                        \"description\": \"Description of your project 1.\",\n                        \"image\": \"1775635049_project_0.png\"\n                    },\n                    {\n                        \"title\": \"Title2\",\n                        \"description\": \"Description of your project 2\",\n                        \"image\": \"1775635049_project_1.png\"\n                    },\n                    {\n                        \"title\": \"Title3\",\n                        \"description\": \"Description of your project 3\",\n                        \"image\": \"1775635049_project_2.png\"\n                    }\n                ]\n            }\n        },\n        {\n            \"type\": \"content\",\n            \"id\": \"vision\",\n            \"enabled\": true,\n            \"data\": {\n                \"title\": \"Vision\",\n                \"text\": \"You can explain your vision, goals for future here.\"\n            }\n        },\n        {\n            \"type\": \"contact\",\n            \"id\": \"contact\",\n            \"enabled\": true,\n            \"data\": {\n                \"title\": \"Contact\",\n                \"items\": [\n                    {\n                        \"name\": \"Email\",\n                        \"value\": \"Write your address for email here\"\n                    },\n                    {\n                        \"name\": \"GitHub\",\n                        \"value\": \"Insert github url\"\n                    },\n                    {\n                        \"name\": \"YouTube\",\n                        \"value\": \"Insert youtube link\"\n                    }\n                ]\n            }\n        },\n        {\n            \"type\": \"footer\",\n            \"id\": \"footer\",\n            \"enabled\": true,\n            \"data\": {\n                \"text\": \"© 2026 Ankit Sharma • Built on Linux with clarity and purpose\"\n            }\n        }\n    ]\n}', '2026-04-08 07:57:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
