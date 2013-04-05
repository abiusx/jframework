<?php
/* jFramework
 * Database Access Layer Library
 */
namespace jf;


class DatabaseSetting extends Model
{
	public $Adapter,$DatabaseName,$Username,$Password,$Host,$TablePrefix;
	function __construct($Adapter,$DatabaseName,$Username,$Password,$Host="localhost",$TablePrefix="jf_")
	{
		$this->Adapter=$Adapter;
		$this->Username=$Username;
		$this->Password=$Password;
		$this->DatabaseName=$DatabaseName;
		$this->Host=$Host;
		$this->TablePrefix=$TablePrefix;
		if($this->TablePrefix=="")
			throw new \Exception("TablePrefix shouldn't be an empty string: ".$this->Adapter);
	}
}
class NoDatabaseSetting extends DatabaseSetting
{
	function __construct()
	{
		$this->Adapter=$this->DatabaseName=$this->Username=$this->Password=$this->Host=$this->TablePrefix=null;
	}
}

jf::import("jf/model/lib/db/base",".");
jf::import("jf/model/lib/db/nestedset/base",".");
jf::import("jf/model/lib/db/nestedset/full",".");

class DatabaseManager extends Model
{
	/**
	 * 
	 * @var DatabaseSetting
	 */
	protected static $Configurations=array();
	protected static $Connections=array();
	/**
	 * Set this to enforce a table prefix on all jframework database tables
	 * @var string
	 */
	static $TablePrefix="";
	static function AddConnection(DatabaseSetting $dbConfig,$Index=null)
	{
		$Classname="\\jf\\DB_{$dbConfig->Adapter}";
		try {
			jf::import("jf/model/lib/db/adapter/{$dbConfig->Adapter}");
		}
		catch (ImportException $e)
		{
			echo "Database adapter '{$dbConfig->Adapter}' not found.";
			throw $e;
		}
		if ($Index===null)
		{
			self::$Configurations[]=$dbConfig;
			return self::$Connections[]=new $Classname($dbConfig);
		}
		else
		{
			self::$Configurations[$Index]=$dbConfig;
			return self::$Connections[$Index]=new $Classname($dbConfig);	
		}
				
	}
	/**
	 * Holds the index of default database, used by db and SQL functions of jf:: accessor
	 * @var integer
	 */
	static $DefaultIndex=0;
	/**
	 * Returns a database connection
	 * @param integer $Index
	 * @return BaseDatabase
	 */
	static function Database($Index=null)
	{
		if ($Index===null)
// 			return reset(self::$Connections);
			return self::$Connections[self::$DefaultIndex];
		else
			return self::$Connections[$Index];
	}
	/**
	 * Returns a database connection setting
	 * @param integer $Index
	 * @return DatabaseSetting
	 */
	static function Configuration($Index=null)
	{
		if ($Index===null)
			return self::$Configurations[self::$DefaultIndex];
		else		
		return self::$Configurations[$Index];
	} 
}







?>