SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `Celestial` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `Celestial`;

DROP TABLE IF EXISTS `User`;
CREATE TABLE IF NOT EXISTS `User` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`username` varchar(50) NOT NULL,
	`password` varchar(50) NOT NULL,
	`forename` varchar(50) NOT NULL,
	`surname` varchar(50) NOT NULL,
	`age` int(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `UserFriend`;
CREATE TABLE IF NOT EXISTS `UserFriend` (
	`friendId1` int(11) NOT NULL AUTO_INCREMENT,
	`friendId2` int(11) NOT NULL,
	PRIMARY KEY (`friendId1`, `friendId2`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `UserAddress`;
CREATE TABLE IF NOT EXISTS `UserAddress` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`userId` int(11) NOT NULL,
	`houseName` varchar(50) NOT NULL,
	`postCode` varchar(50) NOT NULL,
	`landlordId` int(11),
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `UserPost`;
CREATE TABLE IF NOT EXISTS `UserPost` (
	`authorId` int(11) NOT NULL AUTO_INCREMENT,
	`postId` int(11) NOT NULL,
	PRIMARY KEY (`authorId`, `postId`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `Post`;
CREATE TABLE IF NOT EXISTS `Post` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`authorId` int(11) NOT NULL,
	`content` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `Comment`;
CREATE TABLE IF NOT EXISTS `Comment` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`postId` int(11) NOT NULL,
	`parentId` int(11) NOT NULL,
	`authorId` int(11) NOT NULL,
	`content` TEXT NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `AuthenticationCookie`;
CREATE TABLE IF NOT EXISTS `AuthenticationCookie` (
	`identifier` varchar(32) NOT NULL,
	`userId` int(11) NOT NULL,
	`token` varchar(32) NOT NULL,
	`expires` timestamp NOT NULL,
	PRIMARY KEY (`identifier`)
) ENGINE=InnoDB;
