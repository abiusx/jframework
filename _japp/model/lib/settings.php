<?php
/**
 * ApplicationOptions class
 * @version 1.5
 *
 * Saves and Restores options for session,user and application on database.
 *
 */
#TODO: port to jf4
//Note: userID = 0 means general option
namespace jf;
class SettingManager extends Model
{
	static $DefaultTimeout=86400; //24*60*60;
	private $PreparedLoadSetStatement=null;
	private $PreparedLoadStatement=null;
	private $PreparedSaveStatement=null;
	private $PreparedDeleteStatement=null;
	private $PreparedSweepStatement=null;
	private function _Save($Name, $Value, $UserID = 0, $Timeout)
	{
		$Datetime = jf::time () + $Timeout;
		if ($this->PreparedSaveStatement===null)    
	        $this->PreparedSaveStatement=jf::db()->prepare( "REPLACE INTO {$this->TablePrefix()}options (Name,Value, UserID, Expiration) VALUES (?,?,?,?);");
	    $this->PreparedSaveStatement->execute( $Name, serialize ( $Value ), $UserID, $Datetime );
		$this->_Sweep ();
	}
	private function _Delete($Name, $UserID = 0)
	{
	    if ($this->PreparedDeleteStatement===null)    
	        $this->PreparedDeleteStatement=jf::db()->prepare( "DELETE FROM {$this->TablePrefix()}options  WHERE UserID=? AND Name=?");
	    $this->PreparedDeleteStatement->execute($UserID, $Name);
	}
	/**
	 * Loads an option from the database
	 *
	 * @param String $Name
	 * @param Integer $UserID 0 for General Options
	 * @return String Value on success, null on failure
	 */
	private function _Load($Name, $UserID = 0)
	{
	    $this->_Sweep ();
	    if ($this->PreparedLoadStatement===null)    
	        $this->PreparedLoadStatement=jf::db()->prepare("SELECT Value FROM {$this->TablePrefix()}options WHERE UserID=? AND Name=?");
	    $this->PreparedLoadStatement->execute($UserID, $Name);
	    $Res=$this->PreparedLoadStatement->fetchAll();
	    if ($Res )
		{
		    return unserialize ( $Res [0] ['Value'] );
		} 
		else
			return null;
	}
	private function _LoadSet($Prefix,$UserID=0)
	{
	    $this->_Sweep ();
	    if ($this->PreparedLoadSetStatement===null)    
	        $this->PreparedLoadSetStatement=jf::db()->prepare("SELECT * FROM {$this->TablePrefix()}options WHERE UserID=? AND Name LIKE ?");
	    $this->PreparedLoadStatement->execute($UserID, $Prefix);
	    $Res=$this->PreparedLoadStatement->fetchAll();
		return $Res;
	}
	private function _Sweep()
	{
		
		if (rand ( 0, 1000 ) / 1000.0 > .1)
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
	
	function _Loads($UserID)
	{
	    if (func_num_args()<2) return false;
	    $Params=func_get_args();
	    array_shift($Params); //rid UserID
	    $flag=true;
	    foreach ($Params as $k=>$v)
	    {
	        if ($flag)
	            $flag=false;
	        else 
	            $Q.=" OR ";
	        $Q.="Name=?";
	    }
	    
		$Res = 
		call_user_func_array(array($this->DB,"Execute"),
		 array_merge(array("SELECT Name,Value FROM {$this->TablePrefix}options WHERE UserID=? ".
			" AND ($Q)"), array($UserID),$Params));
		if (count ( $Res ))
		{
			foreach ($Res as $k=>$v)
			    foreach ($v as $k2=>$v2)
			    {
			        if ($k2=="Value") 
			        {
			            $Res[$k][$k2]=unserialize($v2);
			            $Out[$Res[$k]["Name"]]=$Res[$k][$k2];
			        }
			    }
		    return $Out;
		} 
		else
			return null;
	    
	    
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
	function Save($Name, $Value,$UserID=null, $Timeout = null)
	{
		if ($UserID===null)
		{
			if ($this->App->Session->UserID == null)
				$this->App->FatalError ( "Can not load user options without a logged in user." );
			else
				$UserID=$this->App->Session->UserID;
		}
		
		if ($Timeout===null ) $Timeout=TIMESTAMP_WEEK;
		
		$this->_Save ( $Name, $Value, $UserID, $Timeout );
	}
	function Load($Name,$UserID=null)
	{
		if ($UserID===null)
		{
			if ($this->App->Session->UserID == null)
				$this->App->FatalError ( "Can not load user options without a logged in user." );
			else
				$UserID=$this->App->Session->UserID;
		}
		return $this->_Load ( $Name, $UserID );
	
	}
	function LoadSet($Prefix)
	{
		if ($this->App->Session->UserID == null)
			$this->App->FatalError ( "Can not load user options without a logged in user." );
		return $this->_LoadSet ( $Prefix, $this->App->Session->UserID );
	}
	function Delete($Name,$UserID=null)
	{
		if ($UserID===null)
		{
			if ($this->App->Session->UserID == null)
				$this->App->FatalError ( "Can not delete user options without a logged in user." );
			else
				$UserID=$this->App->Session->UserID;
		}
		$this->_Delete ( $Name, $UserID );
	}
	function DeleteAll()
	{
		if ($this->App->Session->UserID == null)
			$this->App->FatalError ( "Can not delete user options without a logged in user." );
		$this->Execute (  "DELETE FROM {$this->TablePrefix}options WHERE UserID=?", $this->App->Session->UserID );
	}
	function DeleteGeneral($Name)
	{
		$this->_Delete ( $Name, 0 );
	}
	function SaveSession($Name,$Value,$Timeout = null)
	{
		if ($Timeout===null) $Timeout=reg("jf/session/timeout/General");
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
}
?>
