<?php
namespace jf;
class AutoloadRuleException extends \Exception {}

/**
 * Handles spl_autoload in jframework
 * @author abiusx
 * @version 1.0
 * 
 */
class Autoload
{
	private static $List=array(); 
	function __construct()
	{
		throw new Exception("Autoload class is static and should not be instantiated.");
	}
	
	/**
	 * Register this class as autoload handler
	 */
	static function Register()
	{
		spl_autoload_register ( __NAMESPACE__."\Autoload::Autoload" , true );
		self::AddCoreModules();	
	}
	/**
	 * this function adds rules for autoloading of core jframework modules
	 */
	static function AddCoreModules()
	{
		$Array=array(
				"Model"=>"model/base/model",
				"Controller"=>"model/base/control",
				"View"=>"model/base/view",
				"Plugin"=>"model/base/plugin",
				"Test"=>"model/base/test",

				"ErrorHandler"=>"model/core/errorhandler",
				"FileManager"=>"model/core/fileman",
				"HttpRequest"=>"model/core/http",

				"BaseLauncher"=>"model/launcher/base",
				"ApplicationLauncher"=>"model/launcher/application",
				"SystemLauncher"=>"model/launcher/system",
				"FileLauncher"=>"model/launcher/file",
				"TestLauncher"=>"model/launcher/test",

				"DatabaseManager"=>"model/lib/db",

				"Profiler"=>"model/lib/profiler",
				"LogManager"=>"model/lib/log",
				"UserManager"=>"model/lib/user",
				"ExtendedUserManager"=>"model/lib/xuser",
				"SessionManager"=>"model/lib/session",
				"SecurityManager"=>"model/lib/security",
				"SettingManager"=>"model/lib/settings",
				"RBACManager"=>"model/lib/rbac",
				"ServiceManager"=>"model/service/manager",
				
				);
		
		
		$RuleArray=array();
		foreach ($Array as $k=>$v)
			$RuleArray[__NAMESPACE__."\\{$k}"]=realpath(__DIR__."/../../{$v}.php");
		self::AddRuleArray($RuleArray);
	}
	
	
	/**
	 * this array holds conversion rules for converting a classname to a folder for autoloading it
	 * @var array
	 */
	public static $ClasstypeArray=array(
			"model"=>"Model", //default
			"control"=>"Controller",
			"service"=>"Service",
			"plugin"=>"Plugin",
			"view"=>"View",
			"test"=>"Test",
			);
	/**
	 * Handles autoloading of core jframework modules which follow CamelCaseNotation names corresponding to their folders
	 * @param string $Classname
	 * @return boolean success
	 */
	private static function CoreAutoload($Classname)
	{
		if (substr($Classname,0,3)=="jf\\")
		{
			$Classname=substr($Classname,3);
			$Prefix="_j"; //_japp folder
		}
		else 
			$Prefix="";
		preg_match_all('/((?:^|[A-Z])[a-z]+)/',$Classname,$matches);
		if (!is_array($matches[0]))
			return false;
		
		$Parts=$matches[0];
		$Type=array_pop($Parts);
		$ClasstypeToFolderArray=array_flip(self::$ClasstypeArray);
		if (array_key_exists($Type,$ClasstypeToFolderArray))
		{
			$folder=$ClasstypeToFolderArray[$Type];
		}
		else //default type to model
		{
			array_push($Parts, $Type);
			$folder="model";
		}
			
		$File=realpath(__DIR__."/../../../{$Prefix}app/". //_japp or app folder
			"{$folder}/".strtolower(implode("/",$Parts)).".php");
		if (file_exists($File))
		{
			require_once $File;
			return true;
		}
		return false;
		
	}
	/**
	 * Handles autoloading of a class file. Automatically used by PHP SPL
	 * @param string $Classname
	 * @return boolean success 
	 */
	static function Autoload($Classname)
	{
		if (!array_key_exists($Classname,self::$List))
			if (!self::CoreAutoload($Classname))
				return false;#throw new AutoloadException($Classname);
			else
				return true;
		require_once (self::$List[$Classname]);
	}
	
	/**
	 * Add a single rule for autoload handling. Classname and file to include. Nothing is appended to file.
	 * To get the file for a module, use moduleFile function of JModel class (available in almost all objects)
	 * overwrites on existence
	 * @param string $Classname
	 * @param string $File
	 */
	static function AddRule($Classname,$File)
	{
		if (!file_exists($File))
			throw new AutoloadRuleException("Invalid autoload rule added: {$File} set for autoloading of class '{$Classname}' does not exist.");
		self::$List[$Classname]=$File;
	} 
	
	/**
	 * Adds autoload rules in bulk, array keys are class names and array values are files to be included
	 * @param array $RuleArray
	 */
	static function AddRuleArray(array $RuleArray)
	{
		foreach ($RuleArray as $Classname=>$File)
			self::AddRule($Classname, $File);
		
	}
	/**
	 * Remove a single autoload rule
	 * @param string $Classname
	 */
	static function RemoveRule($Classname)
	{
		$result=array_key_exists($Classname, self::$List);
		unset (self::$List[$Classname]);
		return $result;
	}
	/**
	 * Removes autoload rules in bulk, array keys are classnames to be removed
	 * @param array $RuleArray
	 */
	static function RemoveRuleArrayKeys(array $RuleArray)
	{
		foreach ($RuleArray as $Classname=>$v)
			self::RemoveRule($Classname);
	}
	
	/**
	 * Remove autoload rules in bulk, array values are classnames to be removed
	 * @param array $RuleArray
	 */
	static function RemoveRuleArrayValues(array $RuleArray)
	{
		foreach ($RuleArray as $Classname)
			self::RemoveRule($Classname);
	}
}