-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2017 �?04 �?08 �?16:37
-- 服务器版本: 5.5.40
-- PHP 版本: 5.5.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `test`
--

-- --------------------------------------------------------

--
-- 表的结构 `user_info`
--

CREATE TABLE IF NOT EXISTS `user_info` (
  `id` bigint(64) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `account` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `register_date` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deadline` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` text COLLATE utf8_unicode_ci NOT NULL,
  `token` text COLLATE utf8_unicode_ci,
  `product_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'test',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B1087D9E5E237E06` (`name`),
  UNIQUE KEY `UNIQ_B1087D9E7D3656A4` (`account`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- 转存表中的数据 `user_info`
--

INSERT INTO `user_info` (`id`, `name`, `account`, `password`, `register_date`, `last_login`, `deadline`, `status`, `token`, `product_name`) VALUES
(2, '大多数的', 'vr', 'c4ca4238a0b923820dcc509a6f75849b', '1489061498', '1489137650', '1489140650', '[]', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIiwianRpIjoiMTIzNDU2In0.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3QuY29tIiwiYXVkIjoiaHR0cDpcL1wvbG9jYWxob3N0LmNvbSIsImp0aSI6IjEyMzQ1NiIsImlhdCI6MTQ4OTEzNzY1MCwiZXhwIjoxNDg5MTQwNjUwLCJpZCI6IjIifQ.', 'test'),
(3, '大多数的2', 'vr1', 'c4ca4238a0b923820dcc509a6f75849b', '1489137347', NULL, NULL, '[]', NULL, 'test');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
