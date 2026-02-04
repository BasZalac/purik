-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Dec 03. 09:45
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `vote`
--
CREATE DATABASE IF NOT EXISTS `vote` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_hungarian_ci;
USE `vote`;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `answer`
--

CREATE TABLE `answer` (
  `aid` bigint(20) NOT NULL,
  `atext` varchar(1000) NOT NULL,
  `qid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `answer`
--

INSERT INTO `answer` (`aid`, `atext`, `qid`) VALUES
(7, 'Semmire', 2),
(8, 'Hogy felidegesítsen', 2),
(9, 'Lúzereknekk', 2),
(10, 'Megkönnyíti, hogy hülyék maradhassunk a másikhoz', 2),
(15, 'Nem', 3),
(16, 'Dehogynem', 3),
(17, 'Aligha', 3),
(18, 'Talán', 3);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `question`
--

CREATE TABLE `question` (
  `qid` int(11) NOT NULL,
  `qtext` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `question`
--

INSERT INTO `question` (`qid`, `qtext`) VALUES
(2, 'Mire is jó a Frontend és a Backend szétválasztása?'),
(3, 'Jó lesz ez backendnek FE oktatáshoz?');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `user`
--

CREATE TABLE `user` (
  `uid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `pass` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `user`
--

INSERT INTO `user` (`uid`, `name`, `ip`, `pass`, `role`) VALUES
(0, 'admin', NULL, '$2y$10$okvOUNxBxjKjPIk0xO6au.0nvjdgtKjBbcivZ2POhzjzX6UCdCk8K', 'admin'),
(8, 'greti', NULL, '$2y$10$Pi7SlAsOxPrXr2alb7J38.cB04MVZ8cQqyUWwWivjF.hgnM2f2cry', 'user');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `vote`
--

CREATE TABLE `vote` (
  `vid` bigint(20) NOT NULL,
  `uid` int(11) NOT NULL,
  `qid` int(11) DEFAULT NULL,
  `aid` bigint(20) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `vote`
--

INSERT INTO `vote` (`vid`, `uid`, `qid`, `aid`, `ip`) VALUES
(6, 0, 2, 8, NULL),
(7, 0, 3, 16, NULL),
(8, 0, 3, 18, NULL),
(9, 8, 2, 7, NULL),
(10, 8, 3, 17, NULL);

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `answer`
--
ALTER TABLE `answer`
  ADD PRIMARY KEY (`aid`),
  ADD KEY `qid` (`qid`);

--
-- A tábla indexei `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`qid`);

--
-- A tábla indexei `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`uid`);

--
-- A tábla indexei `vote`
--
ALTER TABLE `vote`
  ADD PRIMARY KEY (`vid`),
  ADD KEY `uid` (`uid`),
  ADD KEY `qid` (`qid`),
  ADD KEY `aid` (`aid`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `answer`
--
ALTER TABLE `answer`
  MODIFY `aid` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT a táblához `question`
--
ALTER TABLE `question`
  MODIFY `qid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT a táblához `user`
--
ALTER TABLE `user`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT a táblához `vote`
--
ALTER TABLE `vote`
  MODIFY `vid` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `answer`
--
ALTER TABLE `answer`
  ADD CONSTRAINT `answer_ibfk_1` FOREIGN KEY (`qid`) REFERENCES `question` (`qid`) ON DELETE CASCADE;

--
-- Megkötések a táblához `vote`
--
ALTER TABLE `vote`
  ADD CONSTRAINT `vote_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE,
  ADD CONSTRAINT `vote_ibfk_2` FOREIGN KEY (`qid`) REFERENCES `question` (`qid`) ON DELETE CASCADE,
  ADD CONSTRAINT `vote_ibfk_3` FOREIGN KEY (`aid`) REFERENCES `answer` (`aid`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
