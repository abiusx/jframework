

INSERT INTO `PREFIX_rbac_permissions` (`ID`, `Left`, `Right`, `Title`, `Description`) VALUES
(0, 1, 2, 'root', 'root');


INSERT INTO `PREFIX_rbac_rolepermissions` (`RoleID`, `PermissionID`, `AssignmentDate`) VALUES
(0, 0, 2009);



INSERT INTO `PREFIX_rbac_roles` (`ID`, `Left`, `Right`, `Title`, `Description`) VALUES
(0, 1, 2, 'root', 'root');


INSERT INTO `PREFIX_rbac_userroles` (`UserID`, `RoleID`, `AssignmentDate`) VALUES
(1, 0, 2009);



INSERT INTO `PREFIX_users` (`ID`, `Username`, `Password`) VALUES
(1, 'root', '119ba00fd73711a09fa82177f48f4e4ac32b1e1d73925fc4f654851b617b2a96fd5a5b3095d59b59e5cdfd71312ba3f61195414758478feced69544447360003');

