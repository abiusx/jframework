<?php
jf::import("jf/test/lib/db/adapter/base");
class LibDbMysqliTest extends LibDbBaseTest
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		$setting=\jf\DatabaseManager::Configuration();
		$config=new \jf\DatabaseSetting("mysqli", $setting->DatabaseName, $setting->Username, $setting->Password, $setting->Host, $setting->TablePrefix);
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
		$this->assertEquals("\'quote-test\'",$insDb->quote("'quote-test'"));
	}
}

class LibDbStatementMysqliTest extends LibDbStatementBaseTest
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		$setting=\jf\DatabaseManager::Configuration();
		$config=new \jf\DatabaseSetting("mysqli", $setting->DatabaseName, $setting->Username, $setting->Password, $setting->Host, $setting->TablePrefix);
		\jf\DatabaseManager::AddConnection($config,2);
		\jf\DatabaseManager::$DefaultIndex=2;
	}
	public static function tearDownAfterClass()
	{
		parent::tearDownAfterClass();
		\jf\DatabaseManager::$DefaultIndex=0;
	}
}