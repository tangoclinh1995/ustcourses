SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `departments` (
`Id` int(11) NOT NULL,
  `Code` varchar(6) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `departments` ADD PRIMARY KEY (`Id`);
ALTER TABLE `departments` ADD UNIQUE(`Code`);
ALTER TABLE `departments` MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `departments` auto_increment = 1; 



CREATE TABLE IF NOT EXISTS `courses` (
  `Id` smallint(6) NOT NULL,
  `Dept` varchar(6) COLLATE utf8_bin NOT NULL,
  `Code` varchar(6) COLLATE utf8_bin NOT NULL,
  `Name` varchar(80) COLLATE utf8_bin NOT NULL,
  `Unit` tinyint(4) NOT NULL,
  `Matching` tinyint(1) NOT NULL,
  `Description` varchar(1000) COLLATE utf8_bin NOT NULL,
  `CommonCore` tinyint(4) NOT NULL,
  `SchoolSponsored` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `courses` ADD PRIMARY KEY (`Id`);



CREATE TABLE IF NOT EXISTS `lectures` (
  `CourseId` smallint(6) NOT NULL,
  `Nbr` smallint(6) NOT NULL,
  `No` tinyint(4) NOT NULL,
  `Suffix` varchar(1) COLLATE utf8_bin NOT NULL,
  `Room` varchar(30) COLLATE utf8_bin NOT NULL,
  `Instructor` varchar(50) COLLATE utf8_bin NOT NULL,
  `Quota` smallint(6) NOT NULL,
  `Avail` smallint(6) NOT NULL,
  `QuotaSpec` smallint(6) NOT NULL,
  `AvailSpec` smallint(6) NOT NULL,
  `Wait` smallint(6) NOT NULL,
  `Consent` tinyint(1) NOT NULL,
  `Notes` varchar(200) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `lectures_time` (
  `CourseId` smallint(6) NOT NULL,
  `Nbr` smallint(6) NOT NULL,
  `WeekDay` tinyint(4) NOT NULL,
  `Start` smallint(6) NOT NULL,
  `End` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `lectures` ADD PRIMARY KEY (`Nbr`);
ALTER TABLE `lectures_time` ADD KEY `Nbr` (`Nbr`);



CREATE TABLE IF NOT EXISTS `tutorials` (
  `CourseId` smallint(6) NOT NULL,
  `Nbr` smallint(6) NOT NULL,
  `No` tinyint(4) NOT NULL,
  `Suffix` varchar(1) COLLATE utf8_bin NOT NULL,
  `Room` varchar(30) COLLATE utf8_bin NOT NULL,
  `Instructor` varchar(50) COLLATE utf8_bin NOT NULL,
  `Quota` smallint(6) NOT NULL,
  `Avail` smallint(6) NOT NULL,
  `QuotaSpec` smallint(6) NOT NULL,
  `AvailSpec` smallint(6) NOT NULL,
  `Wait` smallint(6) NOT NULL,
  `Consent` tinyint(1) NOT NULL,
  `Notes` varchar(200) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `tutorials_time` (
  `CourseId` smallint(6) NOT NULL,
  `Nbr` smallint(6) NOT NULL,
  `WeekDay` tinyint(4) NOT NULL,
  `Start` smallint(6) NOT NULL,
  `End` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `tutorials` ADD PRIMARY KEY (`Nbr`);
ALTER TABLE `tutorials_time` ADD KEY `Nbr` (`Nbr`);



CREATE TABLE IF NOT EXISTS `labs` (
  `CourseId` smallint(6) NOT NULL,
  `Nbr` smallint(6) NOT NULL,
  `No` tinyint(4) NOT NULL,
  `Suffix` varchar(1) COLLATE utf8_bin NOT NULL,
  `Room` varchar(30) COLLATE utf8_bin NOT NULL,
  `Instructor` varchar(50) COLLATE utf8_bin NOT NULL,
  `Quota` smallint(6) NOT NULL,
  `Avail` smallint(6) NOT NULL,
  `QuotaSpec` smallint(6) NOT NULL,
  `AvailSpec` smallint(6) NOT NULL,
  `Wait` smallint(6) NOT NULL,
  `Consent` tinyint(1) NOT NULL,
  `Notes` varchar(200) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `labs_time` (
  `CourseId` smallint(6) NOT NULL,
  `Nbr` smallint(6) NOT NULL,
  `WeekDay` tinyint(4) NOT NULL,
  `Start` smallint(6) NOT NULL,
  `End` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `labs` ADD PRIMARY KEY (`Nbr`);
ALTER TABLE `labs_time` ADD KEY `Nbr` (`Nbr`);