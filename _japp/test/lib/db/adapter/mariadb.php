<?php
jf::import("jf/test/lib/db/adapter/pdo_mysql");
class LibDbMariadbTest extends LibDbPdoMysqlTest
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		$setting=\jf\DatabaseManager::Configuration();
		$config=new \jf\DatabaseSetting("mariaDB", $setting->DatabaseName, $setting->Username, $setting->Password, $setting->Host, $setting->TablePrefix);
		\jf\DatabaseManager::AddConnection($config,2);
		\jf\DatabaseManager::$DefaultIndex=2;
	}
}

class LibJfDbalMariadbStatementTest extends LibJfDbalPdoMysqlStatementTest
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		$setting=\jf\DatabaseManager::Configuration();
		$config=new \jf\DatabaseSetting("mariaDB", $setting->DatabaseName, $setting->Username, $setting->Password, $setting->Host, $setting->TablePrefix);
		\jf\DatabaseManager::AddConnection($config,2);
		\jf\DatabaseManager::$DefaultIndex=2;
	}
}