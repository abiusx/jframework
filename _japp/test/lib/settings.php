<?php
class LibSettingsTest extends JDbTest
{
	function testSave()
	{
		$this->assertTrue(jf::SaveUserSetting("some_name","some_value",1));
		try {
			jf::SaveUserSetting("some_name","some_value");
			$this->fail();
		} catch(Exception $e) {}
	}	
	/**
	 * @depends testSave
	 */
	function testLoad()
	{
		jf::SaveUserSetting("some_name", "some_value",1);
		$this->assertEquals(jf::LoadUserSetting("some_name",1),"some_value");
		jf::SaveUserSetting("some_name", array("a","b","c"),1);
		$this->assertEquals(jf::LoadUserSetting("some_name",1),array("a","b","c"));
		try {
			jf::LoadUserSetting("some_name");
			$this->fail();
		} catch(Exception $e) {}
	}
	/**
	 * @depends testLoad
	 */	
	function testSaveTimeOut()
	{
 		$this->assertTrue(jf::SaveUserSetting("some_name", "some_value",1,24*60*60));
 		$this->movetime(24*60*60);
 		jf::$Settings->_Sweep(true);
 		$this->assertNull(jf::LoadUserSetting("some_name", 1));
	}
	/**
	 * @depends testSave
	 */
	function testDelete()
	{
		jf::SaveUserSetting("some_name", "some_value",1);
		$this->assertTrue(jf::DeleteUserSetting("some_name",1));
		try {
			jf::DeleteUserSetting("some_name");
			$this->fail();
		} catch(Exception $e) {}
	}
}