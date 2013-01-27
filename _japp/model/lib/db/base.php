<?php
namespace jf;

/**
 * BaseDatabase class, intended as parent for all database wrappers
 * See comments for conventions
 * All methods starting with capital are jf introduced (for simplicity)
 * all other methods are PDO interface
 * @author abiusx
 *
 */
abstract class BaseDatabase
{
	protected $QueryCount;
	protected $QueryTime;
	private $tempQueryTime; //for QueryTimeIn()
	

	abstract function __construct(DatabaseSetting $db);
	private function GetMicroTime()
	{
		$match = explode ( " ", microtime () );
		return ($match [1] + $match [0]) * 1000000;
	
	}
	/**
	 * wrappers should call this before running any query
	 */
	public function QueryStart()
	{
		$this->tempQueryTime = $this->GetMicroTime ();
	}
	/**
	 * wrappers should call this after running any query
	 */
	public function QueryEnd()
	{
		$this->QueryTime += ($this->GetMicroTime () - $this->tempQueryTime) / 1000000.0;
		$this->QueryCount++;
	}
	
	/**
	 * returns number of queries executed with this instance
	 * @return int
	 */
	public  function QueryCount()
	{
		return $this->QueryCount;
	}
	/**
	 * returns time for queries consumed by this instance's database
	 * @return double
	 */
	public function QueryTime()
	{
		return $this->QueryTime;
	}
	
	/**
	 * returns insertion ID
	 */
	abstract function LastID();
	
	function __get($name)
	{
		if ($name == "lastInsertId")
			return $this->LastID;
	}
	
	/**
	 * 
	 * run query, return affected rows
	 * SHOULD do query timing
	 * @return int affected rows
	 */
	abstract function exec($Query);
	/**
	 * 
	 * runs a query and returns result statement
	 * SHOULD do query timing
	 * @param string $QueryString
	 * @return jfBaseDatabaseStatement
	 */
	abstract function query($QueryString);
	
	/**
	 * escapes an string and return.
	 * @param string $Param
	 */
	abstract function quote($Param);
	/**
	 * 
	 * alias for quote
	 * @param unknown_type $Param
	 */
	function Escape($Param)
	{
		return $this->quote ( $Param );
	}
	/**
	 * 
	 * prepare a query and returns result statement
	 * @param string $QueryString
	 * @return BaseDatabaseStatement
	 */
	abstract function prepare($QueryString);
	
	/**
	 * 
	 * Perform an SQL operation by preparing query and binding params and running the query, 
	 * if it was an INSERT, return last insert ID,
	 * if it was DELETE or UPDATE, return affected rows
	 * and if it was SELECT, return 2D result array
	 * @param string $Query
	 * @return mixed
	 */
	function SQL($Query)
	{
		$args = func_get_args ();
		array_shift ( $args );
		$statement = $this->prepare ( $Query );
		if (count($args)>1)
			call_user_func_array ( array ($statement, "bindAll" ), $args );
		$statement->execute();
		$type = substr ( trim ( strtoupper ( $Query ) ), 0, 6 );
		if ($type == "INSERT")
			return $this->LastID ();
		elseif ($type == "DELETE" or $type == "UPDATE" or $type == "REPLAC")
			return $statement->rowCount ();
		elseif ($type == "SELECT")
		{
			return $statement->fetchAll ();
		}
		else
			return null;
	}
	
	static $AutoInitialize=true;
	/**
	 * 
	 * This function sets up database from install files.
	 * It should also select the database after its done.
	 * An adapter should reimplement this and use mutliple query function and native syntax
	 */
	function Initialize($DatabaseName)
	{
		$CreateDBSQL="CREATE DATABASE `{$DatabaseName}`;
					USE `{$DatabaseName}`;";
		$Query=$CreateDBSQL.$this->GetInitializationSQL();
		$Queries=explode(";",$Query);
		foreach ($Queries as $Q)
			$this->query($Q);
	}
		
	protected function GetInitializationSQL()
	{
		$Adapter=substr(get_class($this),strlen("jf\\DB_"));
		$SetupFile=realpath(__DIR__."/../../../../".self::$DatabaseSetupFolder."{$Adapter}.sql");
		if (file_exists($SetupFile))
		{
			return  str_replace("PREFIX_",DatabaseManager::$TablePrefix,file_get_contents($SetupFile));
		}
		else
			throw new Exception("No database setup file available for '{$Adapter}'.");
	}
	
	protected static $DatabaseSetupFolder="install/_db/";


}
abstract class BaseDatabaseStatement
{
	/**
	 * 
	 * @var BaseDatabase
	 */
	protected $DB;
	/** 
	 * 
	 * fetch single associative result
	 * @return array or null
	 */
	abstract function fetch();
	
	/**
	 * 
	 * fetch all results
	 */
	function fetchAll()
	{
		$out = array ();
		while ( $r = $this->fetch () )
			$out [] = $r;
		return $out;
	}
	
	/**
	 * 
	 * bind all params
	 */
	abstract function bindAll();
	/**
	 * 
	 * Alias for bindAll
	 */
	function Bind()
	{
		$args = func_get_args ();
		return call_user_func_array ( array ($this, bindAll ), $args );
	}
	/**
	 * execute a prepared statement
	 * SHOULD do query timing
	 * @return boolean
	 */
	abstract function execute();
	
	/**
	 * 
	 * return number of result rows
	 * @return int
	 */
	abstract function rowCount();
	
	/**
	 * Bind all, execute and return all
	 * @return array or null
	 */
	function Run()
	{
		$args=func_get_args();
		$r=call_user_func_array(array($this,'execute'), $args);
		if ($r)
			return $this->fetchAll();
		else
			return null;
	}
} 