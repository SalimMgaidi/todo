-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 24 avr. 2025 à 18:59
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
-- Base de données : `t_ches`
--

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(20, 29, 'dev project a été créé', 1, '2025-04-24 18:14:34'),
(21, 29, 'salim bhim a été créé', 1, '2025-04-24 18:15:02'),
(22, 29, 'salim bhim a été supprimé', 1, '2025-04-24 18:24:19'),
(23, 29, 'idk a été créé', 1, '2025-04-24 18:24:57'),
(24, 29, 'idk a été supprimé', 1, '2025-04-24 18:25:07'),
(25, 29, 'dev project a été créé', 1, '2025-04-24 18:52:24'),
(26, 29, 'dev project a été supprimé', 1, '2025-04-24 18:55:01'),
(27, 29, 'taskq a été créé', 1, '2025-04-24 18:55:26');

-- --------------------------------------------------------

--
-- Structure de la table `tâches`
--

CREATE TABLE `tâches` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priorité` enum('Haute',' Moyenne',' Basse') NOT NULL,
  `statut` enum('En cours',' Terminée',' À faire','Annulée') NOT NULL DEFAULT ' À faire',
  `utilisateur_id` int(11) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tâches`
--

INSERT INTO `tâches` (`id`, `titre`, `description`, `priorité`, `statut`, `utilisateur_id`, `date_creation`) VALUES
(1, 'Préparer la réunion', 'Organiser les points à discuter avec l’équipe', ' Basse', ' Terminée', 14, '2025-04-06 20:58:56'),
(2, 'voler', 'vvvv', ' Basse', ' Terminée', 14, '2025-04-06 21:00:08'),
(30, 'salim bhim', 'chbih yaaml keka? bhim yekhi?', '', ' À faire', 1, '2025-04-24 10:38:56'),
(39, 'dev project', 'BLA BLA', '', ' À faire', 29, '2025-04-24 16:13:18'),
(44, 'taskq', 'dudbvzv', '', ' À faire', 29, '2025-04-24 16:55:26');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `role` enum('admin','utilisateur') NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `nom`, `email`, `mdp`, `role`, `date_creation`) VALUES
(1, 'meriem', 'meriembaganna@gmail.com', '$2y$10$h4s4b4g88kZJVqU877TDoOQR5y0WLRormDZxCXD12wZ0SThXOgEry', 'admin', '2025-03-31 03:11:07'),
(14, 'molka', 'molka@gmail.com', '$2y$10$bhQP9FOxYiYxd4XuL0tIG.AAHqDz94HOlV4fhlBhtGCczLjwPU7hu', 'utilisateur', '2025-04-06 14:31:53'),
(19, 'meriem12', 'meriem@gmail.com', '$2y$10$licxW7tutbt.4QCwKUPuGuq7VX3cyDbG1XKGS7n5Iy8LGOwLzDaeC', 'utilisateur', '2025-04-06 14:51:54'),
(25, 'mayssa', 'mayssa@gmail.com', '$2y$10$Nv1069oJlSWbycCSbBb/feMOCJJg602NMngzYDS5m0F8Rbt08jMsi', 'utilisateur', '2025-04-09 21:28:45'),
(26, 'molka', 'molkad@gmail.com', 'molkadh', 'utilisateur', '2025-04-24 11:05:30'),
(29, 'salim', 'salim@gmail.com', 'salim', 'utilisateur', '2025-04-24 14:26:06');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `tâches`
--
ALTER TABLE `tâches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `tâches`
--
ALTER TABLE `tâches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `tâches`
--
ALTER TABLE `tâches`
  ADD CONSTRAINT `tâches_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
