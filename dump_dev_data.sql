SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

INSERT INTO `activities` (`id`, `name`, `type`, `responsible_staff`, `summary`, `explicit`) VALUES
(1, 'Intro till anime', 'LECTURE', 'Anna Svensson', 'En introduktionsföreläsning om anime och manga för nybörjare.', 0),
(2, 'Cosplay-workshop', 'CREATIVE', 'Björn Larsson', 'Lär dig grunderna i att sy och bygga cosplay-kläder.', 0),
(3, 'Spirited Away', 'MOVIE', 'Filmteamet', '', 0),
(4, 'My Neighbor Totoro', 'MOVIE', 'Filmteamet', '', 0),
(5, 'Mangafigurer hela dagen', 'DRAWING', 'Lena Nilsson', 'Rita mangafigurer i eget tempo under hela konventsdagen.', 0),
(6, 'Vuxen anime-kväll', 'LECTURE', 'Erik Holm', 'Föreläsning om mörkare teman i anime. Ej lämpat för barn.', 1);

-- Activity 1 has a changed time (two rows, max(id) = current)
INSERT INTO `activities_time_and_place` (`id`, `activity_id`, `timestamp`, `room`, `start_time`, `end_time`) VALUES
(1, 1, '2026-06-01 08:00:00', 'Sal B', '09:00:00', '10:00:00'),
(2, 1, '2026-06-10 12:00:00', 'Sal A', '10:00:00', '11:00:00'),
(3, 2, '2026-06-10 12:00:00', 'Workshop-rummet', '11:30:00', '12:30:00'),
(4, 3, '2026-06-10 12:00:00', 'Biosalen', '13:00:00', '15:00:00'),
(5, 4, '2026-06-10 12:00:00', 'Biosalen', '15:30:00', '17:15:00'),
(6, 5, '2026-06-10 12:00:00', 'Atelje', '09:00:00', '17:00:00'),
(7, 6, '2026-06-10 12:00:00', 'Sal A', '16:00:00', '17:00:00');

COMMIT;
