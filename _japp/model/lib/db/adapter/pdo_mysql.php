<?php


/**
 * jFramework PDO_MySQL driver
 * recommended for systems where MySQLi is not installed or not working properly
 * @author abiusx
 * @version 1.03
 */
class DBAL_pdo_mysqli extends BaseDatabase
{
	/**
	 * the actual DB object
	 * @var PDO
	 */
	public $DB;
	
	/**
	 * Debug mode. if set to true DBAL is intended to generate debug output
	 * @var boolean
	 */
	public $Debug=false;
	
	protected  $m_databasename;
	function __construct(DatabaseSetting $db)
	{
		if ($db->Username and $db->Username != "")
		{
			$this->DB = new \PDO ( "mysql:dbname={$db->DatabaseName};host={$db->Host};",$db->Username,$db->Password);
			$this->DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		}
		else
			$this->DB = null; //this is mandatory for no-database jFramework
		$this->m_databasename = $db->Database;
	}

	function __destruct()
	{
		if ($this->DB) $this->DB = null; //destroys the PDO object
	}

	function LastID()
	{
		return $this->DB->lastInsertId ();
	}

	function quote()
	{
		$args = func_get_args ();
		if (count($args)>1)
		{
			foreach ( $args as &$arg )
				if ($x = $this->DB->quote ( $arg )) $arg = $x;
		}
		else
			return $this->DB->quote($args[0]);
	}

	function query($QueryString)
	{
		if (! $this->DB) return null;
		$this->QueryCount += 1;
		return $this->DB->query ( $QueryString );
	}

	function exec($Query)
	{
		if (! $this->DB) return null;
		$this->QueryCount += 1;
		return $this->DB->exec($Query);
	}

	function prepare($Query)
	{
		return new DBAL_PDO_MySQL_Statement ( $this,$Query );
	}
}


/**
 * jFramework DBAL's PDO_MySQL prepared statements class
 * @author abiusx
 * @version 1.00
 */
class jfDBAL_PDO_MySQL_Statement extends BaseDatabaseStatement
{
	/**
	 * DBAL
	 *
	 * @var jfDBAL_PDO_MySQL
	 */
	private $DBAL;
	/**
	 * Enter description here...
	 *
	 * @var PDOStatement
	 */
	private $Statement;
	
	function __construct(jfDBAL_PDO_MySQL $DB,$Query)
	{
		$this->DBAL = $DB;
		$this->Statement=$DB->DB->prepare($Query);
	}

	function __destruct()
	{
		if ($this->Statement) 
			$this->Statement = null;
	}

	/**
	 * Binds a few values to a prepared statement
	 *
	 */
	function bindAll()
	{
		$args = func_get_args ();
		$i = 0;
		foreach ( $args as &$arg )
			$this->Statement->bindValue ( ++ $i, $arg );
	}

	/**
	 * Executes the prepared statement using binded values. if you provide this function with
	 * arguments, Then those would be binded as well.
	 *
	 */
	function execute()
	{
		if (func_num_args () >= 1)
		{
			$args = func_get_args ();
			call_user_func_array ( array (
				$this, "bindAll" 
			), $args );
		}
		$this->DBAL->QueryCount += 1;
		
		$this->DBAL->QueryTimeIn ();
		$r=$this->Statement->execute ();
		$this->DBAL->QueryTimeOut ();
		return $r;
	
	}

	function rowCount()
	{
		return $this->Statement->rowCount ();
	}

	function fetch()
	{
		return $this->Statement->fetch ( PDO::FETCH_ASSOC );
	}
}
?>