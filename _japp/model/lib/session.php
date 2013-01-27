<?php
namespace jf;
/*
 * jframework Session Manager
 * @author abiusx
 * @version 2
	Guest users have their UserID set to null;

 */
#FIXME: use updating and etc. using SessionID since a user can be logged in multiple times
# also add option to only allow one login of the user
/**
This class Handles user session and user management (credentials).
 */
class SessionManager extends Model
{
	private static $SessionName="JFSESSIONID";
	

	function __construct()
	{
		$this->IP = (getenv ( "HTTP_X_FORWARDED_FOR" )) ? getenv ( "HTTP_X_FORWARDED_FOR" ) : getenv ( "REMOTE_ADDR" );
		
		session_name ( self::$SessionName );
		if (!session_start ())
			throw new Exception("Unable to session_start().");
		$this->Refresh();

		session_write_close (); //This would release the session_start lock and prevent request sequentialization
	}
	public $IP;
	/**
	If a user is logged in, This variable holds his UserID, else it would be null.
	 */
	private $UserID;

	/**
	Checks the session and sets the sessiontate variable accordingly
    @return 
		true on New Session,
		false on Existing Session
	 */
	function Refresh()
	{
		$SessionID = session_id ();
		$Result = jf::SQL ( "SELECT * FROM {$this->TablePrefix()}session WHERE SessionID=?", $SessionID );
		if (! $Result)
		{
			$this->CreateSession();
			return true;
		}
		if (count ( $Result ) == 1)
		{
			$Result = $Result [0];
			$this->UserID = $Result ['UserID'];
			if ($this->UserID == 0) $this->UserID = null;
			$LoginDate = $Result ['LoginDate'];
			$LastAccess = $Result ['LastAccess'];
			$LoginTimestamp = $LoginDate; //strtotime($LoginDate);
			$LastAccessTimestamp = $LastAccess; //strtotime($LastAccess);
			$NowTimestamp = time ();
			$Dis = $NowTimestamp - $LastAccessTimestamp;
			$LoginTime = $NowTimestamp - $LoginTimestamp;
			if ($Dis > self::$NoAccessTimeout or $LoginTime > self::$ForcedTimeout)
			{
				$this->ExpireSession();
			}
			jf::SQL ( "UPDATE {$this->TablePrefix()}session SET LastAccess=? ,AccessCount=AccessCount+1 , CurrentRequest=? WHERE SessionID=?", time (), jf::$Request,session_id () );
			return 1;
		}
		$this->_Sweep ();
	}

	public static $SweepRatio=.1;
	public static $NoAccessTimeout=1800;
	public static $ForcedTimeout=604800; //a day
	/**
	Removes outdated session info from session table
	 */
	private function _Sweep()
	{
		//Removes timed out session
		if (rand ( 0, 1000 ) / 1000.0 > self::$SweepRatio) return; //10%
		$Now = time ();
		jf::SQL ( "DELETE FROM {$this->TablePrefix()}session WHERE LastAccess<? OR LoginDate<?", $Now- self::$NoAccessTimeout, $Now- self::$ForcedTimeout);
	}



	/**
    Creates a new session (registers) for current visitor.
	 */
	function CreateSession()
	{
		jf::SQL ( "INSERT INTO {$this->TablePrefix()}session (UserID,SessionID,LoginDate,LastAccess,IP) VALUES (?,?,?,?,?)", 0, session_id (), time (), time (), $this->IP );
	}

	/**
	 * 
	 * Called when a session expires
	 * Destroys the session, and recreates it
	 */
	function ExpireSession()
	{
		$this->DestroySession();
		$this->CreateSession();
		
	}
	
	/**
	 * 
	 * changes sessionID both in cookie and database
	 */
	function RollSession()
	{
		$oldSession=$this->SessionID();
		session_regenerate_id();
		$newSession=$this->SessionID();
		
		$r=jf::SQL("UPDATE {$this->TablePrefix()}session SET SessionID=? WHERE SessionID=?",$newSession,$oldSession);
		if ($r>=1) return true;
		else return false;
	}
	/**
	 * Destroys current session
	 */
	function DestroySession()
	{
		jf::SQL("DELETE FROM {$this->TablePrefix()}session WHERE SessionID=?",$this->SessionID());			
		if (isset ( $_COOKIE [session_name ()] ))
		{
			setcookie ( session_name (), '', time () - 42000, '/' );
		}
		$this->UserID = null;
		session_regenerate_id ( true );
	}

	function SessionID($set=null)
	{
		if ($set)
			session_id($set);
		return session_id();
	}
	/**
	 * Returns the number of online visitors based on established session.
	 *
	 * @return Integer Number of online visitors
	 */
	function OnlineVisitors()
	{
		$Result = jf::SQL( "SELECT COUNT(*) AS Result FROM {$this->TablePrefix()}session" );
		return $Result [0] ["Result"];
	}

	/**
	 * returns current UserID, null on not user logged in
	 * @return Integer or null
	 */
	function UserID()
	{
		return $this->UserID;
	}
}

?>