<?php
namespace jf;
/**
 * 
 * jframework MySQLi wrapper
 * @author abiusx
 * @versino 3.0
 */
class DB_mysqli extends BaseDatabase
{
	/**
	 * 
	 * @var \mysqli
	 */
	public $Connection;
	
	/**
	 * Debug mode. if set to true DB is intended to generate debug output
	 * @var boolean
	 */
	public $Debug=false;
	
	
	protected $m_databasename;

	public $Charset="utf8";
	
	function __construct(DatabaseSetting $db)
	{
		if ($db->Username && $db->Username != "")
		{
			$this->Connection = new \mysqli ( $db->Host, $db->Username, $db->Password);
			if (!$this->Connection->select_db($db->DatabaseName))
			{
				$this->SQL("CREATE DATABASE `{$db->DatabaseName}` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin");
				$this->Connection = new \mysqli ( $db->Host, $db->Username, $db->Password);
				if (!$res=$this->Connection->select_db($db->DatabaseName))
				{
					throw new \Exception("Can not initialize database.");
				}
				$this->Initialize($db->DatabaseName);
			}
			if (mysqli_connect_errno ()) throw new \Exception( "Unable to connect to MySQLi database: ".mysqli_connect_error() );
			if (isset($this->Charset)) $this->Connection->set_charset($this->Charset);
		}
		else
		{
			$this->Connection = null; //this is mandatory for no-database jFramework        
		}
		$this->m_databasename = $db->DatabaseName;
	}

	function __destruct()
	{
		if ($this->Connection) $this->Connection->close ();
	}

	function LastID()
	{
		return $this->Connection->insert_id;
	}

	function quote($Param)
	{
		return $this->Connection->real_escape_string ( $Param );
	}

	function query($QueryString)
	{
		if (! $this->Connection) return null;
		$this->QueryStart();
		$res=$this->Connection->query ( $QueryString );
		$this->QueryEnd();
		return $res;
	}

	function exec($Query)
	{
		if (! $this->Connection) return null;
		$this->QueryStart();
		$this->Connection->query($Query);
		$this->QueryEnd();
		return $this->Connection->affected_rows;
	}

	function prepare($Query)
	{
		if (!$this->Connection) return null;
		return new DB_Statement_mysqli ( $this ,$Query);
	}
	
	function Initialize($DatabaseName)
	{
		#this is a bit faster than the base one, like 5%
		$this->DropAllTables($DatabaseName);
		$r=$this->Connection->multi_query($this->GetInitializationSQL());
		while ($this->Connection->more_results())
		{
			$this->Connection->next_result();
// 			$r=$this->Connection->store_result();
// 			if ($r) $r->free();
		}
	}
	function InitializeData($DatabaseName)
	{
		$this->TruncateAllTables($DatabaseName);
		$r=$this->Connection->multi_query($this->GetDataSQL());
		while ($this->Connection->more_results())
			$this->Connection->next_result();
	}
}


class DB_Statement_mysqli extends BaseDatabaseStatement
{
	/**
	 * Statement
	 *
	 * @var mysqli_stmt
	 */
	private $Statement;

	function __construct(DB_mysqli $DB, $Query)
	{
		$this->DB= $DB;
		$this->Statement=$DB->Connection->prepare ( $Query );
		if (mysqli_errno($DB->Connection)) throw new \Exception("MySQLi error: ".mysqli_error($DB->Connection));
	}

	function __destruct()
	{
		if ($this->Statement) $this->Statement->close ();
	}

	/**
	 * Binds a few variables to a prepared statement
	 *
	 */
	function bindAll()
	{
		if (!$this->DB) return;
		$args = func_get_args ();
		$types = str_repeat ( "s", count ( $args ) );
		array_unshift($args,$types);
		//TODO: optimize this on PHP 5.3
		$a=array();
		foreach ($args as $k=>&$v)
			$a[$k]=&$v;
		call_user_func_array ( array (
			$this->Statement, 'bind_param' 
		), $a );
	}

	/**
	 * Executes the prepared statement using binded values. if you provide this function with
	 * arguments, Then those would be binded as well.
	 *
	 */
	function execute()
	{
		if (!$this->DB) return;
		if (func_num_args () >= 1)
		{
			$args = func_get_args ();
			call_user_func_array ( array (
				$this, "bindAll" 
			), $args );
		}
		$this->DB->QueryStart ();
		$r=$this->Statement->execute ();
		$this->DB->QueryEnd ();
		//$this->Statement->store_result();
		return $r;

	}

	function rowCount()
	{
		if (!$this->DB) return;
			return $this->Statement->affected_rows;
	}

	function fetch()
	{
		if (!$this->DB) return;
		
		$data = $this->Statement->result_metadata ();
		$out = array ();
		$fields = array ();
		if (! $data) return null;
		while ( null != ($field = mysqli_fetch_field ( $data )) )
			$fields [] = &$out [$field->name];
		call_user_func_array ( array (
			$this->Statement, "bind_result" 
		), $fields );
		$this->Statement->fetch ();
		return (count ( $out ) == 0) ? null : $out;
	}

	function fetchAll()
	{
		if (!$this->DB) return;
		$data = $this->Statement->result_metadata ();
		$out = array ();
		$fields = array ();
		if (! $data) return null;
		$length=0;
		while ( null != ($field = mysqli_fetch_field ( $data )) )
		{
			$fields [] = &$out [$field->name];
			$length+=$field->length;
		}
		call_user_func_array ( array (
			$this->Statement, "bind_result" 
		), $fields );
		$output = array ();
		$count = 0;
		//FIXME: store_result is needed, but using it causes crash
		if ($length>=1000000) 
			if(!$this->Statement->store_result())
				throw new \Exception("Store_Result error on MySQLi prepared statement : ".$this->Statement->get_warnings());
		while( $this->Statement->fetch () )
		{
			foreach ( $out as $k => $v )
				$output [$count] [$k] = $v;
			$count ++;
		}
		$this->Statement->free_result();
		return ($count == 0) ? null : $output;
	}
}
?>