<?php
/*
 * 
 Role Based Access Control
	Implemented on NIST RBAC Standard Model
 		level 2. Hierarchical RBAC (Restricted Hierarchical RBAC : only trees of roles)
	by AbiusX[at]Gmail[dot]com

	Restricted Hierarchies for both Permissions and Roles.
	Multiple roles for each user
*/


/**
 * Role Based Access Control class.
 * defines, manages and implements user/role/permission relations, and role based access control. 
 * NIST RBAC Standard Model 2 (Hierarchical RBAC, trees of roles)
 * @verson 0.60
 */
namespace jf;
class RBACManager extends Model 
		implements RBAC_Management ,RBAC_PermissionManagement,RBAC_RoleManagement,RBAC_UserManagement   
{
    /**
     * Permissions Nested Set
     *
     * @var FullNestedSet
     */
    public $Permissions;
    /**
     * Roles Nested Set
     *
     * @var FullNestedSet
     */
    protected $Roles=null;

    protected $TablePrefix;
    function __construct()
    {

    	$this->Permissions=new FullNestedSet($this->TablePrefix()."rbac_permissions","ID",
            "Left","Right");
        $this->Roles=new FullNestedSet($this->TablePrefix()."rbac_roles","ID",
            "Left","Right");
    }
    
	###############################
	####### Roles Interface #######
	###############################
    /**
     * Determines if the input role is a role ID or a role title
     *
     * @param Role $Role ID or Title
     * @return String Fieldname in database that's appropriate
     */
    private function RoleField($Role)
	{
	    if (is_numeric($Role))
	        return "ID";
	    else 
	        return "Title";
	}
	/**
	 * This returns the condition used to determine role for use in nested set 
	 * 
	 *
	 * @param String $Role RoleID or  RoleTitle
	 */
	private function RoleCondition($Role)
	{
	    return $this->RoleField($Role)."=?";
	}
	/**
    Role Title to Role ID
    Returns ID of a RBAC Role from its Title
    @param String $RoleTitle Title of the RBAC Role
    @return ID String, Null on failure
    @see Role_Info()
    */
	function Role_ID($RoleTitle)
	{
        return $this->Roles->GetID("Title"."=?",$RoleTitle);
	}
	
	/**
    Role Information
    Returns ID, ParentID, Title and Description of a RBAC Role from its ID
    @param Role $Role ID or Title of the role
    @return Array 
    @see Role_ID()
    */
	function Role_Info($Role)
	{
		return $this->Roles->GetRecord($this->RoleCondition($Role),$Role);
	}
	
	/**
    Adds a new Role
    Returns new role's ID
    @param String $RoleTitle Title of the new role
    @param String $RoleDescription Description of the new role
    @param Role $RoleParent ID or title of the parent node in the roles hierarchy
    @return ID of the new role
    */
	function Role_Add($RoleTitle,$RoleDescription,$RoleParent=0)
	{
		return $this->Roles->InsertChildData(
    		array("Title"=>$RoleTitle
    		,"Description"=>$RoleDescription)
    		,$this->RoleCondition($RoleParent),$RoleParent
		);
	}
	/**
    Removes a role and all its assignments
	@param String $Role ID or Title of a role
	@param Boolean $Recursive to remove children as well or not
    @see Role_ID()
    */
	function Role_Remove($Role,$Recursive=false)
	{
	    $this->UnassignRolePermissions($Role);
	    $this->UnassignRoleUsers($Role);
        if (!$Recursive)
         $this->Roles->DeleteConditional($this->RoleCondition($Role),$Role);
        else
            $this->Roles->DeleteSubtreeConditional($this->RoleCondition($Role),$Role);
	}
	
	/**
	 * Edits a role title and permission (not position)
	 *
	 * @param String $Role ID or Title of node
	 * @param String $RoleTitle new title
	 * @param String $RoleDescription new description
	 */
	function Role_Edit($Role,$RoleTitle=null,$RoleDescription=null)
	{
        return $this->Roles->EditData(array("Title"=>$RoleTitle
    		,"Description"=>$RoleDescription)
    		,$this->RoleCondition($Role),$Role);
	}
	/**
	 * Returns all direct children of a role
	 *
	 * @param String $Role ID or Title of role
	 * @return Array Children
	 */
	function Role_Children($Role=0)
	{
	    return $this->Roles->ChildrenConditional($this->RoleCondition($Role),$Role);
	}
	/**
	 * Returns all level descendants of a role
	 *
	 * @param String $Role ID or Title of role
	 * @return Array of Descendants (with Depth field)
	 */
	function Role_Descendants($Role=0)
	{
	    return $this->Roles->DescendantsConditional(false,$this->RoleCondition($Role),$Role);
	}

	/**
	 * Returns all roles in a Depth sorted manner
	 * includes the Depth field in each role
	 *
	 * @return Array Roles
	 */
	function Role_All()
	{
	    return $this->Roles->FullTree();
	}
	
	function Role_Reset($Ensure=false)
	{
		if ($Ensure!==true) 
		{
			trigger_error("Make sure you want to reset all roles first!");
			return;
		}
        j::SQL("DELETE FROM {$this->TablePrefix()}rbac_roles");	    
        $this->Role_Add("root","root");
        j::SQL("UPDATE {$this->TablePrefix()}rbac_roles SET ID = '0'");
        $Adapter=DatabaseManager::Configuration()->Adapter;
        if ($Adapter=="mysqli" or $Adapter=="pdo_mysql") 
        	j::SQL("ALTER TABLE {$this->TablePrefix()}rbac_roles AUTO_INCREMENT =1 ");
       	elseif ($Adapter=="pdo_sqlite")
        	j::SQL("delete from sqlite_sequence where name=? ",$this->TablePrefix()."rbac_roles");
        else
			trigger_error("RBAC can not reset table on this type of database: {$Adapter}");
        
	}
	###############################
	#### Permissions Interface ####
	###############################
    /**
     * Determines if the input permission is a permission ID or a permission title
     * then returns the appropriate field name in database to set the condition
     *
     * @param String $Permission ID or Title
     * @return String Fieldname in database that's appropriate
     */
    private function PermissionField($Permission)
	{
	    if (is_numeric($Permission))
	        return "ID";
	    else 
	        return "Title";
	}
	/**
	 * This returns the condition used to determine permission for use in nested set 
	 *
	 * @param String $Permission ID or  Title
	 */
	private function PermissionCondition($Permission)
	{
	    return $this->PermissionField($Permission)."=?";
	}
	/**
    Permission Title to Permission ID
    Returns ID of a RBAC Permission from its Title
    @param String $PermissionTitle Title of the RBAC Permission
    @return ID String, Null on failure
    @see Permission_Info()
    */
	function Permission_ID($PermissionTitle)
	{
        return $this->Permissions->GetID($this->PermissionCondition($PermissionTitle),$PermissionTitle);
	 }
	
	/**
    Permission Information
    Returns ID, Title and Description of a RBAC Permission from its ID (and Depth)
    @param String $Permission ID or Title of the RBAC Permission
    @return Array with 4 elements
    @see Permission_ID()
    */
	function Permission_Info($Permission)
	{
		return $this->Permissions->GetRecord($this->PermissionCondition($Permission),$Permission);
    }
	
	/**
    Adds a new Permission
    Returns new Permission's ID
    @param String $PermissionTitle Title of the new Permission
    @param String $PermissionDescription Description of the new Permission
    @param String $PermissionParent ID or Title of the parent node in the Permissions hierarchy
    @return ID of the new Permission
    @see Permission_ID()
    */
	function Permission_Add($PermissionTitle,$PermissionDescription,$PermissionParent=0)
	{
		return $this->Permissions->InsertChildData(
    		array("Title"=>$PermissionTitle
    		,"Description"=>$PermissionDescription)
    		,$this->PermissionCondition($PermissionParent),$PermissionParent
		);
    }
    /**
    Removes a permission and all its assignments
	@param String $Permission ID or Title of a Permission
	@param Boolean $Recursive to remove descendants as well or not
    @see Permission_ID()
    */
	function Permission_Remove($Permission,$Recursive=false)
	{
        $this->UnassignPermissionRoles($Permission);
	    if (!$Recursive)
         $this->Permissions->DeleteConditional($this->PermissionCondition($Permission),$Permission);
        else
            $this->Permissions->DeleteSubtreeConditional($this->PermissionCondition($Permission),$Permission);
	}
	/**
	 * Edits a Permission title and permission (not position)
	 *
	 * @param String $Permission ID or Title of node
	 * @param String $PermissionTitle new title
	 * @param String $PermissionDescription new description
	 */
	function Permission_Edit($Permission,$PermissionTitle=null,$PermissionDescription=null)
	{
        return $this->Permissions->EditData(array("Title"=>$PermissionTitle
    		,"Description"=>$PermissionDescription)
    		,$this->PermissionCondition($Permission),$Permission);
	}
	/**
	 * Returns all direct children of a Permission
	 *
	 * @param String $Permission ID or Title of Permission
	 * @return Array Children
	 */
	function Permission_Children($Permission=0)
	{
	    return $this->Permissions->ChildrenConditional($this->PermissionCondition($Permission),$Permission);
	}
	/**
	 * Returns all level descendants of a Permission
	 *
	 * @param String $Permission ID or Title of Permission
	 * @return Array of Descendants (with Depth field)
	 */
	function Permission_Descendants($Permission=0)
	{
	    return $this->Permissions->DescendantsConditional(false,$this->PermissionCondition($Permission),$Permission);
	}
	/**
	 * Returns all Permissions in a Depth sorted manner
	 * includes the Depth field in each Permission
	 *
	 * @return Array Permissions
	 */
	function Permission_All()
	{
	    return $this->Permissions->FullTree();
	}
	
	function Permission_Reset($Ensure=false)
	{
		if ($Ensure!==true) 
		{
			trigger_error("Make sure you want to reset all permissions first!");
			return;
		}
		j::SQL("DELETE FROM {$this->TablePrefix()}rbac_permissions");	    
        $this->Permission_Add("root","root");
        j::SQL("UPDATE {$this->TablePrefix()}rbac_permissions SET ID = '0'");
        $Adapter=DatabaseManager::Configuration()->Adapter;
        if ($Adapter=="mysqli" or $Adapter=="pdo_mysql") 
        	j::SQL("ALTER TABLE {$this->TablePrefix()}rbac_permissions AUTO_INCREMENT =1 ");
       	elseif ($Adapter=="pdo_sqlite")
        	j::SQL("delete from sqlite_sequence where name=? ",$this->TablePrefix()."rbac_permissions");
        else
			trigger_error("RBAC can not reset table on this type of database: {$Adapter}");
        
	}
	
	##############################
	######### User-Roles #########
	##############################
	
	/**
	 * Assigns a role to a user
	 *
	 * @param String $Role Title or ID
	 * @param String $UserID optional, UserID or the current user would be used (use 0 for guest)
	 * @param Boolean $Replace to replace the assignment if existing (only updates date)
	 */
	function User_AssignRole($Role,$UserID=null,$Replace=false)
	{
	    if (!is_numeric($Role)) $Role=$this->Role_ID($Role);
	    if ($UserID===null) $UserID=jf::CurrentUser();
        $Query=$Replace?"REPLACE":"INSERT INTO";
        j::SQL("{$Query} {$this->TablePrefix()}rbac_userroles 
        (UserID,RoleID,AssignmentDate)
        VALUES (?,?,?)
        ",$UserID,$Role,jf::time());
	}
	/**
	 * Unassigns a role to a user
	 *
	 * @param String $Role Title or ID
	 * @param String $UserID optional, UserID or the current user would be used (use 0 for guest)
	 */
	function User_UnassignRole($Role,$UserID=null)
	{
	    if (!is_numeric($Role)) $Role=$this->Role_ID($Role);
	    if ($UserID===null) $UserID=jf::CurrentUser();
        j::SQL("DELETE FROM {$this->TablePrefix()}rbac_userroles 
		WHERE UserID=? AND RoleID=?"
        ,$UserID,$Role);
	}
	
	/**
	 * Returns all Role-User relations as a 2D array of arrays with following fields:
	 * UserID, Username, AssignmentDate, RoleID, RoleTitle, RoleDescription
	 * @param Boolean $OnlyIDs if this is set to true, only a 2D array of RoleID-UserID is returned (the actual DB table)
	 * @param Integer $Offset to start the results from
	 * @param Integer $Limit to limit number of results
	 * @return Array 2D
	 */
	function User_AllAssignments($OnlyIDs=true,$SortBy=null,$Offset=null,$Limit=null)
	{
	    if ($Limit)
	        $Limit=" LIMIT {$Offset},{$Limit}";
	    else 
	        $Limit="";
	    if ($SortBy)
	        $SortBy=" ORDER BY {$SortBy}";
	    else 
	        $SortBy="";    
	    if ($OnlyIDs)
	        return j::SQL("SELECT * FROM {$this->TablePrefix()}rbac_userroles{$Limit}");
	    else  
	        return j::SQL("SELECT TRel.AssignmentDate AS AssignmentDate,
	        TU.ID AS UserID,TU.Username
	        AS Username,TR.ID AS RoleID , TR.Title
	         AS RoleTitle,TR.Description AS RoleDescription 
	        FROM 
			{$this->TablePrefix()}rbac_userroles AS `TRel` 
			JOIN {$this->TablePrefix()}.users AS `TU` ON 
			(`TRel`.UserID=TU.ID)
			JOIN {$this->TablePrefix()}rbac_roles AS `TR` ON 
			(`TRel`.RoleID=`TR`.ID)
	        {$SortBy}{$Limit}"
	        );
	}
	function User_AllAssignmentsCount()
	{
        $Res=j::SQL("SELECT COUNT(*) AS Result FROM {$this->TablePrefix()}rbac_userroles");
        return $Res[0]['Result'];
	}	
	
	##############################
	###### Role-Permission #######
	##############################
	/**
	 * Assigns a role to a permission (or vice-versa)
	 *
	 * @param String $Role Title or ID
	 * @param String $Permission Title or ID
	 * @param Boolean $Replace to replace if existing (would only update AssignmentDate)
	 */
	function Assign($Role,$Permission,$Replace=false)
	{
	    if (!is_numeric($Role)) $Role=$this->Role_ID($Role);
	    if (!is_numeric($Permission)) $Permission=$this->Permission_ID($Permission);
	    if ($Replace) $Query="REPLACE";
	    else $Query="INSERT INTO";
	    
	    
	    j::SQL("{$Query} {$this->TablePrefix()}rbac_rolepermissions 
	    (RoleID,PermissionID,AssignmentDate)
	    VALUES (?,?,?)",$Role,$Permission,jf::time());
	}
	/**
	 * Unassigns a role-permission relation
	 *
	 * @param String $Role Title or ID
	 * @param String $Permission Title or ID
	 */
	function Unassign($Role,$Permission)
	{
	    if (!is_numeric($Role)) $Role=$this->Role_ID($Role);
	    if (!is_numeric($Permission)) $Permission=$this->Permission_ID($Permission);
        j::SQL("DELETE FROM {$this->TablePrefix()}rbac_rolepermissions WHERE 
        RoleID=? AND PermissionID=?",$Role,$Permission );
	}
	
	function UnassignRolePermissions ($Role)
	{
		if (!is_numeric($Role)) $Role=$this->Role_ID($Role);
		j::SQL("DELETE FROM {$this->TablePrefix()}rbac_rolepermissions WHERE 
        RoleID=? ",$Role);
	}
	function UnassignPermissionRoles ($Permission)
	{
	    if (!is_numeric($Permission)) $Permission=$this->Permission_ID($Permission);
        j::SQL("DELETE FROM {$this->TablePrefix()}rbac_rolepermissions WHERE 
        PermissionID=?",$Permission );
	}
	function UnassignRoleUsers($Role)
	{
		if (!is_numeric($Role)) $Role=$this->Role_ID($Role);
		j::SQL("DELETE FROM {$this->TablePrefix()}rbac_userroles WHERE 
        RoleID=?",$Role);
	}
	function Assignment_Reset($Ensure=false)
	{
		if ($Ensure!==true) 
		{
			trigger_error("Make sure you want to reset all assignments first!");
			return;
		}
		j::SQL("DELETE FROM {$this->TablePrefix()}rbac_rolepermissions");
        $Adapter=DatabaseManager::Configuration()->Adapter;
        if ($Adapter=="mysqli" or $Adapter=="pdo_mysql") 
        	j::SQL("ALTER TABLE {$this->TablePrefix()}rbac_rolepermissions AUTO_INCREMENT =1 ");
       	elseif ($Adapter=="pdo_sqlite")
        	j::SQL("delete from sqlite_sequence where name=? ",$this->TablePrefix()."_rbac_rolepermissions");
        else
			trigger_error("RBAC can not reset table on this type of database: {$Adapter}");
		$this->Assign("root","root",true);
		return true;
	}     
	/**
	 * Returns all permissions assigned to a role
	 *
	 * @param String $Role Title or ID
	 * @param Boolean $OnlyIDs if true, result would be a 1D array of IDs
	 * @return Array 2D or 1D
	 */
	function RolePermissions($Role,$OnlyIDs=true)
	{
	    if (!is_numeric($Role)) $Role=$this->Role_ID($Role);
	    if ($OnlyIDs)
	    {
	        $Res=j::SQL("SELECT PermissionID AS `ID` FROM
			{$this->TablePrefix()}rbac_rolepermissions WHERE RoleID=?"
	        ,$Role);
	        foreach ($Res as $R)
	            $out[]=$R['ID'];
	        return $out;
	    }
	    else
	        return j::SQL("SELECT `TP`.* FROM 
			{$this->TablePrefix()}rbac_rolepermissions AS `TR` RIGHT JOIN {$this->TablePrefix()}rbac_permissions AS `TP` ON 
			(`TR`.PermissionID=`TP`.ID)
			WHERE RoleID=?"
	        ,$Role);
	}
	/**
	 * Returns all roles assigned to a permission
	 *
	 * @param String $Permission Title or ID
	 * @param Boolean $OnlyIDs if true, result would be a 1D array of IDs
	 * @return Array 2D or 1D
	 */
	function PermissionRoles($Permission,$OnlyIDs=true)
	{
	    if (!is_numeric($Permission)) $Permission=$this->Permission_ID($Permission);
	    if ($OnlyIDs)
	    {
	        $Res=j::SQL("SELECT RoleID AS `ID` FROM
			{$this->TablePrefix()}rbac_rolepermissions WHERE PermissionID=?"
	        ,$Permission);
	        foreach ($Res as $R)
	            $out[]=$R['ID'];
	        return $out;
	    }
	    else
	        return j::SQL("SELECT `TP`.* FROM 
			{$this->TablePrefix()}rbac_rolepermissions AS `TR` RIGHT JOIN {$this->TablePrefix()}rbac_roles AS `TP` ON 
			(`TR`.RoleID=`TP`.ID)
			WHERE PermissionID=?"
	        ,$Permission);
	}
	/**
	 * Returns all Role-Permission relations as a 2D array of arrays with following fields:
	 * PermissionID, PermissionTitle, PermissionDescription, RoleID, RoleTitle, RoleDescription, AssignmentDate
	 * @param Boolean $OnlyIDs if this is set to true, only a 2D array of RoleID-PermissionIDs is returned (the actual DB table)
	 * @param Integer $Offset to start the results from
	 * @param Integer $Limit to limit number of results
	 * @return Array 2D
	 */
	function Assignments_All($OnlyIDs=true,$SortBy=null,$Offset=null,$Limit=null)
	{
	    if ($Limit)
	        $Limit=" LIMIT {$Offset},{$Limit}";
	    else 
	        $Limit="";
	    if ($SortBy)
	        $SortBy=" ORDER BY {$SortBy}";
	    else 
	        $SortBy="";    
	    if ($OnlyIDs)
	        return j::SQL("SELECT * FROM {$this->TablePrefix()}rbac_rolepermissions{$Limit}");
	    else 
	        return j::SQL("SELECT TRel.AssignmentDate AS AssignmentDate,
	        TP.ID AS PermissionID,TP.Title
	        AS PermissionTitle, TP.Description AS PermissionDescription,TR.`".
	        "ID"."` AS RoleID , TR.Title AS RoleTitle,TR.Description AS RoleDescription 
	        FROM 
			{$this->TablePrefix()}rbac_rolepermissions AS `TRel` 
			JOIN {$this->TablePrefix()}rbac_permissions AS `TP` ON 
			(`TRel`.PermissionID=`TP`.ID)
			JOIN {$this->TablePrefix()}rbac_roles AS `TR` ON 
			(`TRel`.RoleID=`TR`.ID)
	        {$SortBy}{$Limit}"
	        );
	}
	function Assignments_Count()
	{
        $Res=j::SQL("SELECT COUNT(*) AS Result FROM {$this->TablePrefix()}rbac_rolepermissions");
        return $Res[0]['Result'];
	}
	
	###############
	### GENERAL ###
	###############
	private $PreparedStatement_Check=array(0=>null,1=>null);
	/**
	 * Checks whether a user has a permission or not.
	 *
	 * @param String $Permission Title or ID
	 * @param Integer $UserID optional
	 * @return true on success (a positive number) false on no permission (zero)
	 */
	function Check($Permission,$UserID=null)
	{
		//To different prepared statements, one for Title lookup another of ID lookup of permission
	    if (is_numeric($Permission)) 
	    {
	        $PermissionCondition="ID=?";
	        $Index=0;
	    }
	    else 
	    {
	        $PermissionCondition="Title=?";
	        $Index=1;
	    }
	    if ($UserID===null) $UserID=jf::CurrentUser();
	    if (!$this->PreparedStatement_Check[$Index])
	    {
	    	$this->PreparedStatement_Check[$Index]=jf::db()->prepare
    ("SELECT COUNT(*) AS Result
    FROM /* Version 2.05 */ 
    	{$this->TablePrefix()}users AS TU
    JOIN {$this->TablePrefix()}rbac_userroles AS TUrel ON (TU.ID=TUrel.UserID)
    	
    JOIN {$this->TablePrefix()}rbac_roles AS TRdirect ON (TRdirect.ID=TUrel.RoleID) 
    JOIN {$this->TablePrefix()}rbac_roles AS TR ON ( TR.Left BETWEEN TRdirect.Left AND TRdirect.Right)
    /* we join direct roles with indirect roles to have all descendants of direct roles */
    JOIN 
    (	{$this->TablePrefix()}rbac_permissions AS TPdirect 
    	JOIN {$this->TablePrefix()}rbac_permissions AS TP ON ( TPdirect.Left BETWEEN TP.Left AND TP.Right)
    /* direct and indirect permissions */
    	JOIN {$this->TablePrefix()}rbac_rolepermissions AS TRel ON (TP.ID=TRel.PermissionID)
    /* joined with role/permissions on roles that are in relation with these permissions*/
    ) ON ( TR.ID = TRel.RoleID)
    WHERE 
    	TU.ID=? 
    AND
	    TPdirect.{$PermissionCondition}
	    ");
	    }
	    $this->PreparedStatement_Check[$Index]->execute(
	   	$UserID
	    ,$Permission
	    );
        $Res=$this->PreparedStatement_Check[$Index]->fetchAll();
	    
	    return $Res[0]['Result'];
	}
	/**
	 * Checks to see if a role has a permission or not
	 *
	 * @param String $Role Title or ID
	 * @param String $Permission Title or ID
	 * @return Integer 0 on no, number of paths to permission on yes
	 * @author AbiusX with all the SQL statements used!
	 */
	function CheckRolePermission($Role,$Permission)
	{
	    if (is_numeric($Permission)) $PermissionCondition="node.ID=?";
	    else $PermissionCondition="node.Title=?";
	    if (is_numeric($Role)) $RoleCondition="ID=?";
	    else $RoleCondition="Title=?";
	    
	    $Res=j::SQL("
    SELECT COUNT(*) AS Result
    FROM {$this->TablePrefix()}rbac_rolepermissions AS TRel
    JOIN {$this->TablePrefix()}rbac_permissions AS TP ON ( TP.ID= TRel.PermissionID)
    JOIN {$this->TablePrefix()}rbac_roles AS TR ON ( TR.ID = TRel.RoleID)
    WHERE TR.Left BETWEEN 
    	(SELECT Left FROM {$this->TablePrefix()}rbac_roles WHERE {$RoleCondition}) 
    	AND 
    	(SELECT Right FROM {$this->TablePrefix()}rbac_roles WHERE {$RoleCondition})
/* the above section means any row that is a descendants of our role (if descendant roles have some permission, then our role has it two) */
    AND TP.ID IN (
                SELECT parent.ID 
                FROM {$this->TablePrefix()}rbac_permissions AS node,
                {$this->TablePrefix()}rbac_permissions AS parent
                WHERE node.Left BETWEEN parent.Left AND parent.Right
                AND ( {$PermissionCondition} )
                ORDER BY parent.Left
    );
/*
the above section returns all the parents of (the path to) our permission, so if one of our role or its descendants
 has an assignment to any of them, we're good. 
*/
	    "
	    ,$Role,$Role
	    ,$Permission
	    );
        return $Res[0]['Result'];
}
	
	
	function Enforce($Permission)
	{
	    if (!$this->Check($Permission))
	    {
	    	if (jf::CurrentUser())
				jf::import("view/_internal/error/403",array("Permission"=>$Permission));
	    	else
				jf::import("view/_internal/error/401",array("Permission"=>$Permission));
	    	exit();
	    }
	}
	/**
	 * Checks to see whether a user has a role or not
	 * @param 	$Role Role Title or ID
	 * @param	$User User ID
	 * @return	Boolean true on yes, false on no
	 */
	function UserInRole($User=null,$Role)
	{
		
		if ($User===null) $User=jf::CurrentUser();
	    if (!is_numeric($Role)) $Role=$this->Role_ID($Role);
		$R=jf::SQL("SELECT * FROM {$this->TablePrefix()}rbac_userroles WHERE
		UserID=? AND RoleID=?",$User,$Role);
		if ($R) return true;
		else return false;
	}	
}
interface RBAC_RoleManagement 
{
	function Role_ID($RoleTitle);
	
	function Role_Info($Role); 
	
	function Role_Add($RoleTitle,$RoleDescription,$RoleParent=0);
	function Role_Remove($Role);
	function Role_Edit($Role,$RoleTitle=null,$RoleDescription=null);
	
    function Role_Children($Role=0);
    function Role_Descendants($Role=0);
	
	function Role_All();
		
}
interface RBAC_PermissionManagement
{
	function Permission_ID($PermissionTitle);
	
	function Permission_Info($Permission);
	
	function Permission_Add($PermissionTitle,$PermissionDescription,$PermissionParent=0);
	function Permission_Remove($Permission);
	function Permission_Edit($Permission,$PermissionTitle=null,$PermissionDescription=null);
	
	function Permission_Children($Permission=0);
	function Permission_Descendants($Permission=0);
	
	function Permission_All();
}
interface RBAC_UserManagement
{
	//function User_AssignRole($UserID,$RoleID);
	
	//function User_UnassignRole($UserID,$RoleID);
	
	//function User_UnassignAllRoles($UserID);
	
	//function User_Validate($PermissionID,$UserID);

	//function User_RoleList($UserID);
	
}
interface RBAC_Management
{
	
	//Role-Permission Relation
	#function Assign($RoleID,$PermissionID);
	#function Unassign($RoleID,$PermissionID);
	#-function UnassignRolePermissions($RoleID);
	#-function UnassignPermissionRoles($PermissionID);

	#function PermissionList($RoleID);
	#function RoleList($PermissionID);
	
	
	
	//Checks for existence of a permission
	#function ValidateRolePermission($RoleID,$PermissionID);
	

	
	
}
?>