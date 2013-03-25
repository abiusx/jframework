<?php
jf::import("jf/test/lib/db/adapter/base");
class LibDbPdoMysqlTest extends LibDbBaseTest
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		$setting=\jf\DatabaseManager::Configuration();
		$config=new \jf\DatabaseSetting("pdo_mysql", $setting->DatabaseName, $setting->Username, $setting->Password);
		\jf\DatabaseManager::AddConnection($config,2);
		\jf\DatabaseManager::$DefaultIndex=2;
	}
	public static function tearDownAfterClass()
	{
		parent::tearDownAfterClass();
		\jf\DatabaseManager::$DefaultIndex=0;
	}
	function testQuote()
	{
		$insDb=jf::db();
		$this->assertEquals("'quote-text'",$insDb->quote("quote-text"));
	}
}

class LibJfDbalPdoMysqlStatementTest extends LibDbStatementBaseTest
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		$setting=\jf\DatabaseManager::Configuration();
		$config=new \jf\DatabaseSetting("pdo_mysql", $setting->DatabaseName, $setting->Username, $setting->Password);
		\jf\DatabaseManager::AddConnection($config,2);
		\jf\DatabaseManager::$DefaultIndex=2;
	}
	public static function tearDownAfterClass()
	{
		parent::tearDownAfterClass();
		\jf\DatabaseManager::$DefaultIndex=0;
	}
}