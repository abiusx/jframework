<?php
/**
 * 
 * @author abiusx
 * @version 1.2.5
 */
class XuserPlugin extends JPlugin
{
	public $LockCount=8;
	public $LockInterval=3600;
	/**
	 * Tries to log in a user
	 * checks for Activation, Locking and etc.
	 * @param $Username
	 * @param $Password
	 * @param output $Error
	 * @return Boolean
	 */
	function Login($Username,$Password,&$Error=null)
	{
		if (!$this->User_Exists($Username)) return null;
		
		$Info=$this->User_Info($Username);
		$UserID=$Info['ID'];
		if ($Info['Activated']==0)
		{
			$Error="Inactive Account";
			return false;
		}
		if ($Info['LockTimeout']>time())
		{
			$Error='Account Locked for '.date("H:i:s",$Info['LockTimeout']-time());
			return false;
		}
		
		$R=j::$Session->Login($Username,$Password);
		if (!$R)
		{
			$this->IncreaseFailedLoginAttempts($UserID);
			if ($Info['FailedLoginAttempts']+1>=$this->LockCount)
			{
				$Error="Account Locked";
				$this->Lock($UserID);
				return false;
			}
			$Error="Invalid Credentials";
			
			return false;
		}
		if ($R)
		{
			j::SQL("UPDATE jfp_xuser SET FailedLoginAttempts=0 , LockTimeout=? , LastLoginTimestamp=? , TemporaryResetPasswordTimeout=? WHERE ID=? LIMIT 1",time(),time(),time(),$UserID);
			return true;
		}
	}
	
	/**
	 * Logs in a user without providing username
	 * @param $UserIDorUsername
	 * @return Boolean
	 */
	function ForceLogin($UserIDorUsername)
	{
		$UserID=$this->GetUserID($UserIDorUsername);
		j::$Session->Login($this->Username($UserID),"",true);
		j::SQL("UPDATE jfp_xuser SET FailedLoginAttempts=0 , LockTimeout=? , LastLoginTimestamp=? , TemporaryResetPasswordTimeout=? WHERE ID=? LIMIT 1",time(),time(),time(),$UserID);
		return true;
	}
	
	
	function Logout($UserIDorUsername)
	{
		$UserID=$this->GetUserID($UserIDorUsername);
		j::$Session->Logout($UserID);
	}
	/**
	 * Tells whether or not a user is logged in
	 * @param $UserIDorUsername
	 * @return Boolean
	 */
	function IsLoggedIn($UserIDorUsername)
	{
		$UserID=$this->GetUserID($UserIDorUsername);
		return j::$Session->IsLoggedIn($UserID);
	}
	/**
	 * Locks an extended user
	 * @param $UserID
	 */
	function Lock($UserID)
	{
		j::SQL("UPDATE jfp_xuser SET LockTimeout=? , FailedLoginAttempts=0 WHERE ID=? LIMIT 1",time()+$this->LockInterval,$UserID);
	}
	/**
	 * Unlocks an extended user
	 * @param $UserID
	 */
	function Unlock($UserID)
	{
		j::SQL("UPDATE jfp_xuser SET LockTimeout=? , FailedLoginAttempts=0 WHERE ID=? LIMIT 1",time(),$UserID);
	}
	/**
	 * Increases user failed login attempts
	 * @param unknown_type $UserID
	 */
	private function IncreaseFailedLoginAttempts($UserID)
	{
		j::SQL("UPDATE jfp_xuser SET FailedLoginAttempts=FailedLoginAttempts+1 WHERE ID=? LIMIT 1",$UserID);
	}	
	/**
	 * Tells whether a user is locked or not
	 * @param $UserID
	 * return Boolean
	 */
	function IsLocked($UserID)
	{
		$R=j::SQL("SELECT LockTimeout FROM jfp_xuser WHERE ID=?",$UserID);
		if (!$R) return false;
		return $R[0]['LockTimeout']>time();
	}
	
	
	
	
	/**
	 * Creates an extended user and returns the user id
	 * @param $Username
	 * @param $Password
	 * @param $Email
	 * @return UserID
	 * 
	 */
	function User_Create($Username,$Password,$Email)
	{
		$IID=j::$Session->CreateUser($Username,$Password);
		if ($IID===null) return null;
		j::SQL("INSERT INTO jfp_xuser (ID,Email,CreateTimestamp) VALUES (?,?,?)",$IID,$Email,time());
		return $IID;
		
	}
	/**
	 * Removes an extended user
	 * @param $UserIDorUsername
	 */
	function User_Remove($UserIDorUsername)
	{
		$UserID=$this->GetUserID($UserIDorUsername);
		j::SQL("DELETE FROM jfp_xuser WHERE ID=?",$UserID);
		j::SQL("DELETE FROM jf_users WHERE ID=?",$UserID);
	}	
	/**
	 * Check whether a user exists or not
	 * @param $UserIDorUsername
	 * @return Boolean
	 */
	function User_Exists($UserIDorUsername)
	{
		$UserID=$this->GetUserID($UserIDorUsername);
		$R=j::SQL("SELECT XU.ID FROM jfp_xuser AS XU JOIN jf_users AS U ON (U.ID=XU.ID) WHERE XU.ID=?",$UserID);
		if ($R) return true;
		else 
		{
			if (j::$Session->UserExists($UserIDorUsername)) return 2;
			return false;
		}
	}
	/**
	 * Check whether a x-user exists or not
	 * @param $UserIDorUsername
	 * @return Boolean
	 */
	function User_x_Exists($UserIDorUsername)
	{
		$UserID=$this->GetUserID($UserIDorUsername);
		$R=j::SQL("SELECT jfp_xuser.ID FROM jfp_xuser AS XU JOIN jf_users ON (jf_users.ID=jfp_xuser.ID) WHERE XU.ID=?",$UserID);
		if ($R) return true;
		else return false;
	}
	/**
	 * Returns the user id for a specified username
	 * @param $Username
	 * @return UserID
	 */
	function User_ID($Username)
	{
		$R=j::SQL("SELECT XU.ID FROM jfp_xuser AS XU JOIN jf_users AS U ON (U.ID=XU.ID) WHERE Username=?",$Username);
		if ($R) return $R[0]['ID'];
		else return null;
		
	}
	
