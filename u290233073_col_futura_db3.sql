-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 23 août 2026 à 02:49
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `u290233073_col_futura_db3`
--

-- --------------------------------------------------------

--
-- Structure de la table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `status` enum('active','archived') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `academic_years`
--

INSERT INTO `academic_years` (`id`, `nom`, `is_active`, `status`, `created_at`, `start_date`, `end_date`) VALUES
(3, '2025-2026', 1, 'active', '2026-05-04 20:46:59', '2026-09-01', '2027-06-30');

-- --------------------------------------------------------

--
-- Structure de la table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_role` varchar(50) DEFAULT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_category` varchar(50) NOT NULL DEFAULT 'system',
  `route` varchar(255) DEFAULT NULL,
  `http_method` varchar(10) DEFAULT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `event_count` int(11) NOT NULL DEFAULT 1,
  `metadata` longtext DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `academic_year_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `user_role`, `event_type`, `event_category`, `route`, `http_method`, `entity_type`, `entity_id`, `event_count`, `metadata`, `ip_address`, `user_agent`, `created_at`, `academic_year_id`) VALUES
(1, 67, 'direction_academique', 'auth_login', 'authentication', '/login', 'POST', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 08:44:03', NULL),
(2, 67, 'direction_academique', 'page_view', 'usage', '/', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 08:44:09', NULL),
(3, 67, 'direction_academique', 'page_view', 'usage', '/subjects', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 08:44:39', NULL),
(41, 67, 'direction_academique', 'page_view', 'usage', '/subjects', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 08:55:28', NULL),
(42, 67, 'direction_academique', 'page_view', 'usage', '/teachers', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 08:56:35', NULL),
(43, 67, 'direction_academique', 'page_view', 'usage', '/teaching_types', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 08:56:43', NULL),
(44, 67, 'direction_academique', 'page_view', 'usage', '/teachers', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 08:57:07', NULL),
(45, 67, 'direction_academique', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 08:57:15', NULL),
(46, 67, 'direction_academique', 'page_view', 'usage', '/subject-groups', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 08:57:28', NULL),
(47, 67, 'direction_academique', 'page_view', 'usage', '/subjects', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 08:57:41', NULL),
(48, 40, 'admin', 'auth_login', 'authentication', '/login', 'POST', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 12:33:55', NULL),
(49, 40, 'admin', 'page_view', 'usage', '/', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 12:33:56', NULL),
(50, 40, 'admin', 'page_view', 'usage', '/subjects', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 12:34:47', NULL),
(51, 40, 'admin', 'page_view', 'usage', '/', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:49:51', NULL),
(52, 40, 'admin', 'page_view', 'usage', '/', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:53:28', NULL),
(53, 40, 'admin', 'auth_login', 'authentication', '/login', 'POST', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:53:57', NULL),
(54, 40, 'admin', 'page_view', 'usage', '/', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:54:03', NULL),
(55, 40, 'admin', 'page_view', 'usage', '/teaching_types', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:54:19', NULL),
(56, 40, 'admin', 'page_view', 'usage', '/teaching_types/toggle', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:54:25', NULL),
(57, 40, 'admin', 'page_view', 'usage', '/teaching_types', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:54:26', NULL),
(58, 40, 'admin', 'page_view', 'usage', '/teaching_types', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:54:33', NULL),
(59, 40, 'admin', 'page_view', 'usage', '/levels', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:54:41', NULL),
(60, 40, 'admin', 'page_view', 'usage', '/cycles', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:54:52', NULL),
(61, 40, 'admin', 'page_view', 'usage', '/departments', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:55:04', NULL),
(62, 40, 'admin', 'page_view', 'usage', '/settings', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:55:12', NULL),
(63, 40, 'admin', 'settings_updated', 'admin_activity', '/settings/store', 'POST', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:55:36', NULL),
(64, 40, 'admin', 'page_view', 'usage', '/settings', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:55:36', NULL),
(65, 40, 'admin', 'settings_updated', 'admin_activity', '/settings/store', 'POST', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:56:10', NULL),
(66, 40, 'admin', 'page_view', 'usage', '/settings', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:56:10', NULL),
(67, 40, 'admin', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:57:11', NULL),
(68, 40, 'admin', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:57:36', NULL),
(69, 40, 'admin', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:57:45', NULL),
(70, 40, 'admin', 'page_view', 'usage', '/classes/edit', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:57:58', NULL),
(71, 40, 'admin', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:58:14', NULL),
(72, 40, 'admin', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:58:21', NULL),
(73, 40, 'admin', 'page_view', 'usage', '/classes/edit', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:58:28', NULL),
(74, 40, 'admin', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:58:50', NULL),
(75, 40, 'admin', 'page_view', 'usage', '/classes/edit', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:58:58', NULL),
(76, 40, 'admin', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:59:06', NULL),
(77, 40, 'admin', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:59:11', NULL),
(78, 40, 'admin', 'page_view', 'usage', '/classes/edit', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:59:15', NULL),
(79, 40, 'admin', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:59:22', NULL),
(80, 40, 'admin', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:59:29', NULL),
(81, 40, 'admin', 'page_view', 'usage', '/classes/edit', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:59:34', NULL),
(82, 40, 'admin', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:59:42', NULL),
(83, 40, 'admin', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:59:47', NULL),
(84, 40, 'admin', 'page_view', 'usage', '/classes/edit', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:59:52', NULL),
(85, 40, 'admin', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 01:59:58', NULL),
(86, 40, 'admin', 'page_view', 'usage', '/classes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:00:04', NULL),
(87, 40, 'admin', 'page_view', 'usage', '/classes/edit', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:00:13', NULL),
(88, 40, 'admin', 'page_view', 'usage', '/levels', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:00:42', NULL),
(89, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:09:55', NULL),
(90, 40, 'admin', 'page_view', 'usage', '/notes/saisie', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:10:07', NULL),
(91, 40, 'admin', 'page_view', 'usage', '/notes/saisie', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:10:17', NULL),
(92, 40, 'admin', 'page_view', 'usage', '/bulletins', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:16:54', NULL),
(93, 40, 'admin', 'page_view', 'usage', '/bulletins', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:17:03', NULL),
(94, 40, 'admin', 'page_view', 'usage', '/honors', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:17:19', NULL),
(95, 40, 'admin', 'page_view', 'usage', '/proces-verbal', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:17:51', NULL),
(96, 40, 'admin', 'page_view', 'usage', '/honors', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:17:59', NULL),
(97, 40, 'admin', 'page_view', 'usage', '/proces-verbal', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:18:14', NULL),
(98, 40, 'admin', 'page_view', 'usage', '/transcripts', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:18:16', NULL),
(99, 40, 'admin', 'page_view', 'usage', '/timetables/print', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:18:26', NULL),
(100, 40, 'admin', 'page_view', 'usage', '/timetables/pdf', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:18:27', NULL),
(101, 40, 'admin', 'page_view', 'usage', '/honors', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:20:30', NULL),
(102, 40, 'admin', 'page_view', 'usage', '/timetables/print', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:20:50', NULL),
(103, 40, 'admin', 'page_view', 'usage', '/timetables/pdf', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:20:50', NULL),
(104, 40, 'admin', 'page_view', 'usage', '/transcripts', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:24:38', NULL),
(105, 40, 'admin', 'page_view', 'usage', '/transcripts', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:28:14', NULL),
(106, 40, 'admin', 'page_view', 'usage', '/timetables/print', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:38:24', NULL),
(107, 40, 'admin', 'page_view', 'usage', '/timetables/pdf', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:38:24', NULL),
(108, 40, 'admin', 'page_view', 'usage', '/timetables/print', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:39:51', NULL),
(109, 40, 'admin', 'page_view', 'usage', '/timetables/pdf', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:39:52', NULL),
(110, 40, 'admin', 'page_view', 'usage', '/timetables', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:40:14', NULL),
(111, 40, 'admin', 'page_view', 'usage', '/timetables', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:42:05', NULL),
(112, 40, 'admin', 'page_view', 'usage', '/timetables', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:42:24', NULL),
(113, 40, 'admin', 'page_view', 'usage', '/timetables/wizard', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:42:36', NULL),
(114, 40, 'admin', 'page_view', 'usage', '/timetables/api/wizard/cycles', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:42:37', NULL),
(115, 40, 'admin', 'page_view', 'usage', '/timetables/api/wizard/levels', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:42:44', NULL),
(116, 40, 'admin', 'page_view', 'usage', '/timetables/grid', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:44:38', NULL),
(117, 40, 'admin', 'page_view', 'usage', '/timetables/pdf', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:45:40', NULL),
(118, 40, 'admin', 'page_view', 'usage', '/levels', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:53:27', NULL),
(119, 40, 'admin', 'page_view', 'usage', '/levels', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:53:35', NULL),
(120, 40, 'admin', 'page_view', 'usage', '/levels', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:54:43', NULL),
(121, 40, 'admin', 'page_view', 'usage', '/cycles', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:54:53', NULL),
(122, 40, 'admin', 'page_view', 'usage', '/levels', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:54:59', NULL),
(123, 40, 'admin', 'page_view', 'usage', '/levels', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:55:16', NULL),
(124, 40, 'admin', 'page_view', 'usage', '/levels', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:56:56', NULL),
(125, 40, 'admin', 'page_view', 'usage', '/levels', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:57:11', NULL),
(126, 40, 'admin', 'page_view', 'usage', '/levels', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:58:16', NULL),
(127, 40, 'admin', 'page_view', 'usage', '/teaching_types', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:59:01', NULL),
(128, 40, 'admin', 'page_view', 'usage', '/cycles', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 02:59:14', NULL),
(129, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 03:01:16', NULL),
(130, 40, 'admin', 'page_view', 'usage', '/notes/saisie', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 03:01:25', NULL),
(131, 40, 'admin', 'page_view', 'usage', '/', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:24:44', NULL),
(132, 40, 'admin', 'page_view', 'usage', '/bulletins', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:28:38', NULL),
(133, 40, 'admin', 'page_view', 'usage', '/bulletins', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:28:50', NULL),
(134, 40, 'admin', 'page_view', 'usage', '/bulletins', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:28:58', NULL),
(135, 40, 'admin', 'page_view', 'usage', '/payments', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:29:07', NULL),
(136, 40, 'admin', 'page_view', 'usage', '/students/create', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:29:09', NULL),
(137, 40, 'admin', 'student_enroll', 'student_activity', '/students/store', 'POST', 'student', 404, 1, '{\"nom\":\"TAMAFO\",\"prenom\":\"Jordan\",\"matricule\":\"2325242\",\"class_id\":76}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:30:04', NULL),
(138, 40, 'admin', 'page_view', 'usage', '/students', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:30:06', NULL),
(139, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:30:22', NULL),
(140, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:30:40', NULL),
(141, 40, 'admin', 'page_view', 'usage', '/notes/saisie', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:30:45', NULL),
(142, 40, 'admin', 'grades_created', 'teacher_activity', '/notes/store', 'POST', 'gradebook', 20, 1, '{\"class_id\":76,\"subject_id\":20,\"periode\":\"Sequence 1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:30:54', NULL),
(143, 40, 'admin', 'page_view', 'usage', '/notes/saisie', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:30:55', NULL),
(144, 40, 'admin', 'grades_created', 'teacher_activity', '/notes/store', 'POST', 'gradebook', 20, 1, '{\"class_id\":76,\"subject_id\":20,\"periode\":\"Sequence 2\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:31:05', NULL),
(145, 40, 'admin', 'page_view', 'usage', '/notes/saisie', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:31:05', NULL),
(146, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:31:10', NULL),
(147, 40, 'admin', 'page_view', 'usage', '/bulletins', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:31:24', NULL),
(148, 40, 'admin', 'page_view', 'usage', '/bulletins', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:31:31', NULL),
(149, 40, 'admin', 'page_view', 'usage', '/bulletins/trimestre/class', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:31:36', NULL),
(150, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:32:19', NULL),
(151, 40, 'admin', 'page_view', 'usage', '/notes/saisie', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:33:33', NULL),
(152, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:37:42', NULL),
(153, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:40:52', NULL),
(154, 40, 'admin', 'page_view', 'usage', '/teaching_types', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:41:13', NULL),
(155, 40, 'admin', 'page_view', 'usage', '/sequences', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:41:34', NULL),
(156, 40, 'admin', 'page_view', 'usage', '/teachers', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:41:44', NULL),
(157, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:41:58', NULL),
(158, 40, 'admin', 'page_view', 'usage', '/timetables', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:42:42', NULL),
(159, 40, 'admin', 'direct_delete_executed', 'system', '/api/smart-delete', 'POST', 'timetable', 30, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:43:09', NULL),
(160, 40, 'admin', 'page_view', 'usage', '/timetables', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:43:10', NULL),
(161, 40, 'admin', 'page_view', 'usage', '/timetables', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:44:45', NULL),
(162, 40, 'admin', 'page_view', 'usage', '/timetables', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:45:34', NULL),
(163, 40, 'admin', 'page_view', 'usage', '/teaching_types', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:45:46', NULL),
(164, 40, 'admin', 'page_view', 'usage', '/students/create', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:45:53', NULL),
(165, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:46:00', NULL),
(166, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:47:21', NULL),
(167, 40, 'admin', 'page_view', 'usage', '/timetables', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:47:46', NULL),
(168, 40, 'admin', 'page_view', 'usage', '/timetables', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:48:01', NULL),
(169, 40, 'admin', 'page_view', 'usage', '/timetables', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:48:38', NULL),
(170, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:48:44', NULL),
(171, 40, 'admin', 'page_view', 'usage', '/notes/history', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:49:50', NULL),
(172, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:49:58', NULL),
(173, 40, 'admin', 'page_view', 'usage', '/timetables', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:50:14', NULL),
(174, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:50:39', NULL),
(175, 40, 'admin', 'page_view', 'usage', '/notes/history', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:50:56', NULL),
(176, 40, 'admin', 'page_view', 'usage', '/notes', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:51:03', NULL),
(177, 40, 'admin', 'page_view', 'usage', '/timetables', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:51:56', NULL),
(178, 40, 'admin', 'page_view', 'usage', '/timetables/wizard', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:52:01', NULL),
(179, 40, 'admin', 'page_view', 'usage', '/timetables/api/wizard/cycles', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:52:02', NULL),
(180, 40, 'admin', 'page_view', 'usage', '/timetables/api/wizard/levels', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:52:06', NULL),
(181, 40, 'admin', 'page_view', 'usage', '/timetables/grid', 'GET', NULL, NULL, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 04:52:17', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `cycle_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `main_teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `teaching_type_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `frais_inscription` decimal(15,2) NOT NULL DEFAULT 0.00,
  `frais_inscription_reinscription` decimal(15,2) NOT NULL DEFAULT 0.00,
  `frais_scolarite_brut` decimal(15,2) NOT NULL DEFAULT 0.00,
  `nbr_tranches` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `classes`
--

INSERT INTO `classes` (`id`, `nom`, `cycle_id`, `section_id`, `department_id`, `main_teacher_id`, `created_at`, `teaching_type_id`, `level_id`, `frais_inscription`, `frais_inscription_reinscription`, `frais_scolarite_brut`, `nbr_tranches`) VALUES
(2, 'FORM 1 COM', 3, 2, 4, NULL, '2026-05-03 07:45:00', 3, NULL, 0.00, 0.00, 0.00, 0),
(3, 'FORM 2 COM', 3, 2, 4, NULL, '2026-05-03 12:01:53', 3, NULL, 0.00, 0.00, 0.00, 0),
(5, '5 ème', 3, 1, 4, NULL, '2026-05-06 02:14:30', 3, NULL, 0.00, 0.00, 0.00, 0),
(6, '4 éme ESP', 3, 1, 4, NULL, '2026-05-06 02:14:49', 3, NULL, 0.00, 0.00, 0.00, 0),
(7, '4éme ALL', 3, 1, 4, NULL, '2026-05-06 02:15:06', 3, NULL, 0.00, 0.00, 0.00, 0),
(8, '3 éme ESP', 3, 1, 4, NULL, '2026-05-06 02:15:33', 3, NULL, 0.00, 0.00, 0.00, 0),
(9, '3 éme ALL', 3, 1, 4, NULL, '2026-05-06 02:15:46', 3, NULL, 0.00, 0.00, 0.00, 0),
(10, '2nd STT', 2, 1, 9, NULL, '2026-05-06 02:16:09', 3, NULL, 0.00, 0.00, 0.00, 0),
(12, '2nd C', 2, 1, 4, NULL, '2026-05-06 02:16:41', 3, NULL, 0.00, 0.00, 0.00, 0),
(13, '2nd A4 ESP', 2, 1, 4, NULL, '2026-05-06 02:17:02', 3, NULL, 0.00, 0.00, 0.00, 0),
(14, '2nd A4 ALL', 2, 1, 4, NULL, '2026-05-06 02:17:15', 3, NULL, 0.00, 0.00, 0.00, 0),
(15, '1 ére A4 ESP', 2, 1, 4, NULL, '2026-05-06 02:17:41', 3, NULL, 0.00, 0.00, 0.00, 0),
(16, '1 ére A4 ALL', 2, 1, 4, NULL, '2026-05-06 02:18:01', 3, NULL, 0.00, 0.00, 0.00, 0),
(17, '1 ère CG', 2, 1, 9, NULL, '2026-05-06 02:18:24', 3, NULL, 0.00, 0.00, 0.00, 0),
(18, '1 ère ACA', 2, 1, 9, NULL, '2026-05-06 02:18:46', 3, NULL, 0.00, 0.00, 0.00, 0),
(19, '1 ère ACC', 2, 1, 9, NULL, '2026-05-06 02:19:03', 3, NULL, 0.00, 0.00, 0.00, 0),
(20, '1 ére C', 2, 1, 4, NULL, '2026-05-06 02:19:21', 3, NULL, 0.00, 0.00, 0.00, 0),
(21, '1 ère D', 2, 1, 4, NULL, '2026-05-06 02:19:36', 3, NULL, 0.00, 0.00, 0.00, 0),
(22, 'TLe A4 ESP', 2, 1, 4, NULL, '2026-05-06 02:20:20', 3, NULL, 0.00, 0.00, 0.00, 0),
(23, 'TLe A4 ALL', 2, 1, 4, NULL, '2026-05-06 02:20:31', 3, NULL, 0.00, 0.00, 0.00, 0),
(24, 'TLe D', 2, 1, 4, NULL, '2026-05-06 02:20:45', 3, NULL, 0.00, 0.00, 0.00, 0),
(25, 'TLe C', 2, 1, 4, NULL, '2026-05-06 02:20:57', 3, NULL, 0.00, 0.00, 0.00, 0),
(26, 'TLe ACA', 2, 1, 9, NULL, '2026-05-06 02:21:13', 3, NULL, 0.00, 0.00, 0.00, 0),
(27, 'TLe CG', 2, 1, 9, NULL, '2026-05-06 02:21:25', 3, NULL, 0.00, 0.00, 0.00, 0),
(28, 'TLe ACC', 2, 1, 9, NULL, '2026-05-06 02:21:37', 3, NULL, 0.00, 0.00, 0.00, 0),
(29, 'FORM 3', 3, 2, 4, NULL, '2026-05-06 02:26:11', 3, NULL, 0.00, 0.00, 0.00, 0),
(30, 'FORM 4', 3, 2, 4, NULL, '2026-05-06 02:27:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(31, 'FORM 5', 3, 2, 4, NULL, '2026-05-06 02:27:56', 3, NULL, 0.00, 0.00, 0.00, 0),
(32, 'Lower Sixth', 2, 2, 4, NULL, '2026-05-06 02:28:23', 3, NULL, 0.00, 0.00, 0.00, 0),
(33, 'UPPER SIXTH ACC', 2, 2, 4, NULL, '2026-05-06 02:28:36', 3, NULL, 0.00, 0.00, 0.00, 0),
(34, '1 ère Année MEFE', 3, 1, 10, NULL, '2026-05-06 04:02:39', 3, 1, 0.00, 0.00, 0.00, 0),
(35, '2 ère Année MEFE', 3, 1, 10, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(36, '3 ère Année MEFE', 3, 1, 10, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(37, '4 ère Année MEFE', 3, 1, 10, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(38, '2nd CH', 2, 1, 10, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(39, '1ère CH-TI', 2, 1, 10, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(40, 'Tle CH-TI', 2, 1, 10, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(41, 'FORM 1 Science', 3, 2, 10, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(42, 'FORM 2 MW', 3, 2, 10, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(43, 'FORM 3 science', 3, 2, 4, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(44, 'FORM 4 science', 3, 2, 4, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(45, 'FORM 5 Science', 3, 2, 4, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(46, 'Lower Sixth MW', 2, 2, 10, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(47, 'Upper Sixth MW', 2, 2, 10, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(48, '1 ère Année MACO', 3, 1, 2, NULL, '2026-05-06 04:02:39', 3, 1, 0.00, 0.00, 0.00, 0),
(49, '2 ère Année MACO', 3, 1, 2, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(50, '3 ère Année MACO', 3, 1, 2, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(51, '4 ère Année MACO', 3, 1, 2, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(52, '2nd F4', 2, 1, 2, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(53, '1ère F4-BA', 2, 1, 2, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(54, 'Tle F4-BA', 2, 1, 2, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(55, 'FORM 1 BC', 3, 2, 2, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(56, 'FORM 2 ACC', 3, 2, 2, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(57, 'FORM 3 ACC', 3, 2, 2, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(58, 'FORM 4 Acc', 3, 2, 2, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(59, 'FORM 5 BC', 3, 2, 2, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(60, 'Lower Sixth BC', 2, 2, 2, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(61, 'UPPER SIXTH ART', 2, 2, 2, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(62, '1 ère Année ELECT', 3, 1, 6, NULL, '2026-05-06 04:02:39', 3, 1, 0.00, 0.00, 0.00, 0),
(63, '2 ère Année ELECT', 3, 1, 6, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(64, '3 ère Année ELECT', 3, 1, 6, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(65, '4 ère Année ELECT', 3, 1, 6, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(66, '2nd F3', 2, 1, 6, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(67, '1ère F3', 2, 1, 6, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(68, 'Tle F3', 2, 1, 6, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(69, 'FORM 1 EE', 3, 2, 6, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(70, 'FORM 2 EE', 3, 2, 6, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(71, 'FORM 3 EE', 3, 2, 6, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(72, 'FORM 4 EE', 3, 2, 6, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(73, 'FORM 5 ART', 3, 2, 4, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(74, 'Lower Sixth EE', 2, 2, 6, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(75, 'UPPER SIXTH SCIENCE', 2, 2, 6, NULL, '2026-05-06 04:02:39', 3, NULL, 0.00, 0.00, 0.00, 0),
(76, '6éme', 3, 1, 4, NULL, '2026-05-30 01:42:32', 3, NULL, 0.00, 0.00, 0.00, 0),
(77, '1 ère année escom', 3, 1, 3, NULL, '2026-06-04 06:41:32', 3, 1, 0.00, 0.00, 0.00, 0),
(78, '2 ème année escom', 3, 1, 3, NULL, '2026-06-04 06:42:18', 3, NULL, 0.00, 0.00, 0.00, 0),
(79, '3 ème année escom', 3, 1, 3, NULL, '2026-06-04 06:42:50', 3, NULL, 0.00, 0.00, 0.00, 0),
(80, '4 ème année escom', 3, NULL, 9, NULL, '2026-06-04 06:43:45', 3, NULL, 0.00, 0.00, 0.00, 0),
(81, '1 ère année MARE', 3, 1, 7, NULL, '2026-06-04 09:34:15', 3, 1, 0.00, 0.00, 0.00, 0),
(82, '2 ère année MARE', 3, 1, 7, NULL, '2026-06-04 09:44:01', 3, NULL, 0.00, 0.00, 0.00, 0),
(84, '2nd F8', 2, 1, 12, NULL, '2026-06-04 10:57:45', 3, NULL, 0.00, 0.00, 0.00, 0),
(85, '2nd IH', 2, 1, 23, NULL, '2026-06-04 10:58:11', 3, NULL, 0.00, 0.00, 0.00, 0),
(86, '1 ère année COME', 3, 1, 11, NULL, '2026-06-04 12:19:03', 3, 1, 0.00, 0.00, 0.00, 0),
(87, '2 ème année COME', 3, 1, 11, NULL, '2026-06-04 12:20:15', 3, NULL, 0.00, 0.00, 0.00, 0),
(88, '3 ème année COME', 3, 1, 11, NULL, '2026-06-04 12:20:44', 3, NULL, 0.00, 0.00, 0.00, 0),
(89, '4 ème année COME', 3, 1, 11, NULL, '2026-06-04 13:40:57', 3, NULL, 0.00, 0.00, 0.00, 0),
(90, '2 ème année SEME', 3, 1, 12, NULL, '2026-06-05 13:11:06', 3, NULL, 0.00, 0.00, 0.00, 0),
(91, '3 ème année SEME', 3, 1, 12, NULL, '2026-06-05 13:12:09', 3, NULL, 0.00, 0.00, 0.00, 0),
(93, '6 eme C', 3, 1, 4, NULL, '2026-06-06 13:36:07', 3, NULL, 0.00, 0.00, 0.00, 0),
(96, 'SIL', 3, 1, 26, NULL, '2026-06-17 14:42:41', 2, NULL, 0.00, 0.00, 0.00, 0),
(97, 'CEP', 3, 1, 26, NULL, '2026-06-17 14:42:57', 2, NULL, 0.00, 0.00, 0.00, 0),
(98, 'CE1', 3, 1, 26, NULL, '2026-06-17 14:44:14', 2, NULL, 0.00, 0.00, 0.00, 0),
(99, 'CE2', 3, 1, 26, NULL, '2026-06-17 14:44:30', 2, NULL, 0.00, 0.00, 0.00, 0);

-- --------------------------------------------------------

--
-- Structure de la table `class_discounts`
--

CREATE TABLE `class_discounts` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `discount_type_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `amount_type` enum('fixed','percentage') NOT NULL,
  `motive` varchar(255) NOT NULL,
  `date_effet` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `commentaire` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `class_installments`
--

CREATE TABLE `class_installments` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `installment_number` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `class_installments`
--

INSERT INTO `class_installments` (`id`, `class_id`, `installment_number`, `amount`, `created_at`) VALUES
(6, 93, 1, 60000.00, '2026-06-27 12:03:38'),
(7, 93, 2, 30000.00, '2026-06-27 12:03:38'),
(8, 93, 3, 10000.00, '2026-06-27 12:03:38'),
(9, 104, 1, 70000.00, '2026-07-01 15:54:43'),
(10, 104, 2, 30000.00, '2026-07-01 15:54:43'),
(11, 104, 3, 20000.00, '2026-07-01 15:54:43'),
(12, 105, 1, 70000.00, '2026-07-01 16:06:06'),
(13, 105, 2, 30000.00, '2026-07-01 16:06:06'),
(14, 105, 3, 20000.00, '2026-07-01 16:06:06'),
(18, 76, 1, 40000.00, '2026-07-09 10:15:11'),
(19, 76, 2, 20000.00, '2026-07-09 10:15:11'),
(20, 76, 3, 10000.00, '2026-07-09 10:15:11'),
(35, 107, 1, 500000.00, '2026-07-29 02:21:18'),
(36, 107, 2, 200000.00, '2026-07-29 02:21:18'),
(40, 106, 1, 0.00, '2026-08-06 01:31:38'),
(41, 106, 2, 0.00, '2026-08-06 01:31:38'),
(42, 106, 3, 0.00, '2026-08-06 01:31:38');

-- --------------------------------------------------------

--
-- Structure de la table `class_rooms`
--

CREATE TABLE `class_rooms` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `capacite` int(11) NOT NULL DEFAULT 30,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `class_rooms`
--

INSERT INTO `class_rooms` (`id`, `nom`, `code`, `capacite`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Salle Amphi A', 'AMPHI-A', 150, 'Grand amphithéâtre principal avec vidéoprojecteur', 1, '2026-08-04 03:53:05', '2026-08-04 03:53:05'),
(2, 'Salle Amphi B', 'AMPHI-B', 120, 'Amphithéâtre secondaire', 1, '2026-08-04 03:53:05', '2026-08-04 03:53:05'),
(3, 'Salle A11', 'MAC-ISTEC-1', 23, 'Salle de 23 places assises', 1, '2026-08-04 03:53:05', '2026-08-04 04:07:43'),
(4, 'Labo Informatique 2', 'LAB-INFO-2', 35, 'Salle équipée de 35 postes informatiques', 1, '2026-08-04 03:53:05', '2026-08-04 03:53:05'),
(5, 'Salle 101', 'S-101', 50, 'Salle de cours standard', 1, '2026-08-04 03:53:05', '2026-08-04 03:53:05'),
(6, 'Salle 102', 'S-102', 50, 'Salle de cours standard', 1, '2026-08-04 03:53:05', '2026-08-04 03:53:05'),
(7, 'Salle 103', 'S-103', 45, 'Salle de cours standard', 1, '2026-08-04 03:53:05', '2026-08-04 03:53:05'),
(8, 'B0111', 'SEC00', 20, '', 1, '2026-08-04 04:08:23', '2026-08-04 04:08:23');

-- --------------------------------------------------------

--
-- Structure de la table `class_scholarships`
--

CREATE TABLE `class_scholarships` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `discount_type_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `amount_type` enum('fixed','percentage') NOT NULL,
  `motive` varchar(255) NOT NULL,
  `date_effet` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `commentaire` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `conseils_classe`
--

CREATE TABLE `conseils_classe` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `date_conseil` date DEFAULT NULL,
  `is_validated` tinyint(1) NOT NULL DEFAULT 0,
  `validated_by` int(11) DEFAULT NULL,
  `validated_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cycles`
--

CREATE TABLE `cycles` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `teaching_type_id` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cycles`
--

INSERT INTO `cycles` (`id`, `nom`, `created_at`, `teaching_type_id`, `status`) VALUES
(2, '2nd Cycle', '2026-03-25 19:39:23', 3, 1),
(3, '1ere Cycle', '2026-03-26 17:43:38', 3, 1),
(14, 'BTS', '2026-07-25 01:41:05', 9, 1),
(15, 'HND', '2026-07-25 01:41:25', 9, 1),
(16, 'LICENCE', '2026-07-25 01:41:33', 9, 1),
(17, 'BACHELOR', '2026-07-25 01:41:50', 9, 1),
(18, 'MASTER', '2026-07-25 01:42:02', 9, 1);

-- --------------------------------------------------------

--
-- Structure de la table `cycle_levels`
--

CREATE TABLE `cycle_levels` (
  `cycle_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cycle_levels`
--

INSERT INTO `cycle_levels` (`cycle_id`, `level_id`, `created_at`) VALUES
(2, 1, '2026-08-07 03:37:30'),
(3, 1, '2026-08-07 03:37:30'),
(14, 2, '2026-08-07 03:37:30'),
(14, 3, '2026-08-07 03:37:30'),
(15, 2, '2026-08-07 03:39:05'),
(15, 3, '2026-08-07 03:39:05'),
(16, 4, '2026-08-07 03:38:54'),
(17, 4, '2026-08-07 03:39:19'),
(18, 5, '2026-08-07 03:39:29'),
(18, 6, '2026-08-07 03:39:29');

-- --------------------------------------------------------

--
-- Structure de la table `decisions_fin_annee`
--

CREATE TABLE `decisions_fin_annee` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `conseil_id` int(11) DEFAULT NULL,
  `moyenne_annuelle` decimal(5,2) DEFAULT NULL,
  `rang` int(11) DEFAULT NULL,
  `decision` enum('admis','admis_avec_reserve','redouble','renvoye','transfert','demissionnaire') NOT NULL,
  `classe_destination_id` int(11) DEFAULT NULL,
  `raison_renvoi` text DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `teaching_type_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `departments`
--

INSERT INTO `departments` (`id`, `nom`, `code`, `status`, `created_at`, `teaching_type_id`) VALUES
(2, 'Maçonnerie', 'MACO', 1, '2026-03-25 20:04:30', 3),
(3, 'Employes et Services Comptable(ESCOM)', 'ESCOM', 1, '2026-03-26 17:47:42', 3),
(4, 'ESG (Enseignement General)', 'ESG', 1, '2026-03-26 17:47:42', 3),
(6, 'Electricite (ELECT)', 'ELECT', 1, '2026-03-26 17:47:42', 3),
(7, 'Mecanique', 'MECA', 1, '2026-03-26 17:47:42', 3),
(8, 'Informatique et Reseaux (TI)', 'TI', 0, '2026-03-26 17:47:42', 3),
(9, 'Gestion', 'CG', 1, '2026-03-26 17:47:42', 3),
(10, 'Chaudronerie et Soudure', 'CH', 1, '2026-03-26 17:47:42', 3),
(11, 'Couture (COME)', 'COME', 1, '2026-03-26 17:47:42', 3),
(12, 'Secretariat Medical (SEME)', 'SEME', 1, '2026-03-26 17:47:42', 3),
(23, 'Couture Industrielle (IH)', 'IH', 1, '2026-05-06 03:41:31', 3),
(24, 'Metaux et Ferre(MEFE)', 'MEFE', 1, '2026-05-06 03:44:16', 3),
(25, 'MARTERNEL', 'MAT', 1, '2026-06-17 14:40:45', 1),
(26, 'PRIMAIRE', 'PRIM', 1, '2026-06-17 14:41:03', 2),
(27, 'Génie Informatique', 'TIC', 1, '2026-06-22 18:31:01', 9),
(28, 'Génie Civil', 'GC', 1, '2026-06-22 18:32:21', 9),
(30, 'Gestionn', 'D', 1, '2026-08-06 06:30:42', 9),
(31, 'Génie Mécanique', 'GME', 1, '2026-08-06 06:32:44', 9);

-- --------------------------------------------------------

--
-- Structure de la table `discipline`
--

CREATE TABLE `discipline` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `periode` varchar(50) NOT NULL,
  `absences_total` int(11) DEFAULT 0,
  `absences_justified` int(11) DEFAULT 0,
  `exclusion_days` int(11) DEFAULT 0,
  `consignes` int(11) DEFAULT 0,
  `conduct` varchar(120) DEFAULT '',
  `warning_conduct` tinyint(1) DEFAULT 0,
  `blame_conduct` tinyint(1) DEFAULT 0,
  `warning_work` tinyint(1) DEFAULT 0,
  `tableau_honneur` tinyint(1) DEFAULT 0,
  `encouragements` tinyint(1) DEFAULT 0,
  `felicitations` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `discount_types`
--

CREATE TABLE `discount_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `discount_types`
--

INSERT INTO `discount_types` (`id`, `name`, `description`, `comment`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Prime d\'ancienneté', 'Prime accordé aux personnes ayant déjà fais au-moins 5ans', NULL, 'inactive', 40, '2026-07-03 23:53:01', '2026-07-03 23:53:01'),
(2, 'Prime d\'excellence', 'Prime accordé aux meilleurs élèves', 'Meilleures élèves de l\'établissement, de la salle', 'inactive', 40, '2026-07-03 23:54:09', '2026-07-03 23:54:09'),
(3, 'Prime Spéciale propreté', 'Réduction accordé aux élèves les plus propres', NULL, 'inactive', 40, '2026-07-03 23:55:23', '2026-07-03 23:55:23'),
(4, 'Prime sur le nombre d\'enfants inscrits', 'Prime accordé pour le nombre d\'enfants inscrits pour un même parent', 'Parents qui inscrits plusieurs enfants', 'inactive', 40, '2026-07-03 23:56:51', '2026-07-03 23:56:51'),
(5, 'Prime pour sinistre', 'Prime d\'aide aux victimes de catastrophes naturelles et aux personnes en difficulté', 'Prime accordé aux personnes malades etc...', 'inactive', 40, '2026-07-03 23:59:41', '2026-07-25 17:22:50');

-- --------------------------------------------------------

--
-- Structure de la table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `student_status` enum('nouveau','ancien') NOT NULL DEFAULT 'nouveau',
  `frais_scolarite_brut` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_reductions` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_bourses` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_paye` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reste_a_payer` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `class_id`, `academic_year_id`, `student_status`, `frais_scolarite_brut`, `total_reductions`, `total_bourses`, `total_paye`, `reste_a_payer`, `created_at`, `updated_at`) VALUES
(1, 1, 106, 3, 'nouveau', 0.00, 0.00, 0.00, 0.00, 0.00, '2026-07-30 23:57:48', '2026-07-30 23:57:48'),
(2, 2, 93, 3, 'nouveau', 100000.00, 0.00, 0.00, 0.00, 100000.00, '2026-07-30 23:58:39', '2026-07-30 23:58:39'),
(3, 404, 76, 3, 'nouveau', 70000.00, 0.00, 0.00, 0.00, 70000.00, '2026-08-21 04:30:04', '2026-08-21 04:30:05');

-- --------------------------------------------------------

--
-- Structure de la table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `reference` varchar(50) NOT NULL,
  `expense_date` date NOT NULL,
  `category_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `motive` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `status` enum('active','inactive','cancelled') DEFAULT 'active',
  `cancel_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `expense_categories`
--

INSERT INTO `expense_categories` (`id`, `name`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Fournitures de bureau', 1, '2026-07-08 21:31:26', '2026-07-08 21:31:26'),
(2, 'Salaires', 1, '2026-07-08 21:31:26', '2026-07-08 21:31:26'),
(3, 'Entretien', 1, '2026-07-08 21:31:26', '2026-07-08 21:31:26'),
(4, 'Électricité', 1, '2026-07-08 21:31:26', '2026-07-08 21:31:26'),
(5, 'Eau', 1, '2026-07-08 21:31:26', '2026-07-08 21:31:26'),
(6, 'Internet', 1, '2026-07-08 21:31:26', '2026-07-08 21:31:26'),
(7, 'Transport', 1, '2026-07-08 21:31:26', '2026-07-08 21:31:26'),
(8, 'Maintenance informatique', 1, '2026-07-08 21:31:26', '2026-07-08 21:31:26'),
(9, 'Événements', 1, '2026-07-08 21:31:26', '2026-07-08 21:31:26'),
(10, 'Divers', 1, '2026-07-08 21:31:26', '2026-07-08 21:31:26'),
(11, 'Rafraîchissement Personnel', 1, '2026-07-25 17:18:37', '2026-07-25 17:18:37');

-- --------------------------------------------------------

--
-- Structure de la table `expense_logs`
--

CREATE TABLE `expense_logs` (
  `id` int(11) NOT NULL,
  `expense_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('create','update','deactivate','reactivate','cancel') NOT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fee_installments`
--

CREATE TABLE `fee_installments` (
  `id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `installment_order` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `deadline_date` date NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `cycle_id` int(11) DEFAULT NULL,
  `teaching_type_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `fee_installments`
--

INSERT INTO `fee_installments` (`id`, `academic_year_id`, `name`, `installment_order`, `amount`, `deadline_date`, `class_id`, `cycle_id`, `teaching_type_id`, `created_at`) VALUES
(6, 3, 'Tranche 1', 1, 60000.00, '2026-07-11', 93, NULL, NULL, '2026-06-27 12:03:38'),
(7, 3, 'Tranche 2', 2, 30000.00, '2026-08-27', 93, NULL, NULL, '2026-06-27 12:03:38'),
(8, 3, 'Tranche 3', 3, 10000.00, '2026-09-25', 93, NULL, NULL, '2026-06-27 12:03:38'),
(9, 3, 'Tranche 1', 1, 70000.00, '2026-07-08', 104, NULL, NULL, '2026-07-01 15:54:43'),
(10, 3, 'Tranche 2', 2, 30000.00, '2026-08-14', 104, NULL, NULL, '2026-07-01 15:54:43'),
(11, 3, 'Tranche 3', 3, 20000.00, '2026-09-16', 104, NULL, NULL, '2026-07-01 15:54:43'),
(12, 3, 'Tranche 1', 1, 70000.00, '2026-07-31', 105, NULL, NULL, '2026-07-01 16:06:06'),
(13, 3, 'Tranche 2', 2, 30000.00, '2026-09-02', 105, NULL, NULL, '2026-07-01 16:06:06'),
(14, 3, 'Tranche 3', 3, 20000.00, '2026-12-02', 105, NULL, NULL, '2026-07-01 16:06:06'),
(18, 3, 'Tranche 1', 1, 40000.00, '2026-09-30', 76, NULL, NULL, '2026-07-09 10:15:11'),
(19, 3, 'Tranche 2', 2, 20000.00, '2026-11-12', 76, NULL, NULL, '2026-07-09 10:15:11'),
(20, 3, 'Tranche 3', 3, 10000.00, '2026-01-12', 76, NULL, NULL, '2026-07-09 10:15:11'),
(34, 3, 'Tranche 1', 1, 500000.00, '2026-12-12', 107, NULL, NULL, '2026-07-29 02:21:18'),
(35, 3, 'Tranche 2', 2, 200000.00, '2027-10-13', 107, NULL, NULL, '2026-07-29 02:21:18'),
(39, 3, 'Tranche 1', 1, 0.00, '2026-12-31', 106, NULL, NULL, '2026-08-06 01:31:38'),
(40, 3, 'Tranche 2', 2, 0.00, '2026-12-31', 106, NULL, NULL, '2026-08-06 01:31:38'),
(41, 3, 'Tranche 3', 3, 0.00, '2026-12-31', 106, NULL, NULL, '2026-08-06 01:31:38');

-- --------------------------------------------------------

--
-- Structure de la table `financial_history`
--

CREATE TABLE `financial_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_date` timestamp NULL DEFAULT current_timestamp(),
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `financial_history`
--

INSERT INTO `financial_history` (`id`, `user_id`, `event_date`, `entity_type`, `entity_id`, `action`, `old_value`, `new_value`) VALUES
(59, 42, '2026-07-25 02:36:42', 'class_finance', 106, 'create', '', '{\"nom\":\"IGL 1\",\"frais_inscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(60, 42, '2026-07-25 02:41:04', 'class_finance', 106, 'update', '{\"nom\":\"IGL 1\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":0,\"tranches\":[]}', '{\"nom\":\"IGL 1\",\"frais_inscription\":30000,\"frais_inscription_reinscription\":30000,\"frais_scolarite_brut\":370000,\"nbr_tranches\":3,\"tranches\":{\"1\":{\"amount\":\"230000\",\"deadline\":\"2026-07-25\"},\"2\":{\"amount\":\"110000\",\"deadline\":\"2026-08-02\"},\"3\":{\"amount\":\"30000\",\"deadline\":\"2026-08-08\"}}}'),
(63, 42, '2026-07-25 16:49:07', 'class_finance', 107, 'create', '', '{\"nom\":\"IGL 4\",\"frais_inscription\":75000,\"frais_scolarite_brut\":645000,\"nbr_tranches\":3,\"tranches\":{\"1\":{\"amount\":\"420000\",\"deadline\":\"2026-07-26\"},\"2\":{\"amount\":\"160000\",\"deadline\":\"2026-07-31\"},\"3\":{\"amount\":\"65000\",\"deadline\":\"2026-08-08\"}}}'),
(68, 42, '2026-07-25 17:03:48', 'class_finance', 107, 'update', '{\"nom\":\"IGL 4\",\"frais_inscription\":\"75000.00\",\"frais_inscription_reinscription\":\"750000.00\",\"frais_scolarite_brut\":\"700000.00\",\"nbr_tranches\":2,\"tranches\":{\"1\":\"500000.00\",\"2\":\"200000.00\"}}', '{\"nom\":\"IGL 4\",\"frais_inscription\":75000,\"frais_inscription_reinscription\":75000,\"frais_scolarite_brut\":700000,\"nbr_tranches\":2,\"tranches\":{\"1\":{\"amount\":\"500000.00\",\"deadline\":\"2026-12-12\"},\"2\":{\"amount\":\"200000.00\",\"deadline\":\"2027-10-13\"}}}'),
(69, 40, '2026-07-29 02:20:45', 'class_finance', 106, 'update', '{\"nom\":\"IGL 1\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":3,\"tranches\":[]}', '{\"nom\":\"IGL 1\",\"frais_inscription\":0,\"frais_inscription_reinscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":3,\"tranches\":[]}'),
(70, 40, '2026-07-29 02:21:07', 'class_finance', 113, 'update', '{\"nom\":\"IGL 2\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":0,\"tranches\":[]}', '{\"nom\":\"IGL 2\",\"frais_inscription\":0,\"frais_inscription_reinscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(71, 40, '2026-07-29 02:21:18', 'class_finance', 107, 'update', '{\"nom\":\"IGL 4\",\"frais_inscription\":\"75000.00\",\"frais_inscription_reinscription\":\"75000.00\",\"frais_scolarite_brut\":\"700000.00\",\"nbr_tranches\":2,\"tranches\":{\"1\":\"500000.00\",\"2\":\"200000.00\"}}', '{\"nom\":\"IGL 4\",\"frais_inscription\":75000,\"frais_inscription_reinscription\":75000,\"frais_scolarite_brut\":700000,\"nbr_tranches\":2,\"tranches\":{\"1\":{\"amount\":\"500000.00\",\"deadline\":\"2026-12-12\"},\"2\":{\"amount\":\"200000.00\",\"deadline\":\"2027-10-13\"}}}'),
(72, 40, '2026-07-30 23:58:39', 'payment', 1, 'create', '', '{\"student_id\":2,\"amount\":20000,\"type\":\"inscription\",\"payment_method\":\"Espèces\",\"reference\":\"0AKUX6Y5STGHOHSXEGJZ\",\"commentaire\":\"Frais d\'inscription réglés lors de la création de l\'élève\"}'),
(73, 40, '2026-07-30 23:58:39', 'payment', 1, 'print', '0', '1'),
(74, 40, '2026-07-31 01:07:00', 'class_finance', 106, 'update', '{\"nom\":\"IGL 1\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":3,\"tranches\":{\"1\":\"0.00\",\"2\":\"0.00\",\"3\":\"0.00\"}}', '{\"nom\":\"Génie Logiciel\",\"frais_inscription\":0,\"frais_inscription_reinscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":3,\"tranches\":[]}'),
(75, 40, '2026-08-02 04:47:49', 'class_finance', 114, 'create', '', '{\"nom\":\"BAT 1\",\"frais_inscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(76, 40, '2026-08-06 01:28:53', 'class_finance', 115, 'create', '', '{\"nom\":\"BAT 2\",\"frais_inscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(77, 40, '2026-08-06 01:29:32', 'class_finance', 116, 'create', '', '{\"nom\":\"MSI 1\",\"frais_inscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(78, 40, '2026-08-06 01:30:01', 'class_finance', 117, 'create', '', '{\"nom\":\"RS 1\",\"frais_inscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(79, 40, '2026-08-06 01:30:55', 'class_finance', 118, 'create', '', '{\"nom\":\"TLecom 1\",\"frais_inscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(80, 40, '2026-08-06 01:31:15', 'class_finance', 119, 'create', '', '{\"nom\":\"GSI 1\",\"frais_inscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(81, 40, '2026-08-06 01:31:38', 'class_finance', 106, 'update', '{\"nom\":\"Génie Logiciel\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":3,\"tranches\":{\"1\":\"0.00\",\"2\":\"0.00\",\"3\":\"0.00\"}}', '{\"nom\":\"IGL 1\",\"frais_inscription\":0,\"frais_inscription_reinscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":3,\"tranches\":[]}'),
(82, 40, '2026-08-08 04:35:41', 'class_finance', 120, 'create', '', '{\"nom\":\"SWE 1\",\"frais_inscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(83, 40, '2026-08-08 04:36:09', 'class_finance', 121, 'create', '', '{\"nom\":\"SWE 2\",\"frais_inscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(84, 40, '2026-08-08 04:36:33', 'class_finance', 122, 'create', '', '{\"nom\":\"ACC 1\",\"frais_inscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(85, 40, '2026-08-08 04:36:54', 'class_finance', 122, 'update', '{\"nom\":\"ACC 1\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":0,\"tranches\":[]}', '{\"nom\":\"ACC 1\",\"frais_inscription\":0,\"frais_inscription_reinscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(86, 40, '2026-08-08 04:37:13', 'class_finance', 121, 'update', '{\"nom\":\"SWE 2\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":0,\"tranches\":[]}', '{\"nom\":\"SWE 2\",\"frais_inscription\":0,\"frais_inscription_reinscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(87, 40, '2026-08-08 04:37:24', 'class_finance', 120, 'update', '{\"nom\":\"SWE 1\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":0,\"tranches\":[]}', '{\"nom\":\"SWE 1\",\"frais_inscription\":0,\"frais_inscription_reinscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(88, 40, '2026-08-08 04:38:41', 'class_finance', 123, 'create', '', '{\"nom\":\"SWE 3\",\"frais_inscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(89, 40, '2026-08-08 04:39:17', 'class_finance', 124, 'create', '', '{\"nom\":\"ACC 3\",\"frais_inscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(90, 40, '2026-08-10 03:01:19', 'class_finance', 121, 'update', '{\"nom\":\"SWE 2\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":0,\"tranches\":[]}', '{\"nom\":\"SWE 2\",\"frais_inscription\":0,\"frais_inscription_reinscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(91, 40, '2026-08-21 01:58:14', 'class_finance', 86, 'update', '{\"nom\":\"1 ère année COME\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":0,\"tranches\":{\"1\":\"65000.00\",\"2\":\"20000.00\",\"3\":\"15000.00\"}}', '{\"nom\":\"1 ère année COME\",\"frais_inscription\":0,\"frais_inscription_reinscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(92, 40, '2026-08-21 01:58:50', 'class_finance', 62, 'update', '{\"nom\":\"1 ère Année ELECT\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":0,\"tranches\":[]}', '{\"nom\":\"1 ère Année ELECT\",\"frais_inscription\":0,\"frais_inscription_reinscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(93, 40, '2026-08-21 01:59:06', 'class_finance', 77, 'update', '{\"nom\":\"1 ère année escom\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":0,\"tranches\":[]}', '{\"nom\":\"1 ère année escom\",\"frais_inscription\":0,\"frais_inscription_reinscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(94, 40, '2026-08-21 01:59:22', 'class_finance', 48, 'update', '{\"nom\":\"1 ère Année MACO\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":0,\"tranches\":[]}', '{\"nom\":\"1 ère Année MACO\",\"frais_inscription\":0,\"frais_inscription_reinscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(95, 40, '2026-08-21 01:59:41', 'class_finance', 81, 'update', '{\"nom\":\"1 ère année MARE\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":0,\"tranches\":[]}', '{\"nom\":\"1 ère année MARE\",\"frais_inscription\":0,\"frais_inscription_reinscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}'),
(96, 40, '2026-08-21 01:59:58', 'class_finance', 34, 'update', '{\"nom\":\"1 ère Année MEFE\",\"frais_inscription\":\"0.00\",\"frais_inscription_reinscription\":\"0.00\",\"frais_scolarite_brut\":\"0.00\",\"nbr_tranches\":0,\"tranches\":[]}', '{\"nom\":\"1 ère Année MEFE\",\"frais_inscription\":0,\"frais_inscription_reinscription\":0,\"frais_scolarite_brut\":0,\"nbr_tranches\":0,\"tranches\":[]}');

-- --------------------------------------------------------

--
-- Structure de la table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `academic_year_id` int(11) DEFAULT NULL,
  `sequence_id` int(11) DEFAULT NULL,
  `periode` varchar(50) NOT NULL,
  `valeur` float DEFAULT NULL,
  `appreciation` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `teacher_nom_snapshot` varchar(100) DEFAULT NULL COMMENT 'Nom de l''enseignant au moment de la saisie',
  `teacher_prenom_snapshot` varchar(100) DEFAULT NULL COMMENT 'Prénom de l''enseignant au moment de la saisie',
  `subject_nom_snapshot` varchar(100) DEFAULT NULL COMMENT 'Nom de la matière au moment de la saisie',
  `created_by_type` enum('enseignant','admin') DEFAULT 'enseignant' COMMENT 'Type de créateur (enseignant ou admin)',
  `teaching_type_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `subject_id`, `teacher_id`, `academic_year_id`, `sequence_id`, `periode`, `valeur`, `appreciation`, `created_at`, `updated_at`, `teacher_nom_snapshot`, `teacher_prenom_snapshot`, `subject_nom_snapshot`, `created_by_type`, `teaching_type_id`) VALUES
(1, 404, 20, 40, 3, 1, 'Sequence 1', 13, 'Assez Bien', '2026-08-21 04:30:54', '2026-08-21 04:30:54', 'Directeur', 'Directeur', 'ANGLAIS', 'admin', NULL),
(2, 404, 20, 40, 3, 2, 'Sequence 2', 13.25, 'Assez Bien', '2026-08-21 04:31:05', '2026-08-21 04:31:05', 'Directeur', 'Directeur', 'ANGLAIS', 'admin', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `historique_modifications_conseil`
--

CREATE TABLE `historique_modifications_conseil` (
  `id` int(11) NOT NULL,
  `decision_id` int(11) NOT NULL,
  `field_name` varchar(50) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `motif` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `historique_passages`
--

CREATE TABLE `historique_passages` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `from_class_id` int(11) DEFAULT NULL,
  `from_academic_year_id` int(11) NOT NULL,
  `to_class_id` int(11) DEFAULT NULL,
  `to_academic_year_id` int(11) DEFAULT NULL,
  `decision` enum('admis','admis_avec_reserve','redouble','renvoye','transfert','demissionnaire') NOT NULL,
  `moyenne_annuelle` decimal(5,2) DEFAULT NULL,
  `processed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `insolvent_students`
--

CREATE TABLE `insolvent_students` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `amount_due` decimal(15,2) NOT NULL,
  `unpaid_installments_count` int(11) NOT NULL,
  `last_overdue_deadline` date NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `insolvent_students`
--

INSERT INTO `insolvent_students` (`id`, `student_id`, `academic_year_id`, `amount_due`, `unpaid_installments_count`, `last_overdue_deadline`, `updated_at`) VALUES
(11, 404, 3, 70000.00, 3, '2026-11-12', '2026-08-21 04:30:05');

-- --------------------------------------------------------

--
-- Structure de la table `installment_deadlines`
--

CREATE TABLE `installment_deadlines` (
  `id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `installment_number` int(11) NOT NULL,
  `deadline_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `installment_deadlines`
--

INSERT INTO `installment_deadlines` (`id`, `academic_year_id`, `class_id`, `installment_number`, `deadline_date`, `created_at`) VALUES
(6, 3, 93, 1, '2026-07-11', '2026-06-27 12:03:38'),
(7, 3, 93, 2, '2026-08-27', '2026-06-27 12:03:38'),
(8, 3, 93, 3, '2026-09-25', '2026-06-27 12:03:38'),
(9, 3, 104, 1, '2026-07-08', '2026-07-01 15:54:43'),
(10, 3, 104, 2, '2026-08-14', '2026-07-01 15:54:43'),
(11, 3, 104, 3, '2026-09-16', '2026-07-01 15:54:43'),
(12, 3, 105, 1, '2026-07-31', '2026-07-01 16:06:06'),
(13, 3, 105, 2, '2026-09-02', '2026-07-01 16:06:06'),
(14, 3, 105, 3, '2026-12-02', '2026-07-01 16:06:06'),
(18, 3, 76, 1, '2026-09-30', '2026-07-09 10:15:11'),
(19, 3, 76, 2, '2026-11-12', '2026-07-09 10:15:11'),
(20, 3, 76, 3, '2026-01-12', '2026-07-09 10:15:11'),
(31, 3, 107, 1, '2026-12-12', '2026-07-29 02:21:18'),
(32, 3, 107, 2, '2027-10-13', '2026-07-29 02:21:18');

-- --------------------------------------------------------

--
-- Structure de la table `levels`
--

CREATE TABLE `levels` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `libelle_fr` varchar(150) NOT NULL,
  `libelle_en` varchar(150) NOT NULL,
  `teaching_type_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `levels`
--

INSERT INTO `levels` (`id`, `code`, `libelle_fr`, `libelle_en`, `teaching_type_id`, `status`, `created_at`, `updated_at`) VALUES
(1, '6éME', 'SIXIÈME', 'class 1', 3, 1, '2026-07-29 02:14:11', '2026-07-29 02:14:33'),
(2, '1', '1', 'Level 1', 9, 1, '2026-07-29 02:15:38', '2026-07-31 01:07:36'),
(3, '2', '2', 'Level 2', 9, 1, '2026-07-29 02:15:53', '2026-07-31 01:07:43'),
(4, '3', '3', 'Level 3', 9, 1, '2026-07-29 02:16:01', '2026-07-31 01:07:50'),
(5, '4', '4', 'Level 4', 9, 1, '2026-07-29 02:16:10', '2026-07-31 01:07:57'),
(6, '5', '5', 'Level 5', 9, 1, '2026-07-29 02:16:18', '2026-07-31 01:08:03'),
(7, '6', '6', 'Level 6', 9, 1, '2026-07-29 02:16:29', '2026-07-31 01:08:11'),
(8, '5éME', 'Cinquième', 'Form 2', 3, 0, '2026-07-31 03:47:51', '2026-07-31 03:47:51'),
(9, '4éME', 'Quatrième', 'Form 3', 9, 0, '2026-07-31 03:49:48', '2026-07-31 03:51:11');

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  `executed_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES
(1, 'scripts/migration_add_student_workflow.php', 1, '2026-07-08 21:26:26'),
(2, 'scripts/migration_add_expenses_module.php', 1, '2026-07-08 21:31:26'),
(3, 'scripts/migration_add_academic_year_columns.php', 1784937215, '2026-07-24 23:53:38'),
(4, 'scripts/migration_add_payment_status_and_parent.php', 1784937215, '2026-07-24 23:53:40'),
(5, 'scripts/migration_add_user_status.php', 1784937215, '2026-07-24 23:53:40'),
(6, 'scripts/migration_receipt_verifications_log.php', 1784937215, '2026-07-24 23:53:40'),
(7, 'scripts/migration_remove_year_from_classes.php', 1784937215, '2026-07-24 23:53:41'),
(8, 'scripts/migration_remove_year_from_classes_subjects.php', 1784937215, '2026-07-24 23:53:41'),
(9, 'scripts/migration_update_academic_years_table.php', 1784937215, '2026-07-24 23:53:42'),
(10, 'scripts/migration_add_teaching_type_to_cycles.php', 1784943445, '2026-07-25 01:37:28'),
(11, 'scripts/migration_add_status_to_cycles.php', 1784944342, '2026-07-25 01:52:23'),
(12, 'scripts/migration_add_status_to_sections.php', 1784944650, '2026-07-25 01:57:30'),
(13, 'scripts/migration_add_teaching_type_to_sequences.php', 1784949111, '2026-07-25 03:11:53'),
(15, 'scripts/migration_create_subject_groups.php', 1784952497, '2026-07-25 04:08:17'),
(16, 'scripts/migration_update_admin_pilotage_rbac.php', 1785285269, '2026-07-29 00:34:30'),
(17, 'scripts/migration_add_teaching_type_to_settings.php', 1785285984, '2026-07-29 00:46:27'),
(18, 'scripts/migration_populate_all_teaching_types_settings.php', 1785286664, '2026-07-29 00:57:49'),
(19, 'scripts/migration_add_academic_levels_and_subject_codes.php', 1785290836, '2026-07-29 02:07:18'),
(20, 'scripts/migration_add_transcripts_rbac.php', 1785481835, '2026-07-31 07:10:37'),
(21, 'scripts/migration_add_timetables_module.php', 1785815582, '2026-08-04 03:53:05'),
(22, 'scripts/migration_add_cycle_levels_pivot.php', 1786073849, '2026-08-07 03:37:31'),
(23, 'scripts/migration_complete_rbac.php', 1786078108, '2026-08-07 04:48:31'),
(24, 'scripts/migration_add_direction_academique_role.php', 1786422032, '2026-08-11 04:20:34');

-- --------------------------------------------------------

--
-- Structure de la table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `parent_payment_id` int(11) DEFAULT NULL,
  `student_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `type` enum('inscription','scolarite') NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'CASH',
  `reference` varchar(100) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `verification_code` varchar(64) DEFAULT NULL,
  `print_count` int(11) NOT NULL DEFAULT 0,
  `status` enum('Valide','En attente','Annulé') NOT NULL DEFAULT 'Valide',
  `cancelled_by` int(11) DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancellation_motive` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `payments`
--

INSERT INTO `payments` (`id`, `parent_payment_id`, `student_id`, `academic_year_id`, `amount`, `type`, `payment_date`, `payment_method`, `reference`, `commentaire`, `created_by`, `created_at`, `verification_code`, `print_count`, `status`, `cancelled_by`, `cancelled_at`, `cancellation_motive`) VALUES
(1, NULL, 2, 3, 20000.00, 'inscription', '2026-07-31', 'Espèces', '0AKUX6Y5STGHOHSXEGJZ', NULL, 40, '2026-07-30 23:58:39', 'REC-7057-8E1C-3967', 1, 'Valide', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `payment_receipts`
--

CREATE TABLE `payment_receipts` (
  `id` int(11) NOT NULL,
  `student_payment_id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `verification_code` varchar(64) NOT NULL,
  `print_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `perm_code` varchar(100) NOT NULL,
  `perm_name` varchar(150) NOT NULL,
  `module` varchar(50) NOT NULL DEFAULT 'general',
  `submodule` varchar(50) NOT NULL DEFAULT 'general',
  `action` varchar(50) NOT NULL DEFAULT 'view',
  `description` text DEFAULT NULL,
  `criticality` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_system` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `permissions`
--

INSERT INTO `permissions` (`id`, `perm_code`, `perm_name`, `module`, `submodule`, `action`, `description`, `criticality`, `status`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'manage_users', 'Gérer les utilisateurs', 'system', 'users', 'manage', 'Créer, modifier et gérer les comptes d\'accès système.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:51'),
(2, 'manage_settings', 'Gérer les paramètres généraux', 'system', 'settings', 'manage', 'Configurer l\'établissement, le logo et les paramètres globaux.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:51'),
(3, 'view_system_logs', 'Consulter les journaux système', 'system', 'audit', 'view', 'Visualiser les logs d\'activité et les événements de sécurité.', 'medium', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:51'),
(4, 'manage_rbac', 'Gérer la sécurité RBAC', 'system', 'rbac', 'manage', 'Configurer les rôles, les autorisations et les exceptions utilisateurs.', 'critical', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:51'),
(5, 'view_classes', 'Consulter les classes', 'pedagogy', 'classes', 'view', 'Afficher la liste des classes et effectifs.', 'low', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:51'),
(6, 'manage_classes_structure', 'Gérer la structure des classes', 'pedagogy', 'classes', 'manage', 'Créer, modifier et supprimer des classes.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:51'),
(7, 'manage_teaching_types', 'Gérer les types d\'enseignement', 'pedagogy', 'structure', 'manage', 'Configurer les types d\'enseignement (Général, Technique, LMD).', 'medium', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:51'),
(8, 'manage_cycles', 'Gérer les cycles', 'pedagogy', 'structure', 'manage', 'Gérer les cycles académiques.', 'medium', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:51'),
(9, 'manage_sections', 'Gérer les sections', 'pedagogy', 'structure', 'manage', 'Gérer les sections francophones / anglophones.', 'medium', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:51'),
(10, 'manage_departments', 'Gérer les départements', 'pedagogy', 'structure', 'manage', 'Gérer les départements d\'enseignement.', 'medium', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:51'),
(11, 'manage_subjects', 'Gérer les matières', 'pedagogy', 'subjects', 'manage', 'Gérer le catalogue des matières et coefficients.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(12, 'manage_teachers', 'Gérer les enseignants', 'pedagogy', 'teachers', 'manage', 'Gérer le registre des enseignants et leurs affectations.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(13, 'manage_timetables', 'Gérer les emplois du temps', 'pedagogy', 'timetables', 'manage', 'Planifier et éditer les emplois du temps des classes.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(14, 'manage_academic_years', 'Gérer les années scolaires', 'pedagogy', 'academic_years', 'manage', 'Activer, clôturer et basculer les années académiques.', 'critical', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(15, 'manage_sequences', 'Gérer les séquences', 'pedagogy', 'sequences', 'manage', 'Définir les séquences et semestres d\'évaluation.', 'medium', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(16, 'view_students', 'Consulter les élèves', 'students', 'registry', 'view', 'Visualiser les registres des élèves.', 'low', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(17, 'manage_students', 'Gérer les registres élèves', 'students', 'registry', 'manage', 'Inscrire, modifier les profils et gérer la scolarité des élèves.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(18, 'manage_marks', 'Saisir et modifier les notes', 'evaluations', 'grades', 'manage', 'Saisir, verrouiller et valider les notes d\'évaluation.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(19, 'manage_bulletins', 'Gérer les bulletins de notes', 'evaluations', 'bulletins', 'manage', 'Calculer les moyennes, éditer les bulletins et PV.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(20, 'manage_absences', 'Gérer les absences et discipline', 'evaluations', 'discipline', 'manage', 'Saisir et récapituler les absences et blâmes.', 'medium', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(21, 'manage_staff', 'Gérer le personnel', 'hr', 'staff', 'manage', 'Gérer les fiches et dossiers administratifs du personnel.', 'medium', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:53'),
(22, 'manage_contracts', 'Gérer les contrats de travail', 'hr', 'contracts', 'manage', 'Gérer la rédaction et le suivi des contrats.', 'medium', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:53'),
(23, 'manage_fees', 'Gérer les frais de scolarité', 'finance', 'fees', 'manage', 'Accès global à la configuration de la scolarité.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(24, 'view_class_finances', 'Consulter les tarifs de scolarité', 'finance', 'fees', 'view', 'Voir la grille tarifaire des frais de scolarité.', 'low', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(25, 'edit_class_finances', 'Configurer la grille tarifaire', 'finance', 'fees', 'edit', 'Définir les échéances et montants des tranches.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(26, 'manage_payments', 'Enregistrer et gérer les paiements', 'finance', 'payments', 'manage', 'Saisir les versements, imprimer les reçus et annuler.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(27, 'manage_discounts', 'Gérer les réductions de scolarité', 'finance', 'discounts', 'manage', 'Accorder des remises ou réductions aux élèves.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(28, 'manage_scholarships', 'Gérer les bourses scolaires', 'finance', 'scholarships', 'manage', 'Attribuer et suivre les bourses d\'études.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:52'),
(29, 'view_financial_history', 'Consulter l\'historique financier', 'finance', 'reports', 'view', 'Consulter le journal des transactions financières.', 'medium', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:53'),
(30, 'view_financial_reports', 'Consulter les rapports et insolvables', 'finance', 'reports', 'view', 'Consulter les bilans d\'encaissement et listes d\'insolvabilité.', 'high', 'active', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:53'),
(31, 'manage_expenses', 'Gérer les dépenses d\'établissement', 'finance', 'expenses', 'manage', 'Saisir et approuver les dépenses et frais d\'exploitation.', 'high', 'active', 1, '2026-07-08 21:31:26', '2026-08-07 04:48:53'),
(32, 'manage_levels', 'Gérer les niveaux d\'étude', 'pedagogy', 'structure', 'manage', 'Configurer les niveaux d\'étude.', 'medium', 'active', 1, '2026-07-29 02:07:18', '2026-08-07 04:48:51'),
(33, 'view_transcripts', 'Consulter les relevés de notes', 'general', 'general', 'view', 'Visualiser et prévisualiser les relevés de notes des élèves.', 'medium', 'active', 1, '2026-07-31 07:10:36', '2026-07-31 07:10:36'),
(34, 'manage_transcripts', 'Gérer les relevés de notes', 'evaluations', 'transcripts', 'manage', 'Générer les relevés de notes officiels.', 'medium', 'active', 1, '2026-07-31 07:10:37', '2026-08-07 04:48:52'),
(36, 'view_timetables', 'Consulter les emplois du temps', 'general', 'general', 'view', 'Visualiser, partager et imprimer les emplois du temps.', 'medium', 'active', 1, '2026-08-04 03:53:05', '2026-08-04 03:53:05'),
(37, 'unlock_timetables', 'Déverrouiller les emplois du temps', 'general', 'general', 'view', 'Réservé au Superadmin pour déverrouiller un emploi du temps clôturé.', 'medium', 'active', 1, '2026-08-04 03:53:05', '2026-08-04 03:53:05'),
(42, 'view_pilotage', 'Accéder au Centre de Pilotage', 'system', 'pilotage', 'view', 'Accéder aux tableaux de bord analytiques et bilans d\'impact.', 'medium', 'active', 1, '2026-08-07 04:48:51', '2026-08-07 04:48:51'),
(51, 'manage_subject_groups', 'Gérer les groupes de matières', 'pedagogy', 'subjects', 'manage', 'Organiser les matières en groupes/UE.', 'medium', 'active', 1, '2026-08-07 04:48:52', '2026-08-07 04:48:52'),
(58, 'export_students', 'Exporter les données élèves', 'students', 'registry', 'export', 'Exporter les registres élèves au format Excel/PDF.', 'medium', 'active', 1, '2026-08-07 04:48:52', '2026-08-07 04:48:52'),
(74, 'academicyear_index', 'View Academicyear (index)', 'pedagogy', 'academicyear', 'view', 'Permission auto-détectée pour la méthode AcademicYear::index()', 'low', 'active', 0, '2026-08-07 04:49:04', '2026-08-07 04:49:04'),
(75, 'academicyear_create', 'Create Academicyear (create)', 'pedagogy', 'academicyear', 'create', 'Permission auto-détectée pour la méthode AcademicYear::create()', 'medium', 'active', 0, '2026-08-07 04:49:04', '2026-08-07 04:49:04'),
(76, 'academicyear_store', 'Create Academicyear (store)', 'pedagogy', 'academicyear', 'create', 'Permission auto-détectée pour la méthode AcademicYear::store()', 'medium', 'active', 0, '2026-08-07 04:49:04', '2026-08-07 04:49:04'),
(77, 'academicyear_activate', 'Manage Academicyear (activate)', 'pedagogy', 'academicyear', 'manage', 'Permission auto-détectée pour la méthode AcademicYear::activate()', 'low', 'active', 0, '2026-08-07 04:49:04', '2026-08-07 04:49:04'),
(78, 'academicyear_rolloverWizard', 'Create Academicyear (rolloverWizard)', 'pedagogy', 'academicyear', 'create', 'Permission auto-détectée pour la méthode AcademicYear::rolloverWizard()', 'medium', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(79, 'academicyear_doRollover', 'Manage Academicyear (doRollover)', 'pedagogy', 'academicyear', 'manage', 'Permission auto-détectée pour la méthode AcademicYear::doRollover()', 'low', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(80, 'academicyear_archiveWizard', 'Create Academicyear (archiveWizard)', 'pedagogy', 'academicyear', 'create', 'Permission auto-détectée pour la méthode AcademicYear::archiveWizard()', 'medium', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(81, 'academicyear_doArchive', 'Manage Academicyear (doArchive)', 'pedagogy', 'academicyear', 'manage', 'Permission auto-détectée pour la méthode AcademicYear::doArchive()', 'low', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(82, 'academicyear_restore', 'Create Academicyear (restore)', 'pedagogy', 'academicyear', 'create', 'Permission auto-détectée pour la méthode AcademicYear::restore()', 'medium', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(83, 'academicyear_unarchive', 'Manage Academicyear (unarchive)', 'pedagogy', 'academicyear', 'manage', 'Permission auto-détectée pour la méthode AcademicYear::unarchive()', 'low', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(84, 'academicyear_doUnarchive', 'Manage Academicyear (doUnarchive)', 'pedagogy', 'academicyear', 'manage', 'Permission auto-détectée pour la méthode AcademicYear::doUnarchive()', 'low', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(85, 'academicyear_edit', 'Edit Academicyear (edit)', 'pedagogy', 'academicyear', 'edit', 'Permission auto-détectée pour la méthode AcademicYear::edit()', 'medium', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(86, 'academicyear_update', 'Edit Academicyear (update)', 'pedagogy', 'academicyear', 'edit', 'Permission auto-détectée pour la méthode AcademicYear::update()', 'medium', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(87, 'academicyear_delete', 'Delete Academicyear (delete)', 'pedagogy', 'academicyear', 'delete', 'Permission auto-détectée pour la méthode AcademicYear::delete()', 'high', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(88, 'auth_loginView', 'View Auth (loginView)', 'general', 'auth', 'view', 'Permission auto-détectée pour la méthode Auth::loginView()', 'low', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(89, 'auth_loginPost', 'Manage Auth (loginPost)', 'general', 'auth', 'manage', 'Permission auto-détectée pour la méthode Auth::loginPost()', 'low', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(90, 'auth_logout', 'Manage Auth (logout)', 'general', 'auth', 'manage', 'Permission auto-détectée pour la méthode Auth::logout()', 'low', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(91, 'auth_registerTeacherView', 'View Auth (registerTeacherView)', 'general', 'auth', 'view', 'Permission auto-détectée pour la méthode Auth::registerTeacherView()', 'low', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(92, 'auth_registerTeacherPost', 'Manage Auth (registerTeacherPost)', 'general', 'auth', 'manage', 'Permission auto-détectée pour la méthode Auth::registerTeacherPost()', 'low', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(93, 'bulletin_index', 'View Bulletin (index)', 'students', 'bulletin', 'view', 'Permission auto-détectée pour la méthode Bulletin::index()', 'low', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(94, 'bulletin_discipline', 'Manage Bulletin (discipline)', 'students', 'bulletin', 'manage', 'Permission auto-détectée pour la méthode Bulletin::discipline()', 'low', 'active', 0, '2026-08-07 04:49:05', '2026-08-07 04:49:05'),
(95, 'bulletin_saveDiscipline', 'Create Bulletin (saveDiscipline)', 'students', 'bulletin', 'create', 'Permission auto-détectée pour la méthode Bulletin::saveDiscipline()', 'medium', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(96, 'bulletin_sequence', 'Manage Bulletin (sequence)', 'students', 'bulletin', 'manage', 'Permission auto-détectée pour la méthode Bulletin::sequence()', 'low', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(97, 'bulletin_trimestre', 'Manage Bulletin (trimestre)', 'students', 'bulletin', 'manage', 'Permission auto-détectée pour la méthode Bulletin::trimestre()', 'low', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(98, 'bulletin_sequenceClass', 'Manage Bulletin (sequenceClass)', 'students', 'bulletin', 'manage', 'Permission auto-détectée pour la méthode Bulletin::sequenceClass()', 'low', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(99, 'bulletin_trimestreClass', 'Manage Bulletin (trimestreClass)', 'students', 'bulletin', 'manage', 'Permission auto-détectée pour la méthode Bulletin::trimestreClass()', 'low', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(100, 'bulletin_annuel', 'Manage Bulletin (annuel)', 'students', 'bulletin', 'manage', 'Permission auto-détectée pour la méthode Bulletin::annuel()', 'low', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(101, 'bulletin_annuelClass', 'Manage Bulletin (annuelClass)', 'students', 'bulletin', 'manage', 'Permission auto-détectée pour la méthode Bulletin::annuelClass()', 'low', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(102, 'bulletin_getClassesBySectionJson', 'View Bulletin (getClassesBySectionJson)', 'students', 'bulletin', 'view', 'Permission auto-détectée pour la méthode Bulletin::getClassesBySectionJson()', 'low', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(103, 'class_index', 'View Class (index)', 'pedagogy', 'class', 'view', 'Permission auto-détectée pour la méthode Class::index()', 'low', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(104, 'class_export', 'Export Class (export)', 'pedagogy', 'class', 'export', 'Permission auto-détectée pour la méthode Class::export()', 'low', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(105, 'class_create', 'Create Class (create)', 'pedagogy', 'class', 'create', 'Permission auto-détectée pour la méthode Class::create()', 'medium', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(106, 'class_store', 'Create Class (store)', 'pedagogy', 'class', 'create', 'Permission auto-détectée pour la méthode Class::store()', 'medium', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(107, 'class_edit', 'Edit Class (edit)', 'pedagogy', 'class', 'edit', 'Permission auto-détectée pour la méthode Class::edit()', 'medium', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(108, 'class_update', 'Edit Class (update)', 'pedagogy', 'class', 'edit', 'Permission auto-détectée pour la méthode Class::update()', 'medium', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(109, 'class_delete', 'Delete Class (delete)', 'pedagogy', 'class', 'delete', 'Permission auto-détectée pour la méthode Class::delete()', 'high', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(110, 'class_manageTeam', 'View Class (manageTeam)', 'pedagogy', 'class', 'view', 'Permission auto-détectée pour la méthode Class::manageTeam()', 'low', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(111, 'class_setMainTeacher', 'Manage Class (setMainTeacher)', 'pedagogy', 'class', 'manage', 'Permission auto-détectée pour la méthode Class::setMainTeacher()', 'low', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(112, 'class_import', 'Import Class (import)', 'pedagogy', 'class', 'import', 'Permission auto-détectée pour la méthode Class::import()', 'low', 'active', 0, '2026-08-07 04:49:06', '2026-08-07 04:49:06'),
(113, 'class_downloadTemplate', 'Export Class (downloadTemplate)', 'pedagogy', 'class', 'export', 'Permission auto-détectée pour la méthode Class::downloadTemplate()', 'low', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(114, 'class_upload', 'Import Class (upload)', 'pedagogy', 'class', 'import', 'Permission auto-détectée pour la méthode Class::upload()', 'low', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(115, 'cycle_index', 'View Cycle (index)', 'pedagogy', 'cycle', 'view', 'Permission auto-détectée pour la méthode Cycle::index()', 'low', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(116, 'cycle_create', 'Create Cycle (create)', 'pedagogy', 'cycle', 'create', 'Permission auto-détectée pour la méthode Cycle::create()', 'medium', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(117, 'cycle_store', 'Create Cycle (store)', 'pedagogy', 'cycle', 'create', 'Permission auto-détectée pour la méthode Cycle::store()', 'medium', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(118, 'cycle_edit', 'Edit Cycle (edit)', 'pedagogy', 'cycle', 'edit', 'Permission auto-détectée pour la méthode Cycle::edit()', 'medium', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(119, 'cycle_update', 'Edit Cycle (update)', 'pedagogy', 'cycle', 'edit', 'Permission auto-détectée pour la méthode Cycle::update()', 'medium', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(120, 'cycle_toggleStatus', 'Edit Cycle (toggleStatus)', 'pedagogy', 'cycle', 'edit', 'Permission auto-détectée pour la méthode Cycle::toggleStatus()', 'medium', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(121, 'cycle_delete', 'Delete Cycle (delete)', 'pedagogy', 'cycle', 'delete', 'Permission auto-détectée pour la méthode Cycle::delete()', 'high', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(122, 'dashboard_index', 'View Dashboard (index)', 'system', 'dashboard', 'view', 'Permission auto-détectée pour la méthode Dashboard::index()', 'low', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(123, 'dashboard_executiveDashboard', 'Manage Dashboard (executiveDashboard)', 'system', 'dashboard', 'manage', 'Permission auto-détectée pour la méthode Dashboard::executiveDashboard()', 'low', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(124, 'dashboard_financialCenter', 'Manage Dashboard (financialCenter)', 'system', 'dashboard', 'manage', 'Permission auto-détectée pour la méthode Dashboard::financialCenter()', 'low', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(125, 'department_index', 'View Department (index)', 'pedagogy', 'department', 'view', 'Permission auto-détectée pour la méthode Department::index()', 'low', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(126, 'department_create', 'Create Department (create)', 'pedagogy', 'department', 'create', 'Permission auto-détectée pour la méthode Department::create()', 'medium', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(127, 'department_store', 'Create Department (store)', 'pedagogy', 'department', 'create', 'Permission auto-détectée pour la méthode Department::store()', 'medium', 'active', 0, '2026-08-07 04:49:07', '2026-08-07 04:49:07'),
(128, 'department_edit', 'Edit Department (edit)', 'pedagogy', 'department', 'edit', 'Permission auto-détectée pour la méthode Department::edit()', 'medium', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(129, 'department_update', 'Edit Department (update)', 'pedagogy', 'department', 'edit', 'Permission auto-détectée pour la méthode Department::update()', 'medium', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(130, 'department_toggleStatus', 'Edit Department (toggleStatus)', 'pedagogy', 'department', 'edit', 'Permission auto-détectée pour la méthode Department::toggleStatus()', 'medium', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(131, 'department_delete', 'Delete Department (delete)', 'pedagogy', 'department', 'delete', 'Permission auto-détectée pour la méthode Department::delete()', 'high', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(132, 'discount_index', 'View Discount (index)', 'finance', 'discount', 'view', 'Permission auto-détectée pour la méthode Discount::index()', 'low', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(133, 'discount_store', 'Create Discount (store)', 'finance', 'discount', 'create', 'Permission auto-détectée pour la méthode Discount::store()', 'medium', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(134, 'discount_toggleStatus', 'Edit Discount (toggleStatus)', 'finance', 'discount', 'edit', 'Permission auto-détectée pour la méthode Discount::toggleStatus()', 'medium', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(135, 'discount_delete', 'Delete Discount (delete)', 'finance', 'discount', 'delete', 'Permission auto-détectée pour la méthode Discount::delete()', 'high', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(136, 'discounttype_index', 'View Discounttype (index)', 'finance', 'discounttype', 'view', 'Permission auto-détectée pour la méthode DiscountType::index()', 'low', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(137, 'discounttype_store', 'Create Discounttype (store)', 'finance', 'discounttype', 'create', 'Permission auto-détectée pour la méthode DiscountType::store()', 'medium', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(138, 'discounttype_toggleStatus', 'Edit Discounttype (toggleStatus)', 'finance', 'discounttype', 'edit', 'Permission auto-détectée pour la méthode DiscountType::toggleStatus()', 'medium', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(139, 'discounttype_delete', 'Delete Discounttype (delete)', 'finance', 'discounttype', 'delete', 'Permission auto-détectée pour la méthode DiscountType::delete()', 'high', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(140, 'documentation_index', 'View Documentation (index)', 'system', 'documentation', 'view', 'Permission auto-détectée pour la méthode Documentation::index()', 'low', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(141, 'documentation_download', 'Export Documentation (download)', 'system', 'documentation', 'export', 'Permission auto-détectée pour la méthode Documentation::download()', 'low', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(142, 'expense_index', 'View Expense (index)', 'finance', 'expense', 'view', 'Permission auto-détectée pour la méthode Expense::index()', 'low', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(143, 'expense_store', 'Create Expense (store)', 'finance', 'expense', 'create', 'Permission auto-détectée pour la méthode Expense::store()', 'medium', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(144, 'expense_update', 'Edit Expense (update)', 'finance', 'expense', 'edit', 'Permission auto-détectée pour la méthode Expense::update()', 'medium', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(145, 'expense_cancel', 'Manage Expense (cancel)', 'finance', 'expense', 'manage', 'Permission auto-détectée pour la méthode Expense::cancel()', 'low', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(146, 'expense_printReport', 'Export Expense (printReport)', 'finance', 'expense', 'export', 'Permission auto-détectée pour la méthode Expense::printReport()', 'low', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(147, 'expense_categories', 'Manage Expense (categories)', 'finance', 'expense', 'manage', 'Permission auto-détectée pour la méthode Expense::categories()', 'low', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(148, 'expense_storeCategory', 'Create Expense (storeCategory)', 'finance', 'expense', 'create', 'Permission auto-détectée pour la méthode Expense::storeCategory()', 'medium', 'active', 0, '2026-08-07 04:49:08', '2026-08-07 04:49:08'),
(149, 'expense_updateCategory', 'Edit Expense (updateCategory)', 'finance', 'expense', 'edit', 'Permission auto-détectée pour la méthode Expense::updateCategory()', 'medium', 'active', 0, '2026-08-07 04:49:09', '2026-08-07 04:49:09'),
(150, 'expense_toggleCategoryStatus', 'Edit Expense (toggleCategoryStatus)', 'finance', 'expense', 'edit', 'Permission auto-détectée pour la méthode Expense::toggleCategoryStatus()', 'medium', 'active', 0, '2026-08-07 04:49:09', '2026-08-07 04:49:09'),
(151, 'expense_auditLogs', 'Manage Expense (auditLogs)', 'finance', 'expense', 'manage', 'Permission auto-détectée pour la méthode Expense::auditLogs()', 'low', 'active', 0, '2026-08-07 04:49:09', '2026-08-07 04:49:09'),
(152, 'financialhistory_index', 'View Financialhistory (index)', 'finance', 'financialhistory', 'view', 'Permission auto-détectée pour la méthode FinancialHistory::index()', 'low', 'active', 0, '2026-08-07 04:49:09', '2026-08-07 04:49:09'),
(153, 'financialhistory_print', 'Export Financialhistory (print)', 'finance', 'financialhistory', 'export', 'Permission auto-détectée pour la méthode FinancialHistory::print()', 'low', 'active', 0, '2026-08-07 04:49:09', '2026-08-07 04:49:09'),
(154, 'grade_index', 'View Grade (index)', 'students', 'grade', 'view', 'Permission auto-détectée pour la méthode Grade::index()', 'low', 'active', 0, '2026-08-07 04:49:09', '2026-08-07 04:49:09'),
(155, 'grade_export', 'Export Grade (export)', 'students', 'grade', 'export', 'Permission auto-détectée pour la méthode Grade::export()', 'low', 'active', 0, '2026-08-07 04:49:09', '2026-08-07 04:49:09'),
(156, 'grade_saisie', 'Manage Grade (saisie)', 'students', 'grade', 'manage', 'Permission auto-détectée pour la méthode Grade::saisie()', 'low', 'active', 0, '2026-08-07 04:49:09', '2026-08-07 04:49:09'),
(157, 'grade_store', 'Create Grade (store)', 'students', 'grade', 'create', 'Permission auto-détectée pour la méthode Grade::store()', 'medium', 'active', 0, '2026-08-07 04:49:09', '2026-08-07 04:49:09'),
(158, 'grade_import', 'Import Grade (import)', 'students', 'grade', 'import', 'Permission auto-détectée pour la méthode Grade::import()', 'low', 'active', 0, '2026-08-07 04:49:09', '2026-08-07 04:49:09'),
(159, 'grade_downloadTemplate', 'Export Grade (downloadTemplate)', 'students', 'grade', 'export', 'Permission auto-détectée pour la méthode Grade::downloadTemplate()', 'low', 'active', 0, '2026-08-07 04:49:09', '2026-08-07 04:49:09'),
(160, 'grade_upload', 'Import Grade (upload)', 'students', 'grade', 'import', 'Permission auto-détectée pour la méthode Grade::upload()', 'low', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(161, 'grade_history', 'Manage Grade (history)', 'students', 'grade', 'manage', 'Permission auto-détectée pour la méthode Grade::history()', 'low', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(162, 'honorroll_index', 'View Honorroll (index)', 'students', 'honorroll', 'view', 'Permission auto-détectée pour la méthode HonorRoll::index()', 'low', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(163, 'honorroll_trimestre', 'Manage Honorroll (trimestre)', 'students', 'honorroll', 'manage', 'Permission auto-détectée pour la méthode HonorRoll::trimestre()', 'low', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(164, 'honorroll_trimesterBulk', 'Manage Honorroll (trimesterBulk)', 'students', 'honorroll', 'manage', 'Permission auto-détectée pour la méthode HonorRoll::trimesterBulk()', 'low', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(165, 'honorroll_annuel', 'Manage Honorroll (annuel)', 'students', 'honorroll', 'manage', 'Permission auto-détectée pour la méthode HonorRoll::annuel()', 'low', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(166, 'honorroll_annuelBulk', 'Manage Honorroll (annuelBulk)', 'students', 'honorroll', 'manage', 'Permission auto-détectée pour la méthode HonorRoll::annuelBulk()', 'low', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(167, 'impactanalysis_getAnalysis', 'View Impactanalysis (getAnalysis)', 'system', 'impactanalysis', 'view', 'Permission auto-détectée pour la méthode ImpactAnalysis::getAnalysis()', 'low', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(168, 'impactanalysis_executeDelete', 'Delete Impactanalysis (executeDelete)', 'system', 'impactanalysis', 'delete', 'Permission auto-détectée pour la méthode ImpactAnalysis::executeDelete()', 'high', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(169, 'landing_index', 'View Landing (index)', 'general', 'landing', 'view', 'Permission auto-détectée pour la méthode Landing::index()', 'low', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(170, 'landing_sendContact', 'Manage Landing (sendContact)', 'general', 'landing', 'manage', 'Permission auto-détectée pour la méthode Landing::sendContact()', 'low', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(171, 'landing_toggleArchiveNotification', 'Edit Landing (toggleArchiveNotification)', 'general', 'landing', 'edit', 'Permission auto-détectée pour la méthode Landing::toggleArchiveNotification()', 'medium', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(172, 'landing_deleteNotification', 'Delete Landing (deleteNotification)', 'general', 'landing', 'delete', 'Permission auto-détectée pour la méthode Landing::deleteNotification()', 'high', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(173, 'landing_marks', 'Manage Landing (marks)', 'general', 'landing', 'manage', 'Permission auto-détectée pour la méthode Landing::marks()', 'low', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(174, 'level_index', 'View Level (index)', 'pedagogy', 'level', 'view', 'Permission auto-détectée pour la méthode Level::index()', 'low', 'active', 0, '2026-08-07 04:49:10', '2026-08-07 04:49:10'),
(175, 'level_create', 'Create Level (create)', 'pedagogy', 'level', 'create', 'Permission auto-détectée pour la méthode Level::create()', 'medium', 'active', 0, '2026-08-07 04:49:11', '2026-08-07 04:49:11'),
(176, 'level_store', 'Create Level (store)', 'pedagogy', 'level', 'create', 'Permission auto-détectée pour la méthode Level::store()', 'medium', 'active', 0, '2026-08-07 04:49:11', '2026-08-07 04:49:11'),
(177, 'level_edit', 'Edit Level (edit)', 'pedagogy', 'level', 'edit', 'Permission auto-détectée pour la méthode Level::edit()', 'medium', 'active', 0, '2026-08-07 04:49:11', '2026-08-07 04:49:11'),
(178, 'level_update', 'Edit Level (update)', 'pedagogy', 'level', 'edit', 'Permission auto-détectée pour la méthode Level::update()', 'medium', 'active', 0, '2026-08-07 04:49:11', '2026-08-07 04:49:11'),
(179, 'level_toggleStatus', 'Edit Level (toggleStatus)', 'pedagogy', 'level', 'edit', 'Permission auto-détectée pour la méthode Level::toggleStatus()', 'medium', 'active', 0, '2026-08-07 04:49:11', '2026-08-07 04:49:11'),
(180, 'level_delete', 'Delete Level (delete)', 'pedagogy', 'level', 'delete', 'Permission auto-détectée pour la méthode Level::delete()', 'high', 'active', 0, '2026-08-07 04:49:11', '2026-08-07 04:49:11'),
(181, 'payment_index', 'View Payment (index)', 'finance', 'payment', 'view', 'Permission auto-détectée pour la méthode Payment::index()', 'low', 'active', 0, '2026-08-07 04:49:11', '2026-08-07 04:49:11'),
(182, 'payment_studentDetails', 'Manage Payment (studentDetails)', 'finance', 'payment', 'manage', 'Permission auto-détectée pour la méthode Payment::studentDetails()', 'low', 'active', 0, '2026-08-07 04:49:11', '2026-08-07 04:49:11'),
(183, 'payment_store', 'Create Payment (store)', 'finance', 'payment', 'create', 'Permission auto-détectée pour la méthode Payment::store()', 'medium', 'active', 0, '2026-08-07 04:49:11', '2026-08-07 04:49:11'),
(184, 'payment_delete', 'Delete Payment (delete)', 'finance', 'payment', 'delete', 'Permission auto-détectée pour la méthode Payment::delete()', 'high', 'active', 0, '2026-08-07 04:49:11', '2026-08-07 04:49:11'),
(185, 'payment_receipt', 'Manage Payment (receipt)', 'finance', 'payment', 'manage', 'Permission auto-détectée pour la méthode Payment::receipt()', 'low', 'active', 0, '2026-08-07 04:49:11', '2026-08-07 04:49:11'),
(186, 'payment_fullHistory', 'Manage Payment (fullHistory)', 'finance', 'payment', 'manage', 'Permission auto-détectée pour la méthode Payment::fullHistory()', 'low', 'active', 0, '2026-08-07 04:49:11', '2026-08-07 04:49:11'),
(187, 'payment_verify', 'Manage Payment (verify)', 'finance', 'payment', 'manage', 'Permission auto-détectée pour la méthode Payment::verify()', 'low', 'active', 0, '2026-08-07 04:49:11', '2026-08-07 04:49:11'),
(188, 'procesverbal_index', 'View Procesverbal (index)', 'students', 'procesverbal', 'view', 'Permission auto-détectée pour la méthode ProcesVerbal::index()', 'low', 'active', 0, '2026-08-07 04:49:11', '2026-08-07 04:49:11'),
(189, 'procesverbal_evaluation', 'Manage Procesverbal (evaluation)', 'students', 'procesverbal', 'manage', 'Permission auto-détectée pour la méthode ProcesVerbal::evaluation()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(190, 'procesverbal_sequence', 'Manage Procesverbal (sequence)', 'students', 'procesverbal', 'manage', 'Permission auto-détectée pour la méthode ProcesVerbal::sequence()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(191, 'procesverbal_trimestre', 'Manage Procesverbal (trimestre)', 'students', 'procesverbal', 'manage', 'Permission auto-détectée pour la méthode ProcesVerbal::trimestre()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(192, 'procesverbal_annuel', 'Manage Procesverbal (annuel)', 'students', 'procesverbal', 'manage', 'Permission auto-détectée pour la méthode ProcesVerbal::annuel()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(193, 'profile_index', 'View Profile (index)', 'general', 'profile', 'view', 'Permission auto-détectée pour la méthode Profile::index()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(194, 'profile_update', 'Edit Profile (update)', 'general', 'profile', 'edit', 'Permission auto-détectée pour la méthode Profile::update()', 'medium', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(195, 'publicverification_verifyPublic', 'Manage Publicverification (verifyPublic)', 'general', 'publicverification', 'manage', 'Permission auto-détectée pour la méthode PublicVerification::verifyPublic()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(196, 'rbac_index', 'View Rbac (index)', 'system', 'rbac', 'view', 'Permission auto-détectée pour la méthode Rbac::index()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(197, 'rbac_getPermissions', 'View Rbac (getPermissions)', 'system', 'rbac', 'view', 'Permission auto-détectée pour la méthode Rbac::getPermissions()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(198, 'rbac_getRoles', 'View Rbac (getRoles)', 'system', 'rbac', 'view', 'Permission auto-détectée pour la méthode Rbac::getRoles()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(199, 'rbac_getRolePermissions', 'View Rbac (getRolePermissions)', 'system', 'rbac', 'view', 'Permission auto-détectée pour la méthode Rbac::getRolePermissions()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(200, 'rbac_saveRolePermissions', 'Create Rbac (saveRolePermissions)', 'system', 'rbac', 'create', 'Permission auto-détectée pour la méthode Rbac::saveRolePermissions()', 'medium', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(201, 'rbac_copyRolePermissions', 'Manage Rbac (copyRolePermissions)', 'system', 'rbac', 'manage', 'Permission auto-détectée pour la méthode Rbac::copyRolePermissions()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(202, 'rbac_compareRoles', 'Manage Rbac (compareRoles)', 'system', 'rbac', 'manage', 'Permission auto-détectée pour la méthode Rbac::compareRoles()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(203, 'rbac_resetRolePermissions', 'Manage Rbac (resetRolePermissions)', 'system', 'rbac', 'manage', 'Permission auto-détectée pour la méthode Rbac::resetRolePermissions()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(204, 'rbac_searchUsers', 'Manage Rbac (searchUsers)', 'system', 'rbac', 'manage', 'Permission auto-détectée pour la méthode Rbac::searchUsers()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(205, 'rbac_getUserPermissions', 'View Rbac (getUserPermissions)', 'system', 'rbac', 'view', 'Permission auto-détectée pour la méthode Rbac::getUserPermissions()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(206, 'rbac_saveUserPermissions', 'Create Rbac (saveUserPermissions)', 'system', 'rbac', 'create', 'Permission auto-détectée pour la méthode Rbac::saveUserPermissions()', 'medium', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(207, 'rbac_runScan', 'Manage Rbac (runScan)', 'system', 'rbac', 'manage', 'Permission auto-détectée pour la méthode Rbac::runScan()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(208, 'rbac_getAuditLogs', 'View Rbac (getAuditLogs)', 'system', 'rbac', 'view', 'Permission auto-détectée pour la méthode Rbac::getAuditLogs()', 'low', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(209, 'rbac_createBackup', 'Create Rbac (createBackup)', 'system', 'rbac', 'create', 'Permission auto-détectée pour la méthode Rbac::createBackup()', 'medium', 'active', 0, '2026-08-07 04:49:12', '2026-08-07 04:49:12'),
(210, 'rbac_getBackups', 'View Rbac (getBackups)', 'system', 'rbac', 'view', 'Permission auto-détectée pour la méthode Rbac::getBackups()', 'low', 'active', 0, '2026-08-07 04:49:13', '2026-08-07 04:49:13'),
(211, 'rbac_restoreBackup', 'Create Rbac (restoreBackup)', 'system', 'rbac', 'create', 'Permission auto-détectée pour la méthode Rbac::restoreBackup()', 'medium', 'active', 0, '2026-08-07 04:49:13', '2026-08-07 04:49:13'),
(212, 'scholarship_index', 'View Scholarship (index)', 'finance', 'scholarship', 'view', 'Permission auto-détectée pour la méthode Scholarship::index()', 'low', 'active', 0, '2026-08-07 04:49:13', '2026-08-07 04:49:13'),
(213, 'scholarship_store', 'Create Scholarship (store)', 'finance', 'scholarship', 'create', 'Permission auto-détectée pour la méthode Scholarship::store()', 'medium', 'active', 0, '2026-08-07 04:49:13', '2026-08-07 04:49:13'),
(214, 'scholarship_toggleStatus', 'Edit Scholarship (toggleStatus)', 'finance', 'scholarship', 'edit', 'Permission auto-détectée pour la méthode Scholarship::toggleStatus()', 'medium', 'active', 0, '2026-08-07 04:49:13', '2026-08-07 04:49:13'),
(215, 'scholarship_delete', 'Delete Scholarship (delete)', 'finance', 'scholarship', 'delete', 'Permission auto-détectée pour la méthode Scholarship::delete()', 'high', 'active', 0, '2026-08-07 04:49:13', '2026-08-07 04:49:13'),
(216, 'schoolfee_grille', 'Manage Schoolfee (grille)', 'finance', 'schoolfee', 'manage', 'Permission auto-détectée pour la méthode SchoolFee::grille()', 'low', 'active', 0, '2026-08-07 04:49:13', '2026-08-07 04:49:13'),
(217, 'schoolfee_tranches', 'Manage Schoolfee (tranches)', 'finance', 'schoolfee', 'manage', 'Permission auto-détectée pour la méthode SchoolFee::tranches()', 'low', 'active', 0, '2026-08-07 04:49:13', '2026-08-07 04:49:13'),
(218, 'schoolfee_versements', 'Manage Schoolfee (versements)', 'finance', 'schoolfee', 'manage', 'Permission auto-détectée pour la méthode SchoolFee::versements()', 'low', 'active', 0, '2026-08-07 04:49:13', '2026-08-07 04:49:13'),
(219, 'schoolfee_storeVersement', 'Create Schoolfee (storeVersement)', 'finance', 'schoolfee', 'create', 'Permission auto-détectée pour la méthode SchoolFee::storeVersement()', 'medium', 'active', 0, '2026-08-07 04:49:13', '2026-08-07 04:49:13'),
(220, 'schoolfee_deleteVersement', 'Delete Schoolfee (deleteVersement)', 'finance', 'schoolfee', 'delete', 'Permission auto-détectée pour la méthode SchoolFee::deleteVersement()', 'high', 'active', 0, '2026-08-07 04:49:13', '2026-08-07 04:49:13'),
(221, 'schoolfee_insolvables', 'Manage Schoolfee (insolvables)', 'finance', 'schoolfee', 'manage', 'Permission auto-détectée pour la méthode SchoolFee::insolvables()', 'low', 'active', 0, '2026-08-07 04:49:13', '2026-08-07 04:49:13'),
(222, 'schoolfee_receipt', 'Manage Schoolfee (receipt)', 'finance', 'schoolfee', 'manage', 'Permission auto-détectée pour la méthode SchoolFee::receipt()', 'low', 'active', 0, '2026-08-07 04:49:13', '2026-08-07 04:49:13'),
(223, 'schoolfee_printInsolvables', 'Export Schoolfee (printInsolvables)', 'finance', 'schoolfee', 'export', 'Permission auto-détectée pour la méthode SchoolFee::printInsolvables()', 'low', 'active', 0, '2026-08-07 04:49:13', '2026-08-07 04:49:13'),
(224, 'schoolfee_printGrille', 'Export Schoolfee (printGrille)', 'finance', 'schoolfee', 'export', 'Permission auto-détectée pour la méthode SchoolFee::printGrille()', 'low', 'active', 0, '2026-08-07 04:49:14', '2026-08-07 04:49:14'),
(225, 'schoolfee_templateGrille', 'Manage Schoolfee (templateGrille)', 'finance', 'schoolfee', 'manage', 'Permission auto-détectée pour la méthode SchoolFee::templateGrille()', 'low', 'active', 0, '2026-08-07 04:49:14', '2026-08-07 04:49:14'),
(226, 'schoolfee_importGrille', 'Import Schoolfee (importGrille)', 'finance', 'schoolfee', 'import', 'Permission auto-détectée pour la méthode SchoolFee::importGrille()', 'low', 'active', 0, '2026-08-07 04:49:14', '2026-08-07 04:49:14'),
(227, 'section_index', 'View Section (index)', 'pedagogy', 'section', 'view', 'Permission auto-détectée pour la méthode Section::index()', 'low', 'active', 0, '2026-08-07 04:49:14', '2026-08-07 04:49:14'),
(228, 'section_create', 'Create Section (create)', 'pedagogy', 'section', 'create', 'Permission auto-détectée pour la méthode Section::create()', 'medium', 'active', 0, '2026-08-07 04:49:14', '2026-08-07 04:49:14'),
(229, 'section_store', 'Create Section (store)', 'pedagogy', 'section', 'create', 'Permission auto-détectée pour la méthode Section::store()', 'medium', 'active', 0, '2026-08-07 04:49:14', '2026-08-07 04:49:14'),
(230, 'section_edit', 'Edit Section (edit)', 'pedagogy', 'section', 'edit', 'Permission auto-détectée pour la méthode Section::edit()', 'medium', 'active', 0, '2026-08-07 04:49:14', '2026-08-07 04:49:14'),
(231, 'section_update', 'Edit Section (update)', 'pedagogy', 'section', 'edit', 'Permission auto-détectée pour la méthode Section::update()', 'medium', 'active', 0, '2026-08-07 04:49:14', '2026-08-07 04:49:14'),
(232, 'section_toggleStatus', 'Edit Section (toggleStatus)', 'pedagogy', 'section', 'edit', 'Permission auto-détectée pour la méthode Section::toggleStatus()', 'medium', 'active', 0, '2026-08-07 04:49:15', '2026-08-07 04:49:15'),
(233, 'section_delete', 'Delete Section (delete)', 'pedagogy', 'section', 'delete', 'Permission auto-détectée pour la méthode Section::delete()', 'high', 'active', 0, '2026-08-07 04:49:15', '2026-08-07 04:49:15'),
(234, 'sequence_index', 'View Sequence (index)', 'pedagogy', 'sequence', 'view', 'Permission auto-détectée pour la méthode Sequence::index()', 'low', 'active', 0, '2026-08-07 04:49:15', '2026-08-07 04:49:15'),
(235, 'sequence_create', 'Create Sequence (create)', 'pedagogy', 'sequence', 'create', 'Permission auto-détectée pour la méthode Sequence::create()', 'medium', 'active', 0, '2026-08-07 04:49:15', '2026-08-07 04:49:15'),
(236, 'sequence_store', 'Create Sequence (store)', 'pedagogy', 'sequence', 'create', 'Permission auto-détectée pour la méthode Sequence::store()', 'medium', 'active', 0, '2026-08-07 04:49:15', '2026-08-07 04:49:15'),
(237, 'sequence_edit', 'Edit Sequence (edit)', 'pedagogy', 'sequence', 'edit', 'Permission auto-détectée pour la méthode Sequence::edit()', 'medium', 'active', 0, '2026-08-07 04:49:16', '2026-08-07 04:49:16'),
(238, 'sequence_update', 'Edit Sequence (update)', 'pedagogy', 'sequence', 'edit', 'Permission auto-détectée pour la méthode Sequence::update()', 'medium', 'active', 0, '2026-08-07 04:49:16', '2026-08-07 04:49:16'),
(239, 'sequence_delete', 'Delete Sequence (delete)', 'pedagogy', 'sequence', 'delete', 'Permission auto-détectée pour la méthode Sequence::delete()', 'high', 'active', 0, '2026-08-07 04:49:16', '2026-08-07 04:49:16'),
(240, 'sequence_toggle', 'Edit Sequence (toggle)', 'pedagogy', 'sequence', 'edit', 'Permission auto-détectée pour la méthode Sequence::toggle()', 'medium', 'active', 0, '2026-08-07 04:49:16', '2026-08-07 04:49:16'),
(241, 'setting_index', 'View Setting (index)', 'system', 'setting', 'view', 'Permission auto-détectée pour la méthode Setting::index()', 'low', 'active', 0, '2026-08-07 04:49:16', '2026-08-07 04:49:16'),
(242, 'setting_store', 'Create Setting (store)', 'system', 'setting', 'create', 'Permission auto-détectée pour la méthode Setting::store()', 'medium', 'active', 0, '2026-08-07 04:49:16', '2026-08-07 04:49:16'),
(243, 'setting_reset', 'Manage Setting (reset)', 'system', 'setting', 'manage', 'Permission auto-détectée pour la méthode Setting::reset()', 'low', 'active', 0, '2026-08-07 04:49:16', '2026-08-07 04:49:16'),
(244, 'setting_runBackup', 'Manage Setting (runBackup)', 'system', 'setting', 'manage', 'Permission auto-détectée pour la méthode Setting::runBackup()', 'low', 'active', 0, '2026-08-07 04:49:16', '2026-08-07 04:49:16'),
(245, 'student_index', 'View Student (index)', 'students', 'student', 'view', 'Permission auto-détectée pour la méthode Student::index()', 'low', 'active', 0, '2026-08-07 04:49:16', '2026-08-07 04:49:16'),
(246, 'student_nonInscrits', 'Manage Student (nonInscrits)', 'students', 'student', 'manage', 'Permission auto-détectée pour la méthode Student::nonInscrits()', 'low', 'active', 0, '2026-08-07 04:49:16', '2026-08-07 04:49:16'),
(247, 'student_export', 'Export Student (export)', 'students', 'student', 'export', 'Permission auto-détectée pour la méthode Student::export()', 'low', 'active', 0, '2026-08-07 04:49:17', '2026-08-07 04:49:17'),
(248, 'student_exportExcel', 'Export Student (exportExcel)', 'students', 'student', 'export', 'Permission auto-détectée pour la méthode Student::exportExcel()', 'low', 'active', 0, '2026-08-07 04:49:17', '2026-08-07 04:49:17'),
(249, 'student_create', 'Create Student (create)', 'students', 'student', 'create', 'Permission auto-détectée pour la méthode Student::create()', 'medium', 'active', 0, '2026-08-07 04:49:17', '2026-08-07 04:49:17'),
(250, 'student_import', 'Import Student (import)', 'students', 'student', 'import', 'Permission auto-détectée pour la méthode Student::import()', 'low', 'active', 0, '2026-08-07 04:49:17', '2026-08-07 04:49:17'),
(251, 'student_downloadTemplate', 'Export Student (downloadTemplate)', 'students', 'student', 'export', 'Permission auto-détectée pour la méthode Student::downloadTemplate()', 'low', 'active', 0, '2026-08-07 04:49:17', '2026-08-07 04:49:17'),
(252, 'student_upload', 'Import Student (upload)', 'students', 'student', 'import', 'Permission auto-détectée pour la méthode Student::upload()', 'low', 'active', 0, '2026-08-07 04:49:17', '2026-08-07 04:49:17'),
(253, 'student_store', 'Create Student (store)', 'students', 'student', 'create', 'Permission auto-détectée pour la méthode Student::store()', 'medium', 'active', 0, '2026-08-07 04:49:17', '2026-08-07 04:49:17'),
(254, 'student_edit', 'Edit Student (edit)', 'students', 'student', 'edit', 'Permission auto-détectée pour la méthode Student::edit()', 'medium', 'active', 0, '2026-08-07 04:49:17', '2026-08-07 04:49:17'),
(255, 'student_update', 'Edit Student (update)', 'students', 'student', 'edit', 'Permission auto-détectée pour la méthode Student::update()', 'medium', 'active', 0, '2026-08-07 04:49:17', '2026-08-07 04:49:17'),
(256, 'student_withdraw', 'Manage Student (withdraw)', 'students', 'student', 'manage', 'Permission auto-détectée pour la méthode Student::withdraw()', 'low', 'active', 0, '2026-08-07 04:49:17', '2026-08-07 04:49:17'),
(257, 'student_restore', 'Create Student (restore)', 'students', 'student', 'create', 'Permission auto-détectée pour la méthode Student::restore()', 'medium', 'active', 0, '2026-08-07 04:49:17', '2026-08-07 04:49:17'),
(258, 'student_delete', 'Delete Student (delete)', 'students', 'student', 'delete', 'Permission auto-détectée pour la méthode Student::delete()', 'high', 'active', 0, '2026-08-07 04:49:17', '2026-08-07 04:49:17'),
(259, 'subject_index', 'View Subject (index)', 'pedagogy', 'subject', 'view', 'Permission auto-détectée pour la méthode Subject::index()', 'low', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(260, 'subject_export', 'Export Subject (export)', 'pedagogy', 'subject', 'export', 'Permission auto-détectée pour la méthode Subject::export()', 'low', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(261, 'subject_create', 'Create Subject (create)', 'pedagogy', 'subject', 'create', 'Permission auto-détectée pour la méthode Subject::create()', 'medium', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18');
INSERT INTO `permissions` (`id`, `perm_code`, `perm_name`, `module`, `submodule`, `action`, `description`, `criticality`, `status`, `is_system`, `created_at`, `updated_at`) VALUES
(262, 'subject_store', 'Create Subject (store)', 'pedagogy', 'subject', 'create', 'Permission auto-détectée pour la méthode Subject::store()', 'medium', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(263, 'subject_edit', 'Edit Subject (edit)', 'pedagogy', 'subject', 'edit', 'Permission auto-détectée pour la méthode Subject::edit()', 'medium', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(264, 'subject_update', 'Edit Subject (update)', 'pedagogy', 'subject', 'edit', 'Permission auto-détectée pour la méthode Subject::update()', 'medium', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(265, 'subject_toggleStatus', 'Edit Subject (toggleStatus)', 'pedagogy', 'subject', 'edit', 'Permission auto-détectée pour la méthode Subject::toggleStatus()', 'medium', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(266, 'subject_delete', 'Delete Subject (delete)', 'pedagogy', 'subject', 'delete', 'Permission auto-détectée pour la méthode Subject::delete()', 'high', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(267, 'subject_import', 'Import Subject (import)', 'pedagogy', 'subject', 'import', 'Permission auto-détectée pour la méthode Subject::import()', 'low', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(268, 'subject_downloadTemplate', 'Export Subject (downloadTemplate)', 'pedagogy', 'subject', 'export', 'Permission auto-détectée pour la méthode Subject::downloadTemplate()', 'low', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(269, 'subject_upload', 'Import Subject (upload)', 'pedagogy', 'subject', 'import', 'Permission auto-détectée pour la méthode Subject::upload()', 'low', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(270, 'subjectgroup_index', 'View Subjectgroup (index)', 'pedagogy', 'subjectgroup', 'view', 'Permission auto-détectée pour la méthode SubjectGroup::index()', 'low', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(271, 'subjectgroup_store', 'Create Subjectgroup (store)', 'pedagogy', 'subjectgroup', 'create', 'Permission auto-détectée pour la méthode SubjectGroup::store()', 'medium', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(272, 'subjectgroup_update', 'Edit Subjectgroup (update)', 'pedagogy', 'subjectgroup', 'edit', 'Permission auto-détectée pour la méthode SubjectGroup::update()', 'medium', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(273, 'subjectgroup_toggle', 'Edit Subjectgroup (toggle)', 'pedagogy', 'subjectgroup', 'edit', 'Permission auto-détectée pour la méthode SubjectGroup::toggle()', 'medium', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(274, 'subjectgroup_delete', 'Delete Subjectgroup (delete)', 'pedagogy', 'subjectgroup', 'delete', 'Permission auto-détectée pour la méthode SubjectGroup::delete()', 'high', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(275, 'teacher_index', 'View Teacher (index)', 'pedagogy', 'teacher', 'view', 'Permission auto-détectée pour la méthode Teacher::index()', 'low', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(276, 'teacher_toggleTeacherNames', 'Edit Teacher (toggleTeacherNames)', 'pedagogy', 'teacher', 'edit', 'Permission auto-détectée pour la méthode Teacher::toggleTeacherNames()', 'medium', 'active', 0, '2026-08-07 04:49:18', '2026-08-07 04:49:18'),
(277, 'teacher_export', 'Export Teacher (export)', 'pedagogy', 'teacher', 'export', 'Permission auto-détectée pour la méthode Teacher::export()', 'low', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(278, 'teacher_create', 'Create Teacher (create)', 'pedagogy', 'teacher', 'create', 'Permission auto-détectée pour la méthode Teacher::create()', 'medium', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(279, 'teacher_store', 'Create Teacher (store)', 'pedagogy', 'teacher', 'create', 'Permission auto-détectée pour la méthode Teacher::store()', 'medium', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(280, 'teacher_delete', 'Delete Teacher (delete)', 'pedagogy', 'teacher', 'delete', 'Permission auto-détectée pour la méthode Teacher::delete()', 'high', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(281, 'teacher_assign', 'Manage Teacher (assign)', 'pedagogy', 'teacher', 'manage', 'Permission auto-détectée pour la méthode Teacher::assign()', 'low', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(282, 'teacher_directAssign', 'Manage Teacher (directAssign)', 'pedagogy', 'teacher', 'manage', 'Permission auto-détectée pour la méthode Teacher::directAssign()', 'low', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(283, 'teacher_storeAssignment', 'Create Teacher (storeAssignment)', 'pedagogy', 'teacher', 'create', 'Permission auto-détectée pour la méthode Teacher::storeAssignment()', 'medium', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(284, 'teacher_import', 'Import Teacher (import)', 'pedagogy', 'teacher', 'import', 'Permission auto-détectée pour la méthode Teacher::import()', 'low', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(285, 'teacher_downloadTemplate', 'Export Teacher (downloadTemplate)', 'pedagogy', 'teacher', 'export', 'Permission auto-détectée pour la méthode Teacher::downloadTemplate()', 'low', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(286, 'teacher_upload', 'Import Teacher (upload)', 'pedagogy', 'teacher', 'import', 'Permission auto-détectée pour la méthode Teacher::upload()', 'low', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(287, 'teacher_edit', 'Edit Teacher (edit)', 'pedagogy', 'teacher', 'edit', 'Permission auto-détectée pour la méthode Teacher::edit()', 'medium', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(288, 'teacher_update', 'Edit Teacher (update)', 'pedagogy', 'teacher', 'edit', 'Permission auto-détectée pour la méthode Teacher::update()', 'medium', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(289, 'teachingtype_index', 'View Teachingtype (index)', 'pedagogy', 'teachingtype', 'view', 'Permission auto-détectée pour la méthode TeachingType::index()', 'low', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(290, 'teachingtype_create', 'Create Teachingtype (create)', 'pedagogy', 'teachingtype', 'create', 'Permission auto-détectée pour la méthode TeachingType::create()', 'medium', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(291, 'teachingtype_store', 'Create Teachingtype (store)', 'pedagogy', 'teachingtype', 'create', 'Permission auto-détectée pour la méthode TeachingType::store()', 'medium', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(292, 'teachingtype_edit', 'Edit Teachingtype (edit)', 'pedagogy', 'teachingtype', 'edit', 'Permission auto-détectée pour la méthode TeachingType::edit()', 'medium', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(293, 'teachingtype_update', 'Edit Teachingtype (update)', 'pedagogy', 'teachingtype', 'edit', 'Permission auto-détectée pour la méthode TeachingType::update()', 'medium', 'active', 0, '2026-08-07 04:49:19', '2026-08-07 04:49:19'),
(294, 'teachingtype_delete', 'Delete Teachingtype (delete)', 'pedagogy', 'teachingtype', 'delete', 'Permission auto-détectée pour la méthode TeachingType::delete()', 'high', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(295, 'timetable_index', 'View Timetable (index)', 'pedagogy', 'timetable', 'view', 'Permission auto-détectée pour la méthode Timetable::index()', 'low', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(296, 'timetable_slots', 'Manage Timetable (slots)', 'pedagogy', 'timetable', 'manage', 'Permission auto-détectée pour la méthode Timetable::slots()', 'low', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(297, 'timetable_storeSlot', 'Create Timetable (storeSlot)', 'pedagogy', 'timetable', 'create', 'Permission auto-détectée pour la méthode Timetable::storeSlot()', 'medium', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(298, 'timetable_updateSlot', 'Edit Timetable (updateSlot)', 'pedagogy', 'timetable', 'edit', 'Permission auto-détectée pour la méthode Timetable::updateSlot()', 'medium', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(299, 'timetable_deleteSlot', 'Delete Timetable (deleteSlot)', 'pedagogy', 'timetable', 'delete', 'Permission auto-détectée pour la méthode Timetable::deleteSlot()', 'high', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(300, 'timetable_rooms', 'Manage Timetable (rooms)', 'pedagogy', 'timetable', 'manage', 'Permission auto-détectée pour la méthode Timetable::rooms()', 'low', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(301, 'timetable_storeRoom', 'Create Timetable (storeRoom)', 'pedagogy', 'timetable', 'create', 'Permission auto-détectée pour la méthode Timetable::storeRoom()', 'medium', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(302, 'timetable_updateRoom', 'Edit Timetable (updateRoom)', 'pedagogy', 'timetable', 'edit', 'Permission auto-détectée pour la méthode Timetable::updateRoom()', 'medium', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(303, 'timetable_deleteRoom', 'Delete Timetable (deleteRoom)', 'pedagogy', 'timetable', 'delete', 'Permission auto-détectée pour la méthode Timetable::deleteRoom()', 'high', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(304, 'timetable_weeks', 'Manage Timetable (weeks)', 'pedagogy', 'timetable', 'manage', 'Permission auto-détectée pour la méthode Timetable::weeks()', 'low', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(305, 'timetable_storeWeek', 'Create Timetable (storeWeek)', 'pedagogy', 'timetable', 'create', 'Permission auto-détectée pour la méthode Timetable::storeWeek()', 'medium', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(306, 'timetable_updateWeek', 'Edit Timetable (updateWeek)', 'pedagogy', 'timetable', 'edit', 'Permission auto-détectée pour la méthode Timetable::updateWeek()', 'medium', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(307, 'timetable_deleteWeek', 'Delete Timetable (deleteWeek)', 'pedagogy', 'timetable', 'delete', 'Permission auto-détectée pour la méthode Timetable::deleteWeek()', 'high', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(308, 'timetable_wizard', 'Create Timetable (wizard)', 'pedagogy', 'timetable', 'create', 'Permission auto-détectée pour la méthode Timetable::wizard()', 'medium', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(309, 'timetable_wizardStepData', 'Create Timetable (wizardStepData)', 'pedagogy', 'timetable', 'create', 'Permission auto-détectée pour la méthode Timetable::wizardStepData()', 'medium', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(310, 'timetable_createTimetable', 'Create Timetable (createTimetable)', 'pedagogy', 'timetable', 'create', 'Permission auto-détectée pour la méthode Timetable::createTimetable()', 'medium', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(311, 'timetable_grid', 'Manage Timetable (grid)', 'pedagogy', 'timetable', 'manage', 'Permission auto-détectée pour la méthode Timetable::grid()', 'low', 'active', 0, '2026-08-07 04:49:20', '2026-08-07 04:49:20'),
(312, 'timetable_saveGridEntry', 'Create Timetable (saveGridEntry)', 'pedagogy', 'timetable', 'create', 'Permission auto-détectée pour la méthode Timetable::saveGridEntry()', 'medium', 'active', 0, '2026-08-07 04:49:21', '2026-08-07 04:49:21'),
(313, 'timetable_apiGetClassSubjects', 'View Timetable (apiGetClassSubjects)', 'pedagogy', 'timetable', 'view', 'Permission auto-détectée pour la méthode Timetable::apiGetClassSubjects()', 'low', 'active', 0, '2026-08-07 04:49:21', '2026-08-07 04:49:21'),
(314, 'timetable_apiGetSubjectTeachers', 'View Timetable (apiGetSubjectTeachers)', 'pedagogy', 'timetable', 'view', 'Permission auto-détectée pour la méthode Timetable::apiGetSubjectTeachers()', 'low', 'active', 0, '2026-08-07 04:49:21', '2026-08-07 04:49:21'),
(315, 'timetable_apiQuickCreateTeacher', 'Create Timetable (apiQuickCreateTeacher)', 'pedagogy', 'timetable', 'create', 'Permission auto-détectée pour la méthode Timetable::apiQuickCreateTeacher()', 'medium', 'active', 0, '2026-08-07 04:49:21', '2026-08-07 04:49:21'),
(316, 'timetable_deleteGridEntry', 'Delete Timetable (deleteGridEntry)', 'pedagogy', 'timetable', 'delete', 'Permission auto-détectée pour la méthode Timetable::deleteGridEntry()', 'high', 'active', 0, '2026-08-07 04:49:21', '2026-08-07 04:49:21'),
(317, 'timetable_apiValidateConflict', 'Manage Timetable (apiValidateConflict)', 'pedagogy', 'timetable', 'manage', 'Permission auto-détectée pour la méthode Timetable::apiValidateConflict()', 'low', 'active', 0, '2026-08-07 04:49:21', '2026-08-07 04:49:21'),
(318, 'timetable_unlock', 'Manage Timetable (unlock)', 'pedagogy', 'timetable', 'manage', 'Permission auto-détectée pour la méthode Timetable::unlock()', 'low', 'active', 0, '2026-08-07 04:49:21', '2026-08-07 04:49:21'),
(319, 'timetable_deleteTimetable', 'Delete Timetable (deleteTimetable)', 'pedagogy', 'timetable', 'delete', 'Permission auto-détectée pour la méthode Timetable::deleteTimetable()', 'high', 'active', 0, '2026-08-07 04:49:21', '2026-08-07 04:49:21'),
(320, 'timetable_exportPdf', 'Export Timetable (exportPdf)', 'pedagogy', 'timetable', 'export', 'Permission auto-détectée pour la méthode Timetable::exportPdf()', 'low', 'active', 0, '2026-08-07 04:49:21', '2026-08-07 04:49:21'),
(321, 'transcript_index', 'View Transcript (index)', 'students', 'transcript', 'view', 'Permission auto-détectée pour la méthode Transcript::index()', 'low', 'active', 0, '2026-08-07 04:49:21', '2026-08-07 04:49:21'),
(322, 'transcript_generate', 'Manage Transcript (generate)', 'students', 'transcript', 'manage', 'Permission auto-détectée pour la méthode Transcript::generate()', 'low', 'active', 0, '2026-08-07 04:49:21', '2026-08-07 04:49:21'),
(323, 'user_index', 'View User (index)', 'system', 'user', 'view', 'Permission auto-détectée pour la méthode User::index()', 'low', 'active', 0, '2026-08-07 04:49:22', '2026-08-07 04:49:22'),
(324, 'user_export', 'Export User (export)', 'system', 'user', 'export', 'Permission auto-détectée pour la méthode User::export()', 'low', 'active', 0, '2026-08-07 04:49:22', '2026-08-07 04:49:22'),
(325, 'user_create', 'Create User (create)', 'system', 'user', 'create', 'Permission auto-détectée pour la méthode User::create()', 'medium', 'active', 0, '2026-08-07 04:49:22', '2026-08-07 04:49:22'),
(326, 'user_store', 'Create User (store)', 'system', 'user', 'create', 'Permission auto-détectée pour la méthode User::store()', 'medium', 'active', 0, '2026-08-07 04:49:22', '2026-08-07 04:49:22'),
(327, 'user_edit', 'Edit User (edit)', 'system', 'user', 'edit', 'Permission auto-détectée pour la méthode User::edit()', 'medium', 'active', 0, '2026-08-07 04:49:22', '2026-08-07 04:49:22'),
(328, 'user_update', 'Edit User (update)', 'system', 'user', 'edit', 'Permission auto-détectée pour la méthode User::update()', 'medium', 'active', 0, '2026-08-07 04:49:22', '2026-08-07 04:49:22'),
(329, 'user_delete', 'Delete User (delete)', 'system', 'user', 'delete', 'Permission auto-détectée pour la méthode User::delete()', 'high', 'active', 0, '2026-08-07 04:49:22', '2026-08-07 04:49:22'),
(330, 'user_createCaissier', 'Create User (createCaissier)', 'system', 'user', 'create', 'Permission auto-détectée pour la méthode User::createCaissier()', 'medium', 'active', 0, '2026-08-07 04:49:22', '2026-08-07 04:49:22'),
(331, 'user_storeCaissier', 'Create User (storeCaissier)', 'system', 'user', 'create', 'Permission auto-détectée pour la méthode User::storeCaissier()', 'medium', 'active', 0, '2026-08-07 04:49:23', '2026-08-07 04:49:23'),
(332, 'user_caissiers', 'Manage User (caissiers)', 'system', 'user', 'manage', 'Permission auto-détectée pour la méthode User::caissiers()', 'low', 'active', 0, '2026-08-07 04:49:23', '2026-08-07 04:49:23'),
(333, 'user_toggleStatus', 'Edit User (toggleStatus)', 'system', 'user', 'edit', 'Permission auto-détectée pour la méthode User::toggleStatus()', 'medium', 'active', 0, '2026-08-07 04:49:23', '2026-08-07 04:49:23'),
(334, 'verificationadmin_index', 'View Verificationadmin (index)', 'general', 'verificationadmin', 'view', 'Permission auto-détectée pour la méthode VerificationAdmin::index()', 'low', 'active', 0, '2026-08-07 04:49:23', '2026-08-07 04:49:23');

-- --------------------------------------------------------

--
-- Structure de la table `permission_audit_logs`
--

CREATE TABLE `permission_audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(150) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL COMMENT 'role_updated, user_override_added, user_override_removed, backup_restored, scan_executed',
  `entity_type` varchar(50) NOT NULL COMMENT 'role, user, system',
  `entity_id` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `payload_before` longtext DEFAULT NULL,
  `payload_after` longtext DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `permission_audit_logs`
--

INSERT INTO `permission_audit_logs` (`id`, `user_id`, `user_name`, `action_type`, `entity_type`, `entity_id`, `details`, `payload_before`, `payload_after`, `ip_address`, `created_at`) VALUES
(1, NULL, 'Système', 'scan_executed', 'system', 'auto_detector', 'Scan automatique exécuté : 261 nouvelle(s) permission(s) détectée(s).', NULL, '{\"scanned_total\":267,\"created\":261,\"existing\":6}', '127.0.0.1', '2026-08-07 04:49:23'),
(2, NULL, 'Système', 'test_action', 'system', 'test_123', 'Unit test audit entry', NULL, NULL, '127.0.0.1', '2026-08-07 04:49:23'),
(3, NULL, 'Système', 'scan_executed', 'system', 'auto_detector', 'Scan automatique exécuté : 0 nouvelle(s) permission(s) détectée(s).', NULL, '{\"scanned_total\":267,\"created\":0,\"existing\":267}', '127.0.0.1', '2026-08-07 04:49:30'),
(4, NULL, 'Système', 'test_action', 'system', 'test_123', 'Unit test audit entry', NULL, NULL, '127.0.0.1', '2026-08-07 04:49:30'),
(5, 42, 'Système', 'scan_executed', 'system', 'auto_detector', 'Scan automatique exécuté : 0 nouvelle(s) permission(s) détectée(s).', NULL, '{\"scanned_total\":268,\"created\":0,\"existing\":268}', '::1', '2026-08-07 05:05:26'),
(6, 42, 'Système', 'role_updated', 'role', '1', 'Mise à jour des privilèges pour le rôle \'Super Administrateur\' (73 permissions).', '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,36,37,42,51,58]', '[5,6,7,8,9,10,11,12,13,14,15,16,18,19,21,22,24,25,26,29,30,32,33,34,36,37,51,58,20,23,3,123,124,122,141,140,168,167,42,209,211,200,206,4,202,201,203,207,204,208,210,197,199,198,205,196,242,243,244,241,2,325,330,326,331,329,327,333,328,324,332,323,1]', '::1', '2026-08-07 05:10:54'),
(7, 42, 'Système', 'role_updated', 'role', '1', 'Mise à jour des privilèges pour le rôle \'Super Administrateur\' (72 permissions).', '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,18,19,20,21,22,23,24,25,26,29,30,32,33,34,36,37,42,51,58,122,123,124,140,141,167,168,196,197,198,199,200,201,202,203,204,205,206,207,208,209,210,211,241,242,243,244,323,324,325,326,327,328,329,330,331,332,333]', '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,19,20,21,22,23,24,25,26,29,30,32,33,34,36,37,42,51,58,122,123,124,140,141,167,168,196,197,198,199,200,201,202,203,204,205,206,207,208,209,210,211,241,242,243,244,323,324,325,326,327,328,329,330,331,332,333]', '::1', '2026-08-07 05:14:05'),
(8, 42, 'Système', 'role_updated', 'role', '1', 'Mise à jour des privilèges pour le rôle \'Super Administrateur\' (88 permissions).', '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,19,20,21,22,23,24,25,26,29,30,32,33,34,36,37,42,51,58,122,123,124,140,141,167,168,196,197,198,199,200,201,202,203,204,205,206,207,208,209,210,211,241,242,243,244,323,324,325,326,327,328,329,330,331,332,333]', '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,19,21,22,23,24,25,26,29,30,32,33,34,36,37,42,51,58,122,123,124,140,141,167,168,196,197,198,199,200,201,202,203,204,205,206,207,208,209,210,211,241,242,243,244,323,324,325,326,327,328,329,330,331,332,333,116,118,82,75,80,78,85,81,76,86,87,109,121,114,113,108,74]', '::1', '2026-08-07 05:30:37'),
(9, 42, 'Système', 'backup_created', 'system', 'backup', 'Création de la sauvegarde RBAC \'Sauvegarde_2026-08-07\'.', NULL, NULL, '::1', '2026-08-07 05:30:43'),
(10, 39, 'Système', 'test_action', 'test_entity', '123', 'Audit verification test', NULL, NULL, '127.0.0.1', '2026-08-07 13:17:44'),
(11, 42, 'Système', 'role_updated', 'role', '1', 'Mise à jour des privilèges pour le rôle \'Super Administrateur\' (84 permissions).', '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,19,21,22,23,24,25,26,29,30,32,33,34,36,37,42,51,58,74,75,76,78,80,81,82,85,86,87,108,109,113,114,116,118,121,122,123,124,140,141,167,168,196,197,198,199,200,201,202,203,204,205,206,207,208,209,210,211,241,242,243,244,323,324,325,326,327,328,329,330,331,332,333]', '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,21,22,23,24,25,29,32,33,36,37,42,51,58,74,75,76,78,80,81,82,85,86,87,108,109,113,114,116,118,121,122,123,124,140,141,167,168,196,197,198,199,200,201,202,203,204,205,206,207,208,209,210,211,241,242,243,244,323,324,325,326,327,328,329,330,331,332,333]', '::1', '2026-08-07 13:44:47'),
(12, 42, 'Système', 'backup_created', 'system', 'backup', 'Création de la sauvegarde RBAC \'Sauvegarde_2026-08-07\'.', NULL, NULL, '::1', '2026-08-07 13:44:53');

-- --------------------------------------------------------

--
-- Structure de la table `permission_backups`
--

CREATE TABLE `permission_backups` (
  `id` int(11) NOT NULL,
  `backup_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `config_data` longtext NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_by_name` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `permission_backups`
--

INSERT INTO `permission_backups` (`id`, `backup_name`, `description`, `config_data`, `created_by`, `created_by_name`, `created_at`) VALUES
(1, 'Sauvegarde_2026-08-07', 'Sauvegarde manuelle RBAC', '{\"timestamp\":\"2026-08-07 07:30:42\",\"roles\":[{\"id\":1,\"role_code\":\"superadmin\",\"role_name\":\"Super Administrateur\",\"description\":\"Accès complet absolu au système.\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:50\"},{\"id\":2,\"role_code\":\"admin\",\"role_name\":\"Administrateur\",\"description\":\"Administration classique et globale de l\'établissement.\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:50\"},{\"id\":3,\"role_code\":\"it_manager\",\"role_name\":\"IT Manager\",\"description\":\"Responsable de la configuration technique et pédagogique.\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:50\"},{\"id\":4,\"role_code\":\"caissier\",\"role_name\":\"Caissier\",\"description\":\"Gestionnaire des encaissements et versements quotidiens.\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":5,\"role_code\":\"comptable\",\"role_name\":\"Comptable\",\"description\":\"Responsable financier, des tarifs, bourses et bilans.\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":6,\"role_code\":\"enseignant\",\"role_name\":\"Enseignant\",\"description\":\"Enseignant accédant aux notes, absences et livrets.\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"}],\"permissions\":[{\"id\":1,\"perm_code\":\"manage_users\",\"perm_name\":\"Gérer les utilisateurs\",\"module\":\"system\",\"submodule\":\"users\",\"action\":\"manage\",\"description\":\"Créer, modifier et gérer les comptes d\'accès système.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":2,\"perm_code\":\"manage_settings\",\"perm_name\":\"Gérer les paramètres généraux\",\"module\":\"system\",\"submodule\":\"settings\",\"action\":\"manage\",\"description\":\"Configurer l\'établissement, le logo et les paramètres globaux.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":3,\"perm_code\":\"view_system_logs\",\"perm_name\":\"Consulter les journaux système\",\"module\":\"system\",\"submodule\":\"audit\",\"action\":\"view\",\"description\":\"Visualiser les logs d\'activité et les événements de sécurité.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":4,\"perm_code\":\"manage_rbac\",\"perm_name\":\"Gérer la sécurité RBAC\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"manage\",\"description\":\"Configurer les rôles, les autorisations et les exceptions utilisateurs.\",\"criticality\":\"critical\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":5,\"perm_code\":\"view_classes\",\"perm_name\":\"Consulter les classes\",\"module\":\"pedagogy\",\"submodule\":\"classes\",\"action\":\"view\",\"description\":\"Afficher la liste des classes et effectifs.\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":6,\"perm_code\":\"manage_classes_structure\",\"perm_name\":\"Gérer la structure des classes\",\"module\":\"pedagogy\",\"submodule\":\"classes\",\"action\":\"manage\",\"description\":\"Créer, modifier et supprimer des classes.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":7,\"perm_code\":\"manage_teaching_types\",\"perm_name\":\"Gérer les types d\'enseignement\",\"module\":\"pedagogy\",\"submodule\":\"structure\",\"action\":\"manage\",\"description\":\"Configurer les types d\'enseignement (Général, Technique, LMD).\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":8,\"perm_code\":\"manage_cycles\",\"perm_name\":\"Gérer les cycles\",\"module\":\"pedagogy\",\"submodule\":\"structure\",\"action\":\"manage\",\"description\":\"Gérer les cycles académiques.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":9,\"perm_code\":\"manage_sections\",\"perm_name\":\"Gérer les sections\",\"module\":\"pedagogy\",\"submodule\":\"structure\",\"action\":\"manage\",\"description\":\"Gérer les sections francophones \\/ anglophones.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":10,\"perm_code\":\"manage_departments\",\"perm_name\":\"Gérer les départements\",\"module\":\"pedagogy\",\"submodule\":\"structure\",\"action\":\"manage\",\"description\":\"Gérer les départements d\'enseignement.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":11,\"perm_code\":\"manage_subjects\",\"perm_name\":\"Gérer les matières\",\"module\":\"pedagogy\",\"submodule\":\"subjects\",\"action\":\"manage\",\"description\":\"Gérer le catalogue des matières et coefficients.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":12,\"perm_code\":\"manage_teachers\",\"perm_name\":\"Gérer les enseignants\",\"module\":\"pedagogy\",\"submodule\":\"teachers\",\"action\":\"manage\",\"description\":\"Gérer le registre des enseignants et leurs affectations.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":13,\"perm_code\":\"manage_timetables\",\"perm_name\":\"Gérer les emplois du temps\",\"module\":\"pedagogy\",\"submodule\":\"timetables\",\"action\":\"manage\",\"description\":\"Planifier et éditer les emplois du temps des classes.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":14,\"perm_code\":\"manage_academic_years\",\"perm_name\":\"Gérer les années scolaires\",\"module\":\"pedagogy\",\"submodule\":\"academic_years\",\"action\":\"manage\",\"description\":\"Activer, clôturer et basculer les années académiques.\",\"criticality\":\"critical\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":15,\"perm_code\":\"manage_sequences\",\"perm_name\":\"Gérer les séquences\",\"module\":\"pedagogy\",\"submodule\":\"sequences\",\"action\":\"manage\",\"description\":\"Définir les séquences et semestres d\'évaluation.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":16,\"perm_code\":\"view_students\",\"perm_name\":\"Consulter les élèves\",\"module\":\"students\",\"submodule\":\"registry\",\"action\":\"view\",\"description\":\"Visualiser les registres des élèves.\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":17,\"perm_code\":\"manage_students\",\"perm_name\":\"Gérer les registres élèves\",\"module\":\"students\",\"submodule\":\"registry\",\"action\":\"manage\",\"description\":\"Inscrire, modifier les profils et gérer la scolarité des élèves.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":18,\"perm_code\":\"manage_marks\",\"perm_name\":\"Saisir et modifier les notes\",\"module\":\"evaluations\",\"submodule\":\"grades\",\"action\":\"manage\",\"description\":\"Saisir, verrouiller et valider les notes d\'évaluation.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":19,\"perm_code\":\"manage_bulletins\",\"perm_name\":\"Gérer les bulletins de notes\",\"module\":\"evaluations\",\"submodule\":\"bulletins\",\"action\":\"manage\",\"description\":\"Calculer les moyennes, éditer les bulletins et PV.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":20,\"perm_code\":\"manage_absences\",\"perm_name\":\"Gérer les absences et discipline\",\"module\":\"evaluations\",\"submodule\":\"discipline\",\"action\":\"manage\",\"description\":\"Saisir et récapituler les absences et blâmes.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":21,\"perm_code\":\"manage_staff\",\"perm_name\":\"Gérer le personnel\",\"module\":\"hr\",\"submodule\":\"staff\",\"action\":\"manage\",\"description\":\"Gérer les fiches et dossiers administratifs du personnel.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:53\"},{\"id\":22,\"perm_code\":\"manage_contracts\",\"perm_name\":\"Gérer les contrats de travail\",\"module\":\"hr\",\"submodule\":\"contracts\",\"action\":\"manage\",\"description\":\"Gérer la rédaction et le suivi des contrats.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:53\"},{\"id\":23,\"perm_code\":\"manage_fees\",\"perm_name\":\"Gérer les frais de scolarité\",\"module\":\"finance\",\"submodule\":\"fees\",\"action\":\"manage\",\"description\":\"Accès global à la configuration de la scolarité.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":24,\"perm_code\":\"view_class_finances\",\"perm_name\":\"Consulter les tarifs de scolarité\",\"module\":\"finance\",\"submodule\":\"fees\",\"action\":\"view\",\"description\":\"Voir la grille tarifaire des frais de scolarité.\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":25,\"perm_code\":\"edit_class_finances\",\"perm_name\":\"Configurer la grille tarifaire\",\"module\":\"finance\",\"submodule\":\"fees\",\"action\":\"edit\",\"description\":\"Définir les échéances et montants des tranches.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":26,\"perm_code\":\"manage_payments\",\"perm_name\":\"Enregistrer et gérer les paiements\",\"module\":\"finance\",\"submodule\":\"payments\",\"action\":\"manage\",\"description\":\"Saisir les versements, imprimer les reçus et annuler.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":27,\"perm_code\":\"manage_discounts\",\"perm_name\":\"Gérer les réductions de scolarité\",\"module\":\"finance\",\"submodule\":\"discounts\",\"action\":\"manage\",\"description\":\"Accorder des remises ou réductions aux élèves.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":28,\"perm_code\":\"manage_scholarships\",\"perm_name\":\"Gérer les bourses scolaires\",\"module\":\"finance\",\"submodule\":\"scholarships\",\"action\":\"manage\",\"description\":\"Attribuer et suivre les bourses d\'études.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":29,\"perm_code\":\"view_financial_history\",\"perm_name\":\"Consulter l\'historique financier\",\"module\":\"finance\",\"submodule\":\"reports\",\"action\":\"view\",\"description\":\"Consulter le journal des transactions financières.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:53\"},{\"id\":30,\"perm_code\":\"view_financial_reports\",\"perm_name\":\"Consulter les rapports et insolvables\",\"module\":\"finance\",\"submodule\":\"reports\",\"action\":\"view\",\"description\":\"Consulter les bilans d\'encaissement et listes d\'insolvabilité.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:53\"},{\"id\":31,\"perm_code\":\"manage_expenses\",\"perm_name\":\"Gérer les dépenses d\'établissement\",\"module\":\"finance\",\"submodule\":\"expenses\",\"action\":\"manage\",\"description\":\"Saisir et approuver les dépenses et frais d\'exploitation.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-07-08 23:31:26\",\"updated_at\":\"2026-08-07 06:48:53\"},{\"id\":32,\"perm_code\":\"manage_levels\",\"perm_name\":\"Gérer les niveaux d\'étude\",\"module\":\"pedagogy\",\"submodule\":\"structure\",\"action\":\"manage\",\"description\":\"Configurer les niveaux d\'étude.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-07-29 04:07:18\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":33,\"perm_code\":\"view_transcripts\",\"perm_name\":\"Consulter les relevés de notes\",\"module\":\"general\",\"submodule\":\"general\",\"action\":\"view\",\"description\":\"Visualiser et prévisualiser les relevés de notes des élèves.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-07-31 09:10:36\",\"updated_at\":\"2026-07-31 09:10:36\"},{\"id\":34,\"perm_code\":\"manage_transcripts\",\"perm_name\":\"Gérer les relevés de notes\",\"module\":\"evaluations\",\"submodule\":\"transcripts\",\"action\":\"manage\",\"description\":\"Générer les relevés de notes officiels.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-07-31 09:10:37\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":36,\"perm_code\":\"view_timetables\",\"perm_name\":\"Consulter les emplois du temps\",\"module\":\"general\",\"submodule\":\"general\",\"action\":\"view\",\"description\":\"Visualiser, partager et imprimer les emplois du temps.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-08-04 05:53:05\",\"updated_at\":\"2026-08-04 05:53:05\"},{\"id\":37,\"perm_code\":\"unlock_timetables\",\"perm_name\":\"Déverrouiller les emplois du temps\",\"module\":\"general\",\"submodule\":\"general\",\"action\":\"view\",\"description\":\"Réservé au Superadmin pour déverrouiller un emploi du temps clôturé.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-08-04 05:53:05\",\"updated_at\":\"2026-08-04 05:53:05\"},{\"id\":42,\"perm_code\":\"view_pilotage\",\"perm_name\":\"Accéder au Centre de Pilotage\",\"module\":\"system\",\"submodule\":\"pilotage\",\"action\":\"view\",\"description\":\"Accéder aux tableaux de bord analytiques et bilans d\'impact.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-08-07 06:48:51\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":51,\"perm_code\":\"manage_subject_groups\",\"perm_name\":\"Gérer les groupes de matières\",\"module\":\"pedagogy\",\"submodule\":\"subjects\",\"action\":\"manage\",\"description\":\"Organiser les matières en groupes\\/UE.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-08-07 06:48:52\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":58,\"perm_code\":\"export_students\",\"perm_name\":\"Exporter les données élèves\",\"module\":\"students\",\"submodule\":\"registry\",\"action\":\"export\",\"description\":\"Exporter les registres élèves au format Excel\\/PDF.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-08-07 06:48:52\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":74,\"perm_code\":\"academicyear_index\",\"perm_name\":\"View Academicyear (index)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:04\",\"updated_at\":\"2026-08-07 06:49:04\"},{\"id\":75,\"perm_code\":\"academicyear_create\",\"perm_name\":\"Create Academicyear (create)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:04\",\"updated_at\":\"2026-08-07 06:49:04\"},{\"id\":76,\"perm_code\":\"academicyear_store\",\"perm_name\":\"Create Academicyear (store)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:04\",\"updated_at\":\"2026-08-07 06:49:04\"},{\"id\":77,\"perm_code\":\"academicyear_activate\",\"perm_name\":\"Manage Academicyear (activate)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::activate()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:04\",\"updated_at\":\"2026-08-07 06:49:04\"},{\"id\":78,\"perm_code\":\"academicyear_rolloverWizard\",\"perm_name\":\"Create Academicyear (rolloverWizard)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::rolloverWizard()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":79,\"perm_code\":\"academicyear_doRollover\",\"perm_name\":\"Manage Academicyear (doRollover)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::doRollover()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":80,\"perm_code\":\"academicyear_archiveWizard\",\"perm_name\":\"Create Academicyear (archiveWizard)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::archiveWizard()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":81,\"perm_code\":\"academicyear_doArchive\",\"perm_name\":\"Manage Academicyear (doArchive)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::doArchive()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":82,\"perm_code\":\"academicyear_restore\",\"perm_name\":\"Create Academicyear (restore)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::restore()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":83,\"perm_code\":\"academicyear_unarchive\",\"perm_name\":\"Manage Academicyear (unarchive)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::unarchive()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":84,\"perm_code\":\"academicyear_doUnarchive\",\"perm_name\":\"Manage Academicyear (doUnarchive)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::doUnarchive()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":85,\"perm_code\":\"academicyear_edit\",\"perm_name\":\"Edit Academicyear (edit)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":86,\"perm_code\":\"academicyear_update\",\"perm_name\":\"Edit Academicyear (update)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":87,\"perm_code\":\"academicyear_delete\",\"perm_name\":\"Delete Academicyear (delete)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":88,\"perm_code\":\"auth_loginView\",\"perm_name\":\"View Auth (loginView)\",\"module\":\"general\",\"submodule\":\"auth\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Auth::loginView()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":89,\"perm_code\":\"auth_loginPost\",\"perm_name\":\"Manage Auth (loginPost)\",\"module\":\"general\",\"submodule\":\"auth\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Auth::loginPost()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":90,\"perm_code\":\"auth_logout\",\"perm_name\":\"Manage Auth (logout)\",\"module\":\"general\",\"submodule\":\"auth\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Auth::logout()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":91,\"perm_code\":\"auth_registerTeacherView\",\"perm_name\":\"View Auth (registerTeacherView)\",\"module\":\"general\",\"submodule\":\"auth\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Auth::registerTeacherView()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":92,\"perm_code\":\"auth_registerTeacherPost\",\"perm_name\":\"Manage Auth (registerTeacherPost)\",\"module\":\"general\",\"submodule\":\"auth\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Auth::registerTeacherPost()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":93,\"perm_code\":\"bulletin_index\",\"perm_name\":\"View Bulletin (index)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":94,\"perm_code\":\"bulletin_discipline\",\"perm_name\":\"Manage Bulletin (discipline)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::discipline()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":95,\"perm_code\":\"bulletin_saveDiscipline\",\"perm_name\":\"Create Bulletin (saveDiscipline)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::saveDiscipline()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":96,\"perm_code\":\"bulletin_sequence\",\"perm_name\":\"Manage Bulletin (sequence)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::sequence()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":97,\"perm_code\":\"bulletin_trimestre\",\"perm_name\":\"Manage Bulletin (trimestre)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::trimestre()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":98,\"perm_code\":\"bulletin_sequenceClass\",\"perm_name\":\"Manage Bulletin (sequenceClass)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::sequenceClass()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":99,\"perm_code\":\"bulletin_trimestreClass\",\"perm_name\":\"Manage Bulletin (trimestreClass)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::trimestreClass()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":100,\"perm_code\":\"bulletin_annuel\",\"perm_name\":\"Manage Bulletin (annuel)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::annuel()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":101,\"perm_code\":\"bulletin_annuelClass\",\"perm_name\":\"Manage Bulletin (annuelClass)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::annuelClass()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":102,\"perm_code\":\"bulletin_getClassesBySectionJson\",\"perm_name\":\"View Bulletin (getClassesBySectionJson)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::getClassesBySectionJson()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":103,\"perm_code\":\"class_index\",\"perm_name\":\"View Class (index)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Class::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":104,\"perm_code\":\"class_export\",\"perm_name\":\"Export Class (export)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Class::export()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":105,\"perm_code\":\"class_create\",\"perm_name\":\"Create Class (create)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Class::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":106,\"perm_code\":\"class_store\",\"perm_name\":\"Create Class (store)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Class::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":107,\"perm_code\":\"class_edit\",\"perm_name\":\"Edit Class (edit)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Class::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":108,\"perm_code\":\"class_update\",\"perm_name\":\"Edit Class (update)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Class::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":109,\"perm_code\":\"class_delete\",\"perm_name\":\"Delete Class (delete)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Class::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":110,\"perm_code\":\"class_manageTeam\",\"perm_name\":\"View Class (manageTeam)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Class::manageTeam()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":111,\"perm_code\":\"class_setMainTeacher\",\"perm_name\":\"Manage Class (setMainTeacher)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Class::setMainTeacher()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":112,\"perm_code\":\"class_import\",\"perm_name\":\"Import Class (import)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Class::import()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":113,\"perm_code\":\"class_downloadTemplate\",\"perm_name\":\"Export Class (downloadTemplate)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Class::downloadTemplate()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":114,\"perm_code\":\"class_upload\",\"perm_name\":\"Import Class (upload)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Class::upload()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":115,\"perm_code\":\"cycle_index\",\"perm_name\":\"View Cycle (index)\",\"module\":\"pedagogy\",\"submodule\":\"cycle\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Cycle::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":116,\"perm_code\":\"cycle_create\",\"perm_name\":\"Create Cycle (create)\",\"module\":\"pedagogy\",\"submodule\":\"cycle\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Cycle::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":117,\"perm_code\":\"cycle_store\",\"perm_name\":\"Create Cycle (store)\",\"module\":\"pedagogy\",\"submodule\":\"cycle\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Cycle::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":118,\"perm_code\":\"cycle_edit\",\"perm_name\":\"Edit Cycle (edit)\",\"module\":\"pedagogy\",\"submodule\":\"cycle\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Cycle::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":119,\"perm_code\":\"cycle_update\",\"perm_name\":\"Edit Cycle (update)\",\"module\":\"pedagogy\",\"submodule\":\"cycle\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Cycle::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":120,\"perm_code\":\"cycle_toggleStatus\",\"perm_name\":\"Edit Cycle (toggleStatus)\",\"module\":\"pedagogy\",\"submodule\":\"cycle\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Cycle::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":121,\"perm_code\":\"cycle_delete\",\"perm_name\":\"Delete Cycle (delete)\",\"module\":\"pedagogy\",\"submodule\":\"cycle\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Cycle::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":122,\"perm_code\":\"dashboard_index\",\"perm_name\":\"View Dashboard (index)\",\"module\":\"system\",\"submodule\":\"dashboard\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Dashboard::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":123,\"perm_code\":\"dashboard_executiveDashboard\",\"perm_name\":\"Manage Dashboard (executiveDashboard)\",\"module\":\"system\",\"submodule\":\"dashboard\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Dashboard::executiveDashboard()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":124,\"perm_code\":\"dashboard_financialCenter\",\"perm_name\":\"Manage Dashboard (financialCenter)\",\"module\":\"system\",\"submodule\":\"dashboard\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Dashboard::financialCenter()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":125,\"perm_code\":\"department_index\",\"perm_name\":\"View Department (index)\",\"module\":\"pedagogy\",\"submodule\":\"department\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Department::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":126,\"perm_code\":\"department_create\",\"perm_name\":\"Create Department (create)\",\"module\":\"pedagogy\",\"submodule\":\"department\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Department::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":127,\"perm_code\":\"department_store\",\"perm_name\":\"Create Department (store)\",\"module\":\"pedagogy\",\"submodule\":\"department\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Department::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":128,\"perm_code\":\"department_edit\",\"perm_name\":\"Edit Department (edit)\",\"module\":\"pedagogy\",\"submodule\":\"department\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Department::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":129,\"perm_code\":\"department_update\",\"perm_name\":\"Edit Department (update)\",\"module\":\"pedagogy\",\"submodule\":\"department\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Department::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":130,\"perm_code\":\"department_toggleStatus\",\"perm_name\":\"Edit Department (toggleStatus)\",\"module\":\"pedagogy\",\"submodule\":\"department\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Department::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":131,\"perm_code\":\"department_delete\",\"perm_name\":\"Delete Department (delete)\",\"module\":\"pedagogy\",\"submodule\":\"department\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Department::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":132,\"perm_code\":\"discount_index\",\"perm_name\":\"View Discount (index)\",\"module\":\"finance\",\"submodule\":\"discount\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Discount::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":133,\"perm_code\":\"discount_store\",\"perm_name\":\"Create Discount (store)\",\"module\":\"finance\",\"submodule\":\"discount\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Discount::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":134,\"perm_code\":\"discount_toggleStatus\",\"perm_name\":\"Edit Discount (toggleStatus)\",\"module\":\"finance\",\"submodule\":\"discount\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Discount::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":135,\"perm_code\":\"discount_delete\",\"perm_name\":\"Delete Discount (delete)\",\"module\":\"finance\",\"submodule\":\"discount\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Discount::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":136,\"perm_code\":\"discounttype_index\",\"perm_name\":\"View Discounttype (index)\",\"module\":\"finance\",\"submodule\":\"discounttype\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode DiscountType::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":137,\"perm_code\":\"discounttype_store\",\"perm_name\":\"Create Discounttype (store)\",\"module\":\"finance\",\"submodule\":\"discounttype\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode DiscountType::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":138,\"perm_code\":\"discounttype_toggleStatus\",\"perm_name\":\"Edit Discounttype (toggleStatus)\",\"module\":\"finance\",\"submodule\":\"discounttype\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode DiscountType::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":139,\"perm_code\":\"discounttype_delete\",\"perm_name\":\"Delete Discounttype (delete)\",\"module\":\"finance\",\"submodule\":\"discounttype\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode DiscountType::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":140,\"perm_code\":\"documentation_index\",\"perm_name\":\"View Documentation (index)\",\"module\":\"system\",\"submodule\":\"documentation\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Documentation::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":141,\"perm_code\":\"documentation_download\",\"perm_name\":\"Export Documentation (download)\",\"module\":\"system\",\"submodule\":\"documentation\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Documentation::download()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":142,\"perm_code\":\"expense_index\",\"perm_name\":\"View Expense (index)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Expense::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":143,\"perm_code\":\"expense_store\",\"perm_name\":\"Create Expense (store)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Expense::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":144,\"perm_code\":\"expense_update\",\"perm_name\":\"Edit Expense (update)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Expense::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":145,\"perm_code\":\"expense_cancel\",\"perm_name\":\"Manage Expense (cancel)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Expense::cancel()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":146,\"perm_code\":\"expense_printReport\",\"perm_name\":\"Export Expense (printReport)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Expense::printReport()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":147,\"perm_code\":\"expense_categories\",\"perm_name\":\"Manage Expense (categories)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Expense::categories()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":148,\"perm_code\":\"expense_storeCategory\",\"perm_name\":\"Create Expense (storeCategory)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Expense::storeCategory()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":149,\"perm_code\":\"expense_updateCategory\",\"perm_name\":\"Edit Expense (updateCategory)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Expense::updateCategory()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":150,\"perm_code\":\"expense_toggleCategoryStatus\",\"perm_name\":\"Edit Expense (toggleCategoryStatus)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Expense::toggleCategoryStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":151,\"perm_code\":\"expense_auditLogs\",\"perm_name\":\"Manage Expense (auditLogs)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Expense::auditLogs()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":152,\"perm_code\":\"financialhistory_index\",\"perm_name\":\"View Financialhistory (index)\",\"module\":\"finance\",\"submodule\":\"financialhistory\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode FinancialHistory::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":153,\"perm_code\":\"financialhistory_print\",\"perm_name\":\"Export Financialhistory (print)\",\"module\":\"finance\",\"submodule\":\"financialhistory\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode FinancialHistory::print()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":154,\"perm_code\":\"grade_index\",\"perm_name\":\"View Grade (index)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Grade::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":155,\"perm_code\":\"grade_export\",\"perm_name\":\"Export Grade (export)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Grade::export()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":156,\"perm_code\":\"grade_saisie\",\"perm_name\":\"Manage Grade (saisie)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Grade::saisie()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":157,\"perm_code\":\"grade_store\",\"perm_name\":\"Create Grade (store)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Grade::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":158,\"perm_code\":\"grade_import\",\"perm_name\":\"Import Grade (import)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Grade::import()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":159,\"perm_code\":\"grade_downloadTemplate\",\"perm_name\":\"Export Grade (downloadTemplate)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Grade::downloadTemplate()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":160,\"perm_code\":\"grade_upload\",\"perm_name\":\"Import Grade (upload)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Grade::upload()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":161,\"perm_code\":\"grade_history\",\"perm_name\":\"Manage Grade (history)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Grade::history()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":162,\"perm_code\":\"honorroll_index\",\"perm_name\":\"View Honorroll (index)\",\"module\":\"students\",\"submodule\":\"honorroll\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode HonorRoll::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":163,\"perm_code\":\"honorroll_trimestre\",\"perm_name\":\"Manage Honorroll (trimestre)\",\"module\":\"students\",\"submodule\":\"honorroll\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode HonorRoll::trimestre()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":164,\"perm_code\":\"honorroll_trimesterBulk\",\"perm_name\":\"Manage Honorroll (trimesterBulk)\",\"module\":\"students\",\"submodule\":\"honorroll\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode HonorRoll::trimesterBulk()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":165,\"perm_code\":\"honorroll_annuel\",\"perm_name\":\"Manage Honorroll (annuel)\",\"module\":\"students\",\"submodule\":\"honorroll\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode HonorRoll::annuel()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":166,\"perm_code\":\"honorroll_annuelBulk\",\"perm_name\":\"Manage Honorroll (annuelBulk)\",\"module\":\"students\",\"submodule\":\"honorroll\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode HonorRoll::annuelBulk()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":167,\"perm_code\":\"impactanalysis_getAnalysis\",\"perm_name\":\"View Impactanalysis (getAnalysis)\",\"module\":\"system\",\"submodule\":\"impactanalysis\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode ImpactAnalysis::getAnalysis()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":168,\"perm_code\":\"impactanalysis_executeDelete\",\"perm_name\":\"Delete Impactanalysis (executeDelete)\",\"module\":\"system\",\"submodule\":\"impactanalysis\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode ImpactAnalysis::executeDelete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":169,\"perm_code\":\"landing_index\",\"perm_name\":\"View Landing (index)\",\"module\":\"general\",\"submodule\":\"landing\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Landing::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":170,\"perm_code\":\"landing_sendContact\",\"perm_name\":\"Manage Landing (sendContact)\",\"module\":\"general\",\"submodule\":\"landing\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Landing::sendContact()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":171,\"perm_code\":\"landing_toggleArchiveNotification\",\"perm_name\":\"Edit Landing (toggleArchiveNotification)\",\"module\":\"general\",\"submodule\":\"landing\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Landing::toggleArchiveNotification()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":172,\"perm_code\":\"landing_deleteNotification\",\"perm_name\":\"Delete Landing (deleteNotification)\",\"module\":\"general\",\"submodule\":\"landing\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Landing::deleteNotification()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":173,\"perm_code\":\"landing_marks\",\"perm_name\":\"Manage Landing (marks)\",\"module\":\"general\",\"submodule\":\"landing\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Landing::marks()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":174,\"perm_code\":\"level_index\",\"perm_name\":\"View Level (index)\",\"module\":\"pedagogy\",\"submodule\":\"level\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Level::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":175,\"perm_code\":\"level_create\",\"perm_name\":\"Create Level (create)\",\"module\":\"pedagogy\",\"submodule\":\"level\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Level::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":176,\"perm_code\":\"level_store\",\"perm_name\":\"Create Level (store)\",\"module\":\"pedagogy\",\"submodule\":\"level\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Level::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":177,\"perm_code\":\"level_edit\",\"perm_name\":\"Edit Level (edit)\",\"module\":\"pedagogy\",\"submodule\":\"level\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Level::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":178,\"perm_code\":\"level_update\",\"perm_name\":\"Edit Level (update)\",\"module\":\"pedagogy\",\"submodule\":\"level\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Level::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":179,\"perm_code\":\"level_toggleStatus\",\"perm_name\":\"Edit Level (toggleStatus)\",\"module\":\"pedagogy\",\"submodule\":\"level\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Level::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":180,\"perm_code\":\"level_delete\",\"perm_name\":\"Delete Level (delete)\",\"module\":\"pedagogy\",\"submodule\":\"level\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Level::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":181,\"perm_code\":\"payment_index\",\"perm_name\":\"View Payment (index)\",\"module\":\"finance\",\"submodule\":\"payment\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Payment::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":182,\"perm_code\":\"payment_studentDetails\",\"perm_name\":\"Manage Payment (studentDetails)\",\"module\":\"finance\",\"submodule\":\"payment\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Payment::studentDetails()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":183,\"perm_code\":\"payment_store\",\"perm_name\":\"Create Payment (store)\",\"module\":\"finance\",\"submodule\":\"payment\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Payment::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":184,\"perm_code\":\"payment_delete\",\"perm_name\":\"Delete Payment (delete)\",\"module\":\"finance\",\"submodule\":\"payment\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Payment::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":185,\"perm_code\":\"payment_receipt\",\"perm_name\":\"Manage Payment (receipt)\",\"module\":\"finance\",\"submodule\":\"payment\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Payment::receipt()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":186,\"perm_code\":\"payment_fullHistory\",\"perm_name\":\"Manage Payment (fullHistory)\",\"module\":\"finance\",\"submodule\":\"payment\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Payment::fullHistory()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":187,\"perm_code\":\"payment_verify\",\"perm_name\":\"Manage Payment (verify)\",\"module\":\"finance\",\"submodule\":\"payment\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Payment::verify()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":188,\"perm_code\":\"procesverbal_index\",\"perm_name\":\"View Procesverbal (index)\",\"module\":\"students\",\"submodule\":\"procesverbal\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode ProcesVerbal::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":189,\"perm_code\":\"procesverbal_evaluation\",\"perm_name\":\"Manage Procesverbal (evaluation)\",\"module\":\"students\",\"submodule\":\"procesverbal\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode ProcesVerbal::evaluation()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":190,\"perm_code\":\"procesverbal_sequence\",\"perm_name\":\"Manage Procesverbal (sequence)\",\"module\":\"students\",\"submodule\":\"procesverbal\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode ProcesVerbal::sequence()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":191,\"perm_code\":\"procesverbal_trimestre\",\"perm_name\":\"Manage Procesverbal (trimestre)\",\"module\":\"students\",\"submodule\":\"procesverbal\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode ProcesVerbal::trimestre()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":192,\"perm_code\":\"procesverbal_annuel\",\"perm_name\":\"Manage Procesverbal (annuel)\",\"module\":\"students\",\"submodule\":\"procesverbal\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode ProcesVerbal::annuel()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":193,\"perm_code\":\"profile_index\",\"perm_name\":\"View Profile (index)\",\"module\":\"general\",\"submodule\":\"profile\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Profile::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":194,\"perm_code\":\"profile_update\",\"perm_name\":\"Edit Profile (update)\",\"module\":\"general\",\"submodule\":\"profile\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Profile::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":195,\"perm_code\":\"publicverification_verifyPublic\",\"perm_name\":\"Manage Publicverification (verifyPublic)\",\"module\":\"general\",\"submodule\":\"publicverification\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode PublicVerification::verifyPublic()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":196,\"perm_code\":\"rbac_index\",\"perm_name\":\"View Rbac (index)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Rbac::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":197,\"perm_code\":\"rbac_getPermissions\",\"perm_name\":\"View Rbac (getPermissions)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Rbac::getPermissions()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":198,\"perm_code\":\"rbac_getRoles\",\"perm_name\":\"View Rbac (getRoles)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Rbac::getRoles()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":199,\"perm_code\":\"rbac_getRolePermissions\",\"perm_name\":\"View Rbac (getRolePermissions)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Rbac::getRolePermissions()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":200,\"perm_code\":\"rbac_saveRolePermissions\",\"perm_name\":\"Create Rbac (saveRolePermissions)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Rbac::saveRolePermissions()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":201,\"perm_code\":\"rbac_copyRolePermissions\",\"perm_name\":\"Manage Rbac (copyRolePermissions)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Rbac::copyRolePermissions()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":202,\"perm_code\":\"rbac_compareRoles\",\"perm_name\":\"Manage Rbac (compareRoles)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Rbac::compareRoles()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":203,\"perm_code\":\"rbac_resetRolePermissions\",\"perm_name\":\"Manage Rbac (resetRolePermissions)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Rbac::resetRolePermissions()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":204,\"perm_code\":\"rbac_searchUsers\",\"perm_name\":\"Manage Rbac (searchUsers)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Rbac::searchUsers()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":205,\"perm_code\":\"rbac_getUserPermissions\",\"perm_name\":\"View Rbac (getUserPermissions)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Rbac::getUserPermissions()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":206,\"perm_code\":\"rbac_saveUserPermissions\",\"perm_name\":\"Create Rbac (saveUserPermissions)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Rbac::saveUserPermissions()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":207,\"perm_code\":\"rbac_runScan\",\"perm_name\":\"Manage Rbac (runScan)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Rbac::runScan()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":208,\"perm_code\":\"rbac_getAuditLogs\",\"perm_name\":\"View Rbac (getAuditLogs)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Rbac::getAuditLogs()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":209,\"perm_code\":\"rbac_createBackup\",\"perm_name\":\"Create Rbac (createBackup)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Rbac::createBackup()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":210,\"perm_code\":\"rbac_getBackups\",\"perm_name\":\"View Rbac (getBackups)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Rbac::getBackups()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":211,\"perm_code\":\"rbac_restoreBackup\",\"perm_name\":\"Create Rbac (restoreBackup)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Rbac::restoreBackup()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":212,\"perm_code\":\"scholarship_index\",\"perm_name\":\"View Scholarship (index)\",\"module\":\"finance\",\"submodule\":\"scholarship\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Scholarship::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":213,\"perm_code\":\"scholarship_store\",\"perm_name\":\"Create Scholarship (store)\",\"module\":\"finance\",\"submodule\":\"scholarship\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Scholarship::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":214,\"perm_code\":\"scholarship_toggleStatus\",\"perm_name\":\"Edit Scholarship (toggleStatus)\",\"module\":\"finance\",\"submodule\":\"scholarship\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Scholarship::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":215,\"perm_code\":\"scholarship_delete\",\"perm_name\":\"Delete Scholarship (delete)\",\"module\":\"finance\",\"submodule\":\"scholarship\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Scholarship::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":216,\"perm_code\":\"schoolfee_grille\",\"perm_name\":\"Manage Schoolfee (grille)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::grille()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":217,\"perm_code\":\"schoolfee_tranches\",\"perm_name\":\"Manage Schoolfee (tranches)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::tranches()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":218,\"perm_code\":\"schoolfee_versements\",\"perm_name\":\"Manage Schoolfee (versements)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::versements()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":219,\"perm_code\":\"schoolfee_storeVersement\",\"perm_name\":\"Create Schoolfee (storeVersement)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::storeVersement()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":220,\"perm_code\":\"schoolfee_deleteVersement\",\"perm_name\":\"Delete Schoolfee (deleteVersement)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::deleteVersement()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":221,\"perm_code\":\"schoolfee_insolvables\",\"perm_name\":\"Manage Schoolfee (insolvables)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::insolvables()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":222,\"perm_code\":\"schoolfee_receipt\",\"perm_name\":\"Manage Schoolfee (receipt)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::receipt()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":223,\"perm_code\":\"schoolfee_printInsolvables\",\"perm_name\":\"Export Schoolfee (printInsolvables)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::printInsolvables()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":224,\"perm_code\":\"schoolfee_printGrille\",\"perm_name\":\"Export Schoolfee (printGrille)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::printGrille()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":225,\"perm_code\":\"schoolfee_templateGrille\",\"perm_name\":\"Manage Schoolfee (templateGrille)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::templateGrille()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":226,\"perm_code\":\"schoolfee_importGrille\",\"perm_name\":\"Import Schoolfee (importGrille)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::importGrille()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":227,\"perm_code\":\"section_index\",\"perm_name\":\"View Section (index)\",\"module\":\"pedagogy\",\"submodule\":\"section\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Section::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":228,\"perm_code\":\"section_create\",\"perm_name\":\"Create Section (create)\",\"module\":\"pedagogy\",\"submodule\":\"section\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Section::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":229,\"perm_code\":\"section_store\",\"perm_name\":\"Create Section (store)\",\"module\":\"pedagogy\",\"submodule\":\"section\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Section::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":230,\"perm_code\":\"section_edit\",\"perm_name\":\"Edit Section (edit)\",\"module\":\"pedagogy\",\"submodule\":\"section\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Section::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":231,\"perm_code\":\"section_update\",\"perm_name\":\"Edit Section (update)\",\"module\":\"pedagogy\",\"submodule\":\"section\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Section::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":232,\"perm_code\":\"section_toggleStatus\",\"perm_name\":\"Edit Section (toggleStatus)\",\"module\":\"pedagogy\",\"submodule\":\"section\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Section::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:15\",\"updated_at\":\"2026-08-07 06:49:15\"},{\"id\":233,\"perm_code\":\"section_delete\",\"perm_name\":\"Delete Section (delete)\",\"module\":\"pedagogy\",\"submodule\":\"section\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Section::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:15\",\"updated_at\":\"2026-08-07 06:49:15\"},{\"id\":234,\"perm_code\":\"sequence_index\",\"perm_name\":\"View Sequence (index)\",\"module\":\"pedagogy\",\"submodule\":\"sequence\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Sequence::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:15\",\"updated_at\":\"2026-08-07 06:49:15\"},{\"id\":235,\"perm_code\":\"sequence_create\",\"perm_name\":\"Create Sequence (create)\",\"module\":\"pedagogy\",\"submodule\":\"sequence\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Sequence::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:15\",\"updated_at\":\"2026-08-07 06:49:15\"},{\"id\":236,\"perm_code\":\"sequence_store\",\"perm_name\":\"Create Sequence (store)\",\"module\":\"pedagogy\",\"submodule\":\"sequence\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Sequence::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:15\",\"updated_at\":\"2026-08-07 06:49:15\"},{\"id\":237,\"perm_code\":\"sequence_edit\",\"perm_name\":\"Edit Sequence (edit)\",\"module\":\"pedagogy\",\"submodule\":\"sequence\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Sequence::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":238,\"perm_code\":\"sequence_update\",\"perm_name\":\"Edit Sequence (update)\",\"module\":\"pedagogy\",\"submodule\":\"sequence\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Sequence::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":239,\"perm_code\":\"sequence_delete\",\"perm_name\":\"Delete Sequence (delete)\",\"module\":\"pedagogy\",\"submodule\":\"sequence\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Sequence::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":240,\"perm_code\":\"sequence_toggle\",\"perm_name\":\"Edit Sequence (toggle)\",\"module\":\"pedagogy\",\"submodule\":\"sequence\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Sequence::toggle()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":241,\"perm_code\":\"setting_index\",\"perm_name\":\"View Setting (index)\",\"module\":\"system\",\"submodule\":\"setting\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Setting::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":242,\"perm_code\":\"setting_store\",\"perm_name\":\"Create Setting (store)\",\"module\":\"system\",\"submodule\":\"setting\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Setting::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":243,\"perm_code\":\"setting_reset\",\"perm_name\":\"Manage Setting (reset)\",\"module\":\"system\",\"submodule\":\"setting\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Setting::reset()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":244,\"perm_code\":\"setting_runBackup\",\"perm_name\":\"Manage Setting (runBackup)\",\"module\":\"system\",\"submodule\":\"setting\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Setting::runBackup()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":245,\"perm_code\":\"student_index\",\"perm_name\":\"View Student (index)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Student::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":246,\"perm_code\":\"student_nonInscrits\",\"perm_name\":\"Manage Student (nonInscrits)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Student::nonInscrits()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":247,\"perm_code\":\"student_export\",\"perm_name\":\"Export Student (export)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Student::export()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":248,\"perm_code\":\"student_exportExcel\",\"perm_name\":\"Export Student (exportExcel)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Student::exportExcel()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":249,\"perm_code\":\"student_create\",\"perm_name\":\"Create Student (create)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Student::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":250,\"perm_code\":\"student_import\",\"perm_name\":\"Import Student (import)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Student::import()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":251,\"perm_code\":\"student_downloadTemplate\",\"perm_name\":\"Export Student (downloadTemplate)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Student::downloadTemplate()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":252,\"perm_code\":\"student_upload\",\"perm_name\":\"Import Student (upload)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Student::upload()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":253,\"perm_code\":\"student_store\",\"perm_name\":\"Create Student (store)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Student::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":254,\"perm_code\":\"student_edit\",\"perm_name\":\"Edit Student (edit)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Student::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":255,\"perm_code\":\"student_update\",\"perm_name\":\"Edit Student (update)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Student::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":256,\"perm_code\":\"student_withdraw\",\"perm_name\":\"Manage Student (withdraw)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Student::withdraw()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":257,\"perm_code\":\"student_restore\",\"perm_name\":\"Create Student (restore)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Student::restore()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":258,\"perm_code\":\"student_delete\",\"perm_name\":\"Delete Student (delete)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Student::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":259,\"perm_code\":\"subject_index\",\"perm_name\":\"View Subject (index)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Subject::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":260,\"perm_code\":\"subject_export\",\"perm_name\":\"Export Subject (export)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Subject::export()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":261,\"perm_code\":\"subject_create\",\"perm_name\":\"Create Subject (create)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Subject::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":262,\"perm_code\":\"subject_store\",\"perm_name\":\"Create Subject (store)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Subject::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":263,\"perm_code\":\"subject_edit\",\"perm_name\":\"Edit Subject (edit)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Subject::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":264,\"perm_code\":\"subject_update\",\"perm_name\":\"Edit Subject (update)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Subject::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":265,\"perm_code\":\"subject_toggleStatus\",\"perm_name\":\"Edit Subject (toggleStatus)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Subject::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":266,\"perm_code\":\"subject_delete\",\"perm_name\":\"Delete Subject (delete)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Subject::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":267,\"perm_code\":\"subject_import\",\"perm_name\":\"Import Subject (import)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Subject::import()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":268,\"perm_code\":\"subject_downloadTemplate\",\"perm_name\":\"Export Subject (downloadTemplate)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Subject::downloadTemplate()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":269,\"perm_code\":\"subject_upload\",\"perm_name\":\"Import Subject (upload)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Subject::upload()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":270,\"perm_code\":\"subjectgroup_index\",\"perm_name\":\"View Subjectgroup (index)\",\"module\":\"pedagogy\",\"submodule\":\"subjectgroup\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode SubjectGroup::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":271,\"perm_code\":\"subjectgroup_store\",\"perm_name\":\"Create Subjectgroup (store)\",\"module\":\"pedagogy\",\"submodule\":\"subjectgroup\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode SubjectGroup::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":272,\"perm_code\":\"subjectgroup_update\",\"perm_name\":\"Edit Subjectgroup (update)\",\"module\":\"pedagogy\",\"submodule\":\"subjectgroup\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode SubjectGroup::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":273,\"perm_code\":\"subjectgroup_toggle\",\"perm_name\":\"Edit Subjectgroup (toggle)\",\"module\":\"pedagogy\",\"submodule\":\"subjectgroup\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode SubjectGroup::toggle()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":274,\"perm_code\":\"subjectgroup_delete\",\"perm_name\":\"Delete Subjectgroup (delete)\",\"module\":\"pedagogy\",\"submodule\":\"subjectgroup\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode SubjectGroup::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":275,\"perm_code\":\"teacher_index\",\"perm_name\":\"View Teacher (index)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Teacher::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":276,\"perm_code\":\"teacher_toggleTeacherNames\",\"perm_name\":\"Edit Teacher (toggleTeacherNames)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Teacher::toggleTeacherNames()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":277,\"perm_code\":\"teacher_export\",\"perm_name\":\"Export Teacher (export)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Teacher::export()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":278,\"perm_code\":\"teacher_create\",\"perm_name\":\"Create Teacher (create)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Teacher::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":279,\"perm_code\":\"teacher_store\",\"perm_name\":\"Create Teacher (store)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Teacher::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":280,\"perm_code\":\"teacher_delete\",\"perm_name\":\"Delete Teacher (delete)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Teacher::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":281,\"perm_code\":\"teacher_assign\",\"perm_name\":\"Manage Teacher (assign)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Teacher::assign()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":282,\"perm_code\":\"teacher_directAssign\",\"perm_name\":\"Manage Teacher (directAssign)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Teacher::directAssign()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":283,\"perm_code\":\"teacher_storeAssignment\",\"perm_name\":\"Create Teacher (storeAssignment)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Teacher::storeAssignment()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":284,\"perm_code\":\"teacher_import\",\"perm_name\":\"Import Teacher (import)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Teacher::import()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":285,\"perm_code\":\"teacher_downloadTemplate\",\"perm_name\":\"Export Teacher (downloadTemplate)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Teacher::downloadTemplate()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":286,\"perm_code\":\"teacher_upload\",\"perm_name\":\"Import Teacher (upload)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Teacher::upload()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":287,\"perm_code\":\"teacher_edit\",\"perm_name\":\"Edit Teacher (edit)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Teacher::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":288,\"perm_code\":\"teacher_update\",\"perm_name\":\"Edit Teacher (update)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Teacher::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":289,\"perm_code\":\"teachingtype_index\",\"perm_name\":\"View Teachingtype (index)\",\"module\":\"pedagogy\",\"submodule\":\"teachingtype\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode TeachingType::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":290,\"perm_code\":\"teachingtype_create\",\"perm_name\":\"Create Teachingtype (create)\",\"module\":\"pedagogy\",\"submodule\":\"teachingtype\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode TeachingType::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":291,\"perm_code\":\"teachingtype_store\",\"perm_name\":\"Create Teachingtype (store)\",\"module\":\"pedagogy\",\"submodule\":\"teachingtype\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode TeachingType::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":292,\"perm_code\":\"teachingtype_edit\",\"perm_name\":\"Edit Teachingtype (edit)\",\"module\":\"pedagogy\",\"submodule\":\"teachingtype\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode TeachingType::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":293,\"perm_code\":\"teachingtype_update\",\"perm_name\":\"Edit Teachingtype (update)\",\"module\":\"pedagogy\",\"submodule\":\"teachingtype\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode TeachingType::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":294,\"perm_code\":\"teachingtype_delete\",\"perm_name\":\"Delete Teachingtype (delete)\",\"module\":\"pedagogy\",\"submodule\":\"teachingtype\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode TeachingType::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":295,\"perm_code\":\"timetable_index\",\"perm_name\":\"View Timetable (index)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Timetable::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":296,\"perm_code\":\"timetable_slots\",\"perm_name\":\"Manage Timetable (slots)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Timetable::slots()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":297,\"perm_code\":\"timetable_storeSlot\",\"perm_name\":\"Create Timetable (storeSlot)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::storeSlot()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":298,\"perm_code\":\"timetable_updateSlot\",\"perm_name\":\"Edit Timetable (updateSlot)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Timetable::updateSlot()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":299,\"perm_code\":\"timetable_deleteSlot\",\"perm_name\":\"Delete Timetable (deleteSlot)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Timetable::deleteSlot()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":300,\"perm_code\":\"timetable_rooms\",\"perm_name\":\"Manage Timetable (rooms)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Timetable::rooms()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":301,\"perm_code\":\"timetable_storeRoom\",\"perm_name\":\"Create Timetable (storeRoom)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::storeRoom()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":302,\"perm_code\":\"timetable_updateRoom\",\"perm_name\":\"Edit Timetable (updateRoom)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Timetable::updateRoom()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":303,\"perm_code\":\"timetable_deleteRoom\",\"perm_name\":\"Delete Timetable (deleteRoom)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Timetable::deleteRoom()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":304,\"perm_code\":\"timetable_weeks\",\"perm_name\":\"Manage Timetable (weeks)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Timetable::weeks()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":305,\"perm_code\":\"timetable_storeWeek\",\"perm_name\":\"Create Timetable (storeWeek)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::storeWeek()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":306,\"perm_code\":\"timetable_updateWeek\",\"perm_name\":\"Edit Timetable (updateWeek)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Timetable::updateWeek()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":307,\"perm_code\":\"timetable_deleteWeek\",\"perm_name\":\"Delete Timetable (deleteWeek)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Timetable::deleteWeek()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":308,\"perm_code\":\"timetable_wizard\",\"perm_name\":\"Create Timetable (wizard)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::wizard()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":309,\"perm_code\":\"timetable_wizardStepData\",\"perm_name\":\"Create Timetable (wizardStepData)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::wizardStepData()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":310,\"perm_code\":\"timetable_createTimetable\",\"perm_name\":\"Create Timetable (createTimetable)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::createTimetable()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":311,\"perm_code\":\"timetable_grid\",\"perm_name\":\"Manage Timetable (grid)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Timetable::grid()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":312,\"perm_code\":\"timetable_saveGridEntry\",\"perm_name\":\"Create Timetable (saveGridEntry)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::saveGridEntry()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":313,\"perm_code\":\"timetable_apiGetClassSubjects\",\"perm_name\":\"View Timetable (apiGetClassSubjects)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Timetable::apiGetClassSubjects()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":314,\"perm_code\":\"timetable_apiGetSubjectTeachers\",\"perm_name\":\"View Timetable (apiGetSubjectTeachers)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Timetable::apiGetSubjectTeachers()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":315,\"perm_code\":\"timetable_apiQuickCreateTeacher\",\"perm_name\":\"Create Timetable (apiQuickCreateTeacher)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::apiQuickCreateTeacher()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":316,\"perm_code\":\"timetable_deleteGridEntry\",\"perm_name\":\"Delete Timetable (deleteGridEntry)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Timetable::deleteGridEntry()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":317,\"perm_code\":\"timetable_apiValidateConflict\",\"perm_name\":\"Manage Timetable (apiValidateConflict)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Timetable::apiValidateConflict()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":318,\"perm_code\":\"timetable_unlock\",\"perm_name\":\"Manage Timetable (unlock)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Timetable::unlock()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":319,\"perm_code\":\"timetable_deleteTimetable\",\"perm_name\":\"Delete Timetable (deleteTimetable)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Timetable::deleteTimetable()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":320,\"perm_code\":\"timetable_exportPdf\",\"perm_name\":\"Export Timetable (exportPdf)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Timetable::exportPdf()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":321,\"perm_code\":\"transcript_index\",\"perm_name\":\"View Transcript (index)\",\"module\":\"students\",\"submodule\":\"transcript\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Transcript::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":322,\"perm_code\":\"transcript_generate\",\"perm_name\":\"Manage Transcript (generate)\",\"module\":\"students\",\"submodule\":\"transcript\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Transcript::generate()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":323,\"perm_code\":\"user_index\",\"perm_name\":\"View User (index)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode User::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":324,\"perm_code\":\"user_export\",\"perm_name\":\"Export User (export)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode User::export()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":325,\"perm_code\":\"user_create\",\"perm_name\":\"Create User (create)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode User::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":326,\"perm_code\":\"user_store\",\"perm_name\":\"Create User (store)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode User::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":327,\"perm_code\":\"user_edit\",\"perm_name\":\"Edit User (edit)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode User::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":328,\"perm_code\":\"user_update\",\"perm_name\":\"Edit User (update)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode User::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":329,\"perm_code\":\"user_delete\",\"perm_name\":\"Delete User (delete)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode User::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":330,\"perm_code\":\"user_createCaissier\",\"perm_name\":\"Create User (createCaissier)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode User::createCaissier()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":331,\"perm_code\":\"user_storeCaissier\",\"perm_name\":\"Create User (storeCaissier)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode User::storeCaissier()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:23\",\"updated_at\":\"2026-08-07 06:49:23\"},{\"id\":332,\"perm_code\":\"user_caissiers\",\"perm_name\":\"Manage User (caissiers)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode User::caissiers()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:23\",\"updated_at\":\"2026-08-07 06:49:23\"},{\"id\":333,\"perm_code\":\"user_toggleStatus\",\"perm_name\":\"Edit User (toggleStatus)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode User::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:23\",\"updated_at\":\"2026-08-07 06:49:23\"},{\"id\":334,\"perm_code\":\"verificationadmin_index\",\"perm_name\":\"View Verificationadmin (index)\",\"module\":\"general\",\"submodule\":\"verificationadmin\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode VerificationAdmin::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:23\",\"updated_at\":\"2026-08-07 06:49:23\"}],\"role_permissions\":[{\"role_id\":1,\"permission_id\":1},{\"role_id\":1,\"permission_id\":2},{\"role_id\":1,\"permission_id\":3},{\"role_id\":1,\"permission_id\":4},{\"role_id\":1,\"permission_id\":5},{\"role_id\":1,\"permission_id\":6},{\"role_id\":1,\"permission_id\":7},{\"role_id\":1,\"permission_id\":8},{\"role_id\":1,\"permission_id\":9},{\"role_id\":1,\"permission_id\":10},{\"role_id\":1,\"permission_id\":11},{\"role_id\":1,\"permission_id\":12},{\"role_id\":1,\"permission_id\":13},{\"role_id\":1,\"permission_id\":14},{\"role_id\":1,\"permission_id\":15},{\"role_id\":1,\"permission_id\":16},{\"role_id\":1,\"permission_id\":19},{\"role_id\":1,\"permission_id\":21},{\"role_id\":1,\"permission_id\":22},{\"role_id\":1,\"permission_id\":23},{\"role_id\":1,\"permission_id\":24},{\"role_id\":1,\"permission_id\":25},{\"role_id\":1,\"permission_id\":26},{\"role_id\":1,\"permission_id\":29},{\"role_id\":1,\"permission_id\":30},{\"role_id\":1,\"permission_id\":32},{\"role_id\":1,\"permission_id\":33},{\"role_id\":1,\"permission_id\":34},{\"role_id\":1,\"permission_id\":36},{\"role_id\":1,\"permission_id\":37},{\"role_id\":1,\"permission_id\":42},{\"role_id\":1,\"permission_id\":51},{\"role_id\":1,\"permission_id\":58},{\"role_id\":1,\"permission_id\":74},{\"role_id\":1,\"permission_id\":75},{\"role_id\":1,\"permission_id\":76},{\"role_id\":1,\"permission_id\":78},{\"role_id\":1,\"permission_id\":80},{\"role_id\":1,\"permission_id\":81},{\"role_id\":1,\"permission_id\":82},{\"role_id\":1,\"permission_id\":85},{\"role_id\":1,\"permission_id\":86},{\"role_id\":1,\"permission_id\":87},{\"role_id\":1,\"permission_id\":108},{\"role_id\":1,\"permission_id\":109},{\"role_id\":1,\"permission_id\":113},{\"role_id\":1,\"permission_id\":114},{\"role_id\":1,\"permission_id\":116},{\"role_id\":1,\"permission_id\":118},{\"role_id\":1,\"permission_id\":121},{\"role_id\":1,\"permission_id\":122},{\"role_id\":1,\"permission_id\":123},{\"role_id\":1,\"permission_id\":124},{\"role_id\":1,\"permission_id\":140},{\"role_id\":1,\"permission_id\":141},{\"role_id\":1,\"permission_id\":167},{\"role_id\":1,\"permission_id\":168},{\"role_id\":1,\"permission_id\":196},{\"role_id\":1,\"permission_id\":197},{\"role_id\":1,\"permission_id\":198},{\"role_id\":1,\"permission_id\":199},{\"role_id\":1,\"permission_id\":200},{\"role_id\":1,\"permission_id\":201},{\"role_id\":1,\"permission_id\":202},{\"role_id\":1,\"permission_id\":203},{\"role_id\":1,\"permission_id\":204},{\"role_id\":1,\"permission_id\":205},{\"role_id\":1,\"permission_id\":206},{\"role_id\":1,\"permission_id\":207},{\"role_id\":1,\"permission_id\":208},{\"role_id\":1,\"permission_id\":209},{\"role_id\":1,\"permission_id\":210},{\"role_id\":1,\"permission_id\":211},{\"role_id\":1,\"permission_id\":241},{\"role_id\":1,\"permission_id\":242},{\"role_id\":1,\"permission_id\":243},{\"role_id\":1,\"permission_id\":244},{\"role_id\":1,\"permission_id\":323},{\"role_id\":1,\"permission_id\":324},{\"role_id\":1,\"permission_id\":325},{\"role_id\":1,\"permission_id\":326},{\"role_id\":1,\"permission_id\":327},{\"role_id\":1,\"permission_id\":328},{\"role_id\":1,\"permission_id\":329},{\"role_id\":1,\"permission_id\":330},{\"role_id\":1,\"permission_id\":331},{\"role_id\":1,\"permission_id\":332},{\"role_id\":1,\"permission_id\":333},{\"role_id\":2,\"permission_id\":1},{\"role_id\":2,\"permission_id\":2},{\"role_id\":2,\"permission_id\":3},{\"role_id\":2,\"permission_id\":5},{\"role_id\":2,\"permission_id\":6},{\"role_id\":2,\"permission_id\":7},{\"role_id\":2,\"permission_id\":8},{\"role_id\":2,\"permission_id\":9},{\"role_id\":2,\"permission_id\":10},{\"role_id\":2,\"permission_id\":11},{\"role_id\":2,\"permission_id\":12},{\"role_id\":2,\"permission_id\":13},{\"role_id\":2,\"permission_id\":15},{\"role_id\":2,\"permission_id\":16},{\"role_id\":2,\"permission_id\":17},{\"role_id\":2,\"permission_id\":18},{\"role_id\":2,\"permission_id\":19},{\"role_id\":2,\"permission_id\":20},{\"role_id\":2,\"permission_id\":21},{\"role_id\":2,\"permission_id\":22},{\"role_id\":2,\"permission_id\":23},{\"role_id\":2,\"permission_id\":24},{\"role_id\":2,\"permission_id\":25},{\"role_id\":2,\"permission_id\":26},{\"role_id\":2,\"permission_id\":27},{\"role_id\":2,\"permission_id\":28},{\"role_id\":2,\"permission_id\":29},{\"role_id\":2,\"permission_id\":30},{\"role_id\":2,\"permission_id\":31},{\"role_id\":2,\"permission_id\":32},{\"role_id\":2,\"permission_id\":33},{\"role_id\":2,\"permission_id\":34},{\"role_id\":2,\"permission_id\":36},{\"role_id\":2,\"permission_id\":42},{\"role_id\":2,\"permission_id\":51},{\"role_id\":2,\"permission_id\":58},{\"role_id\":3,\"permission_id\":1},{\"role_id\":3,\"permission_id\":2},{\"role_id\":3,\"permission_id\":3},{\"role_id\":3,\"permission_id\":5},{\"role_id\":3,\"permission_id\":6},{\"role_id\":3,\"permission_id\":7},{\"role_id\":3,\"permission_id\":8},{\"role_id\":3,\"permission_id\":9},{\"role_id\":3,\"permission_id\":10},{\"role_id\":3,\"permission_id\":11},{\"role_id\":3,\"permission_id\":12},{\"role_id\":3,\"permission_id\":13},{\"role_id\":3,\"permission_id\":14},{\"role_id\":3,\"permission_id\":15},{\"role_id\":3,\"permission_id\":16},{\"role_id\":3,\"permission_id\":17},{\"role_id\":3,\"permission_id\":18},{\"role_id\":3,\"permission_id\":19},{\"role_id\":3,\"permission_id\":20},{\"role_id\":3,\"permission_id\":21},{\"role_id\":3,\"permission_id\":22},{\"role_id\":3,\"permission_id\":32},{\"role_id\":3,\"permission_id\":34},{\"role_id\":3,\"permission_id\":36},{\"role_id\":3,\"permission_id\":42},{\"role_id\":3,\"permission_id\":51},{\"role_id\":3,\"permission_id\":58},{\"role_id\":4,\"permission_id\":5},{\"role_id\":4,\"permission_id\":16},{\"role_id\":4,\"permission_id\":23},{\"role_id\":4,\"permission_id\":24},{\"role_id\":4,\"permission_id\":26},{\"role_id\":4,\"permission_id\":27},{\"role_id\":4,\"permission_id\":28},{\"role_id\":4,\"permission_id\":29},{\"role_id\":4,\"permission_id\":30},{\"role_id\":4,\"permission_id\":31},{\"role_id\":5,\"permission_id\":5},{\"role_id\":5,\"permission_id\":16},{\"role_id\":5,\"permission_id\":21},{\"role_id\":5,\"permission_id\":22},{\"role_id\":5,\"permission_id\":23},{\"role_id\":5,\"permission_id\":24},{\"role_id\":5,\"permission_id\":25},{\"role_id\":5,\"permission_id\":26},{\"role_id\":5,\"permission_id\":27},{\"role_id\":5,\"permission_id\":28},{\"role_id\":5,\"permission_id\":29},{\"role_id\":5,\"permission_id\":30},{\"role_id\":5,\"permission_id\":31},{\"role_id\":6,\"permission_id\":5},{\"role_id\":6,\"permission_id\":16},{\"role_id\":6,\"permission_id\":18},{\"role_id\":6,\"permission_id\":20},{\"role_id\":6,\"permission_id\":36}],\"user_permissions\":[]}', 42, 'Administrateur', '2026-08-07 05:30:42');
INSERT INTO `permission_backups` (`id`, `backup_name`, `description`, `config_data`, `created_by`, `created_by_name`, `created_at`) VALUES
(2, 'Sauvegarde_2026-08-07', 'Sauvegarde manuelle RBAC', '{\"timestamp\":\"2026-08-07 15:44:53\",\"roles\":[{\"id\":1,\"role_code\":\"superadmin\",\"role_name\":\"Super Administrateur\",\"description\":\"Accès complet absolu au système.\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:50\"},{\"id\":2,\"role_code\":\"admin\",\"role_name\":\"Administrateur\",\"description\":\"Administration classique et globale de l\'établissement.\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:50\"},{\"id\":3,\"role_code\":\"it_manager\",\"role_name\":\"IT Manager\",\"description\":\"Responsable de la configuration technique et pédagogique.\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:50\"},{\"id\":4,\"role_code\":\"caissier\",\"role_name\":\"Caissier\",\"description\":\"Gestionnaire des encaissements et versements quotidiens.\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":5,\"role_code\":\"comptable\",\"role_name\":\"Comptable\",\"description\":\"Responsable financier, des tarifs, bourses et bilans.\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":6,\"role_code\":\"enseignant\",\"role_name\":\"Enseignant\",\"description\":\"Enseignant accédant aux notes, absences et livrets.\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"}],\"permissions\":[{\"id\":1,\"perm_code\":\"manage_users\",\"perm_name\":\"Gérer les utilisateurs\",\"module\":\"system\",\"submodule\":\"users\",\"action\":\"manage\",\"description\":\"Créer, modifier et gérer les comptes d\'accès système.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":2,\"perm_code\":\"manage_settings\",\"perm_name\":\"Gérer les paramètres généraux\",\"module\":\"system\",\"submodule\":\"settings\",\"action\":\"manage\",\"description\":\"Configurer l\'établissement, le logo et les paramètres globaux.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":3,\"perm_code\":\"view_system_logs\",\"perm_name\":\"Consulter les journaux système\",\"module\":\"system\",\"submodule\":\"audit\",\"action\":\"view\",\"description\":\"Visualiser les logs d\'activité et les événements de sécurité.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":4,\"perm_code\":\"manage_rbac\",\"perm_name\":\"Gérer la sécurité RBAC\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"manage\",\"description\":\"Configurer les rôles, les autorisations et les exceptions utilisateurs.\",\"criticality\":\"critical\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":5,\"perm_code\":\"view_classes\",\"perm_name\":\"Consulter les classes\",\"module\":\"pedagogy\",\"submodule\":\"classes\",\"action\":\"view\",\"description\":\"Afficher la liste des classes et effectifs.\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":6,\"perm_code\":\"manage_classes_structure\",\"perm_name\":\"Gérer la structure des classes\",\"module\":\"pedagogy\",\"submodule\":\"classes\",\"action\":\"manage\",\"description\":\"Créer, modifier et supprimer des classes.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":7,\"perm_code\":\"manage_teaching_types\",\"perm_name\":\"Gérer les types d\'enseignement\",\"module\":\"pedagogy\",\"submodule\":\"structure\",\"action\":\"manage\",\"description\":\"Configurer les types d\'enseignement (Général, Technique, LMD).\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":8,\"perm_code\":\"manage_cycles\",\"perm_name\":\"Gérer les cycles\",\"module\":\"pedagogy\",\"submodule\":\"structure\",\"action\":\"manage\",\"description\":\"Gérer les cycles académiques.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":9,\"perm_code\":\"manage_sections\",\"perm_name\":\"Gérer les sections\",\"module\":\"pedagogy\",\"submodule\":\"structure\",\"action\":\"manage\",\"description\":\"Gérer les sections francophones \\/ anglophones.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":10,\"perm_code\":\"manage_departments\",\"perm_name\":\"Gérer les départements\",\"module\":\"pedagogy\",\"submodule\":\"structure\",\"action\":\"manage\",\"description\":\"Gérer les départements d\'enseignement.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":11,\"perm_code\":\"manage_subjects\",\"perm_name\":\"Gérer les matières\",\"module\":\"pedagogy\",\"submodule\":\"subjects\",\"action\":\"manage\",\"description\":\"Gérer le catalogue des matières et coefficients.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":12,\"perm_code\":\"manage_teachers\",\"perm_name\":\"Gérer les enseignants\",\"module\":\"pedagogy\",\"submodule\":\"teachers\",\"action\":\"manage\",\"description\":\"Gérer le registre des enseignants et leurs affectations.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":13,\"perm_code\":\"manage_timetables\",\"perm_name\":\"Gérer les emplois du temps\",\"module\":\"pedagogy\",\"submodule\":\"timetables\",\"action\":\"manage\",\"description\":\"Planifier et éditer les emplois du temps des classes.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":14,\"perm_code\":\"manage_academic_years\",\"perm_name\":\"Gérer les années scolaires\",\"module\":\"pedagogy\",\"submodule\":\"academic_years\",\"action\":\"manage\",\"description\":\"Activer, clôturer et basculer les années académiques.\",\"criticality\":\"critical\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":15,\"perm_code\":\"manage_sequences\",\"perm_name\":\"Gérer les séquences\",\"module\":\"pedagogy\",\"submodule\":\"sequences\",\"action\":\"manage\",\"description\":\"Définir les séquences et semestres d\'évaluation.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":16,\"perm_code\":\"view_students\",\"perm_name\":\"Consulter les élèves\",\"module\":\"students\",\"submodule\":\"registry\",\"action\":\"view\",\"description\":\"Visualiser les registres des élèves.\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":17,\"perm_code\":\"manage_students\",\"perm_name\":\"Gérer les registres élèves\",\"module\":\"students\",\"submodule\":\"registry\",\"action\":\"manage\",\"description\":\"Inscrire, modifier les profils et gérer la scolarité des élèves.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":18,\"perm_code\":\"manage_marks\",\"perm_name\":\"Saisir et modifier les notes\",\"module\":\"evaluations\",\"submodule\":\"grades\",\"action\":\"manage\",\"description\":\"Saisir, verrouiller et valider les notes d\'évaluation.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":19,\"perm_code\":\"manage_bulletins\",\"perm_name\":\"Gérer les bulletins de notes\",\"module\":\"evaluations\",\"submodule\":\"bulletins\",\"action\":\"manage\",\"description\":\"Calculer les moyennes, éditer les bulletins et PV.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":20,\"perm_code\":\"manage_absences\",\"perm_name\":\"Gérer les absences et discipline\",\"module\":\"evaluations\",\"submodule\":\"discipline\",\"action\":\"manage\",\"description\":\"Saisir et récapituler les absences et blâmes.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":21,\"perm_code\":\"manage_staff\",\"perm_name\":\"Gérer le personnel\",\"module\":\"hr\",\"submodule\":\"staff\",\"action\":\"manage\",\"description\":\"Gérer les fiches et dossiers administratifs du personnel.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:53\"},{\"id\":22,\"perm_code\":\"manage_contracts\",\"perm_name\":\"Gérer les contrats de travail\",\"module\":\"hr\",\"submodule\":\"contracts\",\"action\":\"manage\",\"description\":\"Gérer la rédaction et le suivi des contrats.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:53\"},{\"id\":23,\"perm_code\":\"manage_fees\",\"perm_name\":\"Gérer les frais de scolarité\",\"module\":\"finance\",\"submodule\":\"fees\",\"action\":\"manage\",\"description\":\"Accès global à la configuration de la scolarité.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":24,\"perm_code\":\"view_class_finances\",\"perm_name\":\"Consulter les tarifs de scolarité\",\"module\":\"finance\",\"submodule\":\"fees\",\"action\":\"view\",\"description\":\"Voir la grille tarifaire des frais de scolarité.\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":25,\"perm_code\":\"edit_class_finances\",\"perm_name\":\"Configurer la grille tarifaire\",\"module\":\"finance\",\"submodule\":\"fees\",\"action\":\"edit\",\"description\":\"Définir les échéances et montants des tranches.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":26,\"perm_code\":\"manage_payments\",\"perm_name\":\"Enregistrer et gérer les paiements\",\"module\":\"finance\",\"submodule\":\"payments\",\"action\":\"manage\",\"description\":\"Saisir les versements, imprimer les reçus et annuler.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":27,\"perm_code\":\"manage_discounts\",\"perm_name\":\"Gérer les réductions de scolarité\",\"module\":\"finance\",\"submodule\":\"discounts\",\"action\":\"manage\",\"description\":\"Accorder des remises ou réductions aux élèves.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":28,\"perm_code\":\"manage_scholarships\",\"perm_name\":\"Gérer les bourses scolaires\",\"module\":\"finance\",\"submodule\":\"scholarships\",\"action\":\"manage\",\"description\":\"Attribuer et suivre les bourses d\'études.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":29,\"perm_code\":\"view_financial_history\",\"perm_name\":\"Consulter l\'historique financier\",\"module\":\"finance\",\"submodule\":\"reports\",\"action\":\"view\",\"description\":\"Consulter le journal des transactions financières.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:53\"},{\"id\":30,\"perm_code\":\"view_financial_reports\",\"perm_name\":\"Consulter les rapports et insolvables\",\"module\":\"finance\",\"submodule\":\"reports\",\"action\":\"view\",\"description\":\"Consulter les bilans d\'encaissement et listes d\'insolvabilité.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-06-27 06:42:09\",\"updated_at\":\"2026-08-07 06:48:53\"},{\"id\":31,\"perm_code\":\"manage_expenses\",\"perm_name\":\"Gérer les dépenses d\'établissement\",\"module\":\"finance\",\"submodule\":\"expenses\",\"action\":\"manage\",\"description\":\"Saisir et approuver les dépenses et frais d\'exploitation.\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-07-08 23:31:26\",\"updated_at\":\"2026-08-07 06:48:53\"},{\"id\":32,\"perm_code\":\"manage_levels\",\"perm_name\":\"Gérer les niveaux d\'étude\",\"module\":\"pedagogy\",\"submodule\":\"structure\",\"action\":\"manage\",\"description\":\"Configurer les niveaux d\'étude.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-07-29 04:07:18\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":33,\"perm_code\":\"view_transcripts\",\"perm_name\":\"Consulter les relevés de notes\",\"module\":\"general\",\"submodule\":\"general\",\"action\":\"view\",\"description\":\"Visualiser et prévisualiser les relevés de notes des élèves.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-07-31 09:10:36\",\"updated_at\":\"2026-07-31 09:10:36\"},{\"id\":34,\"perm_code\":\"manage_transcripts\",\"perm_name\":\"Gérer les relevés de notes\",\"module\":\"evaluations\",\"submodule\":\"transcripts\",\"action\":\"manage\",\"description\":\"Générer les relevés de notes officiels.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-07-31 09:10:37\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":36,\"perm_code\":\"view_timetables\",\"perm_name\":\"Consulter les emplois du temps\",\"module\":\"general\",\"submodule\":\"general\",\"action\":\"view\",\"description\":\"Visualiser, partager et imprimer les emplois du temps.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-08-04 05:53:05\",\"updated_at\":\"2026-08-04 05:53:05\"},{\"id\":37,\"perm_code\":\"unlock_timetables\",\"perm_name\":\"Déverrouiller les emplois du temps\",\"module\":\"general\",\"submodule\":\"general\",\"action\":\"view\",\"description\":\"Réservé au Superadmin pour déverrouiller un emploi du temps clôturé.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-08-04 05:53:05\",\"updated_at\":\"2026-08-04 05:53:05\"},{\"id\":42,\"perm_code\":\"view_pilotage\",\"perm_name\":\"Accéder au Centre de Pilotage\",\"module\":\"system\",\"submodule\":\"pilotage\",\"action\":\"view\",\"description\":\"Accéder aux tableaux de bord analytiques et bilans d\'impact.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-08-07 06:48:51\",\"updated_at\":\"2026-08-07 06:48:51\"},{\"id\":51,\"perm_code\":\"manage_subject_groups\",\"perm_name\":\"Gérer les groupes de matières\",\"module\":\"pedagogy\",\"submodule\":\"subjects\",\"action\":\"manage\",\"description\":\"Organiser les matières en groupes\\/UE.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-08-07 06:48:52\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":58,\"perm_code\":\"export_students\",\"perm_name\":\"Exporter les données élèves\",\"module\":\"students\",\"submodule\":\"registry\",\"action\":\"export\",\"description\":\"Exporter les registres élèves au format Excel\\/PDF.\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":1,\"created_at\":\"2026-08-07 06:48:52\",\"updated_at\":\"2026-08-07 06:48:52\"},{\"id\":74,\"perm_code\":\"academicyear_index\",\"perm_name\":\"View Academicyear (index)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:04\",\"updated_at\":\"2026-08-07 06:49:04\"},{\"id\":75,\"perm_code\":\"academicyear_create\",\"perm_name\":\"Create Academicyear (create)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:04\",\"updated_at\":\"2026-08-07 06:49:04\"},{\"id\":76,\"perm_code\":\"academicyear_store\",\"perm_name\":\"Create Academicyear (store)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:04\",\"updated_at\":\"2026-08-07 06:49:04\"},{\"id\":77,\"perm_code\":\"academicyear_activate\",\"perm_name\":\"Manage Academicyear (activate)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::activate()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:04\",\"updated_at\":\"2026-08-07 06:49:04\"},{\"id\":78,\"perm_code\":\"academicyear_rolloverWizard\",\"perm_name\":\"Create Academicyear (rolloverWizard)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::rolloverWizard()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":79,\"perm_code\":\"academicyear_doRollover\",\"perm_name\":\"Manage Academicyear (doRollover)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::doRollover()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":80,\"perm_code\":\"academicyear_archiveWizard\",\"perm_name\":\"Create Academicyear (archiveWizard)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::archiveWizard()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":81,\"perm_code\":\"academicyear_doArchive\",\"perm_name\":\"Manage Academicyear (doArchive)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::doArchive()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":82,\"perm_code\":\"academicyear_restore\",\"perm_name\":\"Create Academicyear (restore)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::restore()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":83,\"perm_code\":\"academicyear_unarchive\",\"perm_name\":\"Manage Academicyear (unarchive)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::unarchive()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":84,\"perm_code\":\"academicyear_doUnarchive\",\"perm_name\":\"Manage Academicyear (doUnarchive)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::doUnarchive()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":85,\"perm_code\":\"academicyear_edit\",\"perm_name\":\"Edit Academicyear (edit)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":86,\"perm_code\":\"academicyear_update\",\"perm_name\":\"Edit Academicyear (update)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":87,\"perm_code\":\"academicyear_delete\",\"perm_name\":\"Delete Academicyear (delete)\",\"module\":\"pedagogy\",\"submodule\":\"academicyear\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode AcademicYear::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":88,\"perm_code\":\"auth_loginView\",\"perm_name\":\"View Auth (loginView)\",\"module\":\"general\",\"submodule\":\"auth\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Auth::loginView()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":89,\"perm_code\":\"auth_loginPost\",\"perm_name\":\"Manage Auth (loginPost)\",\"module\":\"general\",\"submodule\":\"auth\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Auth::loginPost()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":90,\"perm_code\":\"auth_logout\",\"perm_name\":\"Manage Auth (logout)\",\"module\":\"general\",\"submodule\":\"auth\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Auth::logout()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":91,\"perm_code\":\"auth_registerTeacherView\",\"perm_name\":\"View Auth (registerTeacherView)\",\"module\":\"general\",\"submodule\":\"auth\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Auth::registerTeacherView()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":92,\"perm_code\":\"auth_registerTeacherPost\",\"perm_name\":\"Manage Auth (registerTeacherPost)\",\"module\":\"general\",\"submodule\":\"auth\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Auth::registerTeacherPost()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":93,\"perm_code\":\"bulletin_index\",\"perm_name\":\"View Bulletin (index)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":94,\"perm_code\":\"bulletin_discipline\",\"perm_name\":\"Manage Bulletin (discipline)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::discipline()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:05\",\"updated_at\":\"2026-08-07 06:49:05\"},{\"id\":95,\"perm_code\":\"bulletin_saveDiscipline\",\"perm_name\":\"Create Bulletin (saveDiscipline)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::saveDiscipline()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":96,\"perm_code\":\"bulletin_sequence\",\"perm_name\":\"Manage Bulletin (sequence)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::sequence()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":97,\"perm_code\":\"bulletin_trimestre\",\"perm_name\":\"Manage Bulletin (trimestre)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::trimestre()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":98,\"perm_code\":\"bulletin_sequenceClass\",\"perm_name\":\"Manage Bulletin (sequenceClass)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::sequenceClass()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":99,\"perm_code\":\"bulletin_trimestreClass\",\"perm_name\":\"Manage Bulletin (trimestreClass)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::trimestreClass()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":100,\"perm_code\":\"bulletin_annuel\",\"perm_name\":\"Manage Bulletin (annuel)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::annuel()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":101,\"perm_code\":\"bulletin_annuelClass\",\"perm_name\":\"Manage Bulletin (annuelClass)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::annuelClass()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":102,\"perm_code\":\"bulletin_getClassesBySectionJson\",\"perm_name\":\"View Bulletin (getClassesBySectionJson)\",\"module\":\"students\",\"submodule\":\"bulletin\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Bulletin::getClassesBySectionJson()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":103,\"perm_code\":\"class_index\",\"perm_name\":\"View Class (index)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Class::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":104,\"perm_code\":\"class_export\",\"perm_name\":\"Export Class (export)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Class::export()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":105,\"perm_code\":\"class_create\",\"perm_name\":\"Create Class (create)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Class::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":106,\"perm_code\":\"class_store\",\"perm_name\":\"Create Class (store)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Class::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":107,\"perm_code\":\"class_edit\",\"perm_name\":\"Edit Class (edit)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Class::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":108,\"perm_code\":\"class_update\",\"perm_name\":\"Edit Class (update)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Class::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":109,\"perm_code\":\"class_delete\",\"perm_name\":\"Delete Class (delete)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Class::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":110,\"perm_code\":\"class_manageTeam\",\"perm_name\":\"View Class (manageTeam)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Class::manageTeam()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":111,\"perm_code\":\"class_setMainTeacher\",\"perm_name\":\"Manage Class (setMainTeacher)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Class::setMainTeacher()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":112,\"perm_code\":\"class_import\",\"perm_name\":\"Import Class (import)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Class::import()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:06\",\"updated_at\":\"2026-08-07 06:49:06\"},{\"id\":113,\"perm_code\":\"class_downloadTemplate\",\"perm_name\":\"Export Class (downloadTemplate)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Class::downloadTemplate()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":114,\"perm_code\":\"class_upload\",\"perm_name\":\"Import Class (upload)\",\"module\":\"pedagogy\",\"submodule\":\"class\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Class::upload()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":115,\"perm_code\":\"cycle_index\",\"perm_name\":\"View Cycle (index)\",\"module\":\"pedagogy\",\"submodule\":\"cycle\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Cycle::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":116,\"perm_code\":\"cycle_create\",\"perm_name\":\"Create Cycle (create)\",\"module\":\"pedagogy\",\"submodule\":\"cycle\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Cycle::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":117,\"perm_code\":\"cycle_store\",\"perm_name\":\"Create Cycle (store)\",\"module\":\"pedagogy\",\"submodule\":\"cycle\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Cycle::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":118,\"perm_code\":\"cycle_edit\",\"perm_name\":\"Edit Cycle (edit)\",\"module\":\"pedagogy\",\"submodule\":\"cycle\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Cycle::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":119,\"perm_code\":\"cycle_update\",\"perm_name\":\"Edit Cycle (update)\",\"module\":\"pedagogy\",\"submodule\":\"cycle\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Cycle::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":120,\"perm_code\":\"cycle_toggleStatus\",\"perm_name\":\"Edit Cycle (toggleStatus)\",\"module\":\"pedagogy\",\"submodule\":\"cycle\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Cycle::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":121,\"perm_code\":\"cycle_delete\",\"perm_name\":\"Delete Cycle (delete)\",\"module\":\"pedagogy\",\"submodule\":\"cycle\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Cycle::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":122,\"perm_code\":\"dashboard_index\",\"perm_name\":\"View Dashboard (index)\",\"module\":\"system\",\"submodule\":\"dashboard\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Dashboard::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":123,\"perm_code\":\"dashboard_executiveDashboard\",\"perm_name\":\"Manage Dashboard (executiveDashboard)\",\"module\":\"system\",\"submodule\":\"dashboard\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Dashboard::executiveDashboard()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":124,\"perm_code\":\"dashboard_financialCenter\",\"perm_name\":\"Manage Dashboard (financialCenter)\",\"module\":\"system\",\"submodule\":\"dashboard\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Dashboard::financialCenter()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":125,\"perm_code\":\"department_index\",\"perm_name\":\"View Department (index)\",\"module\":\"pedagogy\",\"submodule\":\"department\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Department::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":126,\"perm_code\":\"department_create\",\"perm_name\":\"Create Department (create)\",\"module\":\"pedagogy\",\"submodule\":\"department\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Department::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":127,\"perm_code\":\"department_store\",\"perm_name\":\"Create Department (store)\",\"module\":\"pedagogy\",\"submodule\":\"department\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Department::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:07\",\"updated_at\":\"2026-08-07 06:49:07\"},{\"id\":128,\"perm_code\":\"department_edit\",\"perm_name\":\"Edit Department (edit)\",\"module\":\"pedagogy\",\"submodule\":\"department\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Department::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":129,\"perm_code\":\"department_update\",\"perm_name\":\"Edit Department (update)\",\"module\":\"pedagogy\",\"submodule\":\"department\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Department::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":130,\"perm_code\":\"department_toggleStatus\",\"perm_name\":\"Edit Department (toggleStatus)\",\"module\":\"pedagogy\",\"submodule\":\"department\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Department::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":131,\"perm_code\":\"department_delete\",\"perm_name\":\"Delete Department (delete)\",\"module\":\"pedagogy\",\"submodule\":\"department\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Department::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":132,\"perm_code\":\"discount_index\",\"perm_name\":\"View Discount (index)\",\"module\":\"finance\",\"submodule\":\"discount\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Discount::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":133,\"perm_code\":\"discount_store\",\"perm_name\":\"Create Discount (store)\",\"module\":\"finance\",\"submodule\":\"discount\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Discount::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":134,\"perm_code\":\"discount_toggleStatus\",\"perm_name\":\"Edit Discount (toggleStatus)\",\"module\":\"finance\",\"submodule\":\"discount\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Discount::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":135,\"perm_code\":\"discount_delete\",\"perm_name\":\"Delete Discount (delete)\",\"module\":\"finance\",\"submodule\":\"discount\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Discount::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":136,\"perm_code\":\"discounttype_index\",\"perm_name\":\"View Discounttype (index)\",\"module\":\"finance\",\"submodule\":\"discounttype\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode DiscountType::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":137,\"perm_code\":\"discounttype_store\",\"perm_name\":\"Create Discounttype (store)\",\"module\":\"finance\",\"submodule\":\"discounttype\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode DiscountType::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":138,\"perm_code\":\"discounttype_toggleStatus\",\"perm_name\":\"Edit Discounttype (toggleStatus)\",\"module\":\"finance\",\"submodule\":\"discounttype\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode DiscountType::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":139,\"perm_code\":\"discounttype_delete\",\"perm_name\":\"Delete Discounttype (delete)\",\"module\":\"finance\",\"submodule\":\"discounttype\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode DiscountType::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":140,\"perm_code\":\"documentation_index\",\"perm_name\":\"View Documentation (index)\",\"module\":\"system\",\"submodule\":\"documentation\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Documentation::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":141,\"perm_code\":\"documentation_download\",\"perm_name\":\"Export Documentation (download)\",\"module\":\"system\",\"submodule\":\"documentation\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Documentation::download()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":142,\"perm_code\":\"expense_index\",\"perm_name\":\"View Expense (index)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Expense::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":143,\"perm_code\":\"expense_store\",\"perm_name\":\"Create Expense (store)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Expense::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":144,\"perm_code\":\"expense_update\",\"perm_name\":\"Edit Expense (update)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Expense::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":145,\"perm_code\":\"expense_cancel\",\"perm_name\":\"Manage Expense (cancel)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Expense::cancel()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":146,\"perm_code\":\"expense_printReport\",\"perm_name\":\"Export Expense (printReport)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Expense::printReport()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":147,\"perm_code\":\"expense_categories\",\"perm_name\":\"Manage Expense (categories)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Expense::categories()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":148,\"perm_code\":\"expense_storeCategory\",\"perm_name\":\"Create Expense (storeCategory)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Expense::storeCategory()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:08\",\"updated_at\":\"2026-08-07 06:49:08\"},{\"id\":149,\"perm_code\":\"expense_updateCategory\",\"perm_name\":\"Edit Expense (updateCategory)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Expense::updateCategory()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":150,\"perm_code\":\"expense_toggleCategoryStatus\",\"perm_name\":\"Edit Expense (toggleCategoryStatus)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Expense::toggleCategoryStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":151,\"perm_code\":\"expense_auditLogs\",\"perm_name\":\"Manage Expense (auditLogs)\",\"module\":\"finance\",\"submodule\":\"expense\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Expense::auditLogs()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":152,\"perm_code\":\"financialhistory_index\",\"perm_name\":\"View Financialhistory (index)\",\"module\":\"finance\",\"submodule\":\"financialhistory\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode FinancialHistory::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":153,\"perm_code\":\"financialhistory_print\",\"perm_name\":\"Export Financialhistory (print)\",\"module\":\"finance\",\"submodule\":\"financialhistory\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode FinancialHistory::print()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":154,\"perm_code\":\"grade_index\",\"perm_name\":\"View Grade (index)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Grade::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":155,\"perm_code\":\"grade_export\",\"perm_name\":\"Export Grade (export)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Grade::export()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":156,\"perm_code\":\"grade_saisie\",\"perm_name\":\"Manage Grade (saisie)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Grade::saisie()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":157,\"perm_code\":\"grade_store\",\"perm_name\":\"Create Grade (store)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Grade::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":158,\"perm_code\":\"grade_import\",\"perm_name\":\"Import Grade (import)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Grade::import()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":159,\"perm_code\":\"grade_downloadTemplate\",\"perm_name\":\"Export Grade (downloadTemplate)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Grade::downloadTemplate()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:09\",\"updated_at\":\"2026-08-07 06:49:09\"},{\"id\":160,\"perm_code\":\"grade_upload\",\"perm_name\":\"Import Grade (upload)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Grade::upload()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":161,\"perm_code\":\"grade_history\",\"perm_name\":\"Manage Grade (history)\",\"module\":\"students\",\"submodule\":\"grade\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Grade::history()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":162,\"perm_code\":\"honorroll_index\",\"perm_name\":\"View Honorroll (index)\",\"module\":\"students\",\"submodule\":\"honorroll\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode HonorRoll::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":163,\"perm_code\":\"honorroll_trimestre\",\"perm_name\":\"Manage Honorroll (trimestre)\",\"module\":\"students\",\"submodule\":\"honorroll\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode HonorRoll::trimestre()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":164,\"perm_code\":\"honorroll_trimesterBulk\",\"perm_name\":\"Manage Honorroll (trimesterBulk)\",\"module\":\"students\",\"submodule\":\"honorroll\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode HonorRoll::trimesterBulk()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":165,\"perm_code\":\"honorroll_annuel\",\"perm_name\":\"Manage Honorroll (annuel)\",\"module\":\"students\",\"submodule\":\"honorroll\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode HonorRoll::annuel()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":166,\"perm_code\":\"honorroll_annuelBulk\",\"perm_name\":\"Manage Honorroll (annuelBulk)\",\"module\":\"students\",\"submodule\":\"honorroll\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode HonorRoll::annuelBulk()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":167,\"perm_code\":\"impactanalysis_getAnalysis\",\"perm_name\":\"View Impactanalysis (getAnalysis)\",\"module\":\"system\",\"submodule\":\"impactanalysis\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode ImpactAnalysis::getAnalysis()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":168,\"perm_code\":\"impactanalysis_executeDelete\",\"perm_name\":\"Delete Impactanalysis (executeDelete)\",\"module\":\"system\",\"submodule\":\"impactanalysis\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode ImpactAnalysis::executeDelete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":169,\"perm_code\":\"landing_index\",\"perm_name\":\"View Landing (index)\",\"module\":\"general\",\"submodule\":\"landing\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Landing::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":170,\"perm_code\":\"landing_sendContact\",\"perm_name\":\"Manage Landing (sendContact)\",\"module\":\"general\",\"submodule\":\"landing\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Landing::sendContact()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":171,\"perm_code\":\"landing_toggleArchiveNotification\",\"perm_name\":\"Edit Landing (toggleArchiveNotification)\",\"module\":\"general\",\"submodule\":\"landing\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Landing::toggleArchiveNotification()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":172,\"perm_code\":\"landing_deleteNotification\",\"perm_name\":\"Delete Landing (deleteNotification)\",\"module\":\"general\",\"submodule\":\"landing\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Landing::deleteNotification()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":173,\"perm_code\":\"landing_marks\",\"perm_name\":\"Manage Landing (marks)\",\"module\":\"general\",\"submodule\":\"landing\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Landing::marks()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":174,\"perm_code\":\"level_index\",\"perm_name\":\"View Level (index)\",\"module\":\"pedagogy\",\"submodule\":\"level\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Level::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:10\",\"updated_at\":\"2026-08-07 06:49:10\"},{\"id\":175,\"perm_code\":\"level_create\",\"perm_name\":\"Create Level (create)\",\"module\":\"pedagogy\",\"submodule\":\"level\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Level::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":176,\"perm_code\":\"level_store\",\"perm_name\":\"Create Level (store)\",\"module\":\"pedagogy\",\"submodule\":\"level\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Level::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":177,\"perm_code\":\"level_edit\",\"perm_name\":\"Edit Level (edit)\",\"module\":\"pedagogy\",\"submodule\":\"level\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Level::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":178,\"perm_code\":\"level_update\",\"perm_name\":\"Edit Level (update)\",\"module\":\"pedagogy\",\"submodule\":\"level\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Level::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":179,\"perm_code\":\"level_toggleStatus\",\"perm_name\":\"Edit Level (toggleStatus)\",\"module\":\"pedagogy\",\"submodule\":\"level\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Level::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":180,\"perm_code\":\"level_delete\",\"perm_name\":\"Delete Level (delete)\",\"module\":\"pedagogy\",\"submodule\":\"level\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Level::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":181,\"perm_code\":\"payment_index\",\"perm_name\":\"View Payment (index)\",\"module\":\"finance\",\"submodule\":\"payment\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Payment::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":182,\"perm_code\":\"payment_studentDetails\",\"perm_name\":\"Manage Payment (studentDetails)\",\"module\":\"finance\",\"submodule\":\"payment\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Payment::studentDetails()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":183,\"perm_code\":\"payment_store\",\"perm_name\":\"Create Payment (store)\",\"module\":\"finance\",\"submodule\":\"payment\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Payment::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":184,\"perm_code\":\"payment_delete\",\"perm_name\":\"Delete Payment (delete)\",\"module\":\"finance\",\"submodule\":\"payment\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Payment::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":185,\"perm_code\":\"payment_receipt\",\"perm_name\":\"Manage Payment (receipt)\",\"module\":\"finance\",\"submodule\":\"payment\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Payment::receipt()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":186,\"perm_code\":\"payment_fullHistory\",\"perm_name\":\"Manage Payment (fullHistory)\",\"module\":\"finance\",\"submodule\":\"payment\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Payment::fullHistory()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":187,\"perm_code\":\"payment_verify\",\"perm_name\":\"Manage Payment (verify)\",\"module\":\"finance\",\"submodule\":\"payment\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Payment::verify()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":188,\"perm_code\":\"procesverbal_index\",\"perm_name\":\"View Procesverbal (index)\",\"module\":\"students\",\"submodule\":\"procesverbal\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode ProcesVerbal::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:11\",\"updated_at\":\"2026-08-07 06:49:11\"},{\"id\":189,\"perm_code\":\"procesverbal_evaluation\",\"perm_name\":\"Manage Procesverbal (evaluation)\",\"module\":\"students\",\"submodule\":\"procesverbal\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode ProcesVerbal::evaluation()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":190,\"perm_code\":\"procesverbal_sequence\",\"perm_name\":\"Manage Procesverbal (sequence)\",\"module\":\"students\",\"submodule\":\"procesverbal\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode ProcesVerbal::sequence()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":191,\"perm_code\":\"procesverbal_trimestre\",\"perm_name\":\"Manage Procesverbal (trimestre)\",\"module\":\"students\",\"submodule\":\"procesverbal\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode ProcesVerbal::trimestre()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":192,\"perm_code\":\"procesverbal_annuel\",\"perm_name\":\"Manage Procesverbal (annuel)\",\"module\":\"students\",\"submodule\":\"procesverbal\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode ProcesVerbal::annuel()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":193,\"perm_code\":\"profile_index\",\"perm_name\":\"View Profile (index)\",\"module\":\"general\",\"submodule\":\"profile\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Profile::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":194,\"perm_code\":\"profile_update\",\"perm_name\":\"Edit Profile (update)\",\"module\":\"general\",\"submodule\":\"profile\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Profile::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":195,\"perm_code\":\"publicverification_verifyPublic\",\"perm_name\":\"Manage Publicverification (verifyPublic)\",\"module\":\"general\",\"submodule\":\"publicverification\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode PublicVerification::verifyPublic()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":196,\"perm_code\":\"rbac_index\",\"perm_name\":\"View Rbac (index)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Rbac::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":197,\"perm_code\":\"rbac_getPermissions\",\"perm_name\":\"View Rbac (getPermissions)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Rbac::getPermissions()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":198,\"perm_code\":\"rbac_getRoles\",\"perm_name\":\"View Rbac (getRoles)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Rbac::getRoles()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":199,\"perm_code\":\"rbac_getRolePermissions\",\"perm_name\":\"View Rbac (getRolePermissions)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Rbac::getRolePermissions()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":200,\"perm_code\":\"rbac_saveRolePermissions\",\"perm_name\":\"Create Rbac (saveRolePermissions)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Rbac::saveRolePermissions()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":201,\"perm_code\":\"rbac_copyRolePermissions\",\"perm_name\":\"Manage Rbac (copyRolePermissions)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Rbac::copyRolePermissions()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":202,\"perm_code\":\"rbac_compareRoles\",\"perm_name\":\"Manage Rbac (compareRoles)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Rbac::compareRoles()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":203,\"perm_code\":\"rbac_resetRolePermissions\",\"perm_name\":\"Manage Rbac (resetRolePermissions)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Rbac::resetRolePermissions()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":204,\"perm_code\":\"rbac_searchUsers\",\"perm_name\":\"Manage Rbac (searchUsers)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Rbac::searchUsers()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":205,\"perm_code\":\"rbac_getUserPermissions\",\"perm_name\":\"View Rbac (getUserPermissions)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Rbac::getUserPermissions()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":206,\"perm_code\":\"rbac_saveUserPermissions\",\"perm_name\":\"Create Rbac (saveUserPermissions)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Rbac::saveUserPermissions()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":207,\"perm_code\":\"rbac_runScan\",\"perm_name\":\"Manage Rbac (runScan)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Rbac::runScan()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":208,\"perm_code\":\"rbac_getAuditLogs\",\"perm_name\":\"View Rbac (getAuditLogs)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Rbac::getAuditLogs()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":209,\"perm_code\":\"rbac_createBackup\",\"perm_name\":\"Create Rbac (createBackup)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Rbac::createBackup()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:12\",\"updated_at\":\"2026-08-07 06:49:12\"},{\"id\":210,\"perm_code\":\"rbac_getBackups\",\"perm_name\":\"View Rbac (getBackups)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Rbac::getBackups()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":211,\"perm_code\":\"rbac_restoreBackup\",\"perm_name\":\"Create Rbac (restoreBackup)\",\"module\":\"system\",\"submodule\":\"rbac\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Rbac::restoreBackup()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":212,\"perm_code\":\"scholarship_index\",\"perm_name\":\"View Scholarship (index)\",\"module\":\"finance\",\"submodule\":\"scholarship\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Scholarship::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":213,\"perm_code\":\"scholarship_store\",\"perm_name\":\"Create Scholarship (store)\",\"module\":\"finance\",\"submodule\":\"scholarship\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Scholarship::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":214,\"perm_code\":\"scholarship_toggleStatus\",\"perm_name\":\"Edit Scholarship (toggleStatus)\",\"module\":\"finance\",\"submodule\":\"scholarship\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Scholarship::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":215,\"perm_code\":\"scholarship_delete\",\"perm_name\":\"Delete Scholarship (delete)\",\"module\":\"finance\",\"submodule\":\"scholarship\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Scholarship::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":216,\"perm_code\":\"schoolfee_grille\",\"perm_name\":\"Manage Schoolfee (grille)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::grille()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":217,\"perm_code\":\"schoolfee_tranches\",\"perm_name\":\"Manage Schoolfee (tranches)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::tranches()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":218,\"perm_code\":\"schoolfee_versements\",\"perm_name\":\"Manage Schoolfee (versements)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::versements()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":219,\"perm_code\":\"schoolfee_storeVersement\",\"perm_name\":\"Create Schoolfee (storeVersement)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::storeVersement()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":220,\"perm_code\":\"schoolfee_deleteVersement\",\"perm_name\":\"Delete Schoolfee (deleteVersement)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::deleteVersement()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":221,\"perm_code\":\"schoolfee_insolvables\",\"perm_name\":\"Manage Schoolfee (insolvables)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::insolvables()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":222,\"perm_code\":\"schoolfee_receipt\",\"perm_name\":\"Manage Schoolfee (receipt)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::receipt()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":223,\"perm_code\":\"schoolfee_printInsolvables\",\"perm_name\":\"Export Schoolfee (printInsolvables)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::printInsolvables()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:13\",\"updated_at\":\"2026-08-07 06:49:13\"},{\"id\":224,\"perm_code\":\"schoolfee_printGrille\",\"perm_name\":\"Export Schoolfee (printGrille)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::printGrille()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":225,\"perm_code\":\"schoolfee_templateGrille\",\"perm_name\":\"Manage Schoolfee (templateGrille)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::templateGrille()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":226,\"perm_code\":\"schoolfee_importGrille\",\"perm_name\":\"Import Schoolfee (importGrille)\",\"module\":\"finance\",\"submodule\":\"schoolfee\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode SchoolFee::importGrille()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":227,\"perm_code\":\"section_index\",\"perm_name\":\"View Section (index)\",\"module\":\"pedagogy\",\"submodule\":\"section\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Section::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":228,\"perm_code\":\"section_create\",\"perm_name\":\"Create Section (create)\",\"module\":\"pedagogy\",\"submodule\":\"section\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Section::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":229,\"perm_code\":\"section_store\",\"perm_name\":\"Create Section (store)\",\"module\":\"pedagogy\",\"submodule\":\"section\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Section::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":230,\"perm_code\":\"section_edit\",\"perm_name\":\"Edit Section (edit)\",\"module\":\"pedagogy\",\"submodule\":\"section\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Section::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":231,\"perm_code\":\"section_update\",\"perm_name\":\"Edit Section (update)\",\"module\":\"pedagogy\",\"submodule\":\"section\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Section::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:14\",\"updated_at\":\"2026-08-07 06:49:14\"},{\"id\":232,\"perm_code\":\"section_toggleStatus\",\"perm_name\":\"Edit Section (toggleStatus)\",\"module\":\"pedagogy\",\"submodule\":\"section\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Section::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:15\",\"updated_at\":\"2026-08-07 06:49:15\"},{\"id\":233,\"perm_code\":\"section_delete\",\"perm_name\":\"Delete Section (delete)\",\"module\":\"pedagogy\",\"submodule\":\"section\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Section::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:15\",\"updated_at\":\"2026-08-07 06:49:15\"},{\"id\":234,\"perm_code\":\"sequence_index\",\"perm_name\":\"View Sequence (index)\",\"module\":\"pedagogy\",\"submodule\":\"sequence\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Sequence::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:15\",\"updated_at\":\"2026-08-07 06:49:15\"},{\"id\":235,\"perm_code\":\"sequence_create\",\"perm_name\":\"Create Sequence (create)\",\"module\":\"pedagogy\",\"submodule\":\"sequence\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Sequence::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:15\",\"updated_at\":\"2026-08-07 06:49:15\"},{\"id\":236,\"perm_code\":\"sequence_store\",\"perm_name\":\"Create Sequence (store)\",\"module\":\"pedagogy\",\"submodule\":\"sequence\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Sequence::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:15\",\"updated_at\":\"2026-08-07 06:49:15\"},{\"id\":237,\"perm_code\":\"sequence_edit\",\"perm_name\":\"Edit Sequence (edit)\",\"module\":\"pedagogy\",\"submodule\":\"sequence\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Sequence::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":238,\"perm_code\":\"sequence_update\",\"perm_name\":\"Edit Sequence (update)\",\"module\":\"pedagogy\",\"submodule\":\"sequence\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Sequence::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":239,\"perm_code\":\"sequence_delete\",\"perm_name\":\"Delete Sequence (delete)\",\"module\":\"pedagogy\",\"submodule\":\"sequence\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Sequence::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":240,\"perm_code\":\"sequence_toggle\",\"perm_name\":\"Edit Sequence (toggle)\",\"module\":\"pedagogy\",\"submodule\":\"sequence\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Sequence::toggle()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":241,\"perm_code\":\"setting_index\",\"perm_name\":\"View Setting (index)\",\"module\":\"system\",\"submodule\":\"setting\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Setting::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":242,\"perm_code\":\"setting_store\",\"perm_name\":\"Create Setting (store)\",\"module\":\"system\",\"submodule\":\"setting\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Setting::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":243,\"perm_code\":\"setting_reset\",\"perm_name\":\"Manage Setting (reset)\",\"module\":\"system\",\"submodule\":\"setting\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Setting::reset()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":244,\"perm_code\":\"setting_runBackup\",\"perm_name\":\"Manage Setting (runBackup)\",\"module\":\"system\",\"submodule\":\"setting\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Setting::runBackup()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":245,\"perm_code\":\"student_index\",\"perm_name\":\"View Student (index)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Student::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":246,\"perm_code\":\"student_nonInscrits\",\"perm_name\":\"Manage Student (nonInscrits)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Student::nonInscrits()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:16\",\"updated_at\":\"2026-08-07 06:49:16\"},{\"id\":247,\"perm_code\":\"student_export\",\"perm_name\":\"Export Student (export)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Student::export()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":248,\"perm_code\":\"student_exportExcel\",\"perm_name\":\"Export Student (exportExcel)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Student::exportExcel()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":249,\"perm_code\":\"student_create\",\"perm_name\":\"Create Student (create)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Student::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":250,\"perm_code\":\"student_import\",\"perm_name\":\"Import Student (import)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Student::import()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":251,\"perm_code\":\"student_downloadTemplate\",\"perm_name\":\"Export Student (downloadTemplate)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Student::downloadTemplate()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":252,\"perm_code\":\"student_upload\",\"perm_name\":\"Import Student (upload)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Student::upload()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":253,\"perm_code\":\"student_store\",\"perm_name\":\"Create Student (store)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Student::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":254,\"perm_code\":\"student_edit\",\"perm_name\":\"Edit Student (edit)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Student::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":255,\"perm_code\":\"student_update\",\"perm_name\":\"Edit Student (update)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Student::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":256,\"perm_code\":\"student_withdraw\",\"perm_name\":\"Manage Student (withdraw)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Student::withdraw()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":257,\"perm_code\":\"student_restore\",\"perm_name\":\"Create Student (restore)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Student::restore()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":258,\"perm_code\":\"student_delete\",\"perm_name\":\"Delete Student (delete)\",\"module\":\"students\",\"submodule\":\"student\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Student::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:17\",\"updated_at\":\"2026-08-07 06:49:17\"},{\"id\":259,\"perm_code\":\"subject_index\",\"perm_name\":\"View Subject (index)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Subject::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":260,\"perm_code\":\"subject_export\",\"perm_name\":\"Export Subject (export)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Subject::export()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":261,\"perm_code\":\"subject_create\",\"perm_name\":\"Create Subject (create)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Subject::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":262,\"perm_code\":\"subject_store\",\"perm_name\":\"Create Subject (store)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Subject::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":263,\"perm_code\":\"subject_edit\",\"perm_name\":\"Edit Subject (edit)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Subject::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":264,\"perm_code\":\"subject_update\",\"perm_name\":\"Edit Subject (update)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Subject::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":265,\"perm_code\":\"subject_toggleStatus\",\"perm_name\":\"Edit Subject (toggleStatus)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Subject::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":266,\"perm_code\":\"subject_delete\",\"perm_name\":\"Delete Subject (delete)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Subject::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":267,\"perm_code\":\"subject_import\",\"perm_name\":\"Import Subject (import)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Subject::import()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":268,\"perm_code\":\"subject_downloadTemplate\",\"perm_name\":\"Export Subject (downloadTemplate)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Subject::downloadTemplate()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":269,\"perm_code\":\"subject_upload\",\"perm_name\":\"Import Subject (upload)\",\"module\":\"pedagogy\",\"submodule\":\"subject\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Subject::upload()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":270,\"perm_code\":\"subjectgroup_index\",\"perm_name\":\"View Subjectgroup (index)\",\"module\":\"pedagogy\",\"submodule\":\"subjectgroup\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode SubjectGroup::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":271,\"perm_code\":\"subjectgroup_store\",\"perm_name\":\"Create Subjectgroup (store)\",\"module\":\"pedagogy\",\"submodule\":\"subjectgroup\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode SubjectGroup::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":272,\"perm_code\":\"subjectgroup_update\",\"perm_name\":\"Edit Subjectgroup (update)\",\"module\":\"pedagogy\",\"submodule\":\"subjectgroup\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode SubjectGroup::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":273,\"perm_code\":\"subjectgroup_toggle\",\"perm_name\":\"Edit Subjectgroup (toggle)\",\"module\":\"pedagogy\",\"submodule\":\"subjectgroup\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode SubjectGroup::toggle()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":274,\"perm_code\":\"subjectgroup_delete\",\"perm_name\":\"Delete Subjectgroup (delete)\",\"module\":\"pedagogy\",\"submodule\":\"subjectgroup\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode SubjectGroup::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":275,\"perm_code\":\"teacher_index\",\"perm_name\":\"View Teacher (index)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Teacher::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":276,\"perm_code\":\"teacher_toggleTeacherNames\",\"perm_name\":\"Edit Teacher (toggleTeacherNames)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Teacher::toggleTeacherNames()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:18\",\"updated_at\":\"2026-08-07 06:49:18\"},{\"id\":277,\"perm_code\":\"teacher_export\",\"perm_name\":\"Export Teacher (export)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Teacher::export()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":278,\"perm_code\":\"teacher_create\",\"perm_name\":\"Create Teacher (create)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Teacher::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":279,\"perm_code\":\"teacher_store\",\"perm_name\":\"Create Teacher (store)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Teacher::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":280,\"perm_code\":\"teacher_delete\",\"perm_name\":\"Delete Teacher (delete)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Teacher::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":281,\"perm_code\":\"teacher_assign\",\"perm_name\":\"Manage Teacher (assign)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Teacher::assign()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":282,\"perm_code\":\"teacher_directAssign\",\"perm_name\":\"Manage Teacher (directAssign)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Teacher::directAssign()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":283,\"perm_code\":\"teacher_storeAssignment\",\"perm_name\":\"Create Teacher (storeAssignment)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Teacher::storeAssignment()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":284,\"perm_code\":\"teacher_import\",\"perm_name\":\"Import Teacher (import)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Teacher::import()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":285,\"perm_code\":\"teacher_downloadTemplate\",\"perm_name\":\"Export Teacher (downloadTemplate)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Teacher::downloadTemplate()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":286,\"perm_code\":\"teacher_upload\",\"perm_name\":\"Import Teacher (upload)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"import\",\"description\":\"Permission auto-détectée pour la méthode Teacher::upload()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":287,\"perm_code\":\"teacher_edit\",\"perm_name\":\"Edit Teacher (edit)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Teacher::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":288,\"perm_code\":\"teacher_update\",\"perm_name\":\"Edit Teacher (update)\",\"module\":\"pedagogy\",\"submodule\":\"teacher\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Teacher::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":289,\"perm_code\":\"teachingtype_index\",\"perm_name\":\"View Teachingtype (index)\",\"module\":\"pedagogy\",\"submodule\":\"teachingtype\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode TeachingType::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":290,\"perm_code\":\"teachingtype_create\",\"perm_name\":\"Create Teachingtype (create)\",\"module\":\"pedagogy\",\"submodule\":\"teachingtype\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode TeachingType::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":291,\"perm_code\":\"teachingtype_store\",\"perm_name\":\"Create Teachingtype (store)\",\"module\":\"pedagogy\",\"submodule\":\"teachingtype\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode TeachingType::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":292,\"perm_code\":\"teachingtype_edit\",\"perm_name\":\"Edit Teachingtype (edit)\",\"module\":\"pedagogy\",\"submodule\":\"teachingtype\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode TeachingType::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":293,\"perm_code\":\"teachingtype_update\",\"perm_name\":\"Edit Teachingtype (update)\",\"module\":\"pedagogy\",\"submodule\":\"teachingtype\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode TeachingType::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:19\",\"updated_at\":\"2026-08-07 06:49:19\"},{\"id\":294,\"perm_code\":\"teachingtype_delete\",\"perm_name\":\"Delete Teachingtype (delete)\",\"module\":\"pedagogy\",\"submodule\":\"teachingtype\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode TeachingType::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":295,\"perm_code\":\"timetable_index\",\"perm_name\":\"View Timetable (index)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Timetable::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":296,\"perm_code\":\"timetable_slots\",\"perm_name\":\"Manage Timetable (slots)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Timetable::slots()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":297,\"perm_code\":\"timetable_storeSlot\",\"perm_name\":\"Create Timetable (storeSlot)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::storeSlot()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":298,\"perm_code\":\"timetable_updateSlot\",\"perm_name\":\"Edit Timetable (updateSlot)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Timetable::updateSlot()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":299,\"perm_code\":\"timetable_deleteSlot\",\"perm_name\":\"Delete Timetable (deleteSlot)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Timetable::deleteSlot()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":300,\"perm_code\":\"timetable_rooms\",\"perm_name\":\"Manage Timetable (rooms)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Timetable::rooms()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":301,\"perm_code\":\"timetable_storeRoom\",\"perm_name\":\"Create Timetable (storeRoom)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::storeRoom()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":302,\"perm_code\":\"timetable_updateRoom\",\"perm_name\":\"Edit Timetable (updateRoom)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Timetable::updateRoom()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":303,\"perm_code\":\"timetable_deleteRoom\",\"perm_name\":\"Delete Timetable (deleteRoom)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Timetable::deleteRoom()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":304,\"perm_code\":\"timetable_weeks\",\"perm_name\":\"Manage Timetable (weeks)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Timetable::weeks()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":305,\"perm_code\":\"timetable_storeWeek\",\"perm_name\":\"Create Timetable (storeWeek)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::storeWeek()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":306,\"perm_code\":\"timetable_updateWeek\",\"perm_name\":\"Edit Timetable (updateWeek)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode Timetable::updateWeek()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":307,\"perm_code\":\"timetable_deleteWeek\",\"perm_name\":\"Delete Timetable (deleteWeek)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Timetable::deleteWeek()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":308,\"perm_code\":\"timetable_wizard\",\"perm_name\":\"Create Timetable (wizard)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::wizard()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":309,\"perm_code\":\"timetable_wizardStepData\",\"perm_name\":\"Create Timetable (wizardStepData)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::wizardStepData()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":310,\"perm_code\":\"timetable_createTimetable\",\"perm_name\":\"Create Timetable (createTimetable)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::createTimetable()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":311,\"perm_code\":\"timetable_grid\",\"perm_name\":\"Manage Timetable (grid)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Timetable::grid()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:20\",\"updated_at\":\"2026-08-07 06:49:20\"},{\"id\":312,\"perm_code\":\"timetable_saveGridEntry\",\"perm_name\":\"Create Timetable (saveGridEntry)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::saveGridEntry()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":313,\"perm_code\":\"timetable_apiGetClassSubjects\",\"perm_name\":\"View Timetable (apiGetClassSubjects)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Timetable::apiGetClassSubjects()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":314,\"perm_code\":\"timetable_apiGetSubjectTeachers\",\"perm_name\":\"View Timetable (apiGetSubjectTeachers)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Timetable::apiGetSubjectTeachers()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":315,\"perm_code\":\"timetable_apiQuickCreateTeacher\",\"perm_name\":\"Create Timetable (apiQuickCreateTeacher)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode Timetable::apiQuickCreateTeacher()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":316,\"perm_code\":\"timetable_deleteGridEntry\",\"perm_name\":\"Delete Timetable (deleteGridEntry)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Timetable::deleteGridEntry()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":317,\"perm_code\":\"timetable_apiValidateConflict\",\"perm_name\":\"Manage Timetable (apiValidateConflict)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Timetable::apiValidateConflict()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":318,\"perm_code\":\"timetable_unlock\",\"perm_name\":\"Manage Timetable (unlock)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Timetable::unlock()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":319,\"perm_code\":\"timetable_deleteTimetable\",\"perm_name\":\"Delete Timetable (deleteTimetable)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode Timetable::deleteTimetable()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":320,\"perm_code\":\"timetable_exportPdf\",\"perm_name\":\"Export Timetable (exportPdf)\",\"module\":\"pedagogy\",\"submodule\":\"timetable\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode Timetable::exportPdf()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":321,\"perm_code\":\"transcript_index\",\"perm_name\":\"View Transcript (index)\",\"module\":\"students\",\"submodule\":\"transcript\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode Transcript::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":322,\"perm_code\":\"transcript_generate\",\"perm_name\":\"Manage Transcript (generate)\",\"module\":\"students\",\"submodule\":\"transcript\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode Transcript::generate()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:21\",\"updated_at\":\"2026-08-07 06:49:21\"},{\"id\":323,\"perm_code\":\"user_index\",\"perm_name\":\"View User (index)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode User::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":324,\"perm_code\":\"user_export\",\"perm_name\":\"Export User (export)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"export\",\"description\":\"Permission auto-détectée pour la méthode User::export()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":325,\"perm_code\":\"user_create\",\"perm_name\":\"Create User (create)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode User::create()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":326,\"perm_code\":\"user_store\",\"perm_name\":\"Create User (store)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode User::store()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":327,\"perm_code\":\"user_edit\",\"perm_name\":\"Edit User (edit)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode User::edit()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":328,\"perm_code\":\"user_update\",\"perm_name\":\"Edit User (update)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode User::update()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":329,\"perm_code\":\"user_delete\",\"perm_name\":\"Delete User (delete)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"delete\",\"description\":\"Permission auto-détectée pour la méthode User::delete()\",\"criticality\":\"high\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":330,\"perm_code\":\"user_createCaissier\",\"perm_name\":\"Create User (createCaissier)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode User::createCaissier()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:22\",\"updated_at\":\"2026-08-07 06:49:22\"},{\"id\":331,\"perm_code\":\"user_storeCaissier\",\"perm_name\":\"Create User (storeCaissier)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"create\",\"description\":\"Permission auto-détectée pour la méthode User::storeCaissier()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:23\",\"updated_at\":\"2026-08-07 06:49:23\"},{\"id\":332,\"perm_code\":\"user_caissiers\",\"perm_name\":\"Manage User (caissiers)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"manage\",\"description\":\"Permission auto-détectée pour la méthode User::caissiers()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:23\",\"updated_at\":\"2026-08-07 06:49:23\"},{\"id\":333,\"perm_code\":\"user_toggleStatus\",\"perm_name\":\"Edit User (toggleStatus)\",\"module\":\"system\",\"submodule\":\"user\",\"action\":\"edit\",\"description\":\"Permission auto-détectée pour la méthode User::toggleStatus()\",\"criticality\":\"medium\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:23\",\"updated_at\":\"2026-08-07 06:49:23\"},{\"id\":334,\"perm_code\":\"verificationadmin_index\",\"perm_name\":\"View Verificationadmin (index)\",\"module\":\"general\",\"submodule\":\"verificationadmin\",\"action\":\"view\",\"description\":\"Permission auto-détectée pour la méthode VerificationAdmin::index()\",\"criticality\":\"low\",\"status\":\"active\",\"is_system\":0,\"created_at\":\"2026-08-07 06:49:23\",\"updated_at\":\"2026-08-07 06:49:23\"}],\"role_permissions\":[{\"role_id\":1,\"permission_id\":1},{\"role_id\":1,\"permission_id\":2},{\"role_id\":1,\"permission_id\":3},{\"role_id\":1,\"permission_id\":4},{\"role_id\":1,\"permission_id\":5},{\"role_id\":1,\"permission_id\":6},{\"role_id\":1,\"permission_id\":7},{\"role_id\":1,\"permission_id\":8},{\"role_id\":1,\"permission_id\":9},{\"role_id\":1,\"permission_id\":10},{\"role_id\":1,\"permission_id\":11},{\"role_id\":1,\"permission_id\":12},{\"role_id\":1,\"permission_id\":13},{\"role_id\":1,\"permission_id\":14},{\"role_id\":1,\"permission_id\":15},{\"role_id\":1,\"permission_id\":16},{\"role_id\":1,\"permission_id\":21},{\"role_id\":1,\"permission_id\":22},{\"role_id\":1,\"permission_id\":23},{\"role_id\":1,\"permission_id\":24},{\"role_id\":1,\"permission_id\":25},{\"role_id\":1,\"permission_id\":29},{\"role_id\":1,\"permission_id\":32},{\"role_id\":1,\"permission_id\":33},{\"role_id\":1,\"permission_id\":36},{\"role_id\":1,\"permission_id\":37},{\"role_id\":1,\"permission_id\":42},{\"role_id\":1,\"permission_id\":51},{\"role_id\":1,\"permission_id\":58},{\"role_id\":1,\"permission_id\":74},{\"role_id\":1,\"permission_id\":75},{\"role_id\":1,\"permission_id\":76},{\"role_id\":1,\"permission_id\":78},{\"role_id\":1,\"permission_id\":80},{\"role_id\":1,\"permission_id\":81},{\"role_id\":1,\"permission_id\":82},{\"role_id\":1,\"permission_id\":85},{\"role_id\":1,\"permission_id\":86},{\"role_id\":1,\"permission_id\":87},{\"role_id\":1,\"permission_id\":108},{\"role_id\":1,\"permission_id\":109},{\"role_id\":1,\"permission_id\":113},{\"role_id\":1,\"permission_id\":114},{\"role_id\":1,\"permission_id\":116},{\"role_id\":1,\"permission_id\":118},{\"role_id\":1,\"permission_id\":121},{\"role_id\":1,\"permission_id\":122},{\"role_id\":1,\"permission_id\":123},{\"role_id\":1,\"permission_id\":124},{\"role_id\":1,\"permission_id\":140},{\"role_id\":1,\"permission_id\":141},{\"role_id\":1,\"permission_id\":167},{\"role_id\":1,\"permission_id\":168},{\"role_id\":1,\"permission_id\":196},{\"role_id\":1,\"permission_id\":197},{\"role_id\":1,\"permission_id\":198},{\"role_id\":1,\"permission_id\":199},{\"role_id\":1,\"permission_id\":200},{\"role_id\":1,\"permission_id\":201},{\"role_id\":1,\"permission_id\":202},{\"role_id\":1,\"permission_id\":203},{\"role_id\":1,\"permission_id\":204},{\"role_id\":1,\"permission_id\":205},{\"role_id\":1,\"permission_id\":206},{\"role_id\":1,\"permission_id\":207},{\"role_id\":1,\"permission_id\":208},{\"role_id\":1,\"permission_id\":209},{\"role_id\":1,\"permission_id\":210},{\"role_id\":1,\"permission_id\":211},{\"role_id\":1,\"permission_id\":241},{\"role_id\":1,\"permission_id\":242},{\"role_id\":1,\"permission_id\":243},{\"role_id\":1,\"permission_id\":244},{\"role_id\":1,\"permission_id\":323},{\"role_id\":1,\"permission_id\":324},{\"role_id\":1,\"permission_id\":325},{\"role_id\":1,\"permission_id\":326},{\"role_id\":1,\"permission_id\":327},{\"role_id\":1,\"permission_id\":328},{\"role_id\":1,\"permission_id\":329},{\"role_id\":1,\"permission_id\":330},{\"role_id\":1,\"permission_id\":331},{\"role_id\":1,\"permission_id\":332},{\"role_id\":1,\"permission_id\":333},{\"role_id\":2,\"permission_id\":1},{\"role_id\":2,\"permission_id\":2},{\"role_id\":2,\"permission_id\":3},{\"role_id\":2,\"permission_id\":5},{\"role_id\":2,\"permission_id\":6},{\"role_id\":2,\"permission_id\":7},{\"role_id\":2,\"permission_id\":8},{\"role_id\":2,\"permission_id\":9},{\"role_id\":2,\"permission_id\":10},{\"role_id\":2,\"permission_id\":11},{\"role_id\":2,\"permission_id\":12},{\"role_id\":2,\"permission_id\":13},{\"role_id\":2,\"permission_id\":15},{\"role_id\":2,\"permission_id\":16},{\"role_id\":2,\"permission_id\":17},{\"role_id\":2,\"permission_id\":18},{\"role_id\":2,\"permission_id\":19},{\"role_id\":2,\"permission_id\":20},{\"role_id\":2,\"permission_id\":21},{\"role_id\":2,\"permission_id\":22},{\"role_id\":2,\"permission_id\":23},{\"role_id\":2,\"permission_id\":24},{\"role_id\":2,\"permission_id\":25},{\"role_id\":2,\"permission_id\":26},{\"role_id\":2,\"permission_id\":27},{\"role_id\":2,\"permission_id\":28},{\"role_id\":2,\"permission_id\":29},{\"role_id\":2,\"permission_id\":30},{\"role_id\":2,\"permission_id\":31},{\"role_id\":2,\"permission_id\":32},{\"role_id\":2,\"permission_id\":33},{\"role_id\":2,\"permission_id\":34},{\"role_id\":2,\"permission_id\":36},{\"role_id\":2,\"permission_id\":42},{\"role_id\":2,\"permission_id\":51},{\"role_id\":2,\"permission_id\":58},{\"role_id\":3,\"permission_id\":1},{\"role_id\":3,\"permission_id\":2},{\"role_id\":3,\"permission_id\":3},{\"role_id\":3,\"permission_id\":5},{\"role_id\":3,\"permission_id\":6},{\"role_id\":3,\"permission_id\":7},{\"role_id\":3,\"permission_id\":8},{\"role_id\":3,\"permission_id\":9},{\"role_id\":3,\"permission_id\":10},{\"role_id\":3,\"permission_id\":11},{\"role_id\":3,\"permission_id\":12},{\"role_id\":3,\"permission_id\":13},{\"role_id\":3,\"permission_id\":14},{\"role_id\":3,\"permission_id\":15},{\"role_id\":3,\"permission_id\":16},{\"role_id\":3,\"permission_id\":17},{\"role_id\":3,\"permission_id\":18},{\"role_id\":3,\"permission_id\":19},{\"role_id\":3,\"permission_id\":20},{\"role_id\":3,\"permission_id\":21},{\"role_id\":3,\"permission_id\":22},{\"role_id\":3,\"permission_id\":32},{\"role_id\":3,\"permission_id\":34},{\"role_id\":3,\"permission_id\":36},{\"role_id\":3,\"permission_id\":42},{\"role_id\":3,\"permission_id\":51},{\"role_id\":3,\"permission_id\":58},{\"role_id\":4,\"permission_id\":5},{\"role_id\":4,\"permission_id\":16},{\"role_id\":4,\"permission_id\":23},{\"role_id\":4,\"permission_id\":24},{\"role_id\":4,\"permission_id\":26},{\"role_id\":4,\"permission_id\":27},{\"role_id\":4,\"permission_id\":28},{\"role_id\":4,\"permission_id\":29},{\"role_id\":4,\"permission_id\":30},{\"role_id\":4,\"permission_id\":31},{\"role_id\":5,\"permission_id\":5},{\"role_id\":5,\"permission_id\":16},{\"role_id\":5,\"permission_id\":21},{\"role_id\":5,\"permission_id\":22},{\"role_id\":5,\"permission_id\":23},{\"role_id\":5,\"permission_id\":24},{\"role_id\":5,\"permission_id\":25},{\"role_id\":5,\"permission_id\":26},{\"role_id\":5,\"permission_id\":27},{\"role_id\":5,\"permission_id\":28},{\"role_id\":5,\"permission_id\":29},{\"role_id\":5,\"permission_id\":30},{\"role_id\":5,\"permission_id\":31},{\"role_id\":6,\"permission_id\":5},{\"role_id\":6,\"permission_id\":16},{\"role_id\":6,\"permission_id\":18},{\"role_id\":6,\"permission_id\":20},{\"role_id\":6,\"permission_id\":36}],\"user_permissions\":[]}', 42, 'Administrateur', '2026-08-07 13:44:53');

-- --------------------------------------------------------

--
-- Structure de la table `receipt_verifications_log`
--

CREATE TABLE `receipt_verifications_log` (
  `id` int(11) NOT NULL,
  `verification_code` varchar(64) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `receipt_type` varchar(50) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `academic_year_id` int(11) DEFAULT NULL,
  `is_valid` tinyint(1) DEFAULT 0,
  `error_case` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_code` varchar(50) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `role_code`, `role_name`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', 'Super Administrateur', 'Accès complet absolu au système.', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:50'),
(2, 'admin', 'Administrateur', 'Administration classique et globale de l\'établissement.', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:50'),
(3, 'it_manager', 'IT Manager', 'Responsable de la configuration technique et pédagogique.', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:50'),
(4, 'caissier', 'Caissier', 'Gestionnaire des encaissements et versements quotidiens.', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:51'),
(5, 'comptable', 'Comptable', 'Responsable financier, des tarifs, bourses et bilans.', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:51'),
(6, 'enseignant', 'Enseignant', 'Enseignant accédant aux notes, absences et livrets.', 1, '2026-06-27 04:42:09', '2026-08-07 04:48:51'),
(13, 'direction_academique', 'Direction Académique', 'Gestionnaire académique autonome (Emplois du temps, Enseignants, Notes, Pilotage)', 1, '2026-08-11 04:20:32', '2026-08-11 04:20:32');

-- --------------------------------------------------------

--
-- Structure de la table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 29),
(1, 32),
(1, 33),
(1, 36),
(1, 37),
(1, 42),
(1, 51),
(1, 58),
(1, 74),
(1, 75),
(1, 76),
(1, 78),
(1, 80),
(1, 81),
(1, 82),
(1, 85),
(1, 86),
(1, 87),
(1, 108),
(1, 109),
(1, 113),
(1, 114),
(1, 116),
(1, 118),
(1, 121),
(1, 122),
(1, 123),
(1, 124),
(1, 140),
(1, 141),
(1, 167),
(1, 168),
(1, 196),
(1, 197),
(1, 198),
(1, 199),
(1, 200),
(1, 201),
(1, 202),
(1, 203),
(1, 204),
(1, 205),
(1, 206),
(1, 207),
(1, 208),
(1, 209),
(1, 210),
(1, 211),
(1, 241),
(1, 242),
(1, 243),
(1, 244),
(1, 323),
(1, 324),
(1, 325),
(1, 326),
(1, 327),
(1, 328),
(1, 329),
(1, 330),
(1, 331),
(1, 332),
(1, 333),
(2, 1),
(2, 2),
(2, 3),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(2, 15),
(2, 16),
(2, 17),
(2, 18),
(2, 19),
(2, 20),
(2, 21),
(2, 22),
(2, 23),
(2, 24),
(2, 25),
(2, 26),
(2, 27),
(2, 28),
(2, 29),
(2, 30),
(2, 31),
(2, 32),
(2, 33),
(2, 34),
(2, 36),
(2, 42),
(2, 51),
(2, 58),
(3, 1),
(3, 2),
(3, 3),
(3, 5),
(3, 6),
(3, 7),
(3, 8),
(3, 9),
(3, 10),
(3, 11),
(3, 12),
(3, 13),
(3, 14),
(3, 15),
(3, 16),
(3, 17),
(3, 18),
(3, 19),
(3, 20),
(3, 21),
(3, 22),
(3, 32),
(3, 34),
(3, 36),
(3, 42),
(3, 51),
(3, 58),
(4, 5),
(4, 16),
(4, 23),
(4, 24),
(4, 26),
(4, 27),
(4, 28),
(4, 29),
(4, 30),
(4, 31),
(5, 5),
(5, 16),
(5, 21),
(5, 22),
(5, 23),
(5, 24),
(5, 25),
(5, 26),
(5, 27),
(5, 28),
(5, 29),
(5, 30),
(5, 31),
(6, 5),
(6, 16),
(6, 18),
(6, 20),
(6, 36),
(13, 2),
(13, 5),
(13, 6),
(13, 7),
(13, 8),
(13, 9),
(13, 10),
(13, 11),
(13, 12),
(13, 13),
(13, 15),
(13, 18),
(13, 19),
(13, 20),
(13, 21),
(13, 22),
(13, 32),
(13, 33),
(13, 34),
(13, 36),
(13, 42),
(13, 51),
(13, 122),
(13, 123);

-- --------------------------------------------------------

--
-- Structure de la table `school_fees`
--

CREATE TABLE `school_fees` (
  `id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `cycle_id` int(11) DEFAULT NULL,
  `teaching_type_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `school_fees`
--

INSERT INTO `school_fees` (`id`, `academic_year_id`, `class_id`, `cycle_id`, `teaching_type_id`, `amount`, `created_at`) VALUES
(1, 3, 93, NULL, NULL, 100000.00, '2026-06-27 05:36:39'),
(2, 3, 86, NULL, NULL, 100000.00, '2026-07-08 22:12:20'),
(3, 3, 76, NULL, NULL, 70000.00, '2026-07-09 10:15:11'),
(6, 3, 106, NULL, NULL, 0.00, '2026-07-25 17:02:25'),
(7, 3, 107, NULL, NULL, 700000.00, '2026-07-25 17:02:25');

-- --------------------------------------------------------

--
-- Structure de la table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sections`
--

INSERT INTO `sections` (`id`, `nom`, `created_at`, `status`) VALUES
(1, 'Francophone', '2026-03-25 19:39:48', 1),
(2, 'Anglophone', '2026-03-25 19:40:00', 1),
(11, 'Bilingue', '2026-07-25 01:56:10', 0);

-- --------------------------------------------------------

--
-- Structure de la table `sequences`
--

CREATE TABLE `sequences` (
  `id` int(11) NOT NULL,
  `teaching_type_id` int(11) DEFAULT NULL,
  `code` varchar(20) NOT NULL,
  `label` varchar(100) NOT NULL,
  `short_label` varchar(20) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `trimestre` tinyint(4) NOT NULL,
  `position` tinyint(4) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `academic_year_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sequences`
--

INSERT INTO `sequences` (`id`, `teaching_type_id`, `code`, `label`, `short_label`, `start_date`, `end_date`, `trimestre`, `position`, `is_active`, `academic_year_id`) VALUES
(1, 3, 'SEQ1', 'Sequence 1', 'SEQUENCE 1', NULL, NULL, 1, 1, 1, 2),
(2, 3, 'SEQ2', 'Sequence 2', 'SEQUENCE 2', NULL, NULL, 1, 3, 1, 2),
(3, 3, 'SEQ3', 'Sequence 3', 'SEQUENCE 3', NULL, NULL, 2, 4, 0, 2),
(4, 3, 'SEQ4', 'Sequence 4', 'SEQUENCE  4', NULL, NULL, 2, 5, 0, 2),
(5, 3, 'SEQ5', 'Sequence 5', 'SEQUENCE 5', NULL, NULL, 3, 6, 0, 2),
(6, 3, 'SEQ6', 'Sequence 6', 'SEQUENCE 6', NULL, NULL, 3, 7, 0, 2),
(25247, 3, 'CC 1', 'Contrôle Continu 1', 'CC 1', NULL, NULL, 1, 0, 0, NULL),
(25417, 9, 'CC1', 'Contrôle - Continue 1', 'CC1', '2026-01-01', '2026-03-07', 1, 1, 1, NULL),
(25418, 9, 'CC02', 'Contrôle - Continue 2', 'CC2', NULL, NULL, 1, 2, 1, NULL),
(25419, 9, 'SN01', 'Session Normale 1', 'SN 1', NULL, NULL, 1, 3, 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `teaching_type_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`, `teaching_type_id`) VALUES
('allow_teacher_registration', '0', 0),
('backup_enabled', '1', 0),
('backup_github_auth', 'ssh', 0),
('backup_github_branch', 'main', 0),
('backup_github_owner', 'evarice13133', 0),
('backup_github_repository', 'notesmaster-backups', 0),
('backup_git_user_email', 'backup-bot@notesmaster.local', 0),
('backup_git_user_name', 'NotesMaster Backup Bot', 0),
('backup_git_worktree', 'storage/backup-repository', 0),
('backup_push_enabled', '1', 0),
('backup_retention_count', '12', 0),
('backup_schedule_day', 'Sunday', 0),
('backup_schedule_time', '02:00', 0),
('backup_storage_path', 'storage/backups', 0),
('bulletin_printing_enabled', '1', 1),
('bulletin_printing_enabled', '1', 2),
('bulletin_printing_enabled', '1', 3),
('bulletin_printing_enabled', '1', 9),
('creation_decree', 'Arrêté n°  08/0269/MINESUP du 03 octobre 2008; En partenariat avec des Universités étrangers, des ONG Internationales et des Organisations Professionnelles', 3),
('creation_decree', 'Arrêté n°  08/0269/MINESUP du 03 octobre 2008; En partenariat avec des Universités étrangers, des ONG Internationales et des Organisations Professionnelles', 9),
('display_school_year', '2026-2027', 1),
('display_school_year', '2026-2027', 2),
('display_school_year', '2025-2026', 3),
('display_school_year', '2026-2027', 9),
('honor_roll_default_threshold', '12', 1),
('honor_roll_default_threshold', '12', 2),
('honor_roll_default_threshold', '12', 3),
('honor_roll_default_threshold', '12', 9),
('honor_roll_threshold_class_101', '', 0),
('honor_roll_threshold_class_104', '12', 0),
('honor_roll_threshold_class_105', '12', 0),
('honor_roll_threshold_class_106', '', 0),
('honor_roll_threshold_class_107', '', 0),
('honor_roll_threshold_class_113', '', 0),
('honor_roll_threshold_class_120', '', 0),
('honor_roll_threshold_class_121', '', 0),
('honor_roll_threshold_class_122', '', 0),
('honor_roll_threshold_class_16', '', 0),
('honor_roll_threshold_class_2', '', 0),
('honor_roll_threshold_class_20', '', 0),
('honor_roll_threshold_class_24', '', 0),
('honor_roll_threshold_class_3', '', 0),
('honor_roll_threshold_class_33', '12', 0),
('honor_roll_threshold_class_34', '', 0),
('honor_roll_threshold_class_41', '', 0),
('honor_roll_threshold_class_43', '12', 0),
('honor_roll_threshold_class_44', '12', 0),
('honor_roll_threshold_class_45', '12', 0),
('honor_roll_threshold_class_48', '', 0),
('honor_roll_threshold_class_56', '', 0),
('honor_roll_threshold_class_57', '12', 0),
('honor_roll_threshold_class_58', '', 0),
('honor_roll_threshold_class_61', '12', 0),
('honor_roll_threshold_class_62', '', 0),
('honor_roll_threshold_class_70', '', 0),
('honor_roll_threshold_class_71', '', 0),
('honor_roll_threshold_class_73', '12', 0),
('honor_roll_threshold_class_75', '', 0),
('honor_roll_threshold_class_77', '12', 0),
('honor_roll_threshold_class_78', '12', 0),
('honor_roll_threshold_class_79', '12', 0),
('honor_roll_threshold_class_80', '12', 0),
('honor_roll_threshold_class_81', '12', 0),
('honor_roll_threshold_class_82', '12', 0),
('honor_roll_threshold_class_84', '12', 0),
('honor_roll_threshold_class_85', '12', 0),
('honor_roll_threshold_class_86', '12', 0),
('honor_roll_threshold_class_87', '12', 0),
('honor_roll_threshold_class_88', '12', 0),
('honor_roll_threshold_class_89', '12', 0),
('honor_roll_threshold_class_90', '12', 0),
('honor_roll_threshold_class_91', '12', 0),
('honor_roll_threshold_class_92', '12', 0),
('honor_roll_threshold_class_93', '', 0),
('honor_roll_threshold_cycle_15', '', 0),
('honor_roll_threshold_cycle_16', '', 0),
('honor_roll_threshold_cycle_17', '', 0),
('honor_roll_threshold_cycle_18', '', 0),
('matricule_counter', '3932', 0),
('matricule_format', '{SCHOOL_CODE}-{CLASS}-MT{COUNTER}', 0),
('payment_methods', 'Espèces,Mobile Money,Orange Money,MTN Mobile Money,Carte bancaire,Virement bancaire,Chèque,Autre', 1),
('payment_methods', 'Espèces,Mobile Money,Orange Money,MTN Mobile Money,Carte bancaire,Virement bancaire,Chèque,Autre', 2),
('payment_methods', 'Espèces,Mobile Money,Orange Money,MTN Mobile Money,Carte bancaire,Virement bancaire,Chèque,Autre', 3),
('payment_methods', 'Espèces,Mobile Money,Orange Money,MTN Mobile Money,Carte bancaire,Virement bancaire,Chèque,Autre', 9),
('principal_name', 'EFFION OKON AKAISO', 1),
('principal_name', 'nom du directeur', 2),
('principal_name', 'Director or principal name', 3),
('principal_name', 'Noutat Bertrand', 9),
('principal_signature', '', 1),
('principal_signature', '', 2),
('principal_signature', '', 3),
('principal_signature', '', 9),
('principal_title', 'PRINCIPALee', 1),
('principal_title', 'Directeur', 2),
('principal_title', 'PRINCIPALe', 3),
('principal_title', 'Directeur de Campus', 9),
('rbac_version', '1786424664', 0),
('registration_fee_policy', 'by_status', 1),
('registration_fee_policy', 'by_status', 2),
('registration_fee_policy', 'by_status', 3),
('registration_fee_policy', 'by_status', 9),
('school_city', 'Douala PK12', 1),
('school_city', 'Douala PK12', 2),
('school_city', 'Douala PK12', 3),
('school_city', 'Douala Bwadibo', 9),
('school_code', 'CMR-COL', 0),
('school_code', 'GSB', 1),
('school_code', 'IMT', 2),
('school_code', 'FUTURA', 3),
('school_code', 'ISTEC', 9),
('school_email', 'fotsomarietherese2024@gmail.com', 1),
('school_email', 'fotsomarietherese2024@gmail.com', 2),
('school_email', 'fotsomarietherese2024@gmail.com', 3),
('school_email', 'istec.cmr@gmail.com', 9),
('school_fax', '656963491', 1),
('school_fax', '656963491', 2),
('school_fax', '656963491', 3),
('school_fax', '656963491', 9),
('school_logo', '/public/uploads/1784999175_cachet_La_solution_intelligente_1.png', 1),
('school_logo', '/public/uploads/1784999175_cachet_La_solution_intelligente_1.png', 2),
('school_logo', '/public/uploads/1787277336_logo-camertech.png', 3),
('school_logo', '/public/uploads/1785483408_logooooo3.jpg', 9),
('school_ministry', 'Ministère des Enseignements Primaire', 1),
('school_ministry', 'Ministère des Enseignements Primaire', 2),
('school_ministry', 'Ministère des Enseignements Secondaires', 3),
('school_ministry', 'Ministère des Enseignements Superieur', 9),
('school_ministry_en', 'Ministry of Primary Education', 1),
('school_ministry_en', 'Ministry of primary Education', 2),
('school_ministry_en', 'Ministry of Secondary Education', 3),
('school_ministry_en', 'Ministry of Hight Education', 9),
('school_motto', 'Paix - Travail - Patrie', 1),
('school_motto', 'Paix - Travail - Patrie', 2),
('school_motto', 'Paix - Travail - Patrie', 3),
('school_motto', 'Paix - Travail - Patrie', 9),
('school_motto_en', 'Peace - Work - Fatherland', 1),
('school_motto_en', 'Peace - Work - Fatherland', 2),
('school_motto_en', 'Peace - Work - Fatherland', 3),
('school_motto_en', 'Peace - Work - Fatherland', 9),
('school_name', 'Nom de votre ecole Maternelle', 1),
('school_name', 'nom de votre etablissement primaire', 2),
('school_name', 'College polyvalent Bilingue FUTURA', 3),
('school_name', 'Institut Supérieur des Techniques Economiques et Comptables', 9),
('school_phone', '686061923/696007229', 1),
('school_phone', '686061923/696007229', 2),
('school_phone', '686061923/696007229', 3),
('school_phone', '686061923/696007229', 9),
('school_po_box', '51442', 1),
('school_po_box', '51442', 2),
('school_po_box', '51442', 3),
('school_po_box', '51442', 9),
('school_republic', 'Republique du Cameroun', 1),
('school_republic', 'Republique du Cameroun', 2),
('school_republic', 'Republique du Cameroun', 3),
('school_republic', 'Republique du Cameroun', 9),
('school_republic_en', 'Republic of Cameroon', 1),
('school_republic_en', 'Republic of Cameroon', 2),
('school_republic_en', 'Republic of Cameroon', 3),
('school_republic_en', 'Republic of Cameroon', 9),
('school_slogan', 'Discipline - Travail - Succes', 1),
('school_slogan', 'Discipline - Travail - Succes', 2),
('school_slogan', 'Discipline - Travail - Succes', 3),
('school_slogan', 'Discipline - Travail - Succes', 9),
('school_slogan_en', 'Discipline - Work - Success', 1),
('school_slogan_en', 'Discipline - Work - Success', 2),
('school_slogan_en', 'Discipline - Work - Success', 3),
('school_slogan_en', 'Discipline - Work - Success', 9),
('school_stamp', '', 1),
('school_stamp', '', 2),
('school_stamp', '', 3),
('school_stamp', '', 9),
('school_website', 'https://copobimat.camertech.com', 1),
('school_website', 'https://copobimat.camertech.com', 2),
('school_website', 'https://futura.camertech.com', 3),
('school_website', 'https://istec-educations.com', 9),
('show_teacher_names_on_bulletins', '1', 0),
('theme_admin_hero_card', '#5d7894', 0),
('theme_admin_hero_end', '#2f6fed', 0),
('theme_admin_hero_glow', '#f4b942', 0),
('theme_admin_hero_start', '#16324f', 0),
('theme_button_bg', '#036305', 0),
('theme_button_text', '#ffffff', 0),
('theme_glow_bg', '#eef4fb', 0),
('theme_glow_text', '#1c4169', 0),
('theme_login_bg_end', '#eaebea', 0),
('theme_login_bg_mid', '#16324f', 0),
('theme_login_bg_start', '#018356', 0),
('theme_login_bubble', '#3a2f18', 0),
('theme_login_button', '#4e3687', 0),
('theme_login_panel_badge_bg', '#000000', 0),
('theme_login_panel_badge_text', '#1f5fbf', 0),
('theme_login_panel_bg', '#aaa1a1', 0),
('theme_login_showcase_end', '#143961', 0),
('theme_login_showcase_glow', '#f4b942', 0),
('theme_login_showcase_start', '#102033', 0),
('theme_navbar_bg', '#00a81c', 0),
('theme_navbar_hover', '#ffffff', 0),
('theme_teacher_hero_card', '#5d7894', 0),
('theme_teacher_hero_end', '#2f6fed', 0),
('theme_teacher_hero_glow', '#f4b942', 0),
('theme_teacher_hero_start', '#16324f', 0),
('tutelage_logo', '/public/uploads/1786501805_dschang.jpg', 3),
('tutelage_logo', '/public/uploads/1785484398_dschang.jpg', 9);

-- --------------------------------------------------------

--
-- Structure de la table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(150) DEFAULT NULL,
  `is_redoublant` tinyint(1) NOT NULL DEFAULT 0,
  `is_withdrawn` tinyint(1) NOT NULL DEFAULT 0,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `photo_eleve` varchar(255) DEFAULT NULL,
  `parent_contact` varchar(50) DEFAULT NULL,
  `guardian_contact` varchar(50) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `class_id` int(11) DEFAULT NULL,
  `sexe` varchar(20) DEFAULT NULL,
  `academic_year_id` bigint(20) UNSIGNED DEFAULT NULL,
  `teaching_type_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `status` enum('Non inscrit','Inscrit','Démissionnaire','Archivé') NOT NULL DEFAULT 'Inscrit'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `students`
--

INSERT INTO `students` (`id`, `nom`, `prenom`, `date_naissance`, `lieu_naissance`, `is_redoublant`, `is_withdrawn`, `actif`, `photo_eleve`, `parent_contact`, `guardian_contact`, `adresse`, `email`, `created_at`, `class_id`, `sexe`, `academic_year_id`, `teaching_type_id`, `created_by`, `status`) VALUES
(404, 'TAMAFO', 'Jordan', NULL, NULL, 0, 0, 1, NULL, NULL, NULL, NULL, '2325242', '2026-08-21 04:30:04', 76, 'M', 3, 3, 40, 'Inscrit');

-- --------------------------------------------------------

--
-- Structure de la table `student_discounts`
--

CREATE TABLE `student_discounts` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `discount_type_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `amount_type` enum('fixed','percentage') NOT NULL,
  `motive` varchar(255) NOT NULL,
  `date_effet` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `commentaire` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `student_installments`
--

CREATE TABLE `student_installments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `installment_number` int(11) NOT NULL,
  `amount_planned` decimal(15,2) NOT NULL,
  `amount_paid` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `student_installments`
--

INSERT INTO `student_installments` (`id`, `student_id`, `academic_year_id`, `installment_number`, `amount_planned`, `amount_paid`, `created_at`, `updated_at`) VALUES
(4, 2, 3, 1, 60000.00, 0.00, '2026-07-30 23:58:39', '2026-07-30 23:58:39'),
(5, 2, 3, 2, 30000.00, 0.00, '2026-07-30 23:58:39', '2026-07-30 23:58:39'),
(6, 2, 3, 3, 10000.00, 0.00, '2026-07-30 23:58:39', '2026-07-30 23:58:39'),
(10, 1, 3, 1, 0.00, 0.00, '2026-08-06 01:31:38', '2026-08-06 01:31:38'),
(11, 1, 3, 2, 0.00, 0.00, '2026-08-06 01:31:38', '2026-08-06 01:31:38'),
(12, 1, 3, 3, 0.00, 0.00, '2026-08-06 01:31:38', '2026-08-06 01:31:38'),
(158, 404, 3, 1, 40000.00, 0.00, '2026-08-21 04:30:05', '2026-08-21 04:30:05'),
(159, 404, 3, 2, 20000.00, 0.00, '2026-08-21 04:30:05', '2026-08-21 04:30:05'),
(160, 404, 3, 3, 10000.00, 0.00, '2026-08-21 04:30:05', '2026-08-21 04:30:05');

-- --------------------------------------------------------

--
-- Structure de la table `student_payments`
--

CREATE TABLE `student_payments` (
  `id` int(11) NOT NULL,
  `parent_payment_id` int(11) DEFAULT NULL,
  `student_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `observation` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'valide',
  `cancelled_by` int(11) DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancellation_motive` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `student_payment_allocations`
--

CREATE TABLE `student_payment_allocations` (
  `id` int(11) NOT NULL,
  `student_payment_id` int(11) NOT NULL,
  `student_installment_id` int(11) NOT NULL,
  `amount_allocated` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `student_scholarships`
--

CREATE TABLE `student_scholarships` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `discount_type_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `amount_type` enum('fixed','percentage') NOT NULL,
  `motive` varchar(255) NOT NULL,
  `date_effet` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `commentaire` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `coefficient` int(11) DEFAULT 1,
  `groupe` varchar(20) NOT NULL DEFAULT 'Groupe 1',
  `code_uv` varchar(50) DEFAULT NULL,
  `code_ue` varchar(50) DEFAULT NULL,
  `subject_group_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) DEFAULT 1,
  `teaching_type_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `vhm` decimal(8,2) DEFAULT NULL,
  `vhp` decimal(8,2) DEFAULT NULL,
  `th_max` decimal(8,2) DEFAULT NULL,
  `observations` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `subjects`
--

INSERT INTO `subjects` (`id`, `nom`, `coefficient`, `groupe`, `code_uv`, `code_ue`, `subject_group_id`, `created_at`, `status`, `teaching_type_id`, `department_id`, `vhm`, `vhp`, `th_max`, `observations`) VALUES
(15, 'LITTÉRATURE', 4, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 04:40:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(16, 'INFORMATIQUE', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 06:00:29', 1, 3, NULL, NULL, NULL, NULL, NULL),
(17, 'LANGUE FRANçAISE', 2, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 06:00:29', 1, 3, NULL, NULL, NULL, NULL, NULL),
(18, 'PHILISOPHIE', 2, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 06:00:29', 1, 3, NULL, NULL, NULL, NULL, NULL),
(19, 'PHILISOPHIE', 4, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 06:00:29', 1, 3, NULL, NULL, NULL, NULL, NULL),
(20, 'ANGLAIS', 4, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 06:00:29', 1, 3, NULL, NULL, NULL, NULL, NULL),
(21, 'LANGUE VIVANTE II', 3, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 06:00:29', 1, 3, NULL, NULL, NULL, NULL, NULL),
(22, 'GEOGRAPHIE', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 06:00:29', 1, 3, NULL, NULL, NULL, NULL, NULL),
(23, 'HISTOIRE', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 06:00:29', 1, 3, NULL, NULL, NULL, NULL, NULL),
(24, 'ECM', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 06:00:29', 1, 3, NULL, NULL, NULL, NULL, NULL),
(25, 'MATHEMATIQUES', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 06:00:29', 1, 3, NULL, NULL, NULL, NULL, NULL),
(26, 'SCIENCES', 1, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 06:00:29', 1, 3, NULL, NULL, NULL, NULL, NULL),
(27, 'TM', 1, 'Groupe 3', NULL, NULL, NULL, '2026-05-06 18:24:29', 1, 3, NULL, NULL, NULL, NULL, NULL),
(28, 'LANGUES NATIONALLES', 1, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 0, 3, NULL, NULL, NULL, NULL, NULL),
(30, 'EDUCATION ARTISTIQUE', 1, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 0, 3, NULL, NULL, NULL, NULL, NULL),
(31, 'TRAVAIL MANUEL', 1, 'Groupe 3', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(32, 'EPS', 2, 'Groupe 3', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(33, 'MATHEMATIQUES', 5, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(34, 'MATHEMATIQUES', 6, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(35, 'MATHEMATIQUES', 7, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(36, 'PHYSIQUE', 3, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(37, 'PHYSIQUE', 4, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(38, 'CHIMIE', 3, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(39, 'CHIMIE', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(40, 'INFORMATIQUE', 3, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(41, 'INFORMATIQUE', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(42, 'INFORMATIQUE', 4, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(43, 'SVTEEHB', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(44, 'LITTÉRATURE', 2, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(45, 'LANGUE FRANçAISE', 1, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(46, 'ANGLAIS', 3, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(47, 'SVTEEHB', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(48, 'PHILOSOPHIE', 1, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(49, 'PHILOSOPHIE', 2, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(50, 'HISTOIRE', 2, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(51, 'GEOGRAPHIE', 2, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(52, 'ECM', 1, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(53, 'SVTEEHB', 6, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(54, 'MATHEMATIQUES', 4, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(55, 'CHIMIE', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(56, 'INFORMATIQUE', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(57, 'PHYSIQUE', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(58, 'HISTOIRE', 2, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(59, 'GEOGRAPHIE', 2, 'Groupe 1', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(60, 'EPS', 2, 'Groupe 3', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(61, 'TRAVAIL MANUEL', 1, 'Groupe 3', NULL, NULL, NULL, '2026-05-06 23:11:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(63, 'Histoire/Géographie', 2, 'Groupe 1', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(64, 'Anglais', 2, 'Groupe 1', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(65, 'Economie d\'entreprise', 1, 'Groupe 1', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(66, 'Français', 3, 'Groupe 1', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(67, 'Mathematique Générale', 3, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(68, 'Redaction Professionnelle', 3, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(70, 'Droit', 2, 'Groupe 1', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(71, 'ECM', 1, 'Groupe 1', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(72, 'Economie générale', 2, 'Groupe 1', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(73, 'GIF', 3, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(74, 'EPS', 1, 'Groupe 3', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(75, 'Travail Manuel', 1, 'Groupe 3', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(76, 'Mathematiques', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(77, 'Finance d\'entreprise', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(78, 'Mathématiques Appliquées', 4, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(79, 'Management', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(80, 'Philosophie', 2, 'Groupe 1', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(81, 'MOB', 3, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(82, 'RPC', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(83, 'OTA', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(84, 'Prise de parole rapide', 3, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(85, 'TAS', 3, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(86, 'Bureautique', 3, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(87, 'Comptabilité', 3, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 08:52:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(88, 'Statistiques', 2, 'Groupe 2', NULL, NULL, NULL, '2026-05-28 13:31:36', 1, 3, NULL, NULL, NULL, NULL, NULL),
(89, 'Examen de Laboratoire', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 11:07:49', 1, 3, NULL, NULL, NULL, NULL, NULL),
(90, 'Gestion des ressources humaines', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 11:08:16', 1, 3, NULL, NULL, NULL, NULL, NULL),
(91, 'GSS', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 11:08:35', 1, 3, NULL, NULL, NULL, NULL, NULL),
(92, 'Santé publique', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 11:09:04', 1, 3, NULL, NULL, NULL, NULL, NULL),
(93, 'Soins infirmiers', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 11:09:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(94, 'Terminologie Médical', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 11:10:07', 1, 3, NULL, NULL, NULL, NULL, NULL),
(95, 'BPPH', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 11:10:33', 1, 3, NULL, NULL, NULL, NULL, NULL),
(96, 'Bureautique', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 11:11:13', 1, 3, NULL, NULL, NULL, NULL, NULL),
(97, 'Science physique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 11:12:25', 1, 3, NULL, NULL, NULL, NULL, NULL),
(98, 'Action sociale', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 11:13:36', 1, 3, NULL, NULL, NULL, NULL, NULL),
(234, 'Metier et formation', 1, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(236, 'Connaissance des materiaux', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(237, 'Dessin technique', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(238, 'EPS', 2, 'Groupe 3', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(239, 'Travail manuel', 1, 'Groupe 3', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(240, 'Français', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(241, 'Anglais', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(242, 'ECM', 1, 'Groupe 1', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(243, 'Informatique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(244, 'Mathematiques', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(245, 'Sciences physique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(246, 'Sante, securite, et envirronnement', 1, 'Groupe 3', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(247, 'Materiaux', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(248, 'Ajustage', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(249, 'RESEO', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(250, 'Technologie professionnel', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(251, 'Tracage', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(252, 'RESEO', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(253, 'Schema electrique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(256, 'Technologie', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(257, 'Machine electrique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(258, 'Dessin technique', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(259, 'Entrepreneuriat', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(260, 'EPS', 1, 'Groupe 3', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(261, 'Travail Manuel', 1, 'Groupe 3', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(262, 'Dessin Industriel', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(263, 'Exploitation', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(264, 'Mecanique Appliquée', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(265, 'Métré', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(266, 'Procédé de Construction', 8, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(267, 'Réglementation', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(268, 'Topographie', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(269, 'Anglais', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(270, 'ECM', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(271, 'HISTOIRE/Géographie', 1, 'Groupe 1', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(272, 'INFORMATIQUE', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(273, 'Mathematiques', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(274, 'Français', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(275, 'Sciences Physiques', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(276, 'Dessin Technique', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(277, 'Entrepreneuriat', 2, 'Groupe 3', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(278, 'Montage', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(279, 'TPFA', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(280, 'Travaux Pratique', 9, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(281, 'Mecanique Appliquée', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(282, 'PPRO', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(283, 'Traçage', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(284, 'Mecanique Appliquée', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(285, 'Laboratoire', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(286, 'MPN', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(287, 'Maintenance', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(288, 'Travaux Pratique', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(289, 'Technologie des Materiaux', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(290, 'Processus Previsionnel', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(291, 'SCHEMA ELECTRIQUE', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(292, 'Electronique et electrotechnique', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(293, 'Technologie Schema', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(294, 'Sols et Materiaux', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(295, 'ANALYSE', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(296, 'Dessin de Mode', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(297, 'PATRONAGE', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(298, 'Dessin Technique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(299, 'ECM', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(300, 'Technologie', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(301, 'Mathematique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-04 12:11:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(302, 'Dessin de Mode', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 11:14:19', 1, 3, NULL, NULL, NULL, NULL, NULL),
(303, 'Coupe', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 11:14:55', 1, 3, NULL, NULL, NULL, NULL, NULL),
(304, 'Couture', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 11:15:21', 1, 3, NULL, NULL, NULL, NULL, NULL),
(305, 'Hygiène', 1, 'Groupe 3', NULL, NULL, NULL, '2026-06-05 11:22:42', 1, 3, NULL, NULL, NULL, NULL, NULL),
(306, 'Bureautique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 11:27:46', 1, 3, NULL, NULL, NULL, NULL, NULL),
(307, 'Rédaction Professionnel', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 11:28:38', 1, 3, NULL, NULL, NULL, NULL, NULL),
(308, 'PRP', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 11:29:48', 1, 3, NULL, NULL, NULL, NULL, NULL),
(309, 'DCC', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 11:30:11', 1, 3, NULL, NULL, NULL, NULL, NULL),
(310, 'OTA', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 11:30:37', 1, 3, NULL, NULL, NULL, NULL, NULL),
(311, 'Commerce', 1, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 11:32:11', 1, 3, NULL, NULL, NULL, NULL, NULL),
(312, 'Législation du travail', 1, 'Groupe 1', NULL, NULL, NULL, '2026-06-05 11:34:45', 1, 3, NULL, NULL, NULL, NULL, NULL),
(314, 'Pratique comptable', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 11:36:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(315, 'Travaux comptable', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 11:36:48', 1, 3, NULL, NULL, NULL, NULL, NULL),
(316, 'Hygiène', 1, 'Groupe 3', NULL, NULL, NULL, '2026-06-05 11:38:17', 1, 3, NULL, NULL, NULL, NULL, NULL),
(317, 'Mathematique Commerciale', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 11:46:33', 1, 3, NULL, NULL, NULL, NULL, NULL),
(319, 'Dessin technique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 12:11:50', 1, 3, NULL, NULL, NULL, NULL, NULL),
(320, 'MOB', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 13:23:51', 1, 3, NULL, NULL, NULL, NULL, NULL),
(321, 'Terminologie Médical', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 13:26:03', 1, 3, NULL, NULL, NULL, NULL, NULL),
(322, 'Deontologie', 1, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 13:42:14', 1, 3, NULL, NULL, NULL, NULL, NULL),
(323, 'GEH', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 13:43:59', 1, 3, NULL, NULL, NULL, NULL, NULL),
(325, 'Technologie Electrique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 19:43:24', 1, 3, NULL, NULL, NULL, NULL, NULL),
(326, 'Technologie Mecanique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 19:44:02', 1, 3, NULL, NULL, NULL, NULL, NULL),
(327, 'Reception d\'un vehicule', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 19:46:20', 1, 3, NULL, NULL, NULL, NULL, NULL),
(328, 'Moteur CI', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 19:46:55', 1, 3, NULL, NULL, NULL, NULL, NULL),
(329, 'Procedes de Realisation', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 20:02:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(330, 'Devis et estimation', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-05 20:10:35', 1, 3, NULL, NULL, NULL, NULL, NULL),
(331, 'Financial Accounting', 1, 'Groupe 1', NULL, NULL, NULL, '2026-06-05 20:58:36', 1, 3, NULL, NULL, NULL, NULL, NULL),
(333, 'Business Managemennt', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 08:38:16', 1, 3, NULL, NULL, NULL, NULL, NULL),
(334, 'Economic', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 08:39:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(335, 'Corporate Accounting', 5, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 08:45:01', 1, 3, NULL, NULL, NULL, NULL, NULL),
(336, 'Business Maths', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(337, 'Commerce And financial', 1, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(338, 'Entrepreuneurship', 5, 'Groupe 3', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(341, 'Economic', 5, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(342, 'Geography', 5, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(343, 'ICT', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(344, 'English', 5, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(345, 'Pure Ma thematics and statistics', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(346, 'Biology', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(347, 'Statistics', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(348, 'Chemistry', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(349, 'Physic', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(350, 'French', 5, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(351, 'Citizenship', 2, 'Groupe 3', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(352, 'Mathematics', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(353, 'Literature in  English', 5, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(354, 'History', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(355, 'Food and nutrition', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(357, 'ENGLISH', 4, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(359, 'FRENCH', 4, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(363, 'CITIZENSHIP EDUCATION', 2, 'Groupe 3', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(364, 'COMPUTER STUDIES', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(365, 'BIOLOGY', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(366, 'CHEMISTRY', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(367, 'MATHEMATICS', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(368, 'PHYSICS', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(370, 'ENGLISH', 4, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(372, 'FRENCH', 4, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(376, 'CITIZENSHIP EDUCATION', 2, 'Groupe 3', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(377, 'COMPUTER STUDIES', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(379, 'CHEMISTRY', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(380, 'MATHEMATICS', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(381, 'PHYSICS', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(382, 'C.S. (General Science)', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(383, 'ENGLISH', 4, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(384, 'LITERATURE', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(385, 'FRENCH', 4, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(386, 'GEOGRAPHY', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(387, 'ECONOMICS', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(388, 'HISTORY', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(389, 'CITIZENSHIP EDUCATION', 3, 'Groupe 3', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(390, 'COMPUTER STUDIES', 1, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(391, 'BIOLOGY', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(392, 'CHEMISTRY', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(393, 'MATHEMATICS', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(394, 'PHYSICS', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(395, 'C.S. (General Science)', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(396, 'F/N/H/BIO (combinée)', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(397, 'A.M.A / LOGIC / PHIL  (selon options)', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(398, 'ELECTRICAL TECHNOLOGIE AND DIASRAM', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(399, 'ELECTRICAL TEST AND MEASUREMENT', 1, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(400, 'ELCTRICAL MACHINE', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(401, 'ELECTRICAL AND ELECTRONIQUE CIRCUITS', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 10:42:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(403, 'ECM', 1, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 12:09:16', 1, 3, NULL, NULL, NULL, NULL, NULL),
(405, 'Hygiène', 1, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 12:14:33', 1, 3, NULL, NULL, NULL, NULL, NULL),
(406, 'Travail manuel', 1, 'Groupe 3', NULL, NULL, NULL, '2026-06-06 12:15:51', 1, 3, NULL, NULL, NULL, NULL, NULL),
(407, 'Mathematique', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 12:24:02', 1, 3, NULL, NULL, NULL, NULL, NULL),
(408, 'Mathematique', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 12:24:30', 1, 3, NULL, NULL, NULL, NULL, NULL),
(409, 'Informatique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 12:25:15', 1, 3, NULL, NULL, NULL, NULL, NULL),
(410, 'Anglais', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 12:25:57', 1, 3, NULL, NULL, NULL, NULL, NULL),
(411, 'Sciences physique', 1, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 12:27:05', 1, 3, NULL, NULL, NULL, NULL, NULL),
(412, 'Français', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 12:28:03', 1, 3, NULL, NULL, NULL, NULL, NULL),
(413, 'Législation', 1, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 13:14:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(414, 'Materiaux', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 21:40:19', 1, 3, NULL, NULL, NULL, NULL, NULL),
(415, 'Dessin', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 21:41:39', 1, 3, NULL, NULL, NULL, NULL, NULL),
(416, 'Dessin', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 21:59:07', 1, 3, NULL, NULL, NULL, NULL, NULL),
(417, 'Français', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-06 22:09:59', 1, 3, NULL, NULL, NULL, NULL, NULL),
(418, 'Informatique', 1, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 22:11:26', 1, 3, NULL, NULL, NULL, NULL, NULL),
(419, 'Travail manuel', 1, 'Groupe 3', NULL, NULL, NULL, '2026-06-06 22:21:08', 1, 3, NULL, NULL, NULL, NULL, NULL),
(420, 'Science physique', 1, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 22:24:14', 1, 3, NULL, NULL, NULL, NULL, NULL),
(421, 'MATHEMATIQUES', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-06 22:24:54', 1, 3, NULL, NULL, NULL, NULL, NULL),
(422, 'Etude de  texte', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-07 05:17:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(423, 'Correction orthographie', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-07 05:17:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(424, 'Expression ecrite', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-07 05:17:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(425, 'Anglais', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-07 05:17:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(426, 'ECM', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-07 05:17:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(427, 'Espagnol', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-07 05:17:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(428, 'HISTOIRE', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-07 05:17:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(429, 'Geographie', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-07 05:17:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(431, 'PCT', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 05:17:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(432, 'Informatique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 05:17:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(433, 'Mathematque', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 05:17:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(434, 'SVTEEHB', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 05:17:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(435, 'ESF', 1, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 05:17:06', 1, 3, NULL, NULL, NULL, NULL, NULL),
(436, 'Schéma électrique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 09:33:49', 1, 3, NULL, NULL, NULL, NULL, NULL),
(437, 'Circuit électrique', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 09:34:11', 1, 3, NULL, NULL, NULL, NULL, NULL),
(438, 'PID', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 09:34:36', 1, 3, NULL, NULL, NULL, NULL, NULL),
(439, 'Dessin technique', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 09:34:56', 1, 3, NULL, NULL, NULL, NULL, NULL),
(440, 'Essaie et mesure', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 09:36:09', 1, 3, NULL, NULL, NULL, NULL, NULL),
(441, 'Circuit électrique', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 09:50:20', 1, 3, NULL, NULL, NULL, NULL, NULL),
(442, 'Essaie et mesure', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 09:51:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(444, 'Eps', 1, 'Groupe 3', NULL, NULL, NULL, '2026-06-07 11:27:09', 1, 3, NULL, NULL, NULL, NULL, NULL),
(445, 'Production', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 11:28:57', 1, 3, NULL, NULL, NULL, NULL, NULL),
(446, 'Mathematics', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 11:52:41', 1, 3, NULL, NULL, NULL, NULL, NULL),
(447, 'H.biology', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 12:50:19', 1, 3, NULL, NULL, NULL, NULL, NULL),
(448, 'Eps', 1, 'Groupe 3', NULL, NULL, NULL, '2026-06-07 13:38:04', 1, 3, NULL, NULL, NULL, NULL, NULL),
(449, 'Technologie professionnelle', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 16:32:46', 1, 3, NULL, NULL, NULL, NULL, NULL),
(450, 'Dessin technique', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 16:33:39', 1, 3, NULL, NULL, NULL, NULL, NULL),
(451, 'Traçage', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 16:34:43', 1, 3, NULL, NULL, NULL, NULL, NULL),
(452, 'RESEO', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-07 16:36:19', 1, 3, NULL, NULL, NULL, NULL, NULL),
(453, 'Français', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-08 08:45:57', 1, 3, NULL, NULL, NULL, NULL, NULL),
(454, 'Anglais', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-08 08:49:02', 1, 3, NULL, NULL, NULL, NULL, NULL),
(455, 'Mécanique Appliqué', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-08 09:18:11', 1, 3, NULL, NULL, NULL, NULL, NULL),
(456, 'Législation', 1, 'Groupe 1', NULL, NULL, NULL, '2026-06-08 10:02:41', 1, 3, NULL, NULL, NULL, NULL, NULL),
(457, 'RESEO', 5, 'Groupe 2', NULL, NULL, NULL, '2026-06-08 10:07:23', 1, 3, NULL, NULL, NULL, NULL, NULL),
(458, 'Commerce', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-08 10:30:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(459, 'B.Maths', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-08 10:31:17', 1, 3, NULL, NULL, NULL, NULL, NULL),
(460, 'Accounting', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-08 10:31:47', 1, 3, NULL, NULL, NULL, NULL, NULL),
(461, 'HYGIÈNE', 1, 'Groupe 3', NULL, NULL, NULL, '2026-06-08 10:32:20', 1, 3, NULL, NULL, NULL, NULL, NULL),
(462, 'PDC', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-08 11:06:35', 1, 3, NULL, NULL, NULL, NULL, NULL),
(463, 'Commerce', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-08 11:43:02', 1, 3, NULL, NULL, NULL, NULL, NULL),
(464, 'Science physique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-08 12:18:19', 1, 3, NULL, NULL, NULL, NULL, NULL),
(465, 'Comptabilité d\'entreprise', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-08 12:51:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(466, 'Mathematique Appliqué', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-08 12:56:08', 1, 3, NULL, NULL, NULL, NULL, NULL),
(467, 'ECM', 1, 'Groupe 1', NULL, NULL, NULL, '2026-06-08 13:02:51', 1, 3, NULL, NULL, NULL, NULL, NULL),
(468, 'MOB', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-08 13:55:36', 1, 3, NULL, NULL, NULL, NULL, NULL),
(469, 'Informatique', 1, 'Groupe 2', NULL, NULL, NULL, '2026-06-08 13:56:03', 1, 3, NULL, NULL, NULL, NULL, NULL),
(470, 'RPC', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-08 13:59:42', 1, 3, NULL, NULL, NULL, NULL, NULL),
(473, 'PCQ', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-08 15:19:58', 1, 3, NULL, NULL, NULL, NULL, NULL),
(475, 'Histoire/Géographie', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-09 11:29:24', 1, 3, NULL, NULL, NULL, NULL, NULL),
(476, 'Anglais', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-09 11:29:45', 1, 3, NULL, NULL, NULL, NULL, NULL),
(477, 'Français', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-09 11:30:02', 1, 3, NULL, NULL, NULL, NULL, NULL),
(478, 'Mathematique', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-09 11:30:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(479, 'Économie générale', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-09 11:31:00', 1, 3, NULL, NULL, NULL, NULL, NULL),
(480, 'OTA', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-09 11:31:25', 1, 3, NULL, NULL, NULL, NULL, NULL),
(481, 'PRP', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-09 11:31:44', 1, 3, NULL, NULL, NULL, NULL, NULL),
(482, 'Bureautique', 3, 'Groupe 2', NULL, NULL, NULL, '2026-06-09 11:32:05', 1, 3, NULL, NULL, NULL, NULL, NULL),
(483, 'Comptabilité financière', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-09 11:32:36', 1, 3, NULL, NULL, NULL, NULL, NULL),
(484, 'ECM', 1, 'Groupe 1', NULL, NULL, NULL, '2026-06-09 11:32:58', 1, 3, NULL, NULL, NULL, NULL, NULL),
(485, 'INFORMATIQUE', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-09 11:33:19', 1, 3, NULL, NULL, NULL, NULL, NULL),
(486, 'EPS', 2, 'Groupe 3', NULL, NULL, NULL, '2026-06-09 11:33:37', 1, 3, NULL, NULL, NULL, NULL, NULL),
(487, 'Travail manuel', 1, 'Groupe 3', NULL, NULL, NULL, '2026-06-09 11:33:54', 1, 3, NULL, NULL, NULL, NULL, NULL),
(488, 'Dessin de Mode', 4, 'Groupe 2', NULL, NULL, NULL, '2026-06-09 13:18:11', 1, 3, NULL, NULL, NULL, NULL, NULL),
(489, 'EPS', 1, 'Groupe 1', NULL, NULL, NULL, '2026-06-09 13:21:17', 1, 3, NULL, NULL, NULL, NULL, NULL),
(492, 'Anglais', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-10 04:24:32', 1, 3, NULL, NULL, NULL, NULL, NULL),
(493, 'ECM', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-10 04:30:00', 1, 3, NULL, NULL, NULL, NULL, NULL),
(494, 'Informatique', 2, 'Groupe 1', NULL, NULL, NULL, '2026-06-10 04:30:34', 1, 3, NULL, NULL, NULL, NULL, NULL),
(495, 'Législation', 1, 'Groupe 1', NULL, NULL, NULL, '2026-06-10 04:31:09', 1, 3, NULL, NULL, NULL, NULL, NULL),
(496, 'Français', 3, 'Groupe 1', NULL, NULL, NULL, '2026-06-10 04:31:36', 1, 3, NULL, NULL, NULL, NULL, NULL),
(497, 'MATHEMATIQUES', 2, 'Groupe 2', NULL, NULL, NULL, '2026-06-10 04:34:03', 1, 3, NULL, NULL, NULL, NULL, NULL),
(498, 'Langue Française', 4, 'Groupe 1', NULL, NULL, NULL, '2026-08-12 08:57:24', 1, 2, 26, NULL, NULL, NULL, NULL),
(499, 'Mathématiques', 4, 'Groupe 1', NULL, NULL, NULL, '2026-08-12 08:57:24', 1, 2, 26, NULL, NULL, NULL, NULL),
(500, 'English Language', 2, 'Groupe 1', NULL, NULL, NULL, '2026-08-12 08:57:24', 1, 2, 26, NULL, NULL, NULL, NULL),
(501, 'Sciences et Technologie', 2, 'Groupe 2', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(502, 'Éducation Civique et Morale', 2, 'Groupe 2', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(503, 'Langues et Cultures Nationales', 1, 'Groupe 2', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(504, 'Informatique', 1, 'Groupe 2', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(505, 'Éducation Artistique', 1, 'Groupe 3', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(506, 'Éducation Physique et Sportive', 1, 'Groupe 3', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(507, 'Activités Pratiques', 1, 'Groupe 3', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(508, 'Langue Française', 5, 'Groupe 1', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(509, 'Mathématiques', 5, 'Groupe 1', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(510, 'English Language', 3, 'Groupe 1', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(511, 'Sciences et Technologie', 3, 'Groupe 2', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(512, 'Histoire et Géographie', 2, 'Groupe 2', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(513, 'Éducation Physique et Sportive', 2, 'Groupe 3', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(514, 'English Language', 4, 'Groupe 1', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(515, 'Mathematics', 4, 'Groupe 1', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(516, 'French Language', 2, 'Groupe 1', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(517, 'Science and Technology', 2, 'Groupe 2', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(518, 'Social Studies', 2, 'Groupe 2', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(519, 'Citizenship / Moral Education', 2, 'Groupe 2', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(520, 'National Languages and Cultures', 1, 'Groupe 2', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(521, 'Computer Science', 1, 'Groupe 2', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(522, 'Arts and Craft', 1, 'Groupe 3', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(523, 'Physical Education', 1, 'Groupe 3', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(524, 'Vocational Studies', 1, 'Groupe 3', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(525, 'English Language', 5, 'Groupe 1', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(526, 'Mathematics', 5, 'Groupe 1', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(527, 'French Language', 3, 'Groupe 1', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(528, 'Science and Technology', 3, 'Groupe 2', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(529, 'Social Studies', 3, 'Groupe 2', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL),
(530, 'Physical Education', 2, 'Groupe 3', NULL, NULL, NULL, '2026-08-12 08:57:25', 1, 2, 26, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `subject_classes`
--

CREATE TABLE `subject_classes` (
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `subject_classes`
--

INSERT INTO `subject_classes` (`subject_id`, `class_id`, `academic_year_id`) VALUES
(15, 5, 3),
(15, 6, 3),
(15, 7, 3),
(15, 8, 3),
(15, 9, 3),
(15, 10, 3),
(15, 12, 3),
(15, 13, 3),
(15, 14, 3),
(15, 15, 3),
(15, 16, 3),
(15, 17, 3),
(15, 18, 3),
(15, 19, 3),
(15, 20, 3),
(15, 21, 3),
(15, 22, 3),
(15, 23, 3),
(15, 24, 3),
(15, 25, 3),
(15, 26, 3),
(15, 27, 3),
(15, 28, 3),
(15, 32, 3),
(15, 33, 3),
(15, 34, 3),
(15, 35, 3),
(15, 36, 3),
(15, 37, 3),
(15, 38, 3),
(15, 39, 3),
(15, 40, 3),
(15, 46, 3),
(15, 47, 3),
(15, 48, 3),
(15, 49, 3),
(15, 50, 3),
(15, 51, 3),
(15, 52, 3),
(15, 53, 3),
(15, 54, 3),
(15, 60, 3),
(15, 61, 3),
(15, 62, 3),
(15, 63, 3),
(15, 64, 3),
(15, 65, 3),
(15, 66, 3),
(15, 67, 3),
(15, 68, 3),
(15, 74, 3),
(15, 75, 3),
(15, 76, 3),
(15, 77, 3),
(15, 78, 3),
(15, 79, 3),
(15, 80, 3),
(15, 81, 3),
(15, 82, 3),
(15, 84, 3),
(15, 85, 3),
(15, 86, 3),
(15, 87, 3),
(15, 88, 3),
(15, 89, 3),
(15, 90, 3),
(15, 91, 3),
(15, 93, 3),
(15, 96, 3),
(15, 97, 3),
(15, 98, 3),
(15, 99, 3),
(16, 5, 3),
(16, 6, 3),
(16, 7, 3),
(16, 8, 3),
(16, 9, 3),
(16, 10, 3),
(16, 12, 3),
(16, 13, 3),
(16, 14, 3),
(16, 15, 3),
(16, 16, 3),
(16, 17, 3),
(16, 18, 3),
(16, 19, 3),
(16, 20, 3),
(16, 21, 3),
(16, 22, 3),
(16, 23, 3),
(16, 24, 3),
(16, 25, 3),
(16, 26, 3),
(16, 27, 3),
(16, 28, 3),
(16, 32, 3),
(16, 33, 3),
(16, 34, 3),
(16, 35, 3),
(16, 36, 3),
(16, 37, 3),
(16, 38, 3),
(16, 39, 3),
(16, 40, 3),
(16, 46, 3),
(16, 47, 3),
(16, 48, 3),
(16, 49, 3),
(16, 50, 3),
(16, 51, 3),
(16, 52, 3),
(16, 53, 3),
(16, 54, 3),
(16, 60, 3),
(16, 61, 3),
(16, 62, 3),
(16, 63, 3),
(16, 64, 3),
(16, 65, 3),
(16, 66, 3),
(16, 67, 3),
(16, 68, 3),
(16, 74, 3),
(16, 75, 3),
(16, 76, 3),
(16, 77, 3),
(16, 78, 3),
(16, 79, 3),
(16, 80, 3),
(16, 81, 3),
(16, 82, 3),
(16, 84, 3),
(16, 85, 3),
(16, 86, 3),
(16, 87, 3),
(16, 88, 3),
(16, 89, 3),
(16, 90, 3),
(16, 91, 3),
(16, 93, 3),
(16, 96, 3),
(16, 97, 3),
(16, 98, 3),
(16, 99, 3),
(17, 5, 3),
(17, 6, 3),
(17, 7, 3),
(17, 8, 3),
(17, 9, 3),
(17, 10, 3),
(17, 12, 3),
(17, 13, 3),
(17, 14, 3),
(17, 15, 3),
(17, 16, 3),
(17, 17, 3),
(17, 18, 3),
(17, 19, 3),
(17, 20, 3),
(17, 21, 3),
(17, 22, 3),
(17, 23, 3),
(17, 24, 3),
(17, 25, 3),
(17, 26, 3),
(17, 27, 3),
(17, 28, 3),
(17, 32, 3),
(17, 33, 3),
(17, 34, 3),
(17, 35, 3),
(17, 36, 3),
(17, 37, 3),
(17, 38, 3),
(17, 39, 3),
(17, 40, 3),
(17, 46, 3),
(17, 47, 3),
(17, 48, 3),
(17, 49, 3),
(17, 50, 3),
(17, 51, 3),
(17, 52, 3),
(17, 53, 3),
(17, 54, 3),
(17, 60, 3),
(17, 61, 3),
(17, 62, 3),
(17, 63, 3),
(17, 64, 3),
(17, 65, 3),
(17, 66, 3),
(17, 67, 3),
(17, 68, 3),
(17, 74, 3),
(17, 75, 3),
(17, 76, 3),
(17, 77, 3),
(17, 78, 3),
(17, 79, 3),
(17, 80, 3),
(17, 81, 3),
(17, 82, 3),
(17, 84, 3),
(17, 85, 3),
(17, 86, 3),
(17, 87, 3),
(17, 88, 3),
(17, 89, 3),
(17, 90, 3),
(17, 91, 3),
(17, 93, 3),
(17, 96, 3),
(17, 97, 3),
(17, 98, 3),
(17, 99, 3),
(18, 5, 3),
(18, 6, 3),
(18, 7, 3),
(18, 8, 3),
(18, 9, 3),
(18, 10, 3),
(18, 12, 3),
(18, 13, 3),
(18, 14, 3),
(18, 15, 3),
(18, 16, 3),
(18, 17, 3),
(18, 18, 3),
(18, 19, 3),
(18, 20, 3),
(18, 21, 3),
(18, 22, 3),
(18, 23, 3),
(18, 24, 3),
(18, 25, 3),
(18, 26, 3),
(18, 27, 3),
(18, 28, 3),
(18, 32, 3),
(18, 33, 3),
(18, 34, 3),
(18, 35, 3),
(18, 36, 3),
(18, 37, 3),
(18, 38, 3),
(18, 39, 3),
(18, 40, 3),
(18, 46, 3),
(18, 47, 3),
(18, 48, 3),
(18, 49, 3),
(18, 50, 3),
(18, 51, 3),
(18, 52, 3),
(18, 53, 3),
(18, 54, 3),
(18, 60, 3),
(18, 61, 3),
(18, 62, 3),
(18, 63, 3),
(18, 64, 3),
(18, 65, 3),
(18, 66, 3),
(18, 67, 3),
(18, 68, 3),
(18, 74, 3),
(18, 75, 3),
(18, 76, 3),
(18, 77, 3),
(18, 78, 3),
(18, 79, 3),
(18, 80, 3),
(18, 81, 3),
(18, 82, 3),
(18, 84, 3),
(18, 85, 3),
(18, 86, 3),
(18, 87, 3),
(18, 88, 3),
(18, 89, 3),
(18, 90, 3),
(18, 91, 3),
(18, 93, 3),
(18, 96, 3),
(18, 97, 3),
(18, 98, 3),
(18, 99, 3),
(19, 5, 3),
(19, 6, 3),
(19, 7, 3),
(19, 8, 3),
(19, 9, 3),
(19, 10, 3),
(19, 12, 3),
(19, 13, 3),
(19, 14, 3),
(19, 15, 3),
(19, 16, 3),
(19, 17, 3),
(19, 18, 3),
(19, 19, 3),
(19, 20, 3),
(19, 21, 3),
(19, 22, 3),
(19, 23, 3),
(19, 24, 3),
(19, 25, 3),
(19, 26, 3),
(19, 27, 3),
(19, 28, 3),
(19, 32, 3),
(19, 33, 3),
(19, 34, 3),
(19, 35, 3),
(19, 36, 3),
(19, 37, 3),
(19, 38, 3),
(19, 39, 3),
(19, 40, 3),
(19, 46, 3),
(19, 47, 3),
(19, 48, 3),
(19, 49, 3),
(19, 50, 3),
(19, 51, 3),
(19, 52, 3),
(19, 53, 3),
(19, 54, 3),
(19, 60, 3),
(19, 61, 3),
(19, 62, 3),
(19, 63, 3),
(19, 64, 3),
(19, 65, 3),
(19, 66, 3),
(19, 67, 3),
(19, 68, 3),
(19, 74, 3),
(19, 75, 3),
(19, 76, 3),
(19, 77, 3),
(19, 78, 3),
(19, 79, 3),
(19, 80, 3),
(19, 81, 3),
(19, 82, 3),
(19, 84, 3),
(19, 85, 3),
(19, 86, 3),
(19, 87, 3),
(19, 88, 3),
(19, 89, 3),
(19, 90, 3),
(19, 91, 3),
(19, 93, 3),
(19, 96, 3),
(19, 97, 3),
(19, 98, 3),
(19, 99, 3),
(20, 5, 3),
(20, 6, 3),
(20, 7, 3),
(20, 8, 3),
(20, 9, 3),
(20, 10, 3),
(20, 12, 3),
(20, 13, 3),
(20, 14, 3),
(20, 15, 3),
(20, 16, 3),
(20, 17, 3),
(20, 18, 3),
(20, 19, 3),
(20, 20, 3),
(20, 21, 3),
(20, 22, 3),
(20, 23, 3),
(20, 24, 3),
(20, 25, 3),
(20, 26, 3),
(20, 27, 3),
(20, 28, 3),
(20, 32, 3),
(20, 33, 3),
(20, 34, 3),
(20, 35, 3),
(20, 36, 3),
(20, 37, 3),
(20, 38, 3),
(20, 39, 3),
(20, 40, 3),
(20, 46, 3),
(20, 47, 3),
(20, 48, 3),
(20, 49, 3),
(20, 50, 3),
(20, 51, 3),
(20, 52, 3),
(20, 53, 3),
(20, 54, 3),
(20, 60, 3),
(20, 61, 3),
(20, 62, 3),
(20, 63, 3),
(20, 64, 3),
(20, 65, 3),
(20, 66, 3),
(20, 67, 3),
(20, 68, 3),
(20, 74, 3),
(20, 75, 3),
(20, 76, 3),
(20, 77, 3),
(20, 78, 3),
(20, 79, 3),
(20, 80, 3),
(20, 81, 3),
(20, 82, 3),
(20, 84, 3),
(20, 85, 3),
(20, 86, 3),
(20, 87, 3),
(20, 88, 3),
(20, 89, 3),
(20, 90, 3),
(20, 91, 3),
(20, 93, 3),
(20, 96, 3),
(20, 97, 3),
(20, 98, 3),
(20, 99, 3),
(21, 5, 3),
(21, 6, 3),
(21, 7, 3),
(21, 8, 3),
(21, 9, 3),
(21, 10, 3),
(21, 12, 3),
(21, 13, 3),
(21, 14, 3),
(21, 15, 3),
(21, 16, 3),
(21, 17, 3),
(21, 18, 3),
(21, 19, 3),
(21, 20, 3),
(21, 21, 3),
(21, 22, 3),
(21, 23, 3),
(21, 24, 3),
(21, 25, 3),
(21, 26, 3),
(21, 27, 3),
(21, 28, 3),
(21, 32, 3),
(21, 33, 3),
(21, 34, 3),
(21, 35, 3),
(21, 36, 3),
(21, 37, 3),
(21, 38, 3),
(21, 39, 3),
(21, 40, 3),
(21, 46, 3),
(21, 47, 3),
(21, 48, 3),
(21, 49, 3),
(21, 50, 3),
(21, 51, 3),
(21, 52, 3),
(21, 53, 3),
(21, 54, 3),
(21, 60, 3),
(21, 61, 3),
(21, 62, 3),
(21, 63, 3),
(21, 64, 3),
(21, 65, 3),
(21, 66, 3),
(21, 67, 3),
(21, 68, 3),
(21, 74, 3),
(21, 75, 3),
(21, 76, 3),
(21, 77, 3),
(21, 78, 3),
(21, 79, 3),
(21, 80, 3),
(21, 81, 3),
(21, 82, 3),
(21, 84, 3),
(21, 85, 3),
(21, 86, 3),
(21, 87, 3),
(21, 88, 3),
(21, 89, 3),
(21, 90, 3),
(21, 91, 3),
(21, 93, 3),
(21, 96, 3),
(21, 97, 3),
(21, 98, 3),
(21, 99, 3),
(22, 5, 3),
(22, 6, 3),
(22, 7, 3),
(22, 8, 3),
(22, 9, 3),
(22, 10, 3),
(22, 12, 3),
(22, 13, 3),
(22, 14, 3),
(22, 15, 3),
(22, 16, 3),
(22, 17, 3),
(22, 18, 3),
(22, 19, 3),
(22, 20, 3),
(22, 21, 3),
(22, 22, 3),
(22, 23, 3),
(22, 24, 3),
(22, 25, 3),
(22, 26, 3),
(22, 27, 3),
(22, 28, 3),
(22, 32, 3),
(22, 33, 3),
(22, 34, 3),
(22, 35, 3),
(22, 36, 3),
(22, 37, 3),
(22, 38, 3),
(22, 39, 3),
(22, 40, 3),
(22, 46, 3),
(22, 47, 3),
(22, 48, 3),
(22, 49, 3),
(22, 50, 3),
(22, 51, 3),
(22, 52, 3),
(22, 53, 3),
(22, 54, 3),
(22, 60, 3),
(22, 61, 3),
(22, 62, 3),
(22, 63, 3),
(22, 64, 3),
(22, 65, 3),
(22, 66, 3),
(22, 67, 3),
(22, 68, 3),
(22, 74, 3),
(22, 75, 3),
(22, 76, 3),
(22, 77, 3),
(22, 78, 3),
(22, 79, 3),
(22, 80, 3),
(22, 81, 3),
(22, 82, 3),
(22, 84, 3),
(22, 85, 3),
(22, 86, 3),
(22, 87, 3),
(22, 88, 3),
(22, 89, 3),
(22, 90, 3),
(22, 91, 3),
(22, 93, 3),
(22, 96, 3),
(22, 97, 3),
(22, 98, 3),
(22, 99, 3),
(23, 5, 3),
(23, 6, 3),
(23, 7, 3),
(23, 8, 3),
(23, 9, 3),
(23, 10, 3),
(23, 12, 3),
(23, 13, 3),
(23, 14, 3),
(23, 15, 3),
(23, 16, 3),
(23, 17, 3),
(23, 18, 3),
(23, 19, 3),
(23, 20, 3),
(23, 21, 3),
(23, 22, 3),
(23, 23, 3),
(23, 24, 3),
(23, 25, 3),
(23, 26, 3),
(23, 27, 3),
(23, 28, 3),
(23, 32, 3),
(23, 33, 3),
(23, 34, 3),
(23, 35, 3),
(23, 36, 3),
(23, 37, 3),
(23, 38, 3),
(23, 39, 3),
(23, 40, 3),
(23, 46, 3),
(23, 47, 3),
(23, 48, 3),
(23, 49, 3),
(23, 50, 3),
(23, 51, 3),
(23, 52, 3),
(23, 53, 3),
(23, 54, 3),
(23, 60, 3),
(23, 61, 3),
(23, 62, 3),
(23, 63, 3),
(23, 64, 3),
(23, 65, 3),
(23, 66, 3),
(23, 67, 3),
(23, 68, 3),
(23, 74, 3),
(23, 75, 3),
(23, 76, 3),
(23, 77, 3),
(23, 78, 3),
(23, 79, 3),
(23, 80, 3),
(23, 81, 3),
(23, 82, 3),
(23, 84, 3),
(23, 85, 3),
(23, 86, 3),
(23, 87, 3),
(23, 88, 3),
(23, 89, 3),
(23, 90, 3),
(23, 91, 3),
(23, 93, 3),
(23, 96, 3),
(23, 97, 3),
(23, 98, 3),
(23, 99, 3),
(24, 5, 3),
(24, 6, 3),
(24, 7, 3),
(24, 8, 3),
(24, 9, 3),
(24, 10, 3),
(24, 12, 3),
(24, 13, 3),
(24, 14, 3),
(24, 15, 3),
(24, 16, 3),
(24, 17, 3),
(24, 18, 3),
(24, 19, 3),
(24, 20, 3),
(24, 21, 3),
(24, 22, 3),
(24, 23, 3),
(24, 24, 3),
(24, 25, 3),
(24, 26, 3),
(24, 27, 3),
(24, 28, 3),
(24, 32, 3),
(24, 33, 3),
(24, 34, 3),
(24, 35, 3),
(24, 36, 3),
(24, 37, 3),
(24, 38, 3),
(24, 39, 3),
(24, 40, 3),
(24, 46, 3),
(24, 47, 3),
(24, 48, 3),
(24, 49, 3),
(24, 50, 3),
(24, 51, 3),
(24, 52, 3),
(24, 53, 3),
(24, 54, 3),
(24, 60, 3),
(24, 61, 3),
(24, 62, 3),
(24, 63, 3),
(24, 64, 3),
(24, 65, 3),
(24, 66, 3),
(24, 67, 3),
(24, 68, 3),
(24, 74, 3),
(24, 75, 3),
(24, 76, 3),
(24, 77, 3),
(24, 78, 3),
(24, 79, 3),
(24, 80, 3),
(24, 81, 3),
(24, 82, 3),
(24, 84, 3),
(24, 85, 3),
(24, 86, 3),
(24, 87, 3),
(24, 88, 3),
(24, 89, 3),
(24, 90, 3),
(24, 91, 3),
(24, 93, 3),
(24, 96, 3),
(24, 97, 3),
(24, 98, 3),
(24, 99, 3),
(25, 2, 3),
(25, 3, 3),
(25, 5, 3),
(25, 6, 3),
(25, 7, 3),
(25, 8, 3),
(25, 9, 3),
(25, 10, 3),
(25, 12, 3),
(25, 13, 3),
(25, 14, 3),
(25, 15, 3),
(25, 16, 3),
(25, 17, 3),
(25, 18, 3),
(25, 19, 3),
(25, 20, 3),
(25, 21, 3),
(25, 22, 3),
(25, 23, 3),
(25, 24, 3),
(25, 25, 3),
(25, 26, 3),
(25, 27, 3),
(25, 28, 3),
(25, 29, 3),
(25, 30, 3),
(25, 31, 3),
(25, 32, 3),
(25, 33, 3),
(25, 34, 3),
(25, 35, 3),
(25, 36, 3),
(25, 37, 3),
(25, 38, 3),
(25, 39, 3),
(25, 40, 3),
(25, 41, 3),
(25, 42, 3),
(25, 43, 3),
(25, 44, 3),
(25, 45, 3),
(25, 46, 3),
(25, 47, 3),
(25, 48, 3),
(25, 49, 3),
(25, 50, 3),
(25, 51, 3),
(25, 52, 3),
(25, 53, 3),
(25, 54, 3),
(25, 55, 3),
(25, 56, 3),
(25, 57, 3),
(25, 58, 3),
(25, 59, 3),
(25, 60, 3),
(25, 61, 3),
(25, 62, 3),
(25, 63, 3),
(25, 64, 3),
(25, 65, 3),
(25, 66, 3),
(25, 67, 3),
(25, 68, 3),
(25, 69, 3),
(25, 70, 3),
(25, 71, 3),
(25, 72, 3),
(25, 73, 3),
(25, 74, 3),
(25, 75, 3),
(25, 76, 3),
(25, 77, 3),
(25, 78, 3),
(25, 79, 3),
(25, 80, 3),
(25, 81, 3),
(25, 82, 3),
(25, 84, 3),
(25, 85, 3),
(25, 86, 3),
(25, 87, 3),
(25, 88, 3),
(25, 89, 3),
(25, 90, 3),
(25, 91, 3),
(25, 93, 3),
(25, 96, 3),
(25, 97, 3),
(25, 98, 3),
(25, 99, 3),
(26, 2, 3),
(26, 3, 3),
(26, 5, 3),
(26, 6, 3),
(26, 7, 3),
(26, 8, 3),
(26, 9, 3),
(26, 10, 3),
(26, 12, 3),
(26, 13, 3),
(26, 14, 3),
(26, 15, 3),
(26, 16, 3),
(26, 17, 3),
(26, 18, 3),
(26, 19, 3),
(26, 20, 3),
(26, 21, 3),
(26, 22, 3),
(26, 23, 3),
(26, 24, 3),
(26, 25, 3),
(26, 26, 3),
(26, 27, 3),
(26, 28, 3),
(26, 29, 3),
(26, 30, 3),
(26, 31, 3),
(26, 32, 3),
(26, 33, 3),
(26, 34, 3),
(26, 35, 3),
(26, 36, 3),
(26, 37, 3),
(26, 38, 3),
(26, 39, 3),
(26, 40, 3),
(26, 41, 3),
(26, 42, 3),
(26, 43, 3),
(26, 44, 3),
(26, 45, 3),
(26, 46, 3),
(26, 47, 3),
(26, 48, 3),
(26, 49, 3),
(26, 50, 3),
(26, 51, 3),
(26, 52, 3),
(26, 53, 3),
(26, 54, 3),
(26, 55, 3),
(26, 56, 3),
(26, 57, 3),
(26, 58, 3),
(26, 59, 3),
(26, 60, 3),
(26, 61, 3),
(26, 62, 3),
(26, 63, 3),
(26, 64, 3),
(26, 65, 3),
(26, 66, 3),
(26, 67, 3),
(26, 68, 3),
(26, 69, 3),
(26, 70, 3),
(26, 71, 3),
(26, 72, 3),
(26, 73, 3),
(26, 74, 3),
(26, 75, 3),
(26, 76, 3),
(26, 77, 3),
(26, 78, 3),
(26, 79, 3),
(26, 80, 3),
(26, 81, 3),
(26, 82, 3),
(26, 84, 3),
(26, 85, 3),
(26, 86, 3),
(26, 87, 3),
(26, 88, 3),
(26, 89, 3),
(26, 90, 3),
(26, 91, 3),
(26, 93, 3),
(26, 96, 3),
(26, 97, 3),
(26, 98, 3),
(26, 99, 3),
(27, 5, 3),
(27, 6, 3),
(27, 7, 3),
(27, 8, 3),
(27, 9, 3),
(27, 10, 3),
(27, 12, 3),
(27, 13, 3),
(27, 14, 3),
(27, 15, 3),
(27, 16, 3),
(27, 17, 3),
(27, 18, 3),
(27, 19, 3),
(27, 20, 3),
(27, 21, 3),
(27, 22, 3),
(27, 23, 3),
(27, 24, 3),
(27, 25, 3),
(27, 26, 3),
(27, 27, 3),
(27, 28, 3),
(27, 32, 3),
(27, 33, 3),
(27, 34, 3),
(27, 35, 3),
(27, 36, 3),
(27, 37, 3),
(27, 38, 3),
(27, 39, 3),
(27, 40, 3),
(27, 46, 3),
(27, 47, 3),
(27, 48, 3),
(27, 49, 3),
(27, 50, 3),
(27, 51, 3),
(27, 52, 3),
(27, 53, 3),
(27, 54, 3),
(27, 60, 3),
(27, 61, 3),
(27, 62, 3),
(27, 63, 3),
(27, 64, 3),
(27, 65, 3),
(27, 66, 3),
(27, 67, 3),
(27, 68, 3),
(27, 74, 3),
(27, 75, 3),
(27, 76, 3),
(27, 77, 3),
(27, 78, 3),
(27, 79, 3),
(27, 80, 3),
(27, 81, 3),
(27, 82, 3),
(27, 84, 3),
(27, 85, 3),
(27, 86, 3),
(27, 87, 3),
(27, 88, 3),
(27, 89, 3),
(27, 90, 3),
(27, 91, 3),
(27, 93, 3),
(27, 96, 3),
(27, 97, 3),
(27, 98, 3),
(27, 99, 3),
(28, 5, 3),
(28, 6, 3),
(28, 7, 3),
(28, 8, 3),
(28, 9, 3),
(28, 10, 3),
(28, 12, 3),
(28, 13, 3),
(28, 14, 3),
(28, 15, 3),
(28, 16, 3),
(28, 17, 3),
(28, 18, 3),
(28, 19, 3),
(28, 20, 3),
(28, 21, 3),
(28, 22, 3),
(28, 23, 3),
(28, 24, 3),
(28, 25, 3),
(28, 26, 3),
(28, 27, 3),
(28, 28, 3),
(28, 32, 3),
(28, 33, 3),
(28, 34, 3),
(28, 35, 3),
(28, 36, 3),
(28, 37, 3),
(28, 38, 3),
(28, 39, 3),
(28, 40, 3),
(28, 46, 3),
(28, 47, 3),
(28, 48, 3),
(28, 49, 3),
(28, 50, 3),
(28, 51, 3),
(28, 52, 3),
(28, 53, 3),
(28, 54, 3),
(28, 60, 3),
(28, 61, 3),
(28, 62, 3),
(28, 63, 3),
(28, 64, 3),
(28, 65, 3),
(28, 66, 3),
(28, 67, 3),
(28, 68, 3),
(28, 74, 3),
(28, 75, 3),
(28, 76, 3),
(28, 77, 3),
(28, 78, 3),
(28, 79, 3),
(28, 80, 3),
(28, 81, 3),
(28, 82, 3),
(28, 84, 3),
(28, 85, 3),
(28, 86, 3),
(28, 87, 3),
(28, 88, 3),
(28, 89, 3),
(28, 90, 3),
(28, 91, 3),
(28, 93, 3),
(28, 96, 3),
(28, 97, 3),
(28, 98, 3),
(28, 99, 3),
(30, 5, 3),
(30, 6, 3),
(30, 7, 3),
(30, 8, 3),
(30, 9, 3),
(30, 10, 3),
(30, 12, 3),
(30, 13, 3),
(30, 14, 3),
(30, 15, 3),
(30, 16, 3),
(30, 17, 3),
(30, 18, 3),
(30, 19, 3),
(30, 20, 3),
(30, 21, 3),
(30, 22, 3),
(30, 23, 3),
(30, 24, 3),
(30, 25, 3),
(30, 26, 3),
(30, 27, 3),
(30, 28, 3),
(30, 32, 3),
(30, 33, 3),
(30, 34, 3),
(30, 35, 3),
(30, 36, 3),
(30, 37, 3),
(30, 38, 3),
(30, 39, 3),
(30, 40, 3),
(30, 46, 3),
(30, 47, 3),
(30, 48, 3),
(30, 49, 3),
(30, 50, 3),
(30, 51, 3),
(30, 52, 3),
(30, 53, 3),
(30, 54, 3),
(30, 60, 3),
(30, 61, 3),
(30, 62, 3),
(30, 63, 3),
(30, 64, 3),
(30, 65, 3),
(30, 66, 3),
(30, 67, 3),
(30, 68, 3),
(30, 74, 3),
(30, 75, 3),
(30, 76, 3),
(30, 77, 3),
(30, 78, 3),
(30, 79, 3),
(30, 80, 3),
(30, 81, 3),
(30, 82, 3),
(30, 84, 3),
(30, 85, 3),
(30, 86, 3),
(30, 87, 3),
(30, 88, 3),
(30, 89, 3),
(30, 90, 3),
(30, 91, 3),
(30, 93, 3),
(30, 96, 3),
(30, 97, 3),
(30, 98, 3),
(30, 99, 3),
(31, 5, 3),
(31, 6, 3),
(31, 7, 3),
(31, 8, 3),
(31, 9, 3),
(31, 10, 3),
(31, 12, 3),
(31, 13, 3),
(31, 14, 3),
(31, 15, 3),
(31, 16, 3),
(31, 17, 3),
(31, 18, 3),
(31, 19, 3),
(31, 20, 3),
(31, 21, 3),
(31, 22, 3),
(31, 23, 3),
(31, 24, 3),
(31, 25, 3),
(31, 26, 3),
(31, 27, 3),
(31, 28, 3),
(31, 32, 3),
(31, 33, 3),
(31, 34, 3),
(31, 35, 3),
(31, 36, 3),
(31, 37, 3),
(31, 38, 3),
(31, 39, 3),
(31, 40, 3),
(31, 46, 3),
(31, 47, 3),
(31, 48, 3),
(31, 49, 3),
(31, 50, 3),
(31, 51, 3),
(31, 52, 3),
(31, 53, 3),
(31, 54, 3),
(31, 60, 3),
(31, 61, 3),
(31, 62, 3),
(31, 63, 3),
(31, 64, 3),
(31, 65, 3),
(31, 66, 3),
(31, 67, 3),
(31, 68, 3),
(31, 74, 3),
(31, 75, 3),
(31, 76, 3),
(31, 77, 3),
(31, 78, 3),
(31, 79, 3),
(31, 80, 3),
(31, 81, 3),
(31, 82, 3),
(31, 84, 3),
(31, 85, 3),
(31, 86, 3),
(31, 87, 3),
(31, 88, 3),
(31, 89, 3),
(31, 90, 3),
(31, 91, 3),
(31, 93, 3),
(31, 96, 3),
(31, 97, 3),
(31, 98, 3),
(31, 99, 3),
(32, 5, 3),
(32, 6, 3),
(32, 7, 3),
(32, 8, 3),
(32, 9, 3),
(32, 10, 3),
(32, 12, 3),
(32, 13, 3),
(32, 14, 3),
(32, 15, 3),
(32, 16, 3),
(32, 17, 3),
(32, 18, 3),
(32, 19, 3),
(32, 20, 3),
(32, 21, 3),
(32, 22, 3),
(32, 23, 3),
(32, 24, 3),
(32, 25, 3),
(32, 26, 3),
(32, 27, 3),
(32, 28, 3),
(32, 32, 3),
(32, 33, 3),
(32, 34, 3),
(32, 35, 3),
(32, 36, 3),
(32, 37, 3),
(32, 38, 3),
(32, 39, 3),
(32, 40, 3),
(32, 46, 3),
(32, 47, 3),
(32, 48, 3),
(32, 49, 3),
(32, 50, 3),
(32, 51, 3),
(32, 52, 3),
(32, 53, 3),
(32, 54, 3),
(32, 60, 3),
(32, 61, 3),
(32, 62, 3),
(32, 63, 3),
(32, 64, 3),
(32, 65, 3),
(32, 66, 3),
(32, 67, 3),
(32, 68, 3),
(32, 74, 3),
(32, 75, 3),
(32, 76, 3),
(32, 77, 3),
(32, 78, 3),
(32, 79, 3),
(32, 80, 3),
(32, 81, 3),
(32, 82, 3),
(32, 84, 3),
(32, 85, 3),
(32, 86, 3),
(32, 87, 3),
(32, 88, 3),
(32, 89, 3),
(32, 90, 3),
(32, 91, 3),
(32, 93, 3),
(32, 96, 3),
(32, 97, 3),
(32, 98, 3),
(32, 99, 3),
(33, 2, 3),
(33, 3, 3),
(33, 5, 3),
(33, 6, 3),
(33, 7, 3),
(33, 8, 3),
(33, 9, 3),
(33, 10, 3),
(33, 12, 3),
(33, 13, 3),
(33, 14, 3),
(33, 15, 3),
(33, 16, 3),
(33, 17, 3),
(33, 18, 3),
(33, 19, 3),
(33, 20, 3),
(33, 21, 3),
(33, 22, 3),
(33, 23, 3),
(33, 24, 3),
(33, 25, 3),
(33, 26, 3),
(33, 27, 3),
(33, 28, 3),
(33, 29, 3),
(33, 30, 3),
(33, 31, 3),
(33, 32, 3),
(33, 33, 3),
(33, 34, 3),
(33, 35, 3),
(33, 36, 3),
(33, 37, 3),
(33, 38, 3),
(33, 39, 3),
(33, 40, 3),
(33, 41, 3),
(33, 42, 3),
(33, 43, 3),
(33, 44, 3),
(33, 45, 3),
(33, 46, 3),
(33, 47, 3),
(33, 48, 3),
(33, 49, 3),
(33, 50, 3),
(33, 51, 3),
(33, 52, 3),
(33, 53, 3),
(33, 54, 3),
(33, 55, 3),
(33, 56, 3),
(33, 57, 3),
(33, 58, 3),
(33, 59, 3),
(33, 60, 3),
(33, 61, 3),
(33, 62, 3),
(33, 63, 3),
(33, 64, 3),
(33, 65, 3),
(33, 66, 3),
(33, 67, 3),
(33, 68, 3),
(33, 69, 3),
(33, 70, 3),
(33, 71, 3),
(33, 72, 3),
(33, 73, 3),
(33, 74, 3),
(33, 75, 3),
(33, 76, 3),
(33, 77, 3),
(33, 78, 3),
(33, 79, 3),
(33, 80, 3),
(33, 81, 3),
(33, 82, 3),
(33, 84, 3),
(33, 85, 3),
(33, 86, 3),
(33, 87, 3),
(33, 88, 3),
(33, 89, 3),
(33, 90, 3),
(33, 91, 3),
(33, 93, 3),
(33, 96, 3),
(33, 97, 3),
(33, 98, 3),
(33, 99, 3),
(34, 2, 3),
(34, 3, 3),
(34, 5, 3),
(34, 6, 3),
(34, 7, 3),
(34, 8, 3),
(34, 9, 3),
(34, 10, 3),
(34, 12, 3),
(34, 13, 3),
(34, 14, 3),
(34, 15, 3),
(34, 16, 3),
(34, 17, 3),
(34, 18, 3),
(34, 19, 3),
(34, 20, 3),
(34, 21, 3),
(34, 22, 3),
(34, 23, 3),
(34, 24, 3),
(34, 25, 3),
(34, 26, 3),
(34, 27, 3),
(34, 28, 3),
(34, 29, 3),
(34, 30, 3),
(34, 31, 3),
(34, 32, 3),
(34, 33, 3),
(34, 34, 3),
(34, 35, 3),
(34, 36, 3),
(34, 37, 3),
(34, 38, 3),
(34, 39, 3),
(34, 40, 3),
(34, 41, 3),
(34, 42, 3),
(34, 43, 3),
(34, 44, 3),
(34, 45, 3),
(34, 46, 3),
(34, 47, 3),
(34, 48, 3),
(34, 49, 3),
(34, 50, 3),
(34, 51, 3),
(34, 52, 3),
(34, 53, 3),
(34, 54, 3),
(34, 55, 3),
(34, 56, 3),
(34, 57, 3),
(34, 58, 3),
(34, 59, 3),
(34, 60, 3),
(34, 61, 3),
(34, 62, 3),
(34, 63, 3),
(34, 64, 3),
(34, 65, 3),
(34, 66, 3),
(34, 67, 3),
(34, 68, 3),
(34, 69, 3),
(34, 70, 3),
(34, 71, 3),
(34, 72, 3),
(34, 73, 3),
(34, 74, 3),
(34, 75, 3),
(34, 76, 3),
(34, 77, 3),
(34, 78, 3),
(34, 79, 3),
(34, 80, 3),
(34, 81, 3),
(34, 82, 3),
(34, 84, 3),
(34, 85, 3),
(34, 86, 3),
(34, 87, 3),
(34, 88, 3),
(34, 89, 3),
(34, 90, 3),
(34, 91, 3),
(34, 93, 3),
(34, 96, 3),
(34, 97, 3),
(34, 98, 3),
(34, 99, 3),
(35, 2, 3),
(35, 3, 3),
(35, 5, 3),
(35, 6, 3),
(35, 7, 3),
(35, 8, 3),
(35, 9, 3),
(35, 10, 3),
(35, 12, 3),
(35, 13, 3),
(35, 14, 3),
(35, 15, 3),
(35, 16, 3),
(35, 17, 3),
(35, 18, 3),
(35, 19, 3),
(35, 20, 3),
(35, 21, 3),
(35, 22, 3),
(35, 23, 3),
(35, 24, 3),
(35, 25, 3),
(35, 26, 3),
(35, 27, 3),
(35, 28, 3),
(35, 29, 3),
(35, 30, 3),
(35, 31, 3),
(35, 32, 3),
(35, 33, 3),
(35, 34, 3),
(35, 35, 3),
(35, 36, 3),
(35, 37, 3),
(35, 38, 3),
(35, 39, 3),
(35, 40, 3),
(35, 41, 3),
(35, 42, 3),
(35, 43, 3),
(35, 44, 3),
(35, 45, 3),
(35, 46, 3),
(35, 47, 3),
(35, 48, 3),
(35, 49, 3),
(35, 50, 3),
(35, 51, 3),
(35, 52, 3),
(35, 53, 3),
(35, 54, 3),
(35, 55, 3),
(35, 56, 3),
(35, 57, 3),
(35, 58, 3),
(35, 59, 3),
(35, 60, 3),
(35, 61, 3),
(35, 62, 3),
(35, 63, 3),
(35, 64, 3),
(35, 65, 3),
(35, 66, 3),
(35, 67, 3),
(35, 68, 3),
(35, 69, 3),
(35, 70, 3),
(35, 71, 3),
(35, 72, 3),
(35, 73, 3),
(35, 74, 3),
(35, 75, 3),
(35, 76, 3),
(35, 77, 3),
(35, 78, 3),
(35, 79, 3),
(35, 80, 3),
(35, 81, 3),
(35, 82, 3),
(35, 84, 3),
(35, 85, 3),
(35, 86, 3),
(35, 87, 3),
(35, 88, 3),
(35, 89, 3),
(35, 90, 3),
(35, 91, 3),
(35, 93, 3),
(35, 96, 3),
(35, 97, 3),
(35, 98, 3),
(35, 99, 3),
(36, 5, 3),
(36, 6, 3),
(36, 7, 3),
(36, 8, 3),
(36, 9, 3),
(36, 10, 3),
(36, 12, 3),
(36, 13, 3),
(36, 14, 3),
(36, 15, 3),
(36, 16, 3),
(36, 17, 3),
(36, 18, 3),
(36, 19, 3),
(36, 20, 3),
(36, 21, 3),
(36, 22, 3),
(36, 23, 3),
(36, 24, 3),
(36, 25, 3),
(36, 26, 3),
(36, 27, 3),
(36, 28, 3),
(36, 32, 3),
(36, 33, 3),
(36, 34, 3),
(36, 35, 3),
(36, 36, 3),
(36, 37, 3),
(36, 38, 3),
(36, 39, 3),
(36, 40, 3),
(36, 46, 3),
(36, 47, 3),
(36, 48, 3),
(36, 49, 3),
(36, 50, 3),
(36, 51, 3),
(36, 52, 3),
(36, 53, 3),
(36, 54, 3),
(36, 60, 3),
(36, 61, 3),
(36, 62, 3),
(36, 63, 3),
(36, 64, 3),
(36, 65, 3),
(36, 66, 3),
(36, 67, 3),
(36, 68, 3),
(36, 74, 3),
(36, 75, 3),
(36, 76, 3),
(36, 77, 3),
(36, 78, 3),
(36, 79, 3),
(36, 80, 3),
(36, 81, 3),
(36, 82, 3),
(36, 84, 3),
(36, 85, 3),
(36, 86, 3),
(36, 87, 3),
(36, 88, 3),
(36, 89, 3),
(36, 90, 3),
(36, 91, 3),
(36, 93, 3),
(36, 96, 3),
(36, 97, 3),
(36, 98, 3),
(36, 99, 3),
(37, 5, 3),
(37, 6, 3),
(37, 7, 3),
(37, 8, 3),
(37, 9, 3),
(37, 10, 3),
(37, 12, 3),
(37, 13, 3),
(37, 14, 3),
(37, 15, 3),
(37, 16, 3),
(37, 17, 3),
(37, 18, 3),
(37, 19, 3),
(37, 20, 3),
(37, 21, 3),
(37, 22, 3),
(37, 23, 3),
(37, 24, 3),
(37, 25, 3),
(37, 26, 3),
(37, 27, 3),
(37, 28, 3),
(37, 32, 3),
(37, 33, 3),
(37, 34, 3),
(37, 35, 3),
(37, 36, 3),
(37, 37, 3),
(37, 38, 3),
(37, 39, 3),
(37, 40, 3),
(37, 46, 3),
(37, 47, 3),
(37, 48, 3),
(37, 49, 3),
(37, 50, 3),
(37, 51, 3),
(37, 52, 3),
(37, 53, 3),
(37, 54, 3),
(37, 60, 3),
(37, 61, 3),
(37, 62, 3),
(37, 63, 3),
(37, 64, 3),
(37, 65, 3),
(37, 66, 3),
(37, 67, 3),
(37, 68, 3),
(37, 74, 3),
(37, 75, 3),
(37, 76, 3),
(37, 77, 3),
(37, 78, 3),
(37, 79, 3),
(37, 80, 3),
(37, 81, 3),
(37, 82, 3),
(37, 84, 3),
(37, 85, 3),
(37, 86, 3),
(37, 87, 3),
(37, 88, 3),
(37, 89, 3),
(37, 90, 3),
(37, 91, 3),
(37, 93, 3),
(37, 96, 3),
(37, 97, 3),
(37, 98, 3),
(37, 99, 3),
(38, 5, 3),
(38, 6, 3),
(38, 7, 3),
(38, 8, 3),
(38, 9, 3),
(38, 10, 3),
(38, 12, 3),
(38, 13, 3),
(38, 14, 3),
(38, 15, 3),
(38, 16, 3),
(38, 17, 3),
(38, 18, 3),
(38, 19, 3),
(38, 20, 3),
(38, 21, 3),
(38, 22, 3),
(38, 23, 3),
(38, 24, 3),
(38, 25, 3),
(38, 26, 3),
(38, 27, 3),
(38, 28, 3),
(38, 32, 3),
(38, 33, 3),
(38, 34, 3),
(38, 35, 3),
(38, 36, 3),
(38, 37, 3),
(38, 38, 3),
(38, 39, 3),
(38, 40, 3),
(38, 46, 3),
(38, 47, 3),
(38, 48, 3),
(38, 49, 3),
(38, 50, 3),
(38, 51, 3),
(38, 52, 3),
(38, 53, 3),
(38, 54, 3),
(38, 60, 3),
(38, 61, 3),
(38, 62, 3),
(38, 63, 3),
(38, 64, 3),
(38, 65, 3),
(38, 66, 3),
(38, 67, 3),
(38, 68, 3),
(38, 74, 3),
(38, 75, 3),
(38, 76, 3),
(38, 77, 3),
(38, 78, 3),
(38, 79, 3),
(38, 80, 3),
(38, 81, 3),
(38, 82, 3),
(38, 84, 3),
(38, 85, 3),
(38, 86, 3),
(38, 87, 3),
(38, 88, 3),
(38, 89, 3),
(38, 90, 3),
(38, 91, 3),
(38, 93, 3),
(38, 96, 3),
(38, 97, 3),
(38, 98, 3),
(38, 99, 3),
(39, 5, 3),
(39, 6, 3),
(39, 7, 3),
(39, 8, 3),
(39, 9, 3),
(39, 10, 3),
(39, 12, 3),
(39, 13, 3),
(39, 14, 3),
(39, 15, 3),
(39, 16, 3),
(39, 17, 3),
(39, 18, 3),
(39, 19, 3),
(39, 20, 3),
(39, 21, 3),
(39, 22, 3),
(39, 23, 3),
(39, 24, 3),
(39, 25, 3),
(39, 26, 3),
(39, 27, 3),
(39, 28, 3),
(39, 32, 3),
(39, 33, 3),
(39, 34, 3),
(39, 35, 3),
(39, 36, 3),
(39, 37, 3),
(39, 38, 3),
(39, 39, 3),
(39, 40, 3),
(39, 46, 3),
(39, 47, 3),
(39, 48, 3),
(39, 49, 3),
(39, 50, 3),
(39, 51, 3),
(39, 52, 3),
(39, 53, 3),
(39, 54, 3),
(39, 60, 3),
(39, 61, 3),
(39, 62, 3),
(39, 63, 3),
(39, 64, 3),
(39, 65, 3),
(39, 66, 3),
(39, 67, 3),
(39, 68, 3),
(39, 74, 3),
(39, 75, 3),
(39, 76, 3),
(39, 77, 3),
(39, 78, 3),
(39, 79, 3),
(39, 80, 3),
(39, 81, 3),
(39, 82, 3),
(39, 84, 3),
(39, 85, 3),
(39, 86, 3),
(39, 87, 3),
(39, 88, 3),
(39, 89, 3),
(39, 90, 3),
(39, 91, 3),
(39, 93, 3),
(39, 96, 3),
(39, 97, 3),
(39, 98, 3),
(39, 99, 3),
(40, 5, 3),
(40, 6, 3),
(40, 7, 3),
(40, 8, 3),
(40, 9, 3),
(40, 10, 3),
(40, 12, 3),
(40, 13, 3),
(40, 14, 3),
(40, 15, 3),
(40, 16, 3),
(40, 17, 3),
(40, 18, 3),
(40, 19, 3),
(40, 20, 3),
(40, 21, 3),
(40, 22, 3),
(40, 23, 3),
(40, 24, 3),
(40, 25, 3),
(40, 26, 3),
(40, 27, 3),
(40, 28, 3),
(40, 32, 3),
(40, 33, 3),
(40, 34, 3),
(40, 35, 3),
(40, 36, 3),
(40, 37, 3),
(40, 38, 3),
(40, 39, 3),
(40, 40, 3),
(40, 46, 3),
(40, 47, 3),
(40, 48, 3),
(40, 49, 3),
(40, 50, 3),
(40, 51, 3),
(40, 52, 3),
(40, 53, 3),
(40, 54, 3),
(40, 60, 3),
(40, 61, 3),
(40, 62, 3),
(40, 63, 3),
(40, 64, 3),
(40, 65, 3),
(40, 66, 3),
(40, 67, 3),
(40, 68, 3),
(40, 74, 3),
(40, 75, 3),
(40, 76, 3),
(40, 77, 3),
(40, 78, 3),
(40, 79, 3),
(40, 80, 3),
(40, 81, 3),
(40, 82, 3),
(40, 84, 3),
(40, 85, 3),
(40, 86, 3),
(40, 87, 3),
(40, 88, 3),
(40, 89, 3),
(40, 90, 3),
(40, 91, 3),
(40, 93, 3),
(40, 96, 3),
(40, 97, 3),
(40, 98, 3),
(40, 99, 3),
(41, 5, 3),
(41, 6, 3),
(41, 7, 3),
(41, 8, 3),
(41, 9, 3),
(41, 10, 3),
(41, 12, 3),
(41, 13, 3),
(41, 14, 3),
(41, 15, 3),
(41, 16, 3),
(41, 17, 3),
(41, 18, 3),
(41, 19, 3),
(41, 20, 3),
(41, 21, 3),
(41, 22, 3),
(41, 23, 3),
(41, 24, 3),
(41, 25, 3),
(41, 26, 3),
(41, 27, 3),
(41, 28, 3),
(41, 32, 3),
(41, 33, 3),
(41, 34, 3),
(41, 35, 3),
(41, 36, 3),
(41, 37, 3),
(41, 38, 3),
(41, 39, 3),
(41, 40, 3),
(41, 46, 3),
(41, 47, 3),
(41, 48, 3),
(41, 49, 3),
(41, 50, 3),
(41, 51, 3),
(41, 52, 3),
(41, 53, 3),
(41, 54, 3),
(41, 60, 3),
(41, 61, 3),
(41, 62, 3),
(41, 63, 3),
(41, 64, 3),
(41, 65, 3),
(41, 66, 3),
(41, 67, 3),
(41, 68, 3),
(41, 74, 3),
(41, 75, 3),
(41, 76, 3),
(41, 77, 3),
(41, 78, 3),
(41, 79, 3),
(41, 80, 3),
(41, 81, 3),
(41, 82, 3),
(41, 84, 3),
(41, 85, 3),
(41, 86, 3),
(41, 87, 3),
(41, 88, 3),
(41, 89, 3),
(41, 90, 3),
(41, 91, 3),
(41, 93, 3),
(41, 96, 3),
(41, 97, 3),
(41, 98, 3),
(41, 99, 3),
(42, 5, 3),
(42, 6, 3),
(42, 7, 3),
(42, 8, 3),
(42, 9, 3),
(42, 10, 3),
(42, 12, 3),
(42, 13, 3),
(42, 14, 3),
(42, 15, 3),
(42, 16, 3),
(42, 17, 3),
(42, 18, 3),
(42, 19, 3),
(42, 20, 3),
(42, 21, 3),
(42, 22, 3),
(42, 23, 3),
(42, 24, 3),
(42, 25, 3),
(42, 26, 3),
(42, 27, 3),
(42, 28, 3),
(42, 32, 3),
(42, 33, 3),
(42, 34, 3),
(42, 35, 3),
(42, 36, 3),
(42, 37, 3),
(42, 38, 3),
(42, 39, 3),
(42, 40, 3),
(42, 46, 3),
(42, 47, 3),
(42, 48, 3),
(42, 49, 3),
(42, 50, 3),
(42, 51, 3),
(42, 52, 3),
(42, 53, 3),
(42, 54, 3),
(42, 60, 3),
(42, 61, 3),
(42, 62, 3),
(42, 63, 3),
(42, 64, 3),
(42, 65, 3),
(42, 66, 3),
(42, 67, 3),
(42, 68, 3),
(42, 74, 3),
(42, 75, 3),
(42, 76, 3),
(42, 77, 3),
(42, 78, 3),
(42, 79, 3),
(42, 80, 3),
(42, 81, 3),
(42, 82, 3),
(42, 84, 3),
(42, 85, 3),
(42, 86, 3),
(42, 87, 3),
(42, 88, 3),
(42, 89, 3),
(42, 90, 3),
(42, 91, 3),
(42, 93, 3),
(42, 96, 3),
(42, 97, 3),
(42, 98, 3),
(42, 99, 3),
(43, 5, 3),
(43, 6, 3),
(43, 7, 3),
(43, 8, 3),
(43, 9, 3),
(43, 10, 3),
(43, 12, 3),
(43, 13, 3),
(43, 14, 3),
(43, 15, 3),
(43, 16, 3),
(43, 17, 3),
(43, 18, 3),
(43, 19, 3),
(43, 20, 3),
(43, 21, 3),
(43, 22, 3),
(43, 23, 3),
(43, 24, 3),
(43, 25, 3),
(43, 26, 3),
(43, 27, 3),
(43, 28, 3),
(43, 32, 3),
(43, 33, 3),
(43, 34, 3),
(43, 35, 3),
(43, 36, 3),
(43, 37, 3),
(43, 38, 3),
(43, 39, 3),
(43, 40, 3),
(43, 46, 3),
(43, 47, 3),
(43, 48, 3),
(43, 49, 3),
(43, 50, 3),
(43, 51, 3),
(43, 52, 3),
(43, 53, 3),
(43, 54, 3),
(43, 60, 3),
(43, 61, 3),
(43, 62, 3),
(43, 63, 3),
(43, 64, 3),
(43, 65, 3),
(43, 66, 3),
(43, 67, 3),
(43, 68, 3),
(43, 74, 3),
(43, 75, 3),
(43, 76, 3),
(43, 77, 3),
(43, 78, 3),
(43, 79, 3),
(43, 80, 3),
(43, 81, 3),
(43, 82, 3),
(43, 84, 3),
(43, 85, 3),
(43, 86, 3),
(43, 87, 3),
(43, 88, 3),
(43, 89, 3),
(43, 90, 3),
(43, 91, 3),
(43, 93, 3),
(43, 96, 3),
(43, 97, 3),
(43, 98, 3),
(43, 99, 3),
(44, 5, 3),
(44, 6, 3),
(44, 7, 3),
(44, 8, 3),
(44, 9, 3),
(44, 10, 3),
(44, 12, 3),
(44, 13, 3),
(44, 14, 3),
(44, 15, 3),
(44, 16, 3),
(44, 17, 3),
(44, 18, 3),
(44, 19, 3),
(44, 20, 3),
(44, 21, 3),
(44, 22, 3),
(44, 23, 3),
(44, 24, 3),
(44, 25, 3),
(44, 26, 3),
(44, 27, 3),
(44, 28, 3),
(44, 32, 3),
(44, 34, 3),
(44, 35, 3),
(44, 36, 3),
(44, 37, 3),
(44, 38, 3),
(44, 39, 3),
(44, 40, 3),
(44, 46, 3),
(44, 48, 3),
(44, 49, 3),
(44, 50, 3),
(44, 51, 3),
(44, 52, 3),
(44, 53, 3),
(44, 60, 3),
(44, 62, 3),
(44, 63, 3),
(44, 64, 3),
(44, 65, 3),
(44, 66, 3),
(44, 67, 3),
(44, 74, 3),
(44, 76, 3),
(44, 77, 3),
(44, 78, 3),
(44, 79, 3),
(44, 80, 3),
(44, 81, 3),
(44, 82, 3),
(44, 84, 3),
(44, 85, 3),
(44, 86, 3),
(44, 87, 3),
(44, 88, 3),
(44, 89, 3),
(44, 90, 3),
(44, 91, 3),
(44, 93, 3),
(44, 96, 3),
(44, 97, 3),
(44, 98, 3),
(44, 99, 3),
(498, 96, 3),
(499, 96, 3),
(500, 96, 3),
(501, 96, 3),
(502, 96, 3),
(502, 98, 3),
(502, 99, 3),
(503, 96, 3),
(503, 98, 3),
(503, 99, 3),
(504, 96, 3),
(504, 98, 3),
(504, 99, 3),
(505, 96, 3),
(505, 98, 3),
(505, 99, 3),
(506, 96, 3),
(507, 96, 3),
(507, 98, 3),
(507, 99, 3),
(508, 98, 3),
(508, 99, 3),
(509, 98, 3),
(509, 99, 3),
(510, 98, 3),
(510, 99, 3),
(511, 98, 3),
(511, 99, 3),
(512, 98, 3),
(512, 99, 3),
(513, 98, 3),
(513, 99, 3);

-- --------------------------------------------------------

--
-- Structure de la table `subject_groups`
--

CREATE TABLE `subject_groups` (
  `id` int(11) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `teaching_type_id` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `subject_groups`
--

INSERT INTO `subject_groups` (`id`, `libelle`, `teaching_type_id`, `status`, `created_at`) VALUES
(1, 'Groupe 1 - Matières Littéraires', 3, 1, '2026-07-25 04:07:08'),
(2, 'Groupe 2 - Matières Scientifiques', 3, 1, '2026-07-25 04:07:09'),
(3, 'Groupe 3 - Développement Personnel', 3, 1, '2026-07-25 04:07:09'),
(4, 'UE Fondamentales', 9, 1, '2026-07-25 04:17:36'),
(5, 'UE Professionnelles', 9, 1, '2026-07-25 04:17:59'),
(6, 'UE Transversales', 9, 1, '2026-07-25 04:18:32');

-- --------------------------------------------------------

--
-- Structure de la table `system_job_runs`
--

CREATE TABLE `system_job_runs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_name` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'running',
  `message` text DEFAULT NULL,
  `details` longtext DEFAULT NULL,
  `attempts` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `started_at` datetime NOT NULL DEFAULT current_timestamp(),
  `finished_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `academic_year_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `user_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED DEFAULT NULL,
  `teaching_type_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `teacher_contracts`
--

CREATE TABLE `teacher_contracts` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `contract_type` enum('PERMANENT','VACATAIRE','CONTRACTUEL','STAGIAIRE','SUSPENDU','RETRAITE','INACTIF') NOT NULL DEFAULT 'VACATAIRE',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `teaching_types`
--

CREATE TABLE `teaching_types` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `position` int(11) DEFAULT 0,
  `actif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `teaching_types`
--

INSERT INTO `teaching_types` (`id`, `nom`, `code`, `position`, `actif`, `created_at`, `updated_at`) VALUES
(1, 'Maternelle', 'MAT', 0, 0, '2026-06-17 14:10:48', '2026-07-29 01:18:07'),
(2, 'Primaire', 'PRI', 1, 0, '2026-06-17 14:10:48', '2026-07-29 01:18:12'),
(3, 'Secondaire', 'SEC00', 2, 1, '2026-06-17 14:10:48', '2026-08-08 05:35:01'),
(9, 'Supérieur', 'LMD', 4, 0, '2026-06-22 18:26:40', '2026-08-21 01:54:32');

-- --------------------------------------------------------

--
-- Structure de la table `timetables`
--

CREATE TABLE `timetables` (
  `id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `teaching_type_id` int(11) NOT NULL,
  `cycle_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `week_id` int(11) NOT NULL,
  `titre` varchar(150) NOT NULL,
  `statut` enum('brouillon','publie','verrouille') NOT NULL DEFAULT 'brouillon',
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `timetables`
--

INSERT INTO `timetables` (`id`, `academic_year_id`, `teaching_type_id`, `cycle_id`, `class_id`, `week_id`, `titre`, `statut`, `is_locked`, `created_by`, `created_at`, `updated_at`) VALUES
(12, 3, 9, 14, 116, 1, 'Emploi du Temps - MSI 1 (Semaine du 06/08/2026)', 'verrouille', 1, 42, '2026-08-06 02:15:36', '2026-08-21 02:40:15'),
(13, 3, 9, 14, 117, 1, 'Emploi du Temps - RS 1 (Semaine du 06/08/2026)', 'verrouille', 1, 42, '2026-08-06 02:15:36', '2026-08-21 02:40:15'),
(16, 3, 9, 14, 118, 1, 'Emploi du Temps - TLecom 1 (Semaine du 06/08/2026)', 'verrouille', 1, 42, '2026-08-06 05:08:55', '2026-08-21 02:42:06'),
(21, 3, 3, 2, 10, 2, 'Emploi du Temps - 2nd STT (Semaine du 17/08/2026)', 'brouillon', 0, 1, '2026-08-07 16:01:49', '2026-08-07 16:01:49'),
(22, 3, 3, 2, 12, 2, 'Emploi du Temps - 2nd C (Semaine du 17/08/2026)', 'brouillon', 0, 1, '2026-08-07 16:01:49', '2026-08-07 16:01:49'),
(28, 3, 9, 14, 115, 3, 'Emploi du Temps - BAT 2 (Semaine du 24/08/2026)', 'brouillon', 0, 67, '2026-08-11 04:28:40', '2026-08-11 04:28:40'),
(29, 3, 9, 14, 113, 3, 'Emploi du Temps - IGL 2 (Semaine du 24/08/2026)', 'brouillon', 0, 67, '2026-08-11 04:28:41', '2026-08-11 04:28:41'),
(43, 3, 3, 3, 86, 3, 'Emploi du Temps - 1 ère année COME (Semaine du 24/08/2026)', 'brouillon', 0, 40, '2026-08-21 04:52:17', '2026-08-21 04:52:17'),
(44, 3, 3, 3, 62, 3, 'Emploi du Temps - 1 ère Année ELECT (Semaine du 24/08/2026)', 'brouillon', 0, 40, '2026-08-21 04:52:17', '2026-08-21 04:52:17'),
(45, 3, 3, 3, 77, 3, 'Emploi du Temps - 1 ère année escom (Semaine du 24/08/2026)', 'brouillon', 0, 40, '2026-08-21 04:52:17', '2026-08-21 04:52:17'),
(46, 3, 3, 3, 48, 3, 'Emploi du Temps - 1 ère Année MACO (Semaine du 24/08/2026)', 'brouillon', 0, 40, '2026-08-21 04:52:17', '2026-08-21 04:52:17'),
(47, 3, 3, 3, 81, 3, 'Emploi du Temps - 1 ère année MARE (Semaine du 24/08/2026)', 'brouillon', 0, 40, '2026-08-21 04:52:17', '2026-08-21 04:52:17'),
(48, 3, 3, 3, 34, 3, 'Emploi du Temps - 1 ère Année MEFE (Semaine du 24/08/2026)', 'brouillon', 0, 40, '2026-08-21 04:52:17', '2026-08-21 04:52:17');

-- --------------------------------------------------------

--
-- Structure de la table `timetable_audit_logs`
--

CREATE TABLE `timetable_audit_logs` (
  `id` int(11) NOT NULL,
  `timetable_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` enum('LOCK','UNLOCK','FORCE_EDIT','DELETE','PUBLISH') NOT NULL,
  `details` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `timetable_audit_logs`
--

INSERT INTO `timetable_audit_logs` (`id`, `timetable_id`, `user_id`, `action_type`, `details`, `ip_address`, `created_at`) VALUES
(1, 10, 42, '', 'Rattachement automatique de la matière \'Mathematique Avancé\' à la classe \'GSI 1\'.', '::1', '2026-08-06 02:39:07'),
(2, 12, 42, '', 'Rattachement automatique de la matière \'Mathematique Avancé\' à la classe \'MSI 1\'.', '::1', '2026-08-06 02:40:08'),
(3, 13, 42, '', 'Rattachement automatique de la matière \'Mathematique Avancé\' à la classe \'RS 1\'.', '::1', '2026-08-06 02:40:27'),
(5, 10, 42, '', 'Rattachement automatique de la matière \'Algorithme de base\' à la classe \'GSI 1\'.', '::1', '2026-08-06 05:08:54'),
(6, 12, 42, '', 'Rattachement automatique de la matière \'Algorithme de base\' à la classe \'MSI 1\'.', '::1', '2026-08-06 05:08:54'),
(7, 13, 42, '', 'Rattachement automatique de la matière \'Algorithme de base\' à la classe \'RS 1\'.', '::1', '2026-08-06 05:08:54'),
(8, 16, 42, '', 'Rattachement automatique de la matière \'Algorithme de base\' à la classe \'TLecom 1\'.', '::1', '2026-08-06 05:08:55'),
(9, 21, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #8).', '127.0.0.1', '2026-08-07 16:01:49'),
(10, 22, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #4).', '127.0.0.1', '2026-08-07 16:01:50'),
(11, 21, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #8).', '127.0.0.1', '2026-08-07 16:02:16'),
(12, 22, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #4).', '127.0.0.1', '2026-08-07 16:02:16'),
(13, 21, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #8).', '127.0.0.1', '2026-08-07 16:11:45'),
(14, 22, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #4).', '127.0.0.1', '2026-08-07 16:11:45'),
(15, 21, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #8).', '127.0.0.1', '2026-08-07 16:15:49'),
(16, 22, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #4).', '127.0.0.1', '2026-08-07 16:15:49'),
(17, 12, 42, '', 'Planification en masse : Cours de \'528\' affecté le Lundi (Créneau #1, Salle #4).', '::1', '2026-08-07 16:22:52'),
(18, 10, 42, '', 'Planification en masse : Cours de \'528\' affecté le Lundi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:52'),
(19, 11, 42, '', 'Planification en masse : Cours de \'528\' affecté le Lundi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:52'),
(20, 12, 42, '', 'Planification en masse : Cours de \'528\' affecté le Lundi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:52'),
(21, 13, 42, '', 'Planification en masse : Cours de \'528\' affecté le Lundi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:52'),
(22, 16, 42, '', 'Planification en masse : Cours de \'528\' affecté le Lundi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:52'),
(23, 10, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mardi (Créneau #1, Salle #4).', '::1', '2026-08-07 16:22:52'),
(24, 11, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mardi (Créneau #1, Salle #4).', '::1', '2026-08-07 16:22:52'),
(25, 12, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mardi (Créneau #1, Salle #4).', '::1', '2026-08-07 16:22:52'),
(26, 13, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mardi (Créneau #1, Salle #4).', '::1', '2026-08-07 16:22:52'),
(27, 16, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mardi (Créneau #1, Salle #4).', '::1', '2026-08-07 16:22:52'),
(28, 10, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mardi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:53'),
(29, 11, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mardi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:53'),
(30, 12, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mardi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:53'),
(31, 13, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mardi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:53'),
(32, 16, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mardi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:53'),
(33, 10, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mercredi (Créneau #1, Salle #4).', '::1', '2026-08-07 16:22:53'),
(34, 11, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mercredi (Créneau #1, Salle #4).', '::1', '2026-08-07 16:22:53'),
(35, 12, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mercredi (Créneau #1, Salle #4).', '::1', '2026-08-07 16:22:53'),
(36, 13, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mercredi (Créneau #1, Salle #4).', '::1', '2026-08-07 16:22:53'),
(37, 16, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mercredi (Créneau #1, Salle #4).', '::1', '2026-08-07 16:22:53'),
(38, 10, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mercredi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:53'),
(39, 11, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mercredi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:53'),
(40, 12, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mercredi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:53'),
(41, 13, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mercredi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:53'),
(42, 16, 42, '', 'Planification en masse : Cours de \'528\' affecté le Mercredi (Créneau #10, Salle #4).', '::1', '2026-08-07 16:22:53'),
(43, 21, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #8).', '127.0.0.1', '2026-08-07 16:30:57'),
(44, 22, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #4).', '127.0.0.1', '2026-08-07 16:30:57'),
(45, 21, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #8).', '127.0.0.1', '2026-08-07 16:47:09'),
(46, 22, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #4).', '127.0.0.1', '2026-08-07 16:47:09'),
(47, 21, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #8).', '127.0.0.1', '2026-08-07 17:11:16'),
(48, 22, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #4).', '127.0.0.1', '2026-08-07 17:11:16'),
(49, 21, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #8).', '127.0.0.1', '2026-08-07 17:15:49'),
(50, 22, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #4).', '127.0.0.1', '2026-08-07 17:15:49'),
(51, 21, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #8).', '127.0.0.1', '2026-08-07 17:20:35'),
(52, 22, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #4).', '127.0.0.1', '2026-08-07 17:20:35'),
(53, 21, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #8).', '127.0.0.1', '2026-08-07 17:25:17'),
(54, 22, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #4).', '127.0.0.1', '2026-08-07 17:25:17'),
(55, 21, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #8).', '127.0.0.1', '2026-08-07 17:28:52'),
(56, 22, 1, '', 'Planification en masse : Cours de \'15\' affecté le Lundi (Créneau #1, Salle #4).', '127.0.0.1', '2026-08-07 17:28:53'),
(59, 12, 42, '', 'Dépublication de l\'emploi du temps (remise en brouillon).', '::1', '2026-08-11 04:01:33'),
(60, 13, 42, '', 'Dépublication de l\'emploi du temps (remise en brouillon).', '::1', '2026-08-11 04:01:33'),
(61, 16, 42, '', 'Dépublication de l\'emploi du temps (remise en brouillon).', '::1', '2026-08-11 04:01:33'),
(62, 12, 42, '', 'Publication officielle de l\'emploi du temps.', '::1', '2026-08-11 04:02:25'),
(63, 13, 42, '', 'Publication officielle de l\'emploi du temps.', '::1', '2026-08-11 04:02:25'),
(64, 16, 42, '', 'Publication officielle de l\'emploi du temps.', '::1', '2026-08-11 04:02:25'),
(65, 12, 40, 'LOCK', 'Verrouillage automatique déclenché après 168h (7 jours post-semaine).', '::1', '2026-08-21 02:40:15'),
(127, 16, 40, 'LOCK', 'Verrouillage automatique déclenché après 168h (7 jours post-semaine).', '::1', '2026-08-21 02:42:06');

-- --------------------------------------------------------

--
-- Structure de la table `timetable_entries`
--

CREATE TABLE `timetable_entries` (
  `id` int(11) NOT NULL,
  `timetable_id` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `day_of_week` enum('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `couleur_hex` varchar(7) DEFAULT '#3b82f6',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `timetable_entries`
--

INSERT INTO `timetable_entries` (`id`, `timetable_id`, `slot_id`, `day_of_week`, `subject_id`, `teacher_id`, `room_id`, `couleur_hex`, `created_at`, `updated_at`) VALUES
(20, 12, 11, 'Lundi', 530, 62, 7, '#3b82f6', '2026-08-06 02:40:08', '2026-08-06 02:40:08'),
(21, 13, 11, 'Lundi', 530, 62, 7, '#3b82f6', '2026-08-06 02:40:26', '2026-08-06 02:40:26'),
(26, 12, 11, 'Mardi', 528, 61, 1, '#f7733b', '2026-08-06 05:08:54', '2026-08-06 05:08:54'),
(27, 13, 11, 'Mardi', 528, 61, 1, '#f7733b', '2026-08-06 05:08:54', '2026-08-06 05:08:54'),
(28, 16, 11, 'Mardi', 528, 61, 1, '#f7733b', '2026-08-06 05:08:55', '2026-08-06 05:08:55'),
(29, 12, 1, 'Lundi', 528, 41, 4, '#f73b3b', '2026-08-06 06:03:45', '2026-08-07 16:22:52'),
(30, 21, 1, 'Lundi', 15, 40, 8, '#3b82f6', '2026-08-07 16:01:49', '2026-08-07 16:01:49'),
(31, 22, 1, 'Lundi', 15, 40, 4, '#3b82f6', '2026-08-07 16:01:50', '2026-08-07 16:01:50'),
(41, 12, 10, 'Lundi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:52', '2026-08-07 16:22:52'),
(42, 13, 10, 'Lundi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:52', '2026-08-07 16:22:52'),
(43, 16, 10, 'Lundi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:52', '2026-08-07 16:22:52'),
(46, 12, 1, 'Mardi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:52', '2026-08-07 16:22:52'),
(47, 13, 1, 'Mardi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:52', '2026-08-07 16:22:52'),
(48, 16, 1, 'Mardi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:52', '2026-08-07 16:22:52'),
(51, 12, 10, 'Mardi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:53', '2026-08-07 16:22:53'),
(52, 13, 10, 'Mardi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:53', '2026-08-07 16:22:53'),
(53, 16, 10, 'Mardi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:53', '2026-08-07 16:22:53'),
(56, 12, 1, 'Mercredi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:53', '2026-08-07 16:22:53'),
(57, 13, 1, 'Mercredi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:53', '2026-08-07 16:22:53'),
(58, 16, 1, 'Mercredi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:53', '2026-08-07 16:22:53'),
(61, 12, 10, 'Mercredi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:53', '2026-08-07 16:22:53'),
(62, 13, 10, 'Mercredi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:53', '2026-08-07 16:22:53'),
(63, 16, 10, 'Mercredi', 528, 41, 4, '#f73b3b', '2026-08-07 16:22:53', '2026-08-07 16:22:53');

-- --------------------------------------------------------

--
-- Structure de la table `timetable_time_slots`
--

CREATE TABLE `timetable_time_slots` (
  `id` int(11) NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `type_creneau` enum('cours','pause') NOT NULL DEFAULT 'cours',
  `duree_minutes` int(11) NOT NULL,
  `ordre_affichage` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `timetable_time_slots`
--

INSERT INTO `timetable_time_slots` (`id`, `heure_debut`, `heure_fin`, `type_creneau`, `duree_minutes`, `ordre_affichage`, `created_at`, `updated_at`) VALUES
(1, '08:00:00', '10:00:00', 'cours', 120, 1, '2026-08-04 03:53:05', '2026-08-07 15:32:31'),
(10, '10:00:00', '12:00:00', 'cours', 120, 2, '2026-08-04 04:14:37', '2026-08-07 15:33:01'),
(11, '12:00:00', '13:00:00', 'pause', 60, 3, '2026-08-04 04:14:54', '2026-08-07 15:33:20'),
(12, '13:00:00', '15:00:00', 'cours', 120, 4, '2026-08-07 15:34:08', '2026-08-07 15:34:08'),
(13, '15:00:00', '17:00:00', 'cours', 120, 5, '2026-08-07 15:34:20', '2026-08-07 15:34:20');

-- --------------------------------------------------------

--
-- Structure de la table `timetable_weeks`
--

CREATE TABLE `timetable_weeks` (
  `id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `timetable_weeks`
--

INSERT INTO `timetable_weeks` (`id`, `academic_year_id`, `libelle`, `date_debut`, `date_fin`, `created_at`, `updated_at`) VALUES
(1, 3, 'Semaine du 06/08/2026', '2026-08-06', '2026-08-11', '2026-08-04 04:17:38', '2026-08-04 04:17:38'),
(2, 3, 'Semaine du 17/08/2026', '2026-08-17', '2026-08-22', '2026-08-07 03:16:08', '2026-08-07 03:16:08'),
(3, 3, 'Semaine du 24/08/2026', '2026-08-24', '2026-08-30', '2026-08-10 01:39:06', '2026-08-10 01:39:06');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'enseignant',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `username`, `email`, `password`, `role`, `created_at`, `updated_at`, `status`) VALUES
(39, 'caisse futura', 'caisse', 'mira', 'futura-col@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$TUxkNk5pejBGaFhCZ2ZrMA$Htv9XIlzMFoKRvYt6HZF3lE0pktcQf48AkONPX61S8M', 'caissier', '2026-05-11 13:26:49', '2026-07-09 09:45:25', 1),
(40, 'Directeur', 'Directeur', 'admin', 'directeur@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$eHRIZU5QMWRCWVR5WUthMA$57iYZ/o3UszJJ309VIPFt5MDm7iPB4Nc/jl9B/kpa3A', 'admin', '2026-05-19 14:15:45', '2026-08-11 01:48:58', 1),
(42, 'Sup', 'sup', 'sup', NULL, '$argon2id$v=19$m=65536,t=4,p=1$ZktDOFQ1a0lqeVdYRlNPcQ$h4TBqD9AVZ5zOBhlc+fBjTb9Xbx/g0eSHx16UYb7N7s', 'superadmin', '2026-06-25 12:12:10', '2026-06-26 09:13:13', 1),
(43, 'It', 'Manager', 'It', NULL, '$argon2id$v=19$m=65536,t=4,p=1$WnJPbTI2MjJIcERabDlHag$OHHrkH6EcqB7VV1dNkBlwpQgilpUCHcXopxBL8EWh/g', 'it_manager', '2026-06-27 12:43:24', '2026-06-27 12:43:24', 1),
(67, 'DAAC', 'TATANG', 'daac', NULL, '$argon2id$v=19$m=65536,t=4,p=1$Z0N3R3E4M0IuWDh6ZWJwbg$gHOhqInzsMHWceVfFctp588SrIyxP3pC5kJzNRg9hjw', 'direction_academique', '2026-08-11 04:24:26', '2026-08-11 04:24:26', 1);

-- --------------------------------------------------------

--
-- Structure de la table `user_departments`
--

CREATE TABLE `user_departments` (
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `is_granted` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Accordé explicitement, 0 = Interdit explicitement',
  `granted_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_teaching_types`
--

CREATE TABLE `user_teaching_types` (
  `user_id` int(11) NOT NULL,
  `teaching_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_teaching_types`
--

INSERT INTO `user_teaching_types` (`user_id`, `teaching_type_id`) VALUES
(41, 3),
(41, 9),
(44, 3),
(57, 9),
(58, 9),
(59, 9),
(60, 9),
(61, 9),
(62, 9),
(63, 3),
(63, 9),
(65, 9);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_logs_created_at` (`created_at`),
  ADD KEY `idx_activity_logs_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_activity_logs_type_created` (`event_type`,`created_at`),
  ADD KEY `idx_activity_logs_category_created` (`event_category`,`created_at`);

--
-- Index pour la table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`),
  ADD KEY `cycle_id` (`cycle_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `fk_main_teacher` (`main_teacher_id`),
  ADD KEY `fk_classes_department` (`department_id`),
  ADD KEY `fk_classes_level` (`level_id`);

--
-- Index pour la table `class_discounts`
--
ALTER TABLE `class_discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `fk_class_discounts_type_id` (`discount_type_id`);

--
-- Index pour la table `class_installments`
--
ALTER TABLE `class_installments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_class_inst` (`class_id`,`installment_number`);

--
-- Index pour la table `class_rooms`
--
ALTER TABLE `class_rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `class_scholarships`
--
ALTER TABLE `class_scholarships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `fk_class_scholarships_type_id` (`discount_type_id`);

--
-- Index pour la table `cycles`
--
ALTER TABLE `cycles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`),
  ADD KEY `fk_cycle_teaching_type` (`teaching_type_id`);

--
-- Index pour la table `cycle_levels`
--
ALTER TABLE `cycle_levels`
  ADD PRIMARY KEY (`cycle_id`,`level_id`),
  ADD KEY `fk_cycle_levels_level` (`level_id`);

--
-- Index pour la table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`),
  ADD KEY `fk_dept_teaching_type` (`teaching_type_id`);

--
-- Index pour la table `discipline`
--
ALTER TABLE `discipline`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`academic_year_id`,`periode`),
  ADD KEY `academic_year_id` (`academic_year_id`);

--
-- Index pour la table `discount_types`
--
ALTER TABLE `discount_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_name` (`name`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_student_year` (`student_id`,`academic_year_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `academic_year_id` (`academic_year_id`);

--
-- Index pour la table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `fk_expenses_category` (`category_id`),
  ADD KEY `fk_expenses_user` (`user_id`),
  ADD KEY `fk_expenses_academic_year` (`academic_year_id`);

--
-- Index pour la table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `expense_logs`
--
ALTER TABLE `expense_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_expense_logs_expense` (`expense_id`),
  ADD KEY `fk_expense_logs_category` (`category_id`),
  ADD KEY `fk_expense_logs_user` (`user_id`);

--
-- Index pour la table `fee_installments`
--
ALTER TABLE `fee_installments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fee_installments_year` (`academic_year_id`),
  ADD KEY `idx_fee_installments_class` (`class_id`),
  ADD KEY `idx_fee_installments_cycle` (`cycle_id`),
  ADD KEY `idx_fee_installments_teaching_type` (`teaching_type_id`);

--
-- Index pour la table `financial_history`
--
ALTER TABLE `financial_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_subject_period` (`student_id`,`subject_id`,`periode`,`academic_year_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `academic_year_id` (`academic_year_id`),
  ADD KEY `idx_grades_stats` (`teacher_id`,`academic_year_id`,`subject_id`),
  ADD KEY `sequence_id` (`sequence_id`);

--
-- Index pour la table `insolvent_students`
--
ALTER TABLE `insolvent_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_insolvent_student_year` (`student_id`,`academic_year_id`),
  ADD KEY `academic_year_id` (`academic_year_id`);

--
-- Index pour la table `installment_deadlines`
--
ALTER TABLE `installment_deadlines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_deadlines_class_year` (`class_id`,`academic_year_id`),
  ADD KEY `academic_year_id` (`academic_year_id`);

--
-- Index pour la table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_levels_teaching_type` (`teaching_type_id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `migration` (`migration`);

--
-- Index pour la table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `verification_code` (`verification_code`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `academic_year_id` (`academic_year_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_payments_cancelled_by` (`cancelled_by`);

--
-- Index pour la table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD UNIQUE KEY `verification_code` (`verification_code`),
  ADD KEY `idx_payment_receipts_pay` (`student_payment_id`);

--
-- Index pour la table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `perm_code` (`perm_code`);

--
-- Index pour la table `permission_audit_logs`
--
ALTER TABLE `permission_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action_type`);

--
-- Index pour la table `permission_backups`
--
ALTER TABLE `permission_backups`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `receipt_verifications_log`
--
ALTER TABLE `receipt_verifications_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_verif_code` (`verification_code`),
  ADD KEY `idx_verif_payment` (`payment_id`),
  ADD KEY `idx_verif_student` (`student_id`),
  ADD KEY `idx_verif_year` (`academic_year_id`),
  ADD KEY `idx_verif_date` (`verified_at`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_code` (`role_code`);

--
-- Index pour la table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_role_permissions_permission` (`permission_id`);

--
-- Index pour la table `school_fees`
--
ALTER TABLE `school_fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school_fees_year` (`academic_year_id`),
  ADD KEY `idx_school_fees_class` (`class_id`),
  ADD KEY `idx_school_fees_cycle` (`cycle_id`),
  ADD KEY `idx_school_fees_teaching_type` (`teaching_type_id`);

--
-- Index pour la table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `sequences`
--
ALTER TABLE `sequences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sequence_teaching_type` (`teaching_type_id`);

--
-- Index pour la table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`,`teaching_type_id`);

--
-- Index pour la table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_students_email` (`email`),
  ADD KEY `idx_students_class` (`class_id`),
  ADD KEY `fk_students_created_by` (`created_by`);

--
-- Index pour la table `student_discounts`
--
ALTER TABLE `student_discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fk_student_discounts_type_id` (`discount_type_id`);

--
-- Index pour la table `student_installments`
--
ALTER TABLE `student_installments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_stud_inst` (`student_id`,`academic_year_id`,`installment_number`),
  ADD KEY `academic_year_id` (`academic_year_id`);

--
-- Index pour la table `student_payments`
--
ALTER TABLE `student_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_payments_student` (`student_id`),
  ADD KEY `idx_student_payments_year` (`academic_year_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_student_payments_cancelled_by` (`cancelled_by`);

--
-- Index pour la table `student_payment_allocations`
--
ALTER TABLE `student_payment_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_allocations_payment` (`student_payment_id`),
  ADD KEY `idx_allocations_installment` (`student_installment_id`);

--
-- Index pour la table `student_scholarships`
--
ALTER TABLE `student_scholarships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fk_student_scholarships_type_id` (`discount_type_id`);

--
-- Index pour la table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_subject_dept` (`department_id`);

--
-- Index pour la table `subject_classes`
--
ALTER TABLE `subject_classes`
  ADD PRIMARY KEY (`subject_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Index pour la table `subject_groups`
--
ALTER TABLE `subject_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teaching_type_id` (`teaching_type_id`);

--
-- Index pour la table `system_job_runs`
--
ALTER TABLE `system_job_runs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_system_job_runs_name_started` (`job_name`,`started_at`),
  ADD KEY `idx_system_job_runs_status_started` (`status`,`started_at`);

--
-- Index pour la table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`user_id`,`subject_id`,`class_id`),
  ADD UNIQUE KEY `idx_teacher_unique_assignment` (`class_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `idx_ta_user` (`user_id`);

--
-- Index pour la table `teacher_contracts`
--
ALTER TABLE `teacher_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_teacher_year_contract` (`teacher_id`,`academic_year_id`),
  ADD KEY `academic_year_id` (`academic_year_id`);

--
-- Index pour la table `teaching_types`
--
ALTER TABLE `teaching_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_class_week` (`class_id`,`week_id`);

--
-- Index pour la table `timetable_audit_logs`
--
ALTER TABLE `timetable_audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `timetable_entries`
--
ALTER TABLE `timetable_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_slot_day_class` (`timetable_id`,`slot_id`,`day_of_week`);

--
-- Index pour la table `timetable_time_slots`
--
ALTER TABLE `timetable_time_slots`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `timetable_weeks`
--
ALTER TABLE `timetable_weeks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_year_date_debut` (`academic_year_id`,`date_debut`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Index pour la table `user_departments`
--
ALTER TABLE `user_departments`
  ADD PRIMARY KEY (`user_id`,`department_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Index pour la table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`user_id`,`permission_id`),
  ADD KEY `fk_user_permissions_permission` (`permission_id`);

--
-- Index pour la table `user_teaching_types`
--
ALTER TABLE `user_teaching_types`
  ADD PRIMARY KEY (`user_id`,`teaching_type_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=182;

--
-- AUTO_INCREMENT pour la table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT pour la table `class_discounts`
--
ALTER TABLE `class_discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `class_installments`
--
ALTER TABLE `class_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT pour la table `class_rooms`
--
ALTER TABLE `class_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `class_scholarships`
--
ALTER TABLE `class_scholarships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `cycles`
--
ALTER TABLE `cycles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT pour la table `discipline`
--
ALTER TABLE `discipline`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `discount_types`
--
ALTER TABLE `discount_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `expense_logs`
--
ALTER TABLE `expense_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `fee_installments`
--
ALTER TABLE `fee_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT pour la table `financial_history`
--
ALTER TABLE `financial_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT pour la table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `insolvent_students`
--
ALTER TABLE `insolvent_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `installment_deadlines`
--
ALTER TABLE `installment_deadlines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pour la table `levels`
--
ALTER TABLE `levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=335;

--
-- AUTO_INCREMENT pour la table `permission_audit_logs`
--
ALTER TABLE `permission_audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `permission_backups`
--
ALTER TABLE `permission_backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `receipt_verifications_log`
--
ALTER TABLE `receipt_verifications_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `school_fees`
--
ALTER TABLE `school_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `sequences`
--
ALTER TABLE `sequences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25420;

--
-- AUTO_INCREMENT pour la table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=405;

--
-- AUTO_INCREMENT pour la table `student_discounts`
--
ALTER TABLE `student_discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `student_installments`
--
ALTER TABLE `student_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT pour la table `student_payments`
--
ALTER TABLE `student_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `student_payment_allocations`
--
ALTER TABLE `student_payment_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `student_scholarships`
--
ALTER TABLE `student_scholarships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=531;

--
-- AUTO_INCREMENT pour la table `subject_groups`
--
ALTER TABLE `subject_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `system_job_runs`
--
ALTER TABLE `system_job_runs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `teacher_contracts`
--
ALTER TABLE `teacher_contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `teaching_types`
--
ALTER TABLE `teaching_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT pour la table `timetable_audit_logs`
--
ALTER TABLE `timetable_audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT pour la table `timetable_entries`
--
ALTER TABLE `timetable_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT pour la table `timetable_time_slots`
--
ALTER TABLE `timetable_time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `timetable_weeks`
--
ALTER TABLE `timetable_weeks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_classes_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_main_teacher` FOREIGN KEY (`main_teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `class_discounts`
--
ALTER TABLE `class_discounts`
  ADD CONSTRAINT `class_discounts_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_class_discounts_type_id` FOREIGN KEY (`discount_type_id`) REFERENCES `discount_types` (`id`);

--
-- Contraintes pour la table `class_installments`
--
ALTER TABLE `class_installments`
  ADD CONSTRAINT `class_installments_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `class_scholarships`
--
ALTER TABLE `class_scholarships`
  ADD CONSTRAINT `class_scholarships_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_class_scholarships_type_id` FOREIGN KEY (`discount_type_id`) REFERENCES `discount_types` (`id`);

--
-- Contraintes pour la table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `fk_dept_teaching_type` FOREIGN KEY (`teaching_type_id`) REFERENCES `teaching_types` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `discipline`
--
ALTER TABLE `discipline`
  ADD CONSTRAINT `discipline_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discipline_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `discount_types`
--
ALTER TABLE `discount_types`
  ADD CONSTRAINT `discount_types_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_3` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `fee_installments`
--
ALTER TABLE `fee_installments`
  ADD CONSTRAINT `fee_installments_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_installments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fee_installments_ibfk_3` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fee_installments_ibfk_4` FOREIGN KEY (`teaching_type_id`) REFERENCES `teaching_types` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `financial_history`
--
ALTER TABLE `financial_history`
  ADD CONSTRAINT `financial_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_fk_teacher_safe` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_4` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `grades_ibfk_5` FOREIGN KEY (`sequence_id`) REFERENCES `sequences` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `insolvent_students`
--
ALTER TABLE `insolvent_students`
  ADD CONSTRAINT `insolvent_students_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `insolvent_students_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `installment_deadlines`
--
ALTER TABLE `installment_deadlines`
  ADD CONSTRAINT `installment_deadlines_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installment_deadlines_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  ADD CONSTRAINT `payment_receipts_ibfk_1` FOREIGN KEY (`student_payment_id`) REFERENCES `student_payments` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `school_fees`
--
ALTER TABLE `school_fees`
  ADD CONSTRAINT `school_fees_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `school_fees_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `school_fees_ibfk_3` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `school_fees_ibfk_4` FOREIGN KEY (`teaching_type_id`) REFERENCES `teaching_types` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `students_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `student_discounts`
--
ALTER TABLE `student_discounts`
  ADD CONSTRAINT `fk_student_discounts_type_id` FOREIGN KEY (`discount_type_id`) REFERENCES `discount_types` (`id`),
  ADD CONSTRAINT `student_discounts_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `student_installments`
--
ALTER TABLE `student_installments`
  ADD CONSTRAINT `student_installments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_installments_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `student_payments`
--
ALTER TABLE `student_payments`
  ADD CONSTRAINT `student_payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_payments_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_payments_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `student_payment_allocations`
--
ALTER TABLE `student_payment_allocations`
  ADD CONSTRAINT `student_payment_allocations_ibfk_1` FOREIGN KEY (`student_payment_id`) REFERENCES `student_payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_payment_allocations_ibfk_2` FOREIGN KEY (`student_installment_id`) REFERENCES `student_installments` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `student_scholarships`
--
ALTER TABLE `student_scholarships`
  ADD CONSTRAINT `fk_student_scholarships_type_id` FOREIGN KEY (`discount_type_id`) REFERENCES `discount_types` (`id`),
  ADD CONSTRAINT `student_scholarships_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_subject_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `subject_classes`
--
ALTER TABLE `subject_classes`
  ADD CONSTRAINT `subject_classes_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD CONSTRAINT `teacher_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_assignments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_assignments_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `teacher_contracts`
--
ALTER TABLE `teacher_contracts`
  ADD CONSTRAINT `fk_teacher_contracts_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_teacher_contracts_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_departments`
--
ALTER TABLE `user_departments`
  ADD CONSTRAINT `user_departments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_departments_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
