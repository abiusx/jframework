<?php
class SystemUsersModel extends BaseApplicationClass 
{
    function AllSessions()
    {
        return $this->DB->Execute("SELECT S.*,U.`".jf_Users_Table_Username."` FROM `".reg("jf/session/table/name")."` AS S LEFT JOIN `".jf_Users_Table_Name."` AS U ON (
        	S.`".reg("jf/session/table/UserID")."`=U.`".jf_Users_Table_UserID."`
        	)
        ");
    }
    function AllUsers($offset=null,$limit=null)
    {
        if ($offset or $limit)
            return $this->DB->Execute("SELECT * FROM `".jf_Users_Table_Name."` LIMIT ?,?",$offset,$limit);
        else
            return $this->DB->Execute("SELECT * FROM `".jf_Users_Table_Name."` ");
    }
    function User($UserID)
    {
         return $this->DB->Execute("SELECT `".jf_Users_Table_UserID."` AS ID, `".jf_Users_Table_Username."` AS Username
         	FROM `".jf_Users_Table_Name."` WHERE ID=?",$UserID);   
    }
    function UserCount()
    {
        $Result=$this->DB->Execute("SELECT COUNT(*) FROM `".jf_Users_Table_Name."` ");
        return $Result[0]["COUNT(*)"];
    }
}
?>