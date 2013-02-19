<?php
class LibRbacMainTest extends JTestSuite
{
	function __construct()
	{
		$this->add("jf/test/lib/rbac/roles");
		$this->add("jf/test/lib/rbac/permissions");
		$this->add("jf/test/lib/rbac/users");
	}
}