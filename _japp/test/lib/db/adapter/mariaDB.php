<?php
jf::import("jf/test/lib/db/adapter/pdo_mysql");
class LibDbMariaDBTest extends LibDbPdoMysqlTest
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		$setting=\jf\DatabaseManager::Configuration();
		$config=new \jf\DatabaseSetting("mariaDB", $setting->DatabaseName, $setting->Username, $setting->Password);
		\jf\DatabaseManager::AddConnection($config,2);
		\jf\DatabaseManager::$DefaultIndex=2;
	}
}

class LibJfDbalMariaDBStatementTest extends LibJfDbalPdoMysqlStatementTest
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		$setting=\jf\DatabaseManager::Configuration();
		$config=new \jf\DatabaseSetting("mariaDB", $setting->DatabaseName, $setting->Username, $setting->Password);
		\jf\DatabaseManager::AddConnection($config,2);
		\jf\DatabaseManager::$DefaultIndex=2;
	}
}