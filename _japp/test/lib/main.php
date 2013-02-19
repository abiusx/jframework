<?php
class LibMainTest extends JTestSuite
{
	function __construct()
	{
		$this->add("jf/test/lib/rbac/main");
		$this->add("jf/test/lib/session");
		$this->add("jf/test/lib/user");
		$this->add("jf/test/lib/xuser");
	}
}