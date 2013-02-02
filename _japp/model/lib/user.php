<?php
namespace jf;
/**
 * Password generator class
 * this class creates a password object, which has a dynamic and a static salt and a hashed password from a username and raw password
 * has protocol versions to be backward compatible, i.e when a hashin mechanism is deemed insecure, the mechanism can change but
 * since we don't have access to textual passwords, new passwords can not be generated so older protocols still supported to perform login
 * and then update the password.
 * @author abiusx
 *
 */
class Password
{

	protected $DynamicSalt=null;
	/**
	 * Static salt concatenated to dynamic salt
	 * @var string
	 * @version 1.0
	 */
	protected static $StaticSalt="7d2cdb76dcc3c97fc55bff3dafb35724031f3e4c47512d4903b6d1fb914774405e74539ea70a49fbc4b52ededb1f5dfb7eebef3bcc89e9578e449ed93cfb2103";
	/**
	 * change this everytime you change the way password is generated to update
	 * @var integer
	 */
	protected static $Protocol=1;
	protected $Username;
	protected $Password;
	
	/**
	 * returns the static salt of password generator 
	 */
	function StaticSalt()
	{
		return self::$StaticSalt;
	}
	/**
	 * Generate a secure textual password
	 * @param float $Security between 0 and 1
	 */
	static function Generate($Security=.5)
	{
		$MaxLen=32;
		if ($Security>.1)
			$UseUpper=true;
		if ($Security>.3)
			$UseNumbers=true;
		if ($Security>.7)
			$UseSymbols=true;
		
		$Length=max($Security*$MaxLen,4);
		
		$chars='abcdefghijklmnopqrstuvwxyz';
		if ($UseUpper)
			$chars.="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if ($UseNumbers)
			$chars.="0123456789";
		if ($UseSymbols)
			$chars.="!@#$%^&*()_+-=?.,";
		
		$Pass="";
		for ($i=0;$i<$Length;++$i)
			$Pass.=$chars[mt_rand(0, strlen($chars)-1)];
		return $Pass;
	}
	
	
	/**
	 * Creates a hashed password
	 * @param string $Username
	 * @param string $RawPassword
	 * @param string $DynamicSalt
	 * @param integer $Protocol
	 */
	public function Make($Username,$RawPassword,$DynamicSalt=null,$Protocol=null)
	{
		if ($DynamicSalt===null) 
			$this->DynamicSalt=hash("sha512",rand());
		else
			$this->DynamicSalt=$DynamicSalt;
		if ($Protocol===null)
			$Protocol=$this->Protocol();
		$this->Username=$Username;
		if ($Protocol==1)
			$this->Password=hash("sha512",strtolower($this->Username()).$RawPassword.$this->Salt().$this->StaticSalt());
				
	}
	
	/**
	 * Validates if a hashed password is the correct hashed password for a given raw password, username and salt
	 * @param string $Username
	 * @param string $RawPassword the textual password (entered by user at login)
	 * @param string $HashedPassword the hashed password (retrived from database)
	 * @param string $Salt the dynamic salt (from database)
	 * @param integer $Protocol optional for backward compatibility
	 * @return boolean
	 */
	static function Validate($Username,$RawPassword,$HashedPassword,$Salt,$Protocol=null)
	{
		$temp=new Password($Username,$RawPassword,$Protocol);
		$temp->Make($Username, $RawPassword,$Salt,$Protocol);
		return ($temp->Password()==$HashedPassword);
	}
	/**
	 * Create a new password
	 * @param string $Username
	 * @param string $RawPassword the textual password
	 * @param int $Protocol protocol of password hashing. omit if wanna use most recent.
	 */
	function __construct($Username,$RawPassword,$Protocol=null)
	{
		$this->Make($Username,$RawPassword,$Protocol);
	}
	function Salt()
	{
		return $this->DynamicSalt;		
	}
	function Username()
	{
		return $this->Username;
	}
	/**
	 * Returns the hashed password
	 */
	function Password()
	{
		return $this->Password;
	}
	/**
	 * determines the hashing protocol
	 * @return string
	 */
	function Protocol()
	{
		return self::$Protocol;
	}
}

/**
 * Manages jframework base users. A base user only has a username and a password.
 * For advanced users having login features and password recovery, use ExtendedUserManager 
 * @author abiusx
 *
 */
class UserManager extends Model
{
	/**
    Removes a user form system users if exists
    @param Username of the user
    @return boolean
	 */
	function DeleteUser($Username)
	{
		return (jf::SQL ( "DELETE FROM {$this->TablePrefix()}users WHERE LOWER(Username)=LOWER(?)", $Username )>=1);
	}
	/**
	 * Tells whether or not a user is logged in
	 * @param Integer $UserID
	 * @return Boolean
	 */
	function IsLoggedIn($UserID)
	{
		$Result=j::SQL("SELECT COUNT(*) AS Result FROM {$this->TablePrefix()}session WHERE UserID=?",$UserID);
		if ($Result[0]['Result']>=1) return true;
		else return false;
	}
	
		/**
	 * Destroys the current session,Hence logging out the user. Then recreates the session.
	 * If UserID provided, destroys the session for that user 
	 * @param $UserID
	 */
	function Logout($UserID=null)
	{
		if ($UserID===null)
			if (jf::CurrentUser()===null)
				return false;
			else
				$UserID=jf::CurrentUser();
		j::SQL ( "UPDATE {$this->TablePrefix()}session SET UserID=0 WHERE UserID=? ", $UserID );
		j::$Session->RollSession();
		$res=j::$Session->Refresh();
	}
	
	
	
