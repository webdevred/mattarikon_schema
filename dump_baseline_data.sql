SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('MUSIC', 'musik.png', 'Musik');
INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('CREATIVE', 'palett.png', 'Måla');
INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('PUZZLE', 'pyssel.png', 'Pyssel');
INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('SEWING', 'workshop-sy.png', 'Sy');
INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('ALERT', 'alarm.png', 'Alarm');
INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('BOARDGAME', 'spel.png', 'Spel');
INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('COMPETITION', 'tavling.png', 'Tävling');
INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('DISPLAY', 'uppvisning.png', 'Uppvisning');
INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('DRAWING', 'teckning.png', 'Teckning');
INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('FOOD', 'mat.png', 'Mat');
INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('LECTURE', 'forelasning.png', 'Föreläsning');
INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('MOVIE', 'filmvisning.png', 'Film');
INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('QUIZ', 'fragesport.png', 'Frågesport');
INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('SALE', 'forsaljning.png', 'Försäljning');
INSERT INTO `activity_types` (`name`, `icon_filename`, `display_name`) VALUES('TVGAME', 'tvspel.png', 'TV-Spel');

INSERT INTO `users` (`id`, `username`, `password_hash`) VALUES('ae4bea2d-62be-11ef-9e4a-42a286aa0aa2', 'admin', '$2y$10$f5plqSKEfeXjAmNZpzr/ueuRtDy/S0qO1ARXsFVQN.BGFfxDQIMi2');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
