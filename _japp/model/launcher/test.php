<?php
namespace jf;
/**
 * Test launcher. Gets a test request and runs an automated test
 * works with PHPUnit
 * @author abiusx
 * @version 1.0
 */
class TestLauncher extends BaseLauncher
{
	public static $PHPUnit="jf/plugin/phpunit";
	
	private static $PHPUnitPhar="_japp/plugin/phpunit.phar";
	
	function  __construct($Request)
	{
		$this->Request=$Request;
		if (!$this->Launch())
			jf::run ( "view/_internal/error/404");
	}	
	/**
	 * Launches a test.
	 * @return boolean
	 */
	function Launch()
	{
		//tests are only allowed in development mode
		if (!jf::$RunMode->IsDevelop())
			return false;
		$Parts=explode("/",$this->Request);
		$Type=array_shift($Parts);
		assert($Type=="test");
		if ( $Parts [0] == "sys")
			$prepend="jf";
		else
			$prepend="";
		$Parts[0]="test";
		if ($prepend)
			array_unshift($Parts, $prepend);
		$module=implode("/",$Parts);
		
		return $this->WebLauncher($module);
	}
	
	/**
	 * The TestSuite object responsible of running tests
	 * @var \PHPUnit_Framework_TestSuite
	 */
	public static $TestSuite;
	/**
	 * List of test files run
	 * @var array
	 */
	public static $TestFiles=array();
	/**
	 * Launches a test module for web inspection of results
	 * @param string $module
	 * @return boolean
	 */
	function WebLauncher($module)
	{
		jf::$ErrorHandler->UnsetErrorHandler();
		$this->LoadFramework();

		self::$TestSuite = new \PHPUnit_Framework_TestSuite();
		self::$TestFiles[]=$this->ModuleFile($module);
		self::$TestSuite->addTestFile(self::$TestFiles[0]);
		$result = new \PHPUnit_Framework_TestResult;
		$Profiler=new Profiler();
		self::$TestSuite->run($result);
		$Profiler->Stop();
		$this->OutputResult($result,$Profiler);
		return true;
	}
	
	/**
	 * Outputs test suite run results in a web friendly interface
	 * @param \PHPUnit_Framework_TestResult $Result
	 * @param Profiler $Profiler
	 */
	function OutputResult($Result,$Profiler)
	{
		if (jf::$RunMode->IsCLI())
			$file="cli";
		else
			$file='web';
		jf::run("jf/view/_internal/test/result/{$file}",array("result"=>$Result,"profiler"=>$Profiler));
		
	}
	
	private function CLILauncher()
	{
		$command="php '".jf::root()."/".self::$PHPUnitPhar."' '".$this->ModuleFile("jf/test/main")."'";
		var_dump($command);
		$res=shell_exec($command);
		var_dump($res);	
		return true;
	}
	
	/**
	 * Loads PHPUnit framework
	 */
	private function LoadFramework()
	{
		jf::import(self::$PHPUnit."/Autoload");
		jf::import("jf/model/namespace/public/test");
	}
	
}