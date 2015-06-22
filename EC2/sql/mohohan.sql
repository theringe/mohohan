-- phpMyAdmin SQL Dump
-- version 3.4.5deb1
-- http://www.phpmyadmin.net
--
-- 主機: localhost
-- 產生日期: 2012 年 04 月 27 日 11:31
-- 伺服器版本: 5.1.62
-- PHP 版本: 5.3.6-13ubuntu3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 資料庫: `mohohan`
--

-- --------------------------------------------------------

--
-- 表的結構 `jobs`
--

CREATE TABLE IF NOT EXISTS `jobs` (
  `jid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `media` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `abr` int(10) unsigned NOT NULL,
  `vbr` int(10) unsigned NOT NULL,
  `starttime` timestamp NULL DEFAULT NULL,
  `endtime` timestamp NULL DEFAULT NULL,
  `duration` int(10) unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'NEW',
  `progress` int(10) unsigned NOT NULL DEFAULT '0',
  `jobid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`jid`),
  UNIQUE KEY `jobid` (`jobid`),
  KEY `uid` (`uid`,`media`,`abr`,`vbr`,`starttime`,`endtime`,`duration`,`status`,`progress`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的結構 `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pass` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `email` (`email`),
  KEY `name` (`name`,`pass`,`phone`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- 轉存資料表中的資料 `users`
--

INSERT INTO `users` (`uid`, `name`, `email`, `pass`, `phone`) VALUES
(1, '模榥槵', 'mohohan@gmail.com', '[[YOUR_SPECIFIC_PASSWORD_MD5]]', '0987654321');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
