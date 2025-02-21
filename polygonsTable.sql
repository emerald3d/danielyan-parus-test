-- Версия сервера: 8.0.41

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Структура таблицы `polygons`
--

CREATE TABLE `polygons` (
  `county` int NOT NULL COMMENT 'Кадастровый округ',
  `district` int NOT NULL COMMENT 'Кадастровый район',
  `quarter` int NOT NULL COMMENT 'Кадастровый квартал',
  `plot` int NOT NULL COMMENT 'Кадастровый номер земельного участка',
  `polygon` geometry NOT NULL COMMENT 'Полигон/Мультиполигон'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Индексы таблицы `polygons`
--
ALTER TABLE `polygons`
  ADD PRIMARY KEY (`county`,`district`,`quarter`,`plot`),
  ADD SPATIAL KEY `polygon` (`polygon`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
