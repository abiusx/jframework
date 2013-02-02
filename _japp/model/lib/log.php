<?php
namespace jf;
class LogManager extends Model  
{
	static function Log($Subject,$Content,$Severity=0)
	{
		if (jf::$App)
		return jf::SQL("INSERT INTO {jf::$App->TablePrefix()}logs (Subject,Data,Severity,UserID,SessionID,Timestamp) 
		"."VALUES (?,?,?,?,?,?)",$Subject,$Content,$Severity,jf::UserID(),session_id(),jf::time());
	}
	
}

?>