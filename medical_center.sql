-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 23 avr. 2026 à 00:45
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `medical_center`
--

-- --------------------------------------------------------

--
-- Structure de la table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_code` char(5) NOT NULL,
  `speciality_code` char(2) NOT NULL DEFAULT '00',
  `name` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admins`
--

INSERT INTO `admins` (`id`, `user_code`, `speciality_code`, `name`, `email`, `password`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '30001', '00', 'Main Admin', 'admin@amercenter.local', '$2y$10$WtUN/uJz12JwTLvsKHqM2Oajwn9qVA3nhzuCOufaMkmZOIQSo.7.C', 1, '2026-04-18 00:54:19', '2026-04-18 00:54:19'),
(2, '30002', '00', 'MASTEN', 'admin@example.com', '$2y$10$caKdf8TZtc9Ulrn2owMPveXFGW5GJZvMDTmXRlb3WCSX3gcHHbDQO', 1, '2026-04-21 11:54:33', '2026-04-21 11:54:33');

-- --------------------------------------------------------

--
-- Structure de la table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(10) UNSIGNED NOT NULL,
  `fullname` varchar(120) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(120) NOT NULL,
  `doctor_id` int(10) UNSIGNED NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','done','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `appointments`
--

INSERT INTO `appointments` (`id`, `fullname`, `phone`, `email`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `created_at`, `updated_at`) VALUES
(1, 'amin mehdaoui', '2525252525', 'aminmehdaoui.2006@gmail.com', 1, '0000-00-00', '11:11:00', 'pending', '2026-04-18 01:36:40', '2026-04-18 01:36:40'),
(2, 'yazid', '2525252525', 'uzetzsutzs@gmail.com', 1, '0000-00-00', '00:49:00', 'pending', '2026-04-20 09:48:56', '2026-04-20 09:48:56'),
(3, 'yazid', '2525252525', 'uzetzsutzs@gmail.com', 1, '0000-00-00', '15:21:00', 'pending', '2026-04-21 11:49:00', '2026-04-21 11:49:00');

-- --------------------------------------------------------

--
-- Structure de la table `consultations`
--

CREATE TABLE `consultations` (
  `id` int(10) UNSIGNED NOT NULL,
  `rendez_vous_id` int(10) UNSIGNED NOT NULL,
  `doctor_id` int(10) UNSIGNED NOT NULL,
  `patient_id` int(10) UNSIGNED NOT NULL,
  `notes` text NOT NULL,
  `consultation_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_code` char(5) NOT NULL,
  `speciality_code` char(2) NOT NULL,
  `name` varchar(120) NOT NULL,
  `speciality` varchar(120) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `working_days` varchar(120) DEFAULT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `doctors`
--

INSERT INTO `doctors` (`id`, `user_code`, `speciality_code`, `name`, `speciality`, `phone`, `working_days`, `email`, `password`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '10101', '01', 'amin mehdaoui', 'DENTISTE', 'admin@example.com', 'SAT', 'aminmehdaoui.2006@gmail.com', '$2y$10$xf7mlT.JRO.WKIytH3FlCefnySpV8sEIZEHncAdlMWQI5fJUcPHEK', 1, '2026-04-18 01:03:30', '2026-04-18 01:03:30');

-- --------------------------------------------------------

--
-- Structure de la table `medical_specialties`
--

CREATE TABLE `medical_specialties` (
  `code` char(2) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `medical_specialties`
--

INSERT INTO `medical_specialties` (`code`, `name`, `is_active`, `created_at`) VALUES
('00', 'General / Non medical', 1, '2026-04-18 00:54:19'),
('01', 'Cardiology', 1, '2026-04-18 00:54:19'),
('02', 'Dermatology', 1, '2026-04-18 00:54:19'),
('03', 'Pediatrics', 1, '2026-04-18 00:54:19'),
('04', 'Neurology', 1, '2026-04-18 00:54:19'),
('05', 'Orthopedics', 1, '2026-04-18 00:54:19'),
('06', 'Gynecology', 1, '2026-04-18 00:54:19'),
('07', 'Ophthalmology', 1, '2026-04-18 00:54:19'),
('08', 'ENT', 1, '2026-04-18 00:54:19'),
('09', 'Radiology', 1, '2026-04-18 00:54:19'),
('10', 'Dentistry', 1, '2026-04-18 00:54:19');

-- --------------------------------------------------------

--
-- Structure de la table `patients`
--

CREATE TABLE `patients` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_code` char(5) NOT NULL,
  `speciality_code` char(2) NOT NULL DEFAULT '00',
  `name` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_code` (`user_code`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_admins_speciality` (`speciality_code`);

--
-- Index pour la table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_appointments_doctor` (`doctor_id`),
  ADD KEY `idx_appointments_date` (`appointment_date`),
  ADD KEY `idx_appointments_status` (`status`);

--
-- Index pour la table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_consultations_rendez_vous` (`rendez_vous_id`),
  ADD KEY `idx_consultations_doctor` (`doctor_id`),
  ADD KEY `idx_consultations_patient` (`patient_id`);

--
-- Index pour la table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_code` (`user_code`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_doctors_speciality_code` (`speciality_code`);

--
-- Index pour la table `medical_specialties`
--
ALTER TABLE `medical_specialties`
  ADD PRIMARY KEY (`code`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_code` (`user_code`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_patients_speciality` (`speciality_code`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `fk_admins_speciality` FOREIGN KEY (`speciality_code`) REFERENCES `medical_specialties` (`code`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `fk_consultations_appointment` FOREIGN KEY (`rendez_vous_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_consultations_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_consultations_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `fk_doctors_speciality` FOREIGN KEY (`speciality_code`) REFERENCES `medical_specialties` (`code`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patients_speciality` FOREIGN KEY (`speciality_code`) REFERENCES `medical_specialties` (`code`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
