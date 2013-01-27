<?php
namespace jf;
abstract class Test extends \PHPUnit_Framework_TestCase
{
	function __construct() {
		parent::__construct();
	}
	/**
	 * Adds another module to the test suite
	 * @param string $TestModule
	 */
	function add($TestModule)
	{
		$file=jf::moduleFile($TestModule);;
		if (!in_array($file, TestLauncher::$TestFiles))
		{
			TestLauncher::$TestFiles[]=$file;
			TestLauncher::$TestSuite->addTestFile($file);
		}
	}
	
}

abstract class TestSuite extends \PHPUnit_Framework_TestCase
{
	/**
	 * Adds another module to the test suite
	 * @param string $TestModule
	 */
	function add($TestModule)
	{
		$file=jf::moduleFile($TestModule);;
		if (!in_array($file, TestLauncher::$TestFiles))
		{
			TestLauncher::$TestFiles[]=$file;
			TestLauncher::$TestSuite->addTestFile($file);
			
		}
	}
	function testTrue()
	{
		
	}
	
}

abstract class DbTest extends \PHPUnit_Framework_TestCase
{
	private static $config=null;
	/**
	 * You can override this to provide custom database connection setting
	 * @returns \jf\DatabaseSetting
	 */
	function dbConfig()
	{
		if (self::$config===null)
		{
			$dbConfig=DatabaseManager::Configuration();
			$dbConfig->DatabaseName.="_test";
			self::$config=$dbConfig;
		}
		return self::$config;
	}
	function setUp()
	{
		jf::db()->Initialize($this->dbConfig()->DatabaseName);
	}
	
	private static $initiated=false;
	
	function __construct() {
		parent::__construct();
		if (!self::$initiated)
		{
			DatabaseManager::AddConnection($this->dbConfig());
			DatabaseManager::$DefaultIndex++;
			self::$initiated=true;
		}
	}
		
}
?>