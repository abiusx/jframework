<?php
class LibMainTest extends JTestSuite
{
	function __construct()
	{
		$this->add("jf/test/lib/session");
		$this->add("jf/test/lib/user");
	}
}