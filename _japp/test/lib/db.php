<?php
class LibDatabaseManagerTest extends JDbTest
{
	function testAddConnection()
	{
		$userConfig= \jf\DatabaseManager::Configuration("test");
		$dbConfig= new \jf\DatabaseSetting($userConfig->Adapter, "db_name", $userConfig->Username, $userConfig->Password, $userConfig->Host, $userConfig->TablePrefix);
		\jf\DatabaseManager::AddConnection($dbConfig); 
		$this->assertNotNull(\jf\DatabaseManager::Database(1)->Connection);
		$this->assertSame(\jf\DatabaseManager::Configuration(1),$dbConfig);

		for($i=5; $i<10; $i++)
		{
			$dbConfig= new \jf\DatabaseSetting($userConfig->Adapter, "db_name{$i}", $userConfig->Username, $userConfig->Password, $userConfig->Host, $userConfig->TablePrefix);
			\jf\DatabaseManager::AddConnection($dbConfig,$i);
			$this->assertNotNull(\jf\DatabaseManager::Database($i)->Connection);
			$this->assertSame(\jf\DatabaseManager::Configuration($i),$dbConfig);
		}
	}
// 	function testRemoveConnection()
// 	{
// 		$userConfig= \jf\DatabaseManager::Configuration(1); //index 1 is for test mode ans 0 for app mode
// 		$dbConfig= new \jf\DatabaseSetting($userConfig->Adapter, "db_name", $userConfig->Username, $userConfig->Password);
// 		\jf\DatabaseManager::AddConnection($dbConfig,20); //first empty place in configuration array
		
// 		\jf\DatabaseManager::RemoveConnection(\jf\DatabaseManager::Configuration(1),20);
// 	}
}