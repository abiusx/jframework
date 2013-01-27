-- phpMyAdmin SQL Dump
-- version 2.11.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 30, 2009 at 01:18 PM
-- Server version: 5.0.67
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `jf`
--

-- --------------------------------------------------------

--
-- Table structure for table `jfp_jchat`
--

CREATE TABLE IF NOT EXISTS `jfp_jchat` (
  `ID` int(11) NOT NULL auto_increment,
  `ChannelID` int(11) NOT NULL,
  `Message` text character set latin1 NOT NULL,
  `Timestamp` datetime NOT NULL,
  `ChatterID` int(11) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Dumping data for table `jfp_jchat`
--


-- --------------------------------------------------------

--
-- Table structure for table `jfp_jchat_users`
--

CREATE TABLE IF NOT EXISTS `jfp_jchat_users` (
  `ID` int(11) NOT NULL auto_increment,
  `Nickname` char(32) NOT NULL,
  `JoinTimestamp` datetime NOT NULL,
  `ChannelID` int(11) NOT NULL,
  `SessionID` char(64) NOT NULL,
  `AccessTimestamp` datetime NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