	/**
	 * Returns the username for a specified userid
	 * @param $UserID
	 * @return Username
	 */
	function Username($UserID)
	{
		$R=j::SQL("SELECT Username FROM jfp_xuser AS XU JOIN jf_users AS U ON (U.ID=XU.ID) WHERE XU.ID=?",$UserID);
		if ($R) return $R[0]['Username'];
		else return null;
		
	}
	
	
	/**
	 * Returns an array of info with all fields of the extended user and the jFramework user
	 * @param $UserIDorUsername
	 * @return Array
	 */
	function User_Info($UserIDorUsername)
	{
		$UserID=$this->GetUserID($UserIDorUsername);
		$R=j::SQL("SELECT * FROM jfp_xuser AS XU JOIN jf_users AS U ON (U.ID=XU.ID) WHERE XU.ID=?",$UserID);
		if ($R) return $R[0];
		else return null;
	}
	/**
	 * returns the number of all extended users
	 * @return XUserCount
	 */
	function User_Count()
	{
		$R=j::SQL("SELECT COUNT(*) AS Result FROM jfp_xuser AS XU");
		if ($R)
			return $R[0]['Result'];
		else 
			return 0;
	}
	/**
	 * Deletes all extended users
	 * @param Boolean $Ensure
	 */
	function User_DeleteAll($Ensure=false)
	{
		if (!$Ensure)
			trigger_error("Ensure you want to delete all users!");
		else
			j::SQL("DELETE FROM jfp_xuser");	
	}
	/**
	 * turns a UserID or Username into a UserID
	 * @param $UserIDorUsername
	 * @return UserID
	 */
	private function GetUserID($UserIDorUsername)
	{
		if (!is_numeric($UserIDorUsername))
			$UserID=$this->User_ID($UserIDorUsername);
		else 
			$UserID=$UserIDorUsername;
		return $UserID;
	}
	
	
	
	function Activate($UserID)
	{
		j::SQL("UPDATE jfp_xuser SET Activated=1 WHERE ID=? LIMIT 1",$UserID);
	}
	function Deactivate($UserID)
	{
		j::SQL("UPDATE jfp_xuser SET Activated=0 WHERE ID=? LIMIT 1",$UserID);
		
	}
	
}