	/**
    Edits a user credentials
    @param String $OldUsername 
    @param String $NewUsername 
    @param String $NewPassword leave null to not change
    @return null on old user doesn't exist, false on new user already exists,  true on success.
	 */
	function EditUser($OldUsername, $NewUsername, $NewPassword = null)
	{
		if (! $this->UserExists ( $OldUsername )) return null;
		if ($OldUsername != $NewUsername and $this->UserExists ( $NewUsername )) return false;
		if ($NewPassword)
		{
			$HashedPass=new Password($NewUsername, $NewPassword);
			j::SQL ( "UPDATE {$this->TablePrefix()}users SET Username=?, Password=?, Salt=?, Protocol=? WHERE LOWER(Username)=LOWER(?)",
				 $NewUsername, $HashedPass->Password(),$HashedPass->Salt(),$HashedPass->Protocol(), $OldUsername);
		}
		else
		{
			j::SQL ( "UPDATE {$this->TablePrefix()}users SET Username=? WHERE LOWER(Username)=LOWER(?)", $NewUsername, $OldUsername );
		}
		return true;
	}
	/**
	Validates a user credentials
    @param Username of the user
    @param Password of the user
    @return boolean
	 */
	function ValidateUserCredentials($Username, $Password)
	{
		$Record=jf::SQL("SELECT * FROM {$this->TablePrefix()}users WHERE LOWER(Username)=LOWER(?)",$Username);
		if (!$Record) return false;
		$Record=$Record[0];
		return Password::Validate($Username, $Password, $Record['Password'], $Record['Salt'],$Record['Protocol']);
		
	}

	/**
	 * Logs a user in only by user ID without needing valid credentials. Intended for system use only.
	 * @param integer $UserID
	 * @return boolean false if user not found
	 */
	function ForceLogin($UserID)
	{
		if (! $this->IsLoggedIn($UserID))
		{
			$r=jf::SQL ( "UPDATE {$this->TablePrefix()}session SET UserID=?,SessionID=?,LoginDate=?,LastAccess=?,AccessCount=? WHERE SessionID=?", $UserID, jf::$Session->SessionID(), jf::time (), jf::time (), 1, jf::$Session->SessionID());
			return $r>0;
		}
		else
		{
			$r=jf::SQL( "UPDATE {$this->TablePrefix()}session SET UserID=?,SessionID=?,LoginDate=?,LastAccess=?,AccessCount=? WHERE UserID=?", $UserID, jf::$Session->SessionID(), jf::time (), jf::time (), 1, $UserID);
			return $r>0;
		}
	}
	/**
	Logs in a user if credentials are valid
    @param string $Username of the user
    @param string $Password textual password of the user
    @return boolean
	 */
	function Login($Username, $Password)
	{
		$Result = $this->ValidateUserCredentials ( $Username, $Password );
		if (!$Result) return false;
		$UserID=$this->UserID($Username);
		$res=$this->ForceLogin($UserID);
		if ($res) jf::$Session->SetCurrentUser($UserID);
		return $res;
	}

	/**
	Checks to see whether a user exists or not
    @param Username of the new user
    @return boolean
	 */
	function UserExists($Username)
	{
		$res=jf::SQL ( "SELECT * FROM {$this->TablePrefix()}users WHERE LOWER(Username)=LOWER(?)", $Username );
		return ($res!==null);
	}


	/**
	 * Checks wether a user ID is valid or not
	 * @param integer $UserID
	 * @return boolean
	 */
	function UserIDExists($UserID)
	{
		$res=jf::SQL ( "SELECT * FROM {$this->TablePrefix()}users WHERE ID=?", $UserID);
		return ($res!==null);
	}	
	/**
    Creates a new user in the system
    @param Username of the new user
    @param Password of the new user
    @return integer UserID on success
		null on User Already Exists
	 */
	function CreateUser($Username, $Password)
	{
		$Result = $this->UserExists ( $Username );
		if ($Result) return null;
		$HashedPass=new Password($Username, $Password);
		$Result = jf::SQL ( "INSERT INTO {$this->TablePrefix()}users (Username,Password,Salt,Protocol) 
			VALUES (?,?,?,?)", $Username, $HashedPass->Password(), $HashedPass->Salt(),$HashedPass->Protocol());
		return $Result;
	}

	
	/**
	 * returns Username of a user
	 *
	 * @param Integer $UserID
	 * @return String
	 */
	function Username($UserID=null)
	{
		if ($UserID===null)
			$UserID=jf::CurrentUser();
		$Result = jf::SQL ( "SELECT Username FROM {$this->TablePrefix()}users WHERE ID=?", $UserID );
		if ($Result)
			return $Result [0] ['Username'];
		else
			return null;
	}
	
	/**
	 * 
	 * @param string $Username
	 * @return integer UserID null on not exists
	 */
	function UserID($Username)
	{
		$res=jf::SQL("SELECT ID FROM {$this->TablePrefix()}users WHERE LOWER(Username)=LOWER(?)",$Username);
		if ($res)
			return $res[0]['ID'];
		else
			return null;
		
	}
	
	/**
	 * Returns total number of users
	 * @return integer
	 */
	function UserCount()
	{
		$res=jf::SQL("SELECT COUNT(*) FROM {$this->TablePrefix()}users");
		return $res[0]['COUNT(*)'];
	}
	
	
}