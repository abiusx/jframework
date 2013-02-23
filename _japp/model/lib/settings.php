<?php
/**
 * SettingManager class
 * Save and load settings on a scope of general, user or session.
 * @version 2.0
 * @author abiusx
 */
//Note: userID = 0 means general option
namespace jf;
class SettingManager extends Model
{
	static $DefaultTimeout=86400; //24*60*60;
	/**
	 * internal function use by other services
	 * @param string $Name
	 * @param mixed $Value
	 * @param int $UserID
	 * @param int $Timeout
	 * @return boolean success
	 */
	private function _Save($Name, $Value, $UserID = 0, $Timeout)
	{
		$Datetime = jf::time () + $Timeout;
		if ($this->PreparedSaveStatement===null)    
	        $this->PreparedSaveStatement=jf::db()->prepare( "REPLACE INTO {$this->TablePrefix()}options (Name,Value, UserID, Expiration) VALUES (?,?,?,?);");
	    $r=$this->PreparedSaveStatement->execute( $Name, serialize ( $Value ), $UserID, $Datetime );
		$this->_Sweep ();
		return $r>=1;
	}
	/**
	 * internal function use by other services
	 * @param string $Name
	 * @param int $UserID
	 * @return boolean success
	 */
	private function _Delete($Name, $UserID = 0)
	{
	    if ($this->PreparedDeleteStatement===null)    
	        $this->PreparedDeleteStatement=jf::db()->prepare( "DELETE FROM {$this->TablePrefix()}options  WHERE UserID=? AND Name=?");
	    $r=$this->PreparedDeleteStatement->execute($UserID, $Name);
	    return $r>=1;
	}
	/**
	 * Loads an option from the database
	 *
	 * @param String $Name
	 * @param Integer $UserID 0 for General Options
	 * @return String Value on success, null on failure
	 */
	function _Sweep($force=false)
	{
		
		if(!$force) if (rand ( 0, 1000 ) / 1000.0 > .1)
			return; //percentage of SweepRatio, don't always do this when called

	    if ($this->PreparedSweepStatement===null)    
	        $this->PreparedSweepStatement=jf::db()->prepare("DELETE FROM {$this->TablePrefix()}options WHERE Expiration<=?");
	    $this->PreparedSweepStatement->execute(jf::time());
			
	
	}
	/**
	 * This function loads a set of options together
	 * It expects to get at least 2 parameters
	 * @param Integer $UserID
	 * @return AssociativeArray of option Name/Value pairs as Key/Value in the array.
	 * 
	 */
	
	private function _Load($Name, $UserID=0)
	{
		$this->_Sweep ();
		if ($this->PreparedLoadStatement===null)
			$this->PreparedLoadStatement=jf::db()->prepare("SELECT * FROM {$this->TablePrefix()}options WHERE Name=? AND UserID=?");
		$this->PreparedLoadStatement->execute($Name, $UserID);
		$Res=$this->PreparedLoadStatement->fetchAll();
		if($Res===null)
			return null;
		else
			return unserialize($Res[0]['Value']);
	}
	function SaveGeneral($Name, $Value, $Timeout = null)
	{
		if ($Timeout===null ) $Timeout=self::$DefaultTimeout;
		$this->_Save ( $Name, $Value, 0, $Timeout );
	}
	function LoadGeneral($Name)
	{
		return $this->_Load ( $Name, 0 );
	}
	function LoadSetGeneral($Prefix)
	{
		return $this->LoadSet($Prefix,0);
	}
	/**
	 * save setting for user
	 * @param string $Name
	 * @param mixed $Value
	 * @param int $UserID
	 * @param int $Timeout
	 * @throws \Exception
	 * @return boolean success for saving data
	 */
	function SaveUser($Name, $Value,$UserID=null, $Timeout = null)
	{
		if ($UserID===null)
		{
			if (jf::CurrentUser() == null)
				throw new \Exception ( "Can not load user options without a logged in user." );
			else
				$UserID=jf::CurrentUser();
		}
		
		if ($Timeout===null ) $Timeout=TIMESTAMP_WEEK;
		
		return $this->_Save ( $Name, $Value, $UserID, $Timeout );
	}
	function Load($Name,$UserID=null)
	{
		if ($UserID===null)
		{
			if (jf::CurrentUser() == null)
				throw new \Exception ( "Can not load user options without a logged in user." );
			else
				$UserID=jf::CurrentUser();
		}
		return $this->_Load ( $Name, $UserID );
	
	}
	function LoadSet($Prefix)
	{
		if (jf::CurrentUser() == null)
			throw new \Exception ( "Can not load user options without a logged in user." );
		return $this->_LoadSet ( $Prefix, jf::CurrentUser() );
	}
	function Delete($Name,$UserID=null)
	{
		if ($UserID===null)
		{
			if (jf::CurrentUser()== null)
				throw new \Exception ( "Can not delete user options without a logged in user." );
			else
				$UserID=jf::CurrentUser();
		}
		return $this->_Delete ( $Name, $UserID );
	}
	function DeleteAll()
	{
		if (jf::CurrentUser() == null)
			throw new \Exception ( "Can not delete user options without a logged in user." );
		$this->Execute (  "DELETE FROM {$this->TablePrefix()}options WHERE UserID=?", jf::CurrentUser() );
	}
	function DeleteGeneral($Name)
	{
		$this->_Delete ( $Name, 0 );
	}
	function SaveSession($Name,$Value,$Timeout = null)
	{
		if ($Timeout===null) $Timeout=self::$DefaultTimeout;
	    $this->SaveGeneral(session_id()."_$Name",$Value,$Timeout);   
	}
	function LoadSession($Name)
	{
	     return $this->LoadGeneral(session_id()."_$Name");   
	}
	function LoadSetSession($Prefix)
	{
		return $this->LoadSetGeneral(session_id()."_{$Prefix}");
	}
	function DeleteSession($Name)
	{
        $this->DeleteGeneral(session_id()."_$Name");
	}
	private $PreparedLoadSetStatement=null;
	private $PreparedLoadStatement=null;
	private $PreparedSaveStatement=null;
	private $PreparedDeleteStatement=null;
	private $PreparedSweepStatement=null;
}
?>
