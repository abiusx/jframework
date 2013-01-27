-- for jFramework version 2.7.4
-- --------------------------------------------------------

--
-- Table structure for table `jf_logs`
--

CREATE TABLE `jf_logs` (
  `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `Subject` varchar(256) NOT NULL,
  `Data` text NOT NULL,
  `Severity` INTEGER NOT NULL,
  `UserID` INTEGER default NULL,
  `SessionID` varchar(64) default NULL,
  `Timestamp` INTEGER NOT NULL
);
--
-- Dumping data for table `jf_logs`
--


-- --------------------------------------------------------

--
-- Table structure for table `jf_options`
--

CREATE TABLE `jf_options` (
  `UserID` INTEGER NOT NULL,
  `Name` varchar(200) NOT NULL,
  `Value` text NOT NULL,
  `Expiration` INTEGER NOT NULL,
  PRIMARY KEY  (`UserID`,`Name`)
);

-- --------------------------------------------------------

--
-- Table structure for table `jf_rbac_permissions`
--

CREATE TABLE `jf_rbac_permissions` (
  `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `Left` INTEGER NOT NULL,
  `Right` INTEGER NOT NULL,
  `Title` char(64) NOT NULL,
  `Description` text NOT NULL
);
--
-- Dumping data for table `jf_rbac_permissions`
--

INSERT INTO `jf_rbac_permissions` (`ID`, `Left`, `Right`, `Title`, `Description`) VALUES
(0, 1, 2, 'root', 'root');

-- --------------------------------------------------------

--
-- Table structure for table `jf_rbac_rolepermissions`
--

CREATE TABLE `jf_rbac_rolepermissions` (
  `RoleID` INTEGER NOT NULL,
  `PermissionID` INTEGER NOT NULL,
  `AssignmentDate` INTEGER NOT NULL,
  PRIMARY KEY  (`RoleID`,`PermissionID`)
);
--
-- Dumping data for table `jf_rbac_rolepermissions`
--

INSERT INTO `jf_rbac_rolepermissions` (`RoleID`, `PermissionID`, `AssignmentDate`) VALUES
(0, 0, '2009-08-16 22:37:58');

-- --------------------------------------------------------

--
-- Table structure for table `jf_rbac_roles`
--

CREATE TABLE `jf_rbac_roles` (
  `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `Left` INTEGER NOT NULL,
  `Right` INTEGER NOT NULL,
  `Title` varchar(128) NOT NULL,
  `Description` text NOT NULL
);
--
-- Dumping data for table `jf_rbac_roles`
--

INSERT INTO `jf_rbac_roles` (`ID`, `Left`, `Right`, `Title`, `Description`) VALUES
(0, 1, 2, 'root', 'root');

-- --------------------------------------------------------

--
-- Table structure for table `jf_rbac_userroles`
--

CREATE TABLE `jf_rbac_userroles` (
  `UserID` INTEGER NOT NULL,
  `RoleID` INTEGER NOT NULL,
  `AssignmentDate` INTEGER NOT NULL,
  PRIMARY KEY  (`UserID`,`RoleID`)
);
--
-- Dumping data for table `jf_rbac_userroles`
--

INSERT INTO `jf_rbac_userroles` (`UserID`, `RoleID`, `AssignmentDate`) VALUES
(1, 0, '2009-08-18 02:52:35');

-- --------------------------------------------------------

--
-- Table structure for table `jf_sessions`
--

CREATE TABLE `jf_sessions` (
  `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `SessionID` char(64) NOT NULL,
  `UserID` INTEGER NOT NULL,
  `IP` char(15) NOT NULL,
  `LoginDate` INTEGER NOT NULL,
  `LastAccess` INTEGER NOT NULL,
  `AccessCount` INTEGER NOT NULL default '1',
  `CurrentRequest` char(1024) NOT NULL,
  UNIQUE (`SessionID`)
);

-- --------------------------------------------------------

--
-- Table structure for table `jf_users`
--

CREATE TABLE `jf_users` (
  `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `Username` char(128) NOT NULL,
  `Password` char(128) NOT NULL,
  UNIQUE (`Username`)
);
--
-- Dumping data for table `jf_users`
--

INSERT INTO `jf_users` (`ID`, `Username`, `Password`) VALUES
(1, 'root', '119ba00fd73711a09fa82177f48f4e4ac32b1e1d73925fc4f654851b617b2a96fd5a5b3095d59b59e5cdfd71312ba3f61195414758478feced69544447360003');

