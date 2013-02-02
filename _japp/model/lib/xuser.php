<?php
namespace jf;
class ExtendedUserErrors
{
	const Inactive=0;
	const NotFound=1;
	const Locked=2;
	const InvalidCredentials=3;
}
/**
 * This is an extended user manager with support for activation, locking, reset password and etc.
 * @author abiusx
 * @version 2.0
 */
class ExtendedUserManager extends UserManager
{
	public static $LockCount=10;
	public static $LockTime=3600;
	
	
	/**
	 * 
	 * @var ExtendedUserErrors
	 */
	public $LastError;
	/**
	 * Return the last error converted to string
	 * use the variable form if you need the constant.
	 * consumes the last error
	 * @return string
	 */
	function LastError()
	{
		$res= ($this->LastError)."";
		$this->LastError=null;
		return $res;
	}
	
	/**
	 * Extends a normal user to extended user
	 * @param integer $UserID
	 * @param string $Email valid email address
	 * @return boolean true on success, false on already extended, null if not found
	 */
	function Extend($UserID,$Email)
	{
		if (jf::$User->UserIDExists($UserID)) return null;
		if ($this->UserIDExists($UserID)) return false;
		jf::SQL("INSERT INTO {$this->TablePrefix()}xuser (ID,Email,CreateTimestamp) VALUES (?,?,?)",$UserID,$Email,jf::time());
		
	}
	/**
	 * Attempt to login an extended user
	 * if user is only normal, it will return notfound error
	 * @param $Username
	 * @param $Password
	 * @return boolean
	 */
	function Login($Username,$Password)
	{
		if (!$this->UserExists($Username)) 
		{
			$this->LastError=ExtendedUserErrors::NotFound;
			return false;
		}
		
		$UserID=$this->UserID($Username);
		$Info=$this->UserInfo($UserID);
		if ($Info['Activated']==0)
		{
			$this->LastError=ExtendedUserErrors::Inactive;
			return false;
		}
		if ($Info['LockTimeout']>time())
		{
			$this->LastError=ExtendedUserErrors::Locked;
// 			$Error='Account Locked for '.date("H:i:s",$Info['LockTimeout']-time());
			return false;
		}
		
		$R=jf::$User->Login($Username,$Password);
		if (!$R)
		{
			$this->IncreaseFailedLoginAttempts($UserID);
			if ($Info['FailedLoginAttempts']+1>=self::$LockCount)
			{
				$this->LastError=ExtendedUserErrors::Locked;
				$this->Lock($UserID);
				return false;
			}
			$this->LastError=ExtendedUserErrors::InvalidCredentials;
			return false;
		}
		else
		{
			jf::SQL("UPDATE {$this->TablePrefix()}xuser SET FailedLoginAttempts=0 , LockTimeout=? , LastLoginTimestamp=? , TemporaryResetPasswordTimeout=? WHERE ID=? LIMIT 1",jf::time(),jf::time(),jf::time(),$UserID);
			return true;
		}
	}
	
	/**
	 * Logs in a user without providing password
	 * @param integer $UserID
	 * @return Boolean
	 */
	function ForceLogin($UserID)
	{
		$res=jf::$User->ForceLogin($UserID);
		if ($res)
		{
			jf::SQL("UPDATE {$this->TablePrefix()}xuser SET FailedLoginAttempts=0 , LockTimeout=? , LastLoginTimestamp=? , TemporaryResetPasswordTimeout=? WHERE ID=? LIMIT 1",jf::time(),jf::time(),jf::time(),$UserID);
			return true;
		}
		else
			return false;
	}
	
