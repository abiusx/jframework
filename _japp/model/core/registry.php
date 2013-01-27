<?php
/**
 * The Registry Class
 * contains global information of the application and the framework
 */
//Note: userID = 0 means general option
define ( "jRegistry_Delimiter", "/" );


class jRegistry
{
//	public $EnableMeta = false;

	function __construct()
	{
	
	}

	function Get($Path)
	{
		$a = explode ( constant ( "jRegistry_Delimiter" ), $Path );
		$t = &$this;
		foreach ( $a as $p )
		{
			$t = &$t->{$p};
		}
		return $t;
	}

	/**
	 * Sets the value in registry
	 * @param $Path
	 * @param $Value
	 * @param $Readonly optional
	 * @return True on success, False on failure
	 */
	function Set($Path, $Value, $Readonly = false)
	{
		$a = explode ( constant ( "jRegistry_Delimiter" ), $Path );
		$t = &$this;
		foreach ( $a as $p )
			$t = &$t->{$p};
		$t = $Value;
		return true;
	}

}
jf::$Registry = new jRegistry ( );

?>
