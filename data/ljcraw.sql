-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2016 at 08:39 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ljcraw`
--

-- --------------------------------------------------------

--
-- Table structure for table `t_area`
--

CREATE TABLE IF NOT EXISTS `t_area` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(30) NOT NULL COMMENT '用户姓名',
  `lj_no` varchar(50) NOT NULL COMMENT '链家网编码',
  `parentid` int(10) unsigned NOT NULL COMMENT '父级ID',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `creator` varchar(50) NOT NULL DEFAULT '' COMMENT '创建者',
  `operate_time` datetime NOT NULL COMMENT '最后操作时间',
  `operator` varchar(50) NOT NULL DEFAULT '' COMMENT '操作者',
  `loginip` varchar(50) NOT NULL DEFAULT '' COMMENT '最后登录IP',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='区域表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_subway`
--

CREATE TABLE IF NOT EXISTS `t_subway` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type` tinyint(1) unsigned NOT NULL COMMENT '1:线路；2：站点',
  `name` varchar(30) NOT NULL COMMENT '线路/站点名称',
  `lj_no` varchar(50) NOT NULL COMMENT '链家网编码',
  `parentid` int(10) unsigned NOT NULL COMMENT '父级ID',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `creator` varchar(50) NOT NULL DEFAULT '' COMMENT '创建者',
  `operate_time` datetime NOT NULL COMMENT '最后操作时间',
  `operator` varchar(50) NOT NULL DEFAULT '' COMMENT '操作者',
  `loginip` varchar(50) NOT NULL DEFAULT '' COMMENT '最后登录IP',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='地铁线路表' AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
