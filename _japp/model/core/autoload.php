<?php
namespace jf;
class AutoloadRuleException extends \Exception {}

/**
 * Handles spl_autoload in jframework
 * @author abiusx
 * @version 1.1
 * 
 */
class Autoload
{
	/**
	 * Holds a list where keys are classnames and values are files
	 * @var array
	 */
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
	private static function AddCoreModules()
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
				"Password"=>"model/lib/security/password",
				
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
		//separates words in a camelCase word (also CamelCase)
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
	 * Handles autoloading of a class file. Automatically used by PHP SPL.
	 * If this fails and class is still needed, and error is thrown so calling it with a string is safe.
	 * @param string $Classname
	 * @return boolean success 
	 */
	static function Autoload($Classname)
	{
		if (!array_key_exists($Classname,self::$List)) 	//no direct rules
		{
			if (!self::CoreAutoload($Classname))		//no core module rules
			{
				foreach (self::$Handlers as $callback)	//try all callback handlers
				{
					if (call_user_func_array($callback,array($Classname)))
						return true;					//one loaded the module, good
				}
				return false;
			}
			else 										//its a core module, and loaded.
				return true;
		}
		require_once (self::$List[$Classname]);
		return true;									//if we got to this line, its loaded!
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
	 * This is a convenient wrapper for AddRule, which calls moduleFile on the module string and then adds the file to rules.
	 * @param string $Classname
	 * @param string $Module e.g model/folder/file
	 * @throws AutoloadRuleException
	 */
	static function AddModule($Classname,$Module)
	{
		$File=jf::moduleFile($Module);
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
	 * An array of callbacks to handle autoloads
	 * if one of them returns true, autoload is successful and is stopped, otherwise next one is processed
	 * last one attached is most priority
	 * @var string $callback
	 */
	private static $Handlers=array();

	/**
	 * Add an autoload handler callback. this is called whenever an autoload is required. last added handler has most priority
	 * NOTE: if the callback already exists in handlers, it is just moved to most priority
	 * @param string $callback a function getting classname as parameter which returns true when found and loaded the class, and false otherwise
	 */
	static function AddHandler($callback)
	{
		if (array_search($callback,self::$Handlers))
			self::RemoveHandler($callback);
		array_unshift(self::$Handlers,$callback);
	}

	/**
	 * Removes a previously added autoload handler
	 * @param string $callback
	 */
	static function RemoveHandler($callback)
	{
		if ($key=array_search($callback,self::$Handlers))
		{
			unset(self::$Handlers[$key]);
			return true;
		}
		return false;
			
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