
-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 26 juin 2026 à 09:30
-- Version du serveur : 11.8.6-MariaDB-log
-- Version de PHP : 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `u290233073_col_futura_db`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `user_role`, `event_type`, `event_category`, `route`, `http_method`, `entity_type`, `entity_id`, `event_count`, `metadata`, `ip_address`, `user_agent`, `created_at`, `academic_year_id`) VALUES
(3, 39, 'superadministrateur', 'auth_login', 'authentication', '/login', 'POST', NULL, NULL, 1, NULL, '129.0.99.119', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 12:05:22', NULL);
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
  `frais_inscription` decimal(15,2) NOT NULL DEFAULT 0.00,
  `frais_inscription_reinscription` decimal(15,2) NOT NULL DEFAULT 0.00,
  `frais_scolarite_brut` decimal(15,2) NOT NULL DEFAULT 0.00,
  `nbr_tranches` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `classes`
--

INSERT INTO `classes` (`id`, `nom`, `cycle_id`, `section_id`, `department_id`, `main_teacher_id`, `created_at`, `teaching_type_id`, `frais_inscription`, `frais_inscription_reinscription`, `frais_scolarite_brut`, `nbr_tranches`) VALUES
(2, 'FORM 1 COM', 3, 2, 4, NULL, '2026-05-03 07:45:00', 3, 0.00, 0.00, 0.00, 0),
(3, 'FORM 2 COM', 3, 2, 4, NULL, '2026-05-03 12:01:53', 3, 0.00, 0.00, 0.00, 0),
(5, '5 ème', 3, 1, 4, NULL, '2026-05-06 02:14:30', 3, 0.00, 0.00, 0.00, 0),
(6, '4 éme ESP', 3, 1, 4, NULL, '2026-05-06 02:14:49', 3, 0.00, 0.00, 0.00, 0),
(7, '4éme ALL', 3, 1, 4, NULL, '2026-05-06 02:15:06', 3, 0.00, 0.00, 0.00, 0),
(8, '3 éme ESP', 3, 1, 4, NULL, '2026-05-06 02:15:33', 3, 0.00, 0.00, 0.00, 0),
(9, '3 éme ALL', 3, 1, 4, NULL, '2026-05-06 02:15:46', 3, 0.00, 0.00, 0.00, 0),
(10, '2nd STT', 2, 1, 9, NULL, '2026-05-06 02:16:09', 3, 0.00, 0.00, 0.00, 0),
(12, '2nd C', 2, 1, 4, NULL, '2026-05-06 02:16:41', 3, 0.00, 0.00, 0.00, 0),
(13, '2nd A4 ESP', 2, 1, 4, NULL, '2026-05-06 02:17:02', 3, 0.00, 0.00, 0.00, 0),
(14, '2nd A4 ALL', 2, 1, 4, NULL, '2026-05-06 02:17:15', 3, 0.00, 0.00, 0.00, 0),
(15, '1 ére A4 ESP', 2, 1, 4, NULL, '2026-05-06 02:17:41', 3, 0.00, 0.00, 0.00, 0),
(16, '1 ére A4 ALL', 2, 1, 4, NULL, '2026-05-06 02:18:01', 3, 0.00, 0.00, 0.00, 0),
(17, '1 ère CG', 2, 1, 9, NULL, '2026-05-06 02:18:24', 3, 0.00, 0.00, 0.00, 0),
(18, '1 ère ACA', 2, 1, 9, NULL, '2026-05-06 02:18:46', 3, 0.00, 0.00, 0.00, 0),
(19, '1 ère ACC', 2, 1, 9, NULL, '2026-05-06 02:19:03', 3, 0.00, 0.00, 0.00, 0),
(20, '1 ére C', 2, 1, 4, NULL, '2026-05-06 02:19:21', 3, 0.00, 0.00, 0.00, 0),
(21, '1 ère D', 2, 1, 4, NULL, '2026-05-06 02:19:36', 3, 0.00, 0.00, 0.00, 0),
(22, 'TLe A4 ESP', 2, 1, 4, NULL, '2026-05-06 02:20:20', 3, 0.00, 0.00, 0.00, 0),
(23, 'TLe A4 ALL', 2, 1, 4, NULL, '2026-05-06 02:20:31', 3, 0.00, 0.00, 0.00, 0),
(24, 'TLe D', 2, 1, 4, NULL, '2026-05-06 02:20:45', 3, 0.00, 0.00, 0.00, 0),
(25, 'TLe C', 2, 1, 4, NULL, '2026-05-06 02:20:57', 3, 0.00, 0.00, 0.00, 0),
(26, 'TLe ACA', 2, 1, 9, NULL, '2026-05-06 02:21:13', 3, 0.00, 0.00, 0.00, 0),
(27, 'TLe CG', 2, 1, 9, NULL, '2026-05-06 02:21:25', 3, 0.00, 0.00, 0.00, 0),
(28, 'TLe ACC', 2, 1, 9, NULL, '2026-05-06 02:21:37', 3, 0.00, 0.00, 0.00, 0),
(29, 'FORM 3', 3, 2, 4, NULL, '2026-05-06 02:26:11', 3, 0.00, 0.00, 0.00, 0),
(30, 'FORM 4', 3, 2, 4, NULL, '2026-05-06 02:27:39', 3, 0.00, 0.00, 0.00, 0),
(31, 'FORM 5', 3, 2, 4, NULL, '2026-05-06 02:27:56', 3, 0.00, 0.00, 0.00, 0),
(32, 'Lower Sixth', 2, 2, 4, NULL, '2026-05-06 02:28:23', 3, 0.00, 0.00, 0.00, 0),
(33, 'UPPER SIXTH ACC', 2, 2, 4, NULL, '2026-05-06 02:28:36', 3, 0.00, 0.00, 0.00, 0),
(34, '1 ère Année MEFE', 3, 1, 10, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(35, '2 ère Année MEFE', 3, 1, 10, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(36, '3 ère Année MEFE', 3, 1, 10, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(37, '4 ère Année MEFE', 3, 1, 10, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(38, '2nd CH', 2, 1, 10, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(39, '1ère CH-TI', 2, 1, 10, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(40, 'Tle CH-TI', 2, 1, 10, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(41, 'FORM 1 Science', 3, 2, 10, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(42, 'FORM 2 MW', 3, 2, 10, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(43, 'FORM 3 science', 3, 2, 4, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(44, 'FORM 4 science', 3, 2, 4, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(45, 'FORM 5 Science', 3, 2, 4, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(46, 'Lower Sixth MW', 2, 2, 10, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(47, 'Upper Sixth MW', 2, 2, 10, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(48, '1 ère Année MACO', 3, 1, 2, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(49, '2 ère Année MACO', 3, 1, 2, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(50, '3 ère Année MACO', 3, 1, 2, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(51, '4 ère Année MACO', 3, 1, 2, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(52, '2nd F4', 2, 1, 2, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(53, '1ère F4-BA', 2, 1, 2, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(54, 'Tle F4-BA', 2, 1, 2, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(55, 'FORM 1 BC', 3, 2, 2, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(56, 'FORM 2 ACC', 3, 2, 2, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(57, 'FORM 3 ACC', 3, 2, 2, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(58, 'FORM 4 Acc', 3, 2, 2, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(59, 'FORM 5 BC', 3, 2, 2, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(60, 'Lower Sixth BC', 2, 2, 2, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(61, 'UPPER SIXTH ART', 2, 2, 2, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(62, '1 ère Année ELECT', 3, 1, 6, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(63, '2 ère Année ELECT', 3, 1, 6, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(64, '3 ère Année ELECT', 3, 1, 6, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(65, '4 ère Année ELECT', 3, 1, 6, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(66, '2nd F3', 2, 1, 6, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(67, '1ère F3', 2, 1, 6, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(68, 'Tle F3', 2, 1, 6, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(69, 'FORM 1 EE', 3, 2, 6, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(70, 'FORM 2 EE', 3, 2, 6, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(71, 'FORM 3 EE', 3, 2, 6, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(72, 'FORM 4 EE', 3, 2, 6, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(73, 'FORM 5 ART', 3, 2, 4, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(74, 'Lower Sixth EE', 2, 2, 6, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(75, 'UPPER SIXTH SCIENCE', 2, 2, 6, NULL, '2026-05-06 04:02:39', 3, 0.00, 0.00, 0.00, 0),
(76, '6éme', 3, 1, 4, NULL, '2026-05-30 01:42:32', 3, 0.00, 0.00, 0.00, 0),
(77, '1 ère année escom', 3, 1, 3, NULL, '2026-06-04 06:41:32', 3, 0.00, 0.00, 0.00, 0),
(78, '2 ème année escom', 3, 1, 3, NULL, '2026-06-04 06:42:18', 3, 0.00, 0.00, 0.00, 0),
(79, '3 ème année escom', 3, 1, 3, NULL, '2026-06-04 06:42:50', 3, 0.00, 0.00, 0.00, 0),
(80, '4 ème année escom', 3, NULL, 9, NULL, '2026-06-04 06:43:45', 3, 0.00, 0.00, 0.00, 0),
(81, '1 ère année MARE', 3, 1, 7, NULL, '2026-06-04 09:34:15', 3, 0.00, 0.00, 0.00, 0),
(82, '2 ère année MARE', 3, 1, 7, NULL, '2026-06-04 09:44:01', 3, 0.00, 0.00, 0.00, 0),
(84, '2nd F8', 2, 1, 12, NULL, '2026-06-04 10:57:45', 3, 0.00, 0.00, 0.00, 0),
(85, '2nd IH', 2, 1, 23, NULL, '2026-06-04 10:58:11', 3, 0.00, 0.00, 0.00, 0),
(86, '1 ère année COME', 3, 1, 11, NULL, '2026-06-04 12:19:03', 3, 0.00, 0.00, 0.00, 0),
(87, '2 ème année COME', 3, 1, 11, NULL, '2026-06-04 12:20:15', 3, 0.00, 0.00, 0.00, 0),
(88, '3 ème année COME', 3, 1, 11, NULL, '2026-06-04 12:20:44', 3, 0.00, 0.00, 0.00, 0),
(89, '4 ème année COME', 3, 1, 11, NULL, '2026-06-04 13:40:57', 3, 0.00, 0.00, 0.00, 0),
(90, '2 ème année SEME', 3, 1, 12, NULL, '2026-06-05 13:11:06', 3, 0.00, 0.00, 0.00, 0),
(91, '3 ème année SEME', 3, 1, 12, NULL, '2026-06-05 13:12:09', 3, 0.00, 0.00, 0.00, 0),
(93, '6 eme C', 3, 1, 4, NULL, '2026-06-06 13:36:07', 3, 0.00, 0.00, 0.00, 0),
(96, 'SIL', 3, 1, 26, NULL, '2026-06-17 14:42:41', 2, 0.00, 0.00, 0.00, 0),
(97, 'CEP', 3, 1, 26, NULL, '2026-06-17 14:42:57', 2, 0.00, 0.00, 0.00, 0),
(98, 'CE1', 3, 1, 26, NULL, '2026-06-17 14:44:14', 2, 0.00, 0.00, 0.00, 0),
(99, 'CE2', 3, 1, 26, NULL, '2026-06-17 14:44:30', 2, 0.00, 0.00, 0.00, 0);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cycles`
--

CREATE TABLE `cycles` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cycles`
--

INSERT INTO `cycles` (`id`, `nom`, `created_at`) VALUES
(2, '2nd Cycle', '2026-03-25 19:39:23'),
(3, '1ere Cycle', '2026-03-26 17:43:38'),
(11, 'BTS/HND', '2026-06-22 18:28:54'),
(12, 'Licence/Bachelor', '2026-06-22 18:29:20'),
(13, 'Master', '2026-06-22 18:29:38');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(28, 'Génie Civil', 'GC', 1, '2026-06-22 18:32:21', 9);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `periode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `valeur` float DEFAULT NULL,
  `appreciation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `teacher_nom_snapshot` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nom de l''enseignant au moment de la saisie',
  `teacher_prenom_snapshot` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Prénom de l''enseignant au moment de la saisie',
  `subject_nom_snapshot` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nom de la matière au moment de la saisie',
  `created_by_type` enum('enseignant','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'enseignant' COMMENT 'Type de créateur (enseignant ou admin)',
  `teaching_type_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
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
  `print_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `perm_code` varchar(100) NOT NULL,
  `perm_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sections`
--

INSERT INTO `sections` (`id`, `nom`, `created_at`) VALUES
(1, 'Francophone', '2026-03-25 19:39:48'),
(2, 'Anglophone', '2026-03-25 19:40:00');

-- --------------------------------------------------------

--
-- Structure de la table `sequences`
--

CREATE TABLE `sequences` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `label` varchar(100) NOT NULL,
  `short_label` varchar(20) DEFAULT NULL,
  `trimestre` tinyint(4) NOT NULL,
  `position` tinyint(4) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `academic_year_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sequences`
--

INSERT INTO `sequences` (`id`, `code`, `label`, `short_label`, `trimestre`, `position`, `is_active`, `academic_year_id`) VALUES
(1, 'SEQ1', 'Sequence 1', 'SEQUENCE 1', 1, 1, 1, 2),
(2, 'SEQ2', 'Sequence 2', 'SEQUENCE 2', 1, 3, 1, 2),
(3, 'SEQ3', 'Sequence 3', 'SEQUENCE 3', 2, 4, 1, 2),
(4, 'SEQ4', 'Sequence 4', 'SEQUENCE  4', 2, 5, 1, 2),
(5, 'SEQ5', 'Sequence 5', 'SEQUENCE 5', 3, 6, 1, 2),
(6, 'SEQ6', 'Sequence 6', 'SEQUENCE 6', 3, 7, 0, 2);

-- --------------------------------------------------------

--
-- Structure de la table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('allow_teacher_registration', '0'),
('backup_enabled', '1'),
('backup_github_auth', 'ssh'),
('backup_github_branch', 'main'),
('backup_github_owner', 'evarice13133'),
('backup_github_repository', 'notesmaster-backups'),
('backup_git_user_email', 'backup-bot@notesmaster.local'),
('backup_git_user_name', 'NotesMaster Backup Bot'),
('backup_git_worktree', 'storage/backup-repository'),
('backup_push_enabled', '1'),
('backup_retention_count', '12'),
('backup_schedule_day', 'Sunday'),
('backup_schedule_time', '02:00'),
('backup_storage_path', 'storage/backups'),
('bulletin_printing_enabled', '1'),
('display_school_year', '2026-2027'),
('honor_roll_default_threshold', '12'),
('honor_roll_threshold_class_101', ''),
('honor_roll_threshold_class_16', ''),
('honor_roll_threshold_class_2', ''),
('honor_roll_threshold_class_20', ''),
('honor_roll_threshold_class_24', ''),
('honor_roll_threshold_class_3', ''),
('honor_roll_threshold_class_33', '12'),
('honor_roll_threshold_class_41', ''),
('honor_roll_threshold_class_43', '12'),
('honor_roll_threshold_class_44', '12'),
('honor_roll_threshold_class_45', '12'),
('honor_roll_threshold_class_56', ''),
('honor_roll_threshold_class_57', '12'),
('honor_roll_threshold_class_58', ''),
('honor_roll_threshold_class_61', '12'),
('honor_roll_threshold_class_70', ''),
('honor_roll_threshold_class_71', ''),
('honor_roll_threshold_class_73', '12'),
('honor_roll_threshold_class_75', ''),
('honor_roll_threshold_class_77', '12'),
('honor_roll_threshold_class_78', '12'),
('honor_roll_threshold_class_79', '12'),
('honor_roll_threshold_class_80', '12'),
('honor_roll_threshold_class_81', '12'),
('honor_roll_threshold_class_82', '12'),
('honor_roll_threshold_class_84', '12'),
('honor_roll_threshold_class_85', '12'),
('honor_roll_threshold_class_86', '12'),
('honor_roll_threshold_class_87', '12'),
('honor_roll_threshold_class_88', '12'),
('honor_roll_threshold_class_89', '12'),
('honor_roll_threshold_class_90', '12'),
('honor_roll_threshold_class_91', '12'),
('honor_roll_threshold_class_92', '12'),
('matricule_counter', '3921'),
('matricule_format', '{SCHOOL_CODE}-{CLASS}-MT{COUNTER}'),
('payment_methods', 'Espèces,Mobile Money,Orange Money,MTN Mobile Money,Carte bancaire,Virement bancaire,Chèque,Autre'),
('principal_name', 'EFFION OKON AKAISO'),
('principal_signature', ''),
('principal_title', 'PRINCIPAL'),
('registration_fee_policy', 'all'),
('school_city', 'Douala PK12'),
('school_code', 'FUTURA'),
('school_email', 'fotsomarietherese2024@gmail.com'),
('school_fax', '656963491'),
('school_logo', '/public/uploads/1782387660_logo-camertech.png'),
('school_ministry', 'Ministère des Enseignements Secondaires'),
('school_ministry_en', 'Ministry of Secondary Education'),
('school_motto', 'Paix - Travail - Patrie'),
('school_motto_en', 'Peace - Work - Fatherland'),
('school_name', 'Complexe Scolaire Bilingue FUTURA'),
('school_phone', '686061923/696007229'),
('school_po_box', '51442'),
('school_republic', 'Republique du Cameroun'),
('school_republic_en', 'Republic of Cameroon'),
('school_slogan', 'Discipline - Travail - Succes'),
('school_slogan_en', 'Discipline - Work - Success'),
('school_stamp', ''),
('school_website', 'https://futura.camertech.com'),
('show_teacher_names_on_bulletins', '0'),
('theme_admin_hero_card', '#5d7894'),
('theme_admin_hero_end', '#2f6fed'),
('theme_admin_hero_glow', '#f4b942'),
('theme_admin_hero_start', '#16324f'),
('theme_button_bg', '#036305'),
('theme_button_text', '#ffffff'),
('theme_glow_bg', '#eef4fb'),
('theme_glow_text', '#1c4169'),
('theme_login_bg_end', '#eaebea'),
('theme_login_bg_mid', '#16324f'),
('theme_login_bg_start', '#018356'),
('theme_login_bubble', '#3a2f18'),
('theme_login_button', '#4e3687'),
('theme_login_panel_badge_bg', '#000000'),
('theme_login_panel_badge_text', '#1f5fbf'),
('theme_login_panel_bg', '#aaa1a1'),
('theme_login_showcase_end', '#143961'),
('theme_login_showcase_glow', '#f4b942'),
('theme_login_showcase_start', '#102033'),
('theme_navbar_bg', '#00a81c'),
('theme_navbar_hover', '#ffffff'),
('theme_teacher_hero_card', '#5d7894'),
('theme_teacher_hero_end', '#2f6fed'),
('theme_teacher_hero_glow', '#f4b942'),
('theme_teacher_hero_start', '#16324f');

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
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Structure de la table `student_payments`
--

CREATE TABLE `student_payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `observation` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `student_payment_allocations`
--

CREATE TABLE `student_payment_allocations` (
  `id` int(11) NOT NULL,
  `student_payment_id` int(11) NOT NULL,
  `student_installment_id` int(11) NOT NULL,
  `amount_allocated` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) DEFAULT 1,
  `teaching_type_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `subjects`
--

INSERT INTO `subjects` (`id`, `nom`, `coefficient`, `groupe`, `created_at`, `status`, `teaching_type_id`, `department_id`) VALUES
(15, 'LITTÉRATURE', 4, 'Groupe 1', '2026-05-06 04:40:32', 1, 3, NULL),
(16, 'INFORMATIQUE', 2, 'Groupe 2', '2026-05-06 06:00:29', 1, 3, NULL),
(17, 'LANGUE FRANçAISE', 2, 'Groupe 1', '2026-05-06 06:00:29', 1, 3, NULL),
(18, 'PHILISOPHIE', 2, 'Groupe 1', '2026-05-06 06:00:29', 1, 3, NULL),
(19, 'PHILISOPHIE', 4, 'Groupe 1', '2026-05-06 06:00:29', 1, 3, NULL),
(20, 'ANGLAIS', 4, 'Groupe 1', '2026-05-06 06:00:29', 1, 3, NULL),
(21, 'LANGUE VIVANTE II', 3, 'Groupe 1', '2026-05-06 06:00:29', 1, 3, NULL),
(22, 'GEOGRAPHIE', 2, 'Groupe 2', '2026-05-06 06:00:29', 1, 3, NULL),
(23, 'HISTOIRE', 2, 'Groupe 2', '2026-05-06 06:00:29', 1, 3, NULL),
(24, 'ECM', 2, 'Groupe 2', '2026-05-06 06:00:29', 1, 3, NULL),
(25, 'MATHEMATIQUES', 2, 'Groupe 2', '2026-05-06 06:00:29', 1, 3, NULL),
(26, 'SCIENCES', 1, 'Groupe 2', '2026-05-06 06:00:29', 1, 3, NULL),
(27, 'TM', 1, 'Groupe 3', '2026-05-06 18:24:29', 1, 3, NULL),
(28, 'LANGUES NATIONALLES', 1, 'Groupe 2', '2026-05-06 23:11:32', 0, 3, NULL),
(30, 'EDUCATION ARTISTIQUE', 1, 'Groupe 2', '2026-05-06 23:11:32', 0, 3, NULL),
(31, 'TRAVAIL MANUEL', 1, 'Groupe 3', '2026-05-06 23:11:32', 1, 3, NULL),
(32, 'EPS', 2, 'Groupe 3', '2026-05-06 23:11:32', 1, 3, NULL),
(33, 'MATHEMATIQUES', 5, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(34, 'MATHEMATIQUES', 6, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(35, 'MATHEMATIQUES', 7, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(36, 'PHYSIQUE', 3, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(37, 'PHYSIQUE', 4, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(38, 'CHIMIE', 3, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(39, 'CHIMIE', 2, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(40, 'INFORMATIQUE', 3, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(41, 'INFORMATIQUE', 2, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(42, 'INFORMATIQUE', 4, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(43, 'SVTEEHB', 2, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(44, 'LITTÉRATURE', 2, 'Groupe 1', '2026-05-06 23:11:32', 1, 3, NULL),
(45, 'LANGUE FRANçAISE', 1, 'Groupe 1', '2026-05-06 23:11:32', 1, 3, NULL),
(46, 'ANGLAIS', 3, 'Groupe 1', '2026-05-06 23:11:32', 1, 3, NULL),
(47, 'SVTEEHB', 2, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(48, 'PHILOSOPHIE', 1, 'Groupe 1', '2026-05-06 23:11:32', 1, 3, NULL),
(49, 'PHILOSOPHIE', 2, 'Groupe 1', '2026-05-06 23:11:32', 1, 3, NULL),
(50, 'HISTOIRE', 2, 'Groupe 1', '2026-05-06 23:11:32', 1, 3, NULL),
(51, 'GEOGRAPHIE', 2, 'Groupe 1', '2026-05-06 23:11:32', 1, 3, NULL),
(52, 'ECM', 1, 'Groupe 1', '2026-05-06 23:11:32', 1, 3, NULL),
(53, 'SVTEEHB', 6, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(54, 'MATHEMATIQUES', 4, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(55, 'CHIMIE', 2, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(56, 'INFORMATIQUE', 2, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(57, 'PHYSIQUE', 2, 'Groupe 2', '2026-05-06 23:11:32', 1, 3, NULL),
(58, 'HISTOIRE', 2, 'Groupe 1', '2026-05-06 23:11:32', 1, 3, NULL),
(59, 'GEOGRAPHIE', 2, 'Groupe 1', '2026-05-06 23:11:32', 1, 3, NULL),
(60, 'EPS', 2, 'Groupe 3', '2026-05-06 23:11:32', 1, 3, NULL),
(61, 'TRAVAIL MANUEL', 1, 'Groupe 3', '2026-05-06 23:11:32', 1, 3, NULL),
(63, 'Histoire/Géographie', 2, 'Groupe 1', '2026-05-28 08:52:34', 1, 3, NULL),
(64, 'Anglais', 2, 'Groupe 1', '2026-05-28 08:52:34', 1, 3, NULL),
(65, 'Economie d\'entreprise', 1, 'Groupe 1', '2026-05-28 08:52:34', 1, 3, NULL),
(66, 'Français', 3, 'Groupe 1', '2026-05-28 08:52:34', 1, 3, NULL),
(67, 'Mathematique Générale', 3, 'Groupe 2', '2026-05-28 08:52:34', 1, 3, NULL),
(68, 'Redaction Professionnelle', 3, 'Groupe 2', '2026-05-28 08:52:34', 1, 3, NULL),
(70, 'Droit', 2, 'Groupe 1', '2026-05-28 08:52:34', 1, 3, NULL),
(71, 'ECM', 1, 'Groupe 1', '2026-05-28 08:52:34', 1, 3, NULL),
(72, 'Economie générale', 2, 'Groupe 1', '2026-05-28 08:52:34', 1, 3, NULL),
(73, 'GIF', 3, 'Groupe 2', '2026-05-28 08:52:34', 1, 3, NULL),
(74, 'EPS', 1, 'Groupe 3', '2026-05-28 08:52:34', 1, 3, NULL),
(75, 'Travail Manuel', 1, 'Groupe 3', '2026-05-28 08:52:34', 1, 3, NULL),
(76, 'Mathematiques', 2, 'Groupe 2', '2026-05-28 08:52:34', 1, 3, NULL),
(77, 'Finance d\'entreprise', 2, 'Groupe 2', '2026-05-28 08:52:34', 1, 3, NULL),
(78, 'Mathématiques Appliquées', 4, 'Groupe 2', '2026-05-28 08:52:34', 1, 3, NULL),
(79, 'Management', 2, 'Groupe 2', '2026-05-28 08:52:34', 1, 3, NULL),
(80, 'Philosophie', 2, 'Groupe 1', '2026-05-28 08:52:34', 1, 3, NULL),
(81, 'MOB', 3, 'Groupe 2', '2026-05-28 08:52:34', 1, 3, NULL),
(82, 'RPC', 2, 'Groupe 2', '2026-05-28 08:52:34', 1, 3, NULL),
(83, 'OTA', 2, 'Groupe 2', '2026-05-28 08:52:34', 1, 3, NULL),
(84, 'Prise de parole rapide', 3, 'Groupe 2', '2026-05-28 08:52:34', 1, 3, NULL),
(85, 'TAS', 3, 'Groupe 2', '2026-05-28 08:52:34', 1, 3, NULL),
(86, 'Bureautique', 3, 'Groupe 2', '2026-05-28 08:52:34', 1, 3, NULL),
(87, 'Comptabilité', 3, 'Groupe 2', '2026-05-28 08:52:34', 1, 3, NULL),
(88, 'Statistiques', 2, 'Groupe 2', '2026-05-28 13:31:36', 1, 3, NULL),
(89, 'Examen de Laboratoire', 3, 'Groupe 2', '2026-06-04 11:07:49', 1, 3, NULL),
(90, 'Gestion des ressources humaines', 3, 'Groupe 2', '2026-06-04 11:08:16', 1, 3, NULL),
(91, 'GSS', 3, 'Groupe 2', '2026-06-04 11:08:35', 1, 3, NULL),
(92, 'Santé publique', 3, 'Groupe 2', '2026-06-04 11:09:04', 1, 3, NULL),
(93, 'Soins infirmiers', 3, 'Groupe 2', '2026-06-04 11:09:32', 1, 3, NULL),
(94, 'Terminologie Médical', 3, 'Groupe 2', '2026-06-04 11:10:07', 1, 3, NULL),
(95, 'BPPH', 4, 'Groupe 2', '2026-06-04 11:10:33', 1, 3, NULL),
(96, 'Bureautique', 3, 'Groupe 2', '2026-06-04 11:11:13', 1, 3, NULL),
(97, 'Science physique', 2, 'Groupe 2', '2026-06-04 11:12:25', 1, 3, NULL),
(98, 'Action sociale', 3, 'Groupe 2', '2026-06-04 11:13:36', 1, 3, NULL),
(234, 'Metier et formation', 1, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(236, 'Connaissance des materiaux', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(237, 'Dessin technique', 4, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(238, 'EPS', 2, 'Groupe 3', '2026-06-04 12:11:44', 1, 3, NULL),
(239, 'Travail manuel', 1, 'Groupe 3', '2026-06-04 12:11:44', 1, 3, NULL),
(240, 'Français', 3, 'Groupe 1', '2026-06-04 12:11:44', 1, 3, NULL),
(241, 'Anglais', 2, 'Groupe 1', '2026-06-04 12:11:44', 1, 3, NULL),
(242, 'ECM', 1, 'Groupe 1', '2026-06-04 12:11:44', 1, 3, NULL),
(243, 'Informatique', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(244, 'Mathematiques', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(245, 'Sciences physique', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(246, 'Sante, securite, et envirronnement', 1, 'Groupe 3', '2026-06-04 12:11:44', 1, 3, NULL),
(247, 'Materiaux', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(248, 'Ajustage', 4, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(249, 'RESEO', 5, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(250, 'Technologie professionnel', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(251, 'Tracage', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(252, 'RESEO', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(253, 'Schema electrique', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(256, 'Technologie', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(257, 'Machine electrique', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(258, 'Dessin technique', 5, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(259, 'Entrepreneuriat', 2, 'Groupe 1', '2026-06-04 12:11:44', 1, 3, NULL),
(260, 'EPS', 1, 'Groupe 3', '2026-06-04 12:11:44', 1, 3, NULL),
(261, 'Travail Manuel', 1, 'Groupe 3', '2026-06-04 12:11:44', 1, 3, NULL),
(262, 'Dessin Industriel', 4, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(263, 'Exploitation', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(264, 'Mecanique Appliquée', 4, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(265, 'Métré', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(266, 'Procédé de Construction', 8, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(267, 'Réglementation', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(268, 'Topographie', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(269, 'Anglais', 3, 'Groupe 1', '2026-06-04 12:11:44', 1, 3, NULL),
(270, 'ECM', 2, 'Groupe 1', '2026-06-04 12:11:44', 1, 3, NULL),
(271, 'HISTOIRE/Géographie', 1, 'Groupe 1', '2026-06-04 12:11:44', 1, 3, NULL),
(272, 'INFORMATIQUE', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(273, 'Mathematiques', 4, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(274, 'Français', 3, 'Groupe 1', '2026-06-04 12:11:44', 1, 3, NULL),
(275, 'Sciences Physiques', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(276, 'Dessin Technique', 4, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(277, 'Entrepreneuriat', 2, 'Groupe 3', '2026-06-04 12:11:44', 1, 3, NULL),
(278, 'Montage', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(279, 'TPFA', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(280, 'Travaux Pratique', 9, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(281, 'Mecanique Appliquée', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(282, 'PPRO', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(283, 'Traçage', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(284, 'Mecanique Appliquée', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(285, 'Laboratoire', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(286, 'MPN', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(287, 'Maintenance', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(288, 'Travaux Pratique', 4, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(289, 'Technologie des Materiaux', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(290, 'Processus Previsionnel', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(291, 'SCHEMA ELECTRIQUE', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(292, 'Electronique et electrotechnique', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(293, 'Technologie Schema', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(294, 'Sols et Materiaux', 5, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(295, 'ANALYSE', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(296, 'Dessin de Mode', 3, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(297, 'PATRONAGE', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(298, 'Dessin Technique', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(299, 'ECM', 2, 'Groupe 1', '2026-06-04 12:11:44', 1, 3, NULL),
(300, 'Technologie', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(301, 'Mathematique', 2, 'Groupe 2', '2026-06-04 12:11:44', 1, 3, NULL),
(302, 'Dessin de Mode', 2, 'Groupe 2', '2026-06-05 11:14:19', 1, 3, NULL),
(303, 'Coupe', 3, 'Groupe 2', '2026-06-05 11:14:55', 1, 3, NULL),
(304, 'Couture', 5, 'Groupe 2', '2026-06-05 11:15:21', 1, 3, NULL),
(305, 'Hygiène', 1, 'Groupe 3', '2026-06-05 11:22:42', 1, 3, NULL),
(306, 'Bureautique', 2, 'Groupe 2', '2026-06-05 11:27:46', 1, 3, NULL),
(307, 'Rédaction Professionnel', 2, 'Groupe 2', '2026-06-05 11:28:38', 1, 3, NULL),
(308, 'PRP', 2, 'Groupe 2', '2026-06-05 11:29:48', 1, 3, NULL),
(309, 'DCC', 2, 'Groupe 2', '2026-06-05 11:30:11', 1, 3, NULL),
(310, 'OTA', 2, 'Groupe 2', '2026-06-05 11:30:37', 1, 3, NULL),
(311, 'Commerce', 1, 'Groupe 2', '2026-06-05 11:32:11', 1, 3, NULL),
(312, 'Législation du travail', 1, 'Groupe 1', '2026-06-05 11:34:45', 1, 3, NULL),
(314, 'Pratique comptable', 2, 'Groupe 2', '2026-06-05 11:36:06', 1, 3, NULL),
(315, 'Travaux comptable', 3, 'Groupe 2', '2026-06-05 11:36:48', 1, 3, NULL),
(316, 'Hygiène', 1, 'Groupe 3', '2026-06-05 11:38:17', 1, 3, NULL),
(317, 'Mathematique Commerciale', 2, 'Groupe 2', '2026-06-05 11:46:33', 1, 3, NULL),
(319, 'Dessin technique', 2, 'Groupe 2', '2026-06-05 12:11:50', 1, 3, NULL),
(320, 'MOB', 4, 'Groupe 2', '2026-06-05 13:23:51', 1, 3, NULL),
(321, 'Terminologie Médical', 2, 'Groupe 2', '2026-06-05 13:26:03', 1, 3, NULL),
(322, 'Deontologie', 1, 'Groupe 2', '2026-06-05 13:42:14', 1, 3, NULL),
(323, 'GEH', 2, 'Groupe 2', '2026-06-05 13:43:59', 1, 3, NULL),
(325, 'Technologie Electrique', 2, 'Groupe 2', '2026-06-05 19:43:24', 1, 3, NULL),
(326, 'Technologie Mecanique', 2, 'Groupe 2', '2026-06-05 19:44:02', 1, 3, NULL),
(327, 'Reception d\'un vehicule', 2, 'Groupe 2', '2026-06-05 19:46:20', 1, 3, NULL),
(328, 'Moteur CI', 3, 'Groupe 2', '2026-06-05 19:46:55', 1, 3, NULL),
(329, 'Procedes de Realisation', 3, 'Groupe 2', '2026-06-05 20:02:06', 1, 3, NULL),
(330, 'Devis et estimation', 2, 'Groupe 2', '2026-06-05 20:10:35', 1, 3, NULL),
(331, 'Financial Accounting', 1, 'Groupe 1', '2026-06-05 20:58:36', 1, 3, NULL),
(333, 'Business Managemennt', 3, 'Groupe 2', '2026-06-06 08:38:16', 1, 3, NULL),
(334, 'Economic', 3, 'Groupe 1', '2026-06-06 08:39:32', 1, 3, NULL),
(335, 'Corporate Accounting', 5, 'Groupe 1', '2026-06-06 08:45:01', 1, 3, NULL),
(336, 'Business Maths', 5, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(337, 'Commerce And financial', 1, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(338, 'Entrepreuneurship', 5, 'Groupe 3', '2026-06-06 10:42:44', 1, 3, NULL),
(341, 'Economic', 5, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(342, 'Geography', 5, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(343, 'ICT', 5, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(344, 'English', 5, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(345, 'Pure Ma thematics and statistics', 5, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(346, 'Biology', 5, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(347, 'Statistics', 5, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(348, 'Chemistry', 5, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(349, 'Physic', 5, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(350, 'French', 5, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(351, 'Citizenship', 2, 'Groupe 3', '2026-06-06 10:42:44', 1, 3, NULL),
(352, 'Mathematics', 5, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(353, 'Literature in  English', 5, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(354, 'History', 3, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(355, 'Food and nutrition', 2, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(357, 'ENGLISH', 4, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(359, 'FRENCH', 4, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(363, 'CITIZENSHIP EDUCATION', 2, 'Groupe 3', '2026-06-06 10:42:44', 1, 3, NULL),
(364, 'COMPUTER STUDIES', 3, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(365, 'BIOLOGY', 2, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(366, 'CHEMISTRY', 2, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(367, 'MATHEMATICS', 4, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(368, 'PHYSICS', 2, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(370, 'ENGLISH', 4, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(372, 'FRENCH', 4, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(376, 'CITIZENSHIP EDUCATION', 2, 'Groupe 3', '2026-06-06 10:42:44', 1, 3, NULL),
(377, 'COMPUTER STUDIES', 2, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(379, 'CHEMISTRY', 2, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(380, 'MATHEMATICS', 4, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(381, 'PHYSICS', 2, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(382, 'C.S. (General Science)', 2, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(383, 'ENGLISH', 4, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(384, 'LITERATURE', 3, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(385, 'FRENCH', 4, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(386, 'GEOGRAPHY', 3, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(387, 'ECONOMICS', 3, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(388, 'HISTORY', 3, 'Groupe 1', '2026-06-06 10:42:44', 1, 3, NULL),
(389, 'CITIZENSHIP EDUCATION', 3, 'Groupe 3', '2026-06-06 10:42:44', 1, 3, NULL),
(390, 'COMPUTER STUDIES', 1, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(391, 'BIOLOGY', 3, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(392, 'CHEMISTRY', 3, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(393, 'MATHEMATICS', 4, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(394, 'PHYSICS', 3, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(395, 'C.S. (General Science)', 3, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(396, 'F/N/H/BIO (combinée)', 3, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(397, 'A.M.A / LOGIC / PHIL  (selon options)', 3, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(398, 'ELECTRICAL TECHNOLOGIE AND DIASRAM', 4, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(399, 'ELECTRICAL TEST AND MEASUREMENT', 1, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(400, 'ELCTRICAL MACHINE', 3, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(401, 'ELECTRICAL AND ELECTRONIQUE CIRCUITS', 3, 'Groupe 2', '2026-06-06 10:42:44', 1, 3, NULL),
(403, 'ECM', 1, 'Groupe 1', '2026-06-06 12:09:16', 1, 3, NULL),
(405, 'Hygiène', 1, 'Groupe 1', '2026-06-06 12:14:33', 1, 3, NULL),
(406, 'Travail manuel', 1, 'Groupe 3', '2026-06-06 12:15:51', 1, 3, NULL),
(407, 'Mathematique', 4, 'Groupe 2', '2026-06-06 12:24:02', 1, 3, NULL),
(408, 'Mathematique', 3, 'Groupe 2', '2026-06-06 12:24:30', 1, 3, NULL),
(409, 'Informatique', 2, 'Groupe 2', '2026-06-06 12:25:15', 1, 3, NULL),
(410, 'Anglais', 3, 'Groupe 1', '2026-06-06 12:25:57', 1, 3, NULL),
(411, 'Sciences physique', 1, 'Groupe 2', '2026-06-06 12:27:05', 1, 3, NULL),
(412, 'Français', 3, 'Groupe 1', '2026-06-06 12:28:03', 1, 3, NULL),
(413, 'Législation', 1, 'Groupe 1', '2026-06-06 13:14:06', 1, 3, NULL),
(414, 'Materiaux', 2, 'Groupe 2', '2026-06-06 21:40:19', 1, 3, NULL),
(415, 'Dessin', 4, 'Groupe 2', '2026-06-06 21:41:39', 1, 3, NULL),
(416, 'Dessin', 4, 'Groupe 2', '2026-06-06 21:59:07', 1, 3, NULL),
(417, 'Français', 3, 'Groupe 1', '2026-06-06 22:09:59', 1, 3, NULL),
(418, 'Informatique', 1, 'Groupe 2', '2026-06-06 22:11:26', 1, 3, NULL),
(419, 'Travail manuel', 1, 'Groupe 3', '2026-06-06 22:21:08', 1, 3, NULL),
(420, 'Science physique', 1, 'Groupe 2', '2026-06-06 22:24:14', 1, 3, NULL),
(421, 'MATHEMATIQUES', 4, 'Groupe 2', '2026-06-06 22:24:54', 1, 3, NULL),
(422, 'Etude de  texte', 2, 'Groupe 1', '2026-06-07 05:17:06', 1, 3, NULL),
(423, 'Correction orthographie', 2, 'Groupe 1', '2026-06-07 05:17:06', 1, 3, NULL),
(424, 'Expression ecrite', 2, 'Groupe 1', '2026-06-07 05:17:06', 1, 3, NULL),
(425, 'Anglais', 3, 'Groupe 1', '2026-06-07 05:17:06', 1, 3, NULL),
(426, 'ECM', 2, 'Groupe 1', '2026-06-07 05:17:06', 1, 3, NULL),
(427, 'Espagnol', 2, 'Groupe 1', '2026-06-07 05:17:06', 1, 3, NULL),
(428, 'HISTOIRE', 2, 'Groupe 1', '2026-06-07 05:17:06', 1, 3, NULL),
(429, 'Geographie', 2, 'Groupe 1', '2026-06-07 05:17:06', 1, 3, NULL),
(431, 'PCT', 3, 'Groupe 2', '2026-06-07 05:17:06', 1, 3, NULL),
(432, 'Informatique', 2, 'Groupe 2', '2026-06-07 05:17:06', 1, 3, NULL),
(433, 'Mathematque', 4, 'Groupe 2', '2026-06-07 05:17:06', 1, 3, NULL),
(434, 'SVTEEHB', 2, 'Groupe 2', '2026-06-07 05:17:06', 1, 3, NULL),
(435, 'ESF', 1, 'Groupe 2', '2026-06-07 05:17:06', 1, 3, NULL),
(436, 'Schéma électrique', 2, 'Groupe 2', '2026-06-07 09:33:49', 1, 3, NULL),
(437, 'Circuit électrique', 3, 'Groupe 2', '2026-06-07 09:34:11', 1, 3, NULL),
(438, 'PID', 2, 'Groupe 2', '2026-06-07 09:34:36', 1, 3, NULL),
(439, 'Dessin technique', 4, 'Groupe 2', '2026-06-07 09:34:56', 1, 3, NULL),
(440, 'Essaie et mesure', 2, 'Groupe 2', '2026-06-07 09:36:09', 1, 3, NULL),
(441, 'Circuit électrique', 3, 'Groupe 2', '2026-06-07 09:50:20', 1, 3, NULL),
(442, 'Essaie et mesure', 2, 'Groupe 2', '2026-06-07 09:51:32', 1, 3, NULL),
(444, 'Eps', 1, 'Groupe 3', '2026-06-07 11:27:09', 1, 3, NULL),
(445, 'Production', 2, 'Groupe 2', '2026-06-07 11:28:57', 1, 3, NULL),
(446, 'Mathematics', 5, 'Groupe 2', '2026-06-07 11:52:41', 1, 3, NULL),
(447, 'H.biology', 5, 'Groupe 2', '2026-06-07 12:50:19', 1, 3, NULL),
(448, 'Eps', 1, 'Groupe 3', '2026-06-07 13:38:04', 1, 3, NULL),
(449, 'Technologie professionnelle', 2, 'Groupe 2', '2026-06-07 16:32:46', 1, 3, NULL),
(450, 'Dessin technique', 5, 'Groupe 2', '2026-06-07 16:33:39', 1, 3, NULL),
(451, 'Traçage', 2, 'Groupe 2', '2026-06-07 16:34:43', 1, 3, NULL),
(452, 'RESEO', 4, 'Groupe 2', '2026-06-07 16:36:19', 1, 3, NULL),
(453, 'Français', 3, 'Groupe 1', '2026-06-08 08:45:57', 1, 3, NULL),
(454, 'Anglais', 3, 'Groupe 1', '2026-06-08 08:49:02', 1, 3, NULL),
(455, 'Mécanique Appliqué', 2, 'Groupe 2', '2026-06-08 09:18:11', 1, 3, NULL),
(456, 'Législation', 1, 'Groupe 1', '2026-06-08 10:02:41', 1, 3, NULL),
(457, 'RESEO', 5, 'Groupe 2', '2026-06-08 10:07:23', 1, 3, NULL),
(458, 'Commerce', 2, 'Groupe 1', '2026-06-08 10:30:44', 1, 3, NULL),
(459, 'B.Maths', 3, 'Groupe 2', '2026-06-08 10:31:17', 1, 3, NULL),
(460, 'Accounting', 3, 'Groupe 2', '2026-06-08 10:31:47', 1, 3, NULL),
(461, 'HYGIÈNE', 1, 'Groupe 3', '2026-06-08 10:32:20', 1, 3, NULL),
(462, 'PDC', 3, 'Groupe 2', '2026-06-08 11:06:35', 1, 3, NULL),
(463, 'Commerce', 2, 'Groupe 1', '2026-06-08 11:43:02', 1, 3, NULL),
(464, 'Science physique', 2, 'Groupe 2', '2026-06-08 12:18:19', 1, 3, NULL),
(465, 'Comptabilité d\'entreprise', 4, 'Groupe 2', '2026-06-08 12:51:44', 1, 3, NULL),
(466, 'Mathematique Appliqué', 2, 'Groupe 2', '2026-06-08 12:56:08', 1, 3, NULL),
(467, 'ECM', 1, 'Groupe 1', '2026-06-08 13:02:51', 1, 3, NULL),
(468, 'MOB', 2, 'Groupe 2', '2026-06-08 13:55:36', 1, 3, NULL),
(469, 'Informatique', 1, 'Groupe 2', '2026-06-08 13:56:03', 1, 3, NULL),
(470, 'RPC', 2, 'Groupe 2', '2026-06-08 13:59:42', 1, 3, NULL),
(473, 'PCQ', 3, 'Groupe 2', '2026-06-08 15:19:58', 1, 3, NULL),
(475, 'Histoire/Géographie', 2, 'Groupe 1', '2026-06-09 11:29:24', 1, 3, NULL),
(476, 'Anglais', 3, 'Groupe 2', '2026-06-09 11:29:45', 1, 3, NULL),
(477, 'Français', 3, 'Groupe 1', '2026-06-09 11:30:02', 1, 3, NULL),
(478, 'Mathematique', 2, 'Groupe 2', '2026-06-09 11:30:32', 1, 3, NULL),
(479, 'Économie générale', 3, 'Groupe 1', '2026-06-09 11:31:00', 1, 3, NULL),
(480, 'OTA', 3, 'Groupe 2', '2026-06-09 11:31:25', 1, 3, NULL),
(481, 'PRP', 2, 'Groupe 1', '2026-06-09 11:31:44', 1, 3, NULL),
(482, 'Bureautique', 3, 'Groupe 2', '2026-06-09 11:32:05', 1, 3, NULL),
(483, 'Comptabilité financière', 4, 'Groupe 2', '2026-06-09 11:32:36', 1, 3, NULL),
(484, 'ECM', 1, 'Groupe 1', '2026-06-09 11:32:58', 1, 3, NULL),
(485, 'INFORMATIQUE', 2, 'Groupe 2', '2026-06-09 11:33:19', 1, 3, NULL),
(486, 'EPS', 2, 'Groupe 3', '2026-06-09 11:33:37', 1, 3, NULL),
(487, 'Travail manuel', 1, 'Groupe 3', '2026-06-09 11:33:54', 1, 3, NULL),
(488, 'Dessin de Mode', 4, 'Groupe 2', '2026-06-09 13:18:11', 1, 3, NULL),
(489, 'EPS', 1, 'Groupe 1', '2026-06-09 13:21:17', 1, 3, NULL),
(492, 'Anglais', 3, 'Groupe 1', '2026-06-10 04:24:32', 1, 3, NULL),
(493, 'ECM', 3, 'Groupe 1', '2026-06-10 04:30:00', 1, 3, NULL),
(494, 'Informatique', 2, 'Groupe 1', '2026-06-10 04:30:34', 1, 3, NULL),
(495, 'Législation', 1, 'Groupe 1', '2026-06-10 04:31:09', 1, 3, NULL),
(496, 'Français', 3, 'Groupe 1', '2026-06-10 04:31:36', 1, 3, NULL),
(497, 'MATHEMATIQUES', 2, 'Groupe 2', '2026-06-10 04:34:03', 1, 3, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `subject_classes`
--

CREATE TABLE `subject_classes` (
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `teaching_types`
--

INSERT INTO `teaching_types` (`id`, `nom`, `code`, `position`, `actif`, `created_at`, `updated_at`) VALUES
(1, 'Maternelle', 'MAT', 0, 1, '2026-06-17 14:10:48', '2026-06-22 18:25:01'),
(2, 'Primaire', 'PRI', 1, 1, '2026-06-17 14:10:48', '2026-06-22 18:25:12'),
(3, 'Secondaire', 'SEC', 2, 1, '2026-06-17 14:10:48', '2026-06-22 18:25:25'),
(9, 'Supérieur', 'LMD', 4, 0, '2026-06-22 18:26:40', '2026-06-22 18:28:29');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `username`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(39, 'Futura', 'Admin', 'futura', 'futura-col@gmail.com', '$2y$10$.QqSc2VBvuMOLoFUviDGlOpH2Aet6NM5TMDCLhhidhC3R.R5anHHm', 'superadmin', '2026-05-11 13:26:49', '2026-06-26 09:18:02'),
(40, 'futura', 'futura', 'admin', 'futura@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$UFhHNFRqRHdsalA0UnFyNQ$y6Dog10X0SG2ZRhFNYafsjVoSeBulzswHjN4vjoqkKE', 'admin', '2026-05-19 14:15:45', '2026-06-25 12:04:11'),
(41, 'admin2', 'admin2', 'admin2', NULL, '$argon2id$v=19$m=65536,t=4,p=1$QTFmRG1WbWNER3pmOW04aQ$K7CeAgjtgoLaVvXqsNk6der0OMn9P/I+81VqnT3iJzY', 'enseignant', '2026-06-25 12:11:10', '2026-06-25 12:11:10'),
(42, 'Sup', 'sup', 'sup', NULL, '$argon2id$v=19$m=65536,t=4,p=1$ZktDOFQ1a0lqeVdYRlNPcQ$h4TBqD9AVZ5zOBhlc+fBjTb9Xbx/g0eSHx16UYb7N7s', 'superadmin', '2026-06-25 12:12:10', '2026-06-26 09:13:13');

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
-- Structure de la table `user_teaching_types`
--

CREATE TABLE `user_teaching_types` (
  `user_id` int(11) NOT NULL,
  `teaching_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  ADD KEY `fk_classes_department` (`department_id`);

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
  ADD UNIQUE KEY `nom` (`nom`);

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
-- Index pour la table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `verification_code` (`verification_code`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `academic_year_id` (`academic_year_id`),
  ADD KEY `created_by` (`created_by`);

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
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `label` (`label`);

--
-- Index pour la table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

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
  ADD KEY `created_by` (`created_by`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `class_scholarships`
--
ALTER TABLE `class_scholarships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `cycles`
--
ALTER TABLE `cycles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `discipline`
--
ALTER TABLE `discipline`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `discount_types`
--
ALTER TABLE `discount_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `fee_installments`
--
ALTER TABLE `fee_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `financial_history`
--
ALTER TABLE `financial_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `insolvent_students`
--
ALTER TABLE `insolvent_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `installment_deadlines`
--
ALTER TABLE `installment_deadlines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `school_fees`
--
ALTER TABLE `school_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `sequences`
--
ALTER TABLE `sequences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25211;

--
-- AUTO_INCREMENT pour la table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=404;

--
-- AUTO_INCREMENT pour la table `student_discounts`
--
ALTER TABLE `student_discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `student_installments`
--
ALTER TABLE `student_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=498;

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
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

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





