	/**
	 * Locks an extended user
	 * @param $UserID
	 */
	function Lock($UserID)
	{
		jf::SQL("UPDATE {$this->TablePrefix()}xuser SET LockTimeout=? , FailedLoginAttempts=0 WHERE ID=? LIMIT 1",jf::time()+self::$LockTime,$UserID);
	}
	/**
	 * Unlocks an extended user
	 * @param $UserID
	 */
	function Unlock($UserID)
	{
		jf::SQL("UPDATE {$this->TablePrefix()}xuser SET LockTimeout=? , FailedLoginAttempts=0 WHERE ID=? LIMIT 1",jf::time(),$UserID);
	}
	/**
	 * Increases user failed login attempts
	 * @param integer $UserID
	 */
	protected function IncreaseFailedLoginAttempts($UserID)
	{
		jf::SQL("UPDATE {$this->TablePrefix()}xuser SET FailedLoginAttempts=FailedLoginAttempts+1 WHERE ID=? LIMIT 1",$UserID);
	}	
	/**
	 * Tells whether a user is locked or not
	 * @param $UserID
	 * return Boolean
	 */
	function IsLocked($UserID)
	{
		$R=jf::SQL("SELECT LockTimeout FROM {$this->TablePrefix()}xuser WHERE ID=?",$UserID);
		if (!$R) return false;
		return $R[0]['LockTimeout']>jf::time();
	}
	
	/**
	 * Creates an extended user and returns the user id
	 * @param $Username
	 * @param $Password
	 * @param $Email
	 * @return UserID
	 * 
	 */
	function CreateUser($Username,$Password)
	{
		$Email=func_get_args()[2];
		if ($Email===null)
			throw new Exception("You have to provide valid email address.");
		$IID=jf::$User->CreateUser($Username,$Password);
		if ($IID===null) return null;
		jf::SQL("INSERT INTO {$this->TablePrefix()}xuser (ID,Email,CreateTimestamp) VALUES (?,?,?)",$IID,$Email,jf::time());
		return $IID;
		
	}
	/**
	 * Removes an extended user
	 * @param $UserIDorUsername
	 * @return boolean
	 */
	function DeleteUser($Username)
	{
		$UserID=$this->UserID($Username);
		if (!$UserID) return false;
		if (jf::$User->DeleteUser($Username))
		{
			$res=jf::SQL("DELETE FROM {$this->TablePrefix()}xuser WHERE ID=?",$UserID);
			return $res>=1;
		}
		return false;
	}	
	/**
	 * Check whether a user exists or not in extended users
	 * @param $Username
	 * @return Boolean
	 */
	function UserExists($Username)
	{
		$UserID=$this->UserID($Username);
		return $this->UserIDExists($UserID);
	}
	
	/**
	 * Checks whether a user ID exists or not in extended users
	 * @see \jf\UserManager::UserIDExists()
	 */
	function UserIDExists($UserID)
	{
		$R=jf::SQL("SELECT XU.ID FROM {$this->TablePrefix()}xuser AS XU JOIN {$this->TablePrefix()}users AS U ON (U.ID=XU.ID) WHERE XU.ID=?",$UserID);
		if ($R) return true;
		else
			return false;
	}
	/**
	 * Returns an array of info with all fields of the extended user and the jFramework user
	 * @param integer $UserID
	 * @return Array|null
	 */
	function UserInfo($UserID)
	{
		$R=jf::SQL("SELECT * FROM {$this->TablePrefix()}xuser AS XU JOIN {$this->TablePrefix()}users AS U ON (U.ID=XU.ID) WHERE XU.ID=?",$UserID);
		if ($R) return $R[0];
		else return null;
	}
	/**
	 * returns the number of all extended users
	 * @return integer
	 */
	function UserCount()
	{
		$R=jf::SQL("SELECT COUNT(*) AS Result FROM {$this->TablePrefix()}xuser AS XU");
		return $R[0]['Result'];
	}
	
	/**
	 * Activate an extendeduser 
	 * @param integer $UserID
	 * @return boolean true on success, false on already active
	 */	
	function Activate($UserID)
	{
		$res=jf::SQL("UPDATE {$this->TablePrefix()}xuser SET Activated=1 WHERE ID=? LIMIT 1",$UserID);
		return $res==1;
	}
	/**
	 * Deactivate an extended user
	 * @param integer $UserID
	 * @return boolean true on success, false on already inactive
	 */
	function Deactivate($UserID)
	{
		$res=jf::SQL("UPDATE {$this->TablePrefix()}xuser SET Activated=0 WHERE ID=? LIMIT 1",$UserID);
		return $res==1;
	}
	
}

