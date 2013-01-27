<?php
class LibUserTest extends JDbTest
{
	function setUp()
	{
		parent::setUp();
		$res=jf::$User->CreateUser("hasan", "taghi");
			
	}
	
	function testUser()
	{
		$this->assertTrue(true);
	}
	function testRed()
	{
		$this->assertTrue(false);
	}
	function testSomething()
	{
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}
	
}