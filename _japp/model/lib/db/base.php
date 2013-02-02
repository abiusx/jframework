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
		if (count($args)>=1)
			call_user_func_array ( array ($statement, "bindAll" ), $args );
		$statement->execute();
		$type = substr ( trim ( strtoupper ( $Query ) ), 0, 6 );
		if ($type == "INSERT")
		{
			$res=$this->LastID (); //returns 0 if no auto-increment found
			if ($res==0)
				$res=$statement->rowCount();
			return $res;
		}
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

		$this->DropAllTables($DatabaseName);		

		$Query=$this->GetInitializationSQL();
		$Queries=explode(";",$Query);
		foreach ($Queries as $Q)
			$this->query($Q);
	}
	/**
	 * this function sets up database from install files, but only
	 * empties tables and inserts initial data into them.
	 * This is much faster than Initialize for testing fixture.
	 * @param string $DatabaseName
	 */
	function InitializeData($DatabaseName)
	{
		$this->TruncateAllTables($DatabaseName);
		
		$Query=$this->GetDataSQL();
		$Queries=explode(";",$Query);
		foreach ($Queries as $Q)
			$this->query($Q);
	}
		
	/**
	 * Returns list of tables in a database
	 * @param string $DatabaseName
	 * @return array|null
	 */
	protected function ListTables($DatabaseName)
	{
		$TablesQuery=$this->SQL("SELECT table_name
				FROM information_schema.tables
				WHERE TABLE_SCHEMA = '{$DatabaseName}'");
		$out=array();
		if (is_array($TablesQuery))
		foreach ($TablesQuery as $t)
			$out[]=$t['table_name'];
		return $out;
	}
	/**
	 * Drops all tables of a database
	 * @param string $DatabaseName
	 */
	protected function DropAllTables($DatabaseName)
	{
		$tables=$this->ListTables($DatabaseName);
		if (is_array($tables))
		foreach ($tables as $tableName)
			$this->SQL("DROP TABLE ".$tableName);
	}

	/**
	 * Truncates all data from all tables
	 * @param string $DatabaseName
	 */
	protected function TruncateAllTables($DatabaseName)
	{
		$tables=$this->ListTables($DatabaseName);
		if (is_array($tables))
		foreach ($tables as $tableName)
			$this->SQL("TRUNCATE ".$tableName);
	}
	/**
	 * Gets sql from a setup sql file
	 * @param string $Type
	 * @throws Exception
	 */
	private function GetSQL($Type)
	{
		$Adapter=substr(get_class($this),strlen("jf\\DB_"));
		$SetupFile=realpath(__DIR__."/../../../../".self::$DatabaseSetupFolder."{$Adapter}.{$Type}.sql");
		if (file_exists($SetupFile))
		{
			return  str_replace("PREFIX_",DatabaseManager::$TablePrefix,file_get_contents($SetupFile));
		}
		else
			throw new \Exception("No database setup file available for '{$Adapter}'.");
	}
	/**
	 * Returns the SQL for schema generation
	 * @return string
	 */
	protected function GetSchemaSQL()
	{
		return $this->GetSQL("schema");	
	}
	/**
	 * Returns the SQL for initial data
	 * @return string
	 */
	protected function GetDataSQL()
	{
		return $this->GetSQL("data");	
	}
	/**
	 * Returns SQL for setup, which is a mixture of Schema and Data SQLs
	 * @return string
	 */
	protected function GetInitializationSQL()
	{
		return $this->GetSchemaSQL().$this->GetDataSQL();
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
	 * return number of affected rows by UPDATE, INSERT and DELETE. On some drivers might return number of selected rows.
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