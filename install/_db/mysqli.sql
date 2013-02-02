-- phpMyAdmin SQL Dump
-- version 2.11.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 31, 2009 at 01:06 AM
-- Server version: 5.0.67
-- PHP Version: 5.2.6
-- jFramework Version 2.7.5
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `jf`
--

-- --------------------------------------------------------

--
-- Table structure for table `PREFIX_logs`
--

CREATE TABLE IF NOT EXISTS `PREFIX_logs` (
  `ID` int(11) NOT NULL auto_increment,
  `Subject` varchar(256) collate utf8_bin NOT NULL,
  `Data` text collate utf8_bin NOT NULL,
  `Severity` int(11) NOT NULL,
  `UserID` int(11) default NULL,
  `SessionID` varchar(64) collate utf8_bin default NULL,
  `Timestamp` int(11) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Dumping data for table `PREFIX_logs`
--


-- --------------------------------------------------------

--
-- Table structure for table `PREFIX_options`
--

CREATE TABLE IF NOT EXISTS `PREFIX_options` (
  `UserID` int(11) NOT NULL,
  `Name` varchar(200) collate utf8_bin NOT NULL,
  `Value` text collate utf8_bin NOT NULL,
  `Expiration` int(11) NOT NULL,
  PRIMARY KEY  (`UserID`,`Name`),
  KEY `Expiration` (`Expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `PREFIX_rbac_permissions`
--

CREATE TABLE IF NOT EXISTS `PREFIX_rbac_permissions` (
  `ID` int(11) NOT NULL auto_increment,
  `Left` int(11) NOT NULL,
  `Right` int(11) NOT NULL,
  `Title` char(64) NOT NULL,
  `Description` text NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `Title` (`Title`),
  KEY `Left` (`Left`),
  KEY `Right` (`Right`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `PREFIX_rbac_permissions`
--

INSERT INTO `PREFIX_rbac_permissions` (`ID`, `Left`, `Right`, `Title`, `Description`) VALUES
(0, 1, 2, 'root', 'root');

-- --------------------------------------------------------

--
-- Table structure for table `PREFIX_rbac_rolepermissions`
--

CREATE TABLE IF NOT EXISTS `PREFIX_rbac_rolepermissions` (
  `RoleID` int(11) NOT NULL,
  `PermissionID` int(11) NOT NULL,
  `AssignmentDate` int(11) NOT NULL,
  PRIMARY KEY  (`RoleID`,`PermissionID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `PREFIX_rbac_rolepermissions`
--

INSERT INTO `PREFIX_rbac_rolepermissions` (`RoleID`, `PermissionID`, `AssignmentDate`) VALUES
(0, 0, 2009);

-- --------------------------------------------------------

--
-- Table structure for table `PREFIX_rbac_roles`
--

CREATE TABLE IF NOT EXISTS `PREFIX_rbac_roles` (
  `ID` int(11) NOT NULL auto_increment,
  `Left` int(11) NOT NULL,
  `Right` int(11) NOT NULL,
  `Title` varchar(128) NOT NULL,
  `Description` text NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `Title` (`Title`),
  KEY `Left` (`Left`),
  KEY `Right` (`Right`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `PREFIX_rbac_roles`
--

INSERT INTO `PREFIX_rbac_roles` (`ID`, `Left`, `Right`, `Title`, `Description`) VALUES
(0, 1, 2, 'root', 'root');

-- --------------------------------------------------------

--
-- Table structure for table `PREFIX_rbac_userroles`
--

CREATE TABLE IF NOT EXISTS `PREFIX_rbac_userroles` (
  `UserID` int(11) NOT NULL,
  `RoleID` int(11) NOT NULL,
  `AssignmentDate` int(11) NOT NULL,
  PRIMARY KEY  (`UserID`,`RoleID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `PREFIX_rbac_userroles`
--

INSERT INTO `PREFIX_rbac_userroles` (`UserID`, `RoleID`, `AssignmentDate`) VALUES
(1, 0, 2009);

-- --------------------------------------------------------

--
-- Table structure for table `PREFIX_session`
--

CREATE TABLE IF NOT EXISTS `PREFIX_session` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SessionID` char(64) COLLATE utf8_bin NOT NULL,
  `UserID` int(11) NOT NULL,
  `IP` char(15) COLLATE utf8_bin NOT NULL,
  `LoginDate` int(11) NOT NULL,
  `LastAccess` int(11) NOT NULL,
  `AccessCount` int(11) NOT NULL DEFAULT '1',
  `CurrentRequest` varchar(1024) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `SessionID` (`SessionID`),
  KEY `UserID` (`UserID`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `PREFIX_users`
--

CREATE TABLE IF NOT EXISTS `PREFIX_users` (
  `ID` int(11) NOT NULL auto_increment,
  `Username` char(128) collate utf8_bin NOT NULL,
  `Password` char(128) collate utf8_bin NOT NULL,
  `Salt` varchar(128) collate utf8_bin NOT NULL,
  `Protocol` float collate utf8_bin NOT NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=6 ;

--
-- Dumping data for table `PREFIX_users`
--

INSERT INTO `PREFIX_users` (`ID`, `Username`, `Password`) VALUES
(1, 'root', '119ba00fd73711a09fa82177f48f4e4ac32b1e1d73925fc4f654851b617b2a96fd5a5b3095d59b59e5cdfd71312ba3f61195414758478feced69544447360003');


CREATE TABLE IF NOT EXISTS `PREFIX_xuser` (
  `ID` int(11) NOT NULL,
  `Email` varchar(256) COLLATE utf8_bin NOT NULL,
  `PasswordChangeTimestamp` int(11) NOT NULL DEFAULT '0',
  `TemporaryResetPassword` varchar(256) COLLATE utf8_bin NOT NULL DEFAULT '',
  `TemporaryResetPasswordTimeout` int(11) NOT NULL DEFAULT '0',
  `LastLoginTimestamp` int(11) NOT NULL DEFAULT '0',
  `FailedLoginAttempts` int(11) NOT NULL DEFAULT '0',
  `LockTimeout` int(16) NOT NULL DEFAULT '0',
  `Activated` int(11) NOT NULL DEFAULT '0',
  `CreateTimestamp` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
