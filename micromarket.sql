-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 28 avr. 2025 à 10:36
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `micromarket`
--

-- --------------------------------------------------------

--
-- Structure de la table `assets`
--

DROP TABLE IF EXISTS `assets`;
CREATE TABLE IF NOT EXISTS `assets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `assets`
--

INSERT INTO `assets` (`id`, `file_path`, `file_name`) VALUES
(1, 'C:/wamp64/www/MDM/assets/', 'TVF-01.jpg'),
(2, 'C:/wamp64/www/MDM/assets/', 'CAO-01.jpg'),
(3, 'C:/wamp64/www/MDM/assets/', 'SRO-01.jpg'),
(4, 'C:/wamp64/www/MDM/assets/', 'MCI-01.jpg'),
(5, 'C:/wamp64/www/MDM/assets/', 'PAI-02.jpg'),
(6, 'C:/wamp64/www/MDM/assets/', 'JUC-01.jpg'),
(7, 'C:/wamp64/www/MDM/assets/', 'LAS-01.jpg'),
(8, 'C:/wamp64/www/MDM/assets/', 'EDS-01.jpg'),
(9, 'C:/wamp64/www/MDM/assets/', 'CFE-01.jpg'),
(10, 'C:/wamp64/www/MDM/assets/', 'RIB-01.jpg'),
(11, 'C:/wamp64/www/MDM/assets/', 'LEV-01.jpg'),
(12, 'C:/wamp64/www/MDM/assets/', 'BAG-01.jpg'),
(13, 'C:/wamp64/www/MDM/assets/', 'PCC-01.jpg'),
(14, 'C:/wamp64/www/MDM/assets/', 'PAS-01.jpg'),
(15, 'C:/wamp64/www/MDM/assets/', 'FDS-02.jpg'),
(16, 'C:/wamp64/www/MDM/assets/', 'TRU-01.jpg'),
(17, 'C:/wamp64/www/MDM/assets/', 'DDC-01.jpg'),
(18, 'C:/wamp64/www/MDM/assets/', 'CAL-01.jpg'),
(19, 'C:/wamp64/www/MDM/assets/', 'EMR-01.jpg'),
(20, 'C:/wamp64/www/MDM/assets/', 'CSE-01.jpg'),
(21, 'C:/wamp64/www/MDM/assets/', 'POF-01.jpg'),
(22, 'C:/wamp64/www/MDM/assets/', 'CDB-01.jpg'),
(23, 'C:/wamp64/www/MDM/assets/', 'MDC-01.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `assets_product`
--

DROP TABLE IF EXISTS `assets_product`;
CREATE TABLE IF NOT EXISTS `assets_product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produit_id` int NOT NULL,
  `asset_id` int NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `produit_id` (`produit_id`,`asset_id`),
  KEY `fk_assets_product_asset` (`asset_id`)
) ENGINE=MyISAM AUTO_INCREMENT=130 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `assets_product`
--

INSERT INTO `assets_product` (`id`, `produit_id`, `asset_id`, `is_primary`) VALUES
(107, 1, 1, 1),
(108, 2, 2, 1),
(109, 3, 3, 1),
(110, 4, 4, 1),
(111, 5, 5, 1),
(112, 6, 6, 1),
(113, 7, 7, 1),
(114, 8, 8, 1),
(115, 9, 9, 1),
(116, 10, 10, 1),
(117, 11, 11, 1),
(118, 12, 12, 1),
(119, 13, 13, 1),
(120, 14, 14, 1),
(121, 15, 15, 1),
(122, 16, 16, 1),
(123, 17, 17, 1),
(124, 18, 18, 1),
(125, 19, 19, 1),
(126, 20, 20, 1),
(127, 21, 21, 1),
(128, 22, 22, 1),
(129, 23, 23, 1);

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `category`
--

INSERT INTO `category` (`id`, `name`) VALUES
(1, 'Boulangerie/Pâtisserie'),
(2, 'Epicerie salée'),
(3, 'Epicerie sucrée'),
(4, 'Boissons'),
(5, 'Fromagerie'),
(6, 'Poissonnerie'),
(7, 'Boucherie'),
(8, 'Libre-service'),
(9, 'Vente à l’étalage'),
(10, 'Tête de gondole');

-- --------------------------------------------------------

--
-- Structure de la table `category_product`
--

DROP TABLE IF EXISTS `category_product`;
CREATE TABLE IF NOT EXISTS `category_product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=542 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `category_product`
--

INSERT INTO `category_product` (`id`, `product_id`, `category_id`) VALUES
(512, 1, 4),
(513, 2, 2),
(514, 3, 2),
(515, 4, 3),
(539, 5, 10),
(517, 6, 4),
(518, 7, 4),
(519, 8, 4),
(540, 9, 10),
(521, 10, 2),
(522, 11, 2),
(535, 12, 8),
(524, 13, 1),
(525, 14, 1),
(537, 15, 9),
(527, 16, 6),
(528, 17, 6),
(529, 18, 5),
(536, 19, 8),
(531, 20, 5),
(541, 21, 8),
(538, 22, 9),
(534, 23, 7);

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

DROP TABLE IF EXISTS `produits`;
CREATE TABLE IF NOT EXISTS `produits` (
  `id_product` int NOT NULL AUTO_INCREMENT,
  `code` varchar(4) NOT NULL,
  `description` varchar(255) NOT NULL,
  `price` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `statut_id` int DEFAULT NULL,
  `supplier_id` int DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  PRIMARY KEY (`id_product`),
  UNIQUE KEY `code` (`code`),
  KEY `category_id` (`category_id`),
  KEY `statut_id` (`statut_id`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id_product`, `code`, `description`, `price`, `category_id`, `statut_id`, `supplier_id`, `purchase_date`, `expiration_date`) VALUES
(1, 'TVF', 'Thé vert saveur framboise', 149, 4, 2, 1, '2021-04-01', '2023-04-01'),
(2, 'CAO', 'Croutons ail et olive', 210, 2, 4, 2, '2021-04-01', '2022-04-01'),
(3, 'SRO', 'Sauce rouille', 305, 2, 2, 3, '2022-04-01', '2023-04-01'),
(4, 'MCI', 'Madeleine citron', 249, 3, 1, 4, '2022-04-01', '2023-04-01'),
(5, 'PAI', 'Panettone italien', 539, 10, 2, 5, '2022-04-01', '2022-10-01'),
(6, 'JUC', 'Jus de clémentines', 154, 4, 3, 6, '2022-04-01', '2023-04-01'),
(7, 'LAS', 'La salvetat', 130, 4, 2, 7, '2022-04-01', '2023-04-01'),
(8, 'EDS', 'Eau de source', 130, 4, 2, 8, '2022-04-01', '2023-04-01'),
(9, 'CFE', 'Chutney de figues aux épices', 429, 10, 2, 9, '2022-04-01', '2023-04-01'),
(10, 'RIB', 'Riz basmati', 115, 2, 2, 10, '2022-04-01', '2023-04-01'),
(11, 'LEV', 'Lentille verte', 317, 2, 2, 10, '2022-04-01', '2023-04-01'),
(12, 'BAG', 'La baguette', 357, 8, 3, 11, '2022-04-09', '2022-04-13'),
(13, 'PCC', 'Le Pain de campagne aux céréales', 489, 1, 3, 11, '2022-04-09', '2022-04-13'),
(14, 'PAS', 'Le Pastourin', 377, 1, 1, 11, '2022-04-09', '2022-04-13'),
(15, 'FDS', 'Filet de saumon', 855, 9, 4, 12, '2022-04-09', '2022-04-13'),
(16, 'TRU', 'Truite', 625, 6, 1, 12, '2022-04-09', '2022-04-13'),
(17, 'DDC', 'Filet de colin panés', 419, 6, 2, 13, '2022-04-09', '2022-04-13'),
(18, 'CAL', 'Camembert au lait cru', 329, 5, 3, 14, '2022-04-09', '2022-05-09'),
(19, 'EMR', 'Emental rapé', 155, 8, 2, 14, '2022-04-09', '2023-04-09'),
(20, 'CSE', 'Crème semi-épaisse', 229, 5, 3, 15, '2022-04-01', '2023-04-01'),
(21, 'POF', 'Poulet fermier', 1599, 8, 2, 16, '2022-04-09', '2022-04-13'),
(22, 'CDB', 'Cote de bœuf', 2159, 9, 2, 16, '2022-04-09', '2022-04-13'),
(23, 'MDC', 'Magret de canard', 1669, 7, 2, 16, '2022-04-09', '2022-04-13');

-- --------------------------------------------------------

--
-- Structure de la table `statut`
--

DROP TABLE IF EXISTS `statut`;
CREATE TABLE IF NOT EXISTS `statut` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `statut`
--

INSERT INTO `statut` (`id`, `name`) VALUES
(1, 'En cours d’approvisionnement'),
(2, 'En stock'),
(3, 'Epuisé'),
(4, 'Retiré des rayons');

-- --------------------------------------------------------

--
-- Structure de la table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `address`) VALUES
(1, 'May Tea', ''),
(2, 'Locale-Provence', ''),
(3, 'Benedicta', ''),
(4, 'Bonne Maman', ''),
(5, 'Speciality cake', ''),
(6, 'Andros', ''),
(7, 'Danone', ''),
(8, 'Cristaline', ''),
(9, 'Picard', ''),
(10, 'Lustucru', ''),
(11, 'Les boulangers inspirés', ''),
(12, 'Les pecheurs de Saint-Baldoph', ''),
(13, 'Cité marine', ''),
(14, 'Président', ''),
(15, 'Nestlé', ''),
(16, 'Boucherie chamberienne', '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
