<?php
/*
 * Role Based Access Control Implemented on NIST RBAC Standard Model level 2.
 * Hierarchical RBAC (Restricted Hierarchical RBAC : only trees of roles) by
 * AbiusX Features: Restricted Hierarchies for both Permissions and Roles.
 * Multiple roles for each user Optimized queries
 */


/**
 * Role Based Access Control class.
 * defines, manages and implements user/role/permission relations, and role
 * based access control.
 * NIST RBAC Standard Model 2 (Hierarchical RBAC, trees of roles)
 * @verson .9
 */
namespace jf;

abstract class BaseRBAC extends Model
{
	function RootID()
	{
		return 1;
	}
	
	/**
	 * Return type of current instance, e.g roles, permissions
	 *
	 * @return string
	 */
	abstract protected function Type();
	/**
	 * Adds a new role or permission
	 * Returns new entry's ID
	 *
	 * @param string $Title
	 *        	Title of the new entry
	 * @param integer $Description
	 *        	Description of the new entry
	 * @param integer $ParentID
	 *        	optional ID of the parent node in the hierarchy
	 * @return integer ID of the new entry
	 */
	function Add($Title, $Description, $ParentID = null)
	{
		if ($ParentID === null)
			$ParentID = $this->RootID ();
		return $this->{$this->Type ()}->InsertChildData ( array ("Title" => $Title, "Description" => $Description ), "ID=?", $ParentID );
	}
	/**
	 * Return count of the entity
	 *
	 * @return integer
	 */
	function Count()
	{
		$Res = jf::SQL ( "SELECT COUNT(*) FROM {$this->TablePrefix()}rbac_{$this->Type()}" );
		return $Res [0] ['COUNT(*)'];
	}
	
	/**
	 * Returns ID of a path
	 *
	 * @todo this has a limit of 1000 characters on $Path
	 * @param string $Path
	 *        	such as /role1/role2/role3 ( a single slash is root)
	 * @return integer NULL
	 */
	function PathID($Path)
	{
		$Path = "root" . $Path;
		if ($Path [strlen ( $Path ) - 1] == "/")
			$Path = substr ( $Path, 0, strlen ( $Path ) - 1 );
		$Parts = explode ( "/", $Path );
		$res = jf::SQL ( "SELECT node.ID,GROUP_CONCAT(parent.Title ORDER BY parent.Lft SEPARATOR '/' ) AS Path
		FROM {$this->TablePrefix()}rbac_{$this->Type()} AS node,
		{$this->TablePrefix()}rbac_{$this->Type()} AS parent
		WHERE node.Lft BETWEEN parent.Lft AND parent.Rght
		AND  node.Title=?
				GROUP BY node.ID
				HAVING Path = ?
				ORDER BY parent.Lft
		", $Parts [count ( $Parts ) - 1], $Path );
		if ($res)
			return $res [0] ['ID'];
		else
			return null;
			// TODO: make the below SQL work, so that 1024 limit is over
		
		$QueryBase = ("SELECT n0.ID  \nFROM {$this->TablePrefix()}rbac_{$this->Type()} AS n0");
		$QueryCondition = "\nWHERE 	n0.Title=?";
		
		for($i = 1; $i < count ( $Parts ); ++ $i)
		{
			$j = $i - 1;
			$QueryBase .= "\nJOIN 		{$this->TablePrefix()}rbac_{$this->Type()} AS n{$i} ON (n{$j}.Lft BETWEEN n{$i}.Lft+1 AND n{$i}.Rght)";
			$QueryCondition .= "\nAND 	n{$i}.Title=?";
			// Forcing middle elements
			$QueryBase .= "\nLEFT JOIN 	{$this->TablePrefix()}rbac_{$this->Type()} AS nn{$i} ON (nn{$i}.Lft BETWEEN n{$i}.Lft+1 AND n{$j}.Lft-1)";
			$QueryCondition .= "\nAND 	nn{$i}.Lft IS NULL";
		}
		$Query = $QueryBase . $QueryCondition;
		$PartsRev = array_reverse ( $Parts );
		array_unshift ( $PartsRev, $Query );
		
		print_ ( $PartsRev );
		$res = call_user_func_array ( "jf::SQL", $PartsRev );
		if ($res)
			return $res [0] ['ID'];
		else
			return null;
	}
	
	/**
	 * Returns ID belonging to a title, and the first one on that
	 *
	 * @param unknown_type $Title        	
	 */
	function TitleID($Title)
	{
		return $this->{$this->Type ()}->GetID ( "Title=?", $Title );
	}
	/**
	 * Return the whole record of a single entry (including Rght and Lft fields)
	 *
	 * @param integer $ID        	
	 */
	protected function GetRecord($ID)
	{
		$args = func_get_args ();
		return call_user_func_array ( array ($this->{$this->Type ()}, "GetRecord" ), $args );
	}
	/**
	 * Returns title of entity
	 *
	 * @param integer $ID        	
	 * @return string NULL
	 */
	function GetTitle($ID)
	{
		$r = $this->GetRecord ( "ID=?", $ID );
		if ($r)
			return $r ['Title'];
		else
			return null;
	}
	/**
	 * Return description of entity
	 *
	 * @param integer $ID        	
	 * @return string NULL
	 */
	function GetDescription($ID)
	{
		$r = $this->GetRecord ( "ID=?", $ID );
		if ($r)
			return $r ['Description'];
		else
			return null;
	}
	/**
	 * Adds a path and all its components.
	 * Will not replace or create siblings if a component exists.
	 *
	 * @param string $Path
	 *        	such as /some/role/some/where
	 * @param array $Descriptions
	 *        	array of descriptions (will add with empty description if not
	 *        	avialable)
	 * @return integer NULL components ID
	 */
	function AddPath($Path, array $Descriptions = null)
	{
		assert ( $Path [0] == "/" );
		
		$Path = substr ( $Path, 1 );
		$Parts = explode ( "/", $Path );
		$Parent = 1;
		$index = 0;
		$CurrentPath = "";
		foreach ( $Parts as $p )
		{
			if (isset ( $Descriptions [$index] ))
				$Description = $Descriptions [$index];
			else
				$Description = "";
			$CurrentPath .= "/{$p}";
			$t = $this->PathID ( $CurrentPath );
			if (! $t)
			{
				$IID = $this->Add ( $p, $Description, $Parent );
				$Parent = $IID;
			}
			else
			{
				$Parent = $t;
			}
		}
		return $Parent;
	}
	
	/**
	 * Edits an entity, changing title and/or description
	 *
	 * @param integer $ID        	
	 * @param string $NewTitle        	
	 * @param string $NewDescription        	
	 */
	function Edit($ID, $NewTitle = null, $NewDescription = null)
	{
		$Data = array ();
		if ($NewTitle !== null)
			$Data ['Title'] = $NewTitle;
		if ($NewDescription !== null)
			$Data ['Description'] = $NewDescription;
		return $this->{$this->Type ()}->EditData ( $Data, "ID=?", $ID ) == 1;
	}
	
	/**
	 * Returns children of an entity
	 *
	 * @return array
	 */
	function Children($ID)
	{
		return $this->{$this->Type ()}->ChildrenConditional ( "ID=?", $ID );
	}
	
	/**
	 * Returns descendants of a node, with their depths in integer
	 *
	 * @param integer $ID        	
	 * @return array with keys as titles and Title,ID, Depth and Description
	 */
	function Descendants($ID)
	{
		$res = $this->{$this->Type ()}->DescendantsConditional(/* absolute depths*/false, "ID=?", $ID );
		$out = array ();
		if (is_array ( $res ))
			foreach ( $res as $v )
				$out [$v ['Title']] = $v;
		return $out;
	}
	
	/**
	 * Return depth of a node
	 *
	 * @param integer $ID        	
	 */
	function Depth($ID)
	{
		return $this->{$this->Type ()}->DepthConditional ( "ID=?", $ID );
	}
	
	/**
	 * Returns path of a node
	 *
	 * @param integer $ID        	
	 * @return string path
	 */
	function Path($ID)
	{
		$res = $this->{$this->Type ()}->PathConditional ( "ID=?", $ID );
		$out = null;
		if (is_array ( $res ))
			foreach ( $res as $r )
				if ($r ['ID'] == 1)
					$out = '/';
				else
					$out .= "/" . $r ['Title'];
		if (strlen ( $out ) > 1)
			return substr ( $out, 1 );
		else
			return $out;
	}
	
	/**
	 * Returns parent of a node
	 *
	 * @param integer $ID        	
	 * @return array including Title, Description and ID
	 */
	function ParentNode($ID)
	{
		return $this->{$this->Type ()}->ParentNodeConditional ( "ID=?", $ID );
	}
	
	/**
	 * Reset the table back to its initial state
	 * Keep in mind that this will not touch relations
	 *
	 * @param boolean $Ensure
	 *        	must be true to work, otherwise error
	 * @throws \Exception
	 * @return integer number of deleted entries
	 */
	function Reset($Ensure = false)
	{
		if ($Ensure !== true)
		{
			throw new \Exception ( "You must pass true to this function, otherwise it won't work." );
			return;
		}
		$res = jf::SQL ( "DELETE FROM {$this->TablePrefix()}rbac_{$this->Type()}" );
		$Adapter = DatabaseManager::Configuration ()->Adapter;
		if ($Adapter == "mysqli" or $Adapter == "pdo_mysql")
			jf::SQL ( "ALTER TABLE {$this->TablePrefix()}rbac_{$this->Type()} AUTO_INCREMENT=1 " );
		elseif ($Adapter == "pdo_sqlite")
			jf::SQL ( "delete from sqlite_sequence where name=? ", $this->TablePrefix () . "rbac_{$this->Type()}" );
		else
			throw new \Exception ( "RBAC can not reset table on this type of database: {$Adapter}" );
		$iid = jf::SQL ( "INSERT INTO {$this->TablePrefix()}rbac_{$this->Type()} (Title,Description) VALUES (?,?)", "root", "root" );
		return $res;
	}
	
	
	/**
	 * Assigns a role to a permission (or vice-versa)
	 *
	 * @param integer $Role
	 * @param integer $Permission
	 * @return boolean inserted or existing
	 */
	function Assign($Role, $Permission)
	{
		return jf::SQL ( "INSERT INTO {$this->TablePrefix()}rbac_rolepermissions
			(RoleID,PermissionID,AssignmentDate)
			VALUES (?,?,?)", $Role, $Permission, jf::time () ) > 1;
	}
	/**
	 * Unassigns a role-permission relation
	 * @param integer $Role
	 * @param integer $Permission
	 * @return number of deleted relations
	 */
	function Unassign($Role, $Permission)
	{
		return jf::SQL ( "DELETE FROM {$this->TablePrefix()}rbac_rolepermissions WHERE
				RoleID=? AND PermissionID=?", $Role, $Permission );
	}
	
	/**
	 * Remove all role-permission relations
	 * mostly used for testing
	 * @param boolean $Ensure must set or throws error
	 * @return number of deleted relations
	 */
	function ResetAssignments($Ensure = false)
	{
		if ($Ensure !== true)
		{
			throw new \Exception ( "You must pass true to this function, otherwise it won't work." );
			return;
		}
		$res=jf::SQL ( "DELETE FROM {$this->TablePrefix()}rbac_rolepermissions" );
		
		$Adapter = DatabaseManager::Configuration ()->Adapter;
		if ($Adapter == "mysqli" or $Adapter == "pdo_mysql")
			jf::SQL ( "ALTER TABLE {$this->TablePrefix()}rbac_rolepermissions AUTO_INCREMENT =1 " );
		elseif ($Adapter == "pdo_sqlite")
			jf::SQL ( "delete from sqlite_sequence where name=? ", $this->TablePrefix () . "_rbac_rolepermissions" );
		else
			throw new \Exception ( "RBAC can not reset table on this type of database: {$Adapter}" );
		$this->Assign ( "root", "root");
		return $res;
	}	
}
class RoleManager extends BaseRBAC
{
	/**
	 * Roles Nested Set
	 *
	 * @var FullNestedSet
	 */
	protected $roles = null;
	protected function Type()
	{
		return "roles";
	}
	function __construct()
	{
		$this->Type = "roles";
		$this->roles = new FullNestedSet ( $this->TablePrefix () . "rbac_roles", "ID", "Lft", "Rght" );
	}
	
	/**
	 * Remove a role from system
	 *
	 * @param integer $ID
	 *        	role id
	 * @param boolean $Recursive
	 *        	delete all descendants
	 *        	
	 */
	function Remove($ID, $Recursive = false)
	{
		$this->UnassignPermissions ( $ID );
		$this->UnassignUsers ( $ID );
		if (! $Recursive)
			return $this->roles->DeleteConditional ( "ID=?", $ID );
		else
			return $this->roles->DeleteSubtreeConditional ( "ID=?", $ID );
	}
	/**
	 * Unassigns all permissions belonging to a role
	 *
	 * @param integer $ID
	 *        	role ID
	 * @return integer number of assignments deleted
	 */
	function UnassignPermissions($ID)
	{
		$r = jf::SQL ( "DELETE FROM {$this->TablePrefix()}rbac_rolepermissions WHERE
			RoleID=? ", $ID );
		return $r;
	}
	/**
	 * Unassign all users that have a certain role
	 *
	 * @param integer $ID
	 *        	role ID
	 * @return integer number of deleted assignments
	 */
	function UnassignUsers($ID)
	{
		return jf::SQL ( "DELETE FROM {$this->TablePrefix()}rbac_userroles WHERE
			RoleID=?", $ID );
	}
	

	/**
	 * Checks to see if a role has a permission or not
	 *
	 * @param integer $Role
	 *        	ID
	 * @param integer $Permission
	 *        	ID
	 * @return boolean
	 */
	function HasPermission($Role, $Permission)
	{
		$Res = jf::SQL ( "
				SELECT COUNT(*) AS Result
				FROM {$this->TablePrefix()}rbac_rolepermissions AS TRel
				JOIN {$this->TablePrefix()}rbac_permissions AS TP ON ( TP.ID= TRel.PermissionID)
				JOIN {$this->TablePrefix()}rbac_roles AS TR ON ( TR.ID = TRel.RoleID)
				WHERE TR.Left BETWEEN
				(SELECT Left FROM {$this->TablePrefix()}rbac_roles WHERE ID=?)
				AND
			(SELECT Right FROM {$this->TablePrefix()}rbac_roles WHERE ID=?)
			/* the above section means any row that is a descendants of our role (if descendant roles have some permission, then our role has it two) */
			AND TP.ID IN (
			SELECT parent.ID
			FROM {$this->TablePrefix()}rbac_permissions AS node,
			{$this->TablePrefix()}rbac_permissions AS parent
			WHERE node.Left BETWEEN parent.Left AND parent.Right
			AND ( node.ID=? )
			ORDER BY parent.Left
			);
			/*
			the above section returns all the parents of (the path to) our permission, so if one of our role or its descendants
			has an assignment to any of them, we're good.
			*/
		", $Role, $Role, $Permission );
		return $Res [0] ['Result'] >= 1;
	}
	/**
	 * Returns all permissions assigned to a role
	 *
	 * @param integer $Role
	 *        	ID
	 * @param boolean $OnlyIDs
	 *        	if true, result would be a 1D array of IDs
	 * @return Array 2D or 1D or null
	 */
	function Permissions($Role, $OnlyIDs = true)
	{
		if ($OnlyIDs)
		{
			$Res = jf::SQL ( "SELECT PermissionID AS `ID` FROM {$this->TablePrefix()}rbac_rolepermissions WHERE RoleID=?", $Role );
			if (is_array ( $Res ))
			{
				$out = array ();
				foreach ( $Res as $R )
					$out [] = $R ['ID'];
				return $out;
			}
			else
				return null;
		}
		else
			return jf::SQL ( "SELECT `TP`.* FROM {$this->TablePrefix()}rbac_rolepermissions AS `TR` 
				RIGHT JOIN {$this->TablePrefix()}rbac_permissions AS `TP` ON (`TR`.PermissionID=`TP`.ID)
				WHERE RoleID=?", $Role );
	}
}
class PermissionManager extends BaseRBAC
{
	/**
	 * Permissions Nested Set
	 *
	 * @var FullNestedSet
	 */
	protected $permissions;
	protected function Type()
	{
		return "permissions";
	}
	function __construct()
	{
		$this->permissions = new FullNestedSet ( $this->TablePrefix () . "rbac_permissions", "ID", "Lft", "Rght" );
	}
	/**
	 * Remove a permission from system
	 *
	 * @param integer $ID
	 *        	permission id
	 * @param boolean $Recursive
	 *        	delete all descendants
	 *        	
	 */
	function Remove($ID, $Recursive = false)
	{
		$this->UnassignRoles ( $ID );
		if (! $Recursive)
			return $this->permissions->DeleteConditional ( "ID=?", $ID );
		else
			return $this->permissions->DeleteSubtreeConditional ( "ID=?", $ID );
	}
	
	/**
	 * Unassignes all roles of this permission, and returns their number
	 *
	 * @param integer $ID        	
	 * @return integer
	 */
	function UnassignRoles($ID)
	{
		$res = jf::SQL ( "DELETE FROM {$this->TablePrefix()}rbac_rolepermissions WHERE
		PermissionID=?", $ID );
		return $res;
	}
	
	/**
	 * Returns all roles assigned to a permission
	 *
	 * @param integer $Permission
	 *        	ID
	 * @param boolean $OnlyIDs
	 *        	if true, result would be a 1D array of IDs
	 * @return Array 2D or 1D or null
	 */
	function Roles($Permission, $OnlyIDs = true)
	{
		if (! is_numeric ( $Permission ))
			$Permission = $this->Permission_ID ( $Permission );
		if ($OnlyIDs)
		{
			$Res = jf::SQL ( "SELECT RoleID AS `ID` FROM
				{$this->TablePrefix()}rbac_rolepermissions WHERE PermissionID=?", $Permission );
			if (is_array ( $Res ))
			{
				$out = array ();
				foreach ( $Res as $R )
					$out [] = $R ['ID'];
				return $out;
			}
			else
				return null;
		}
		else
			return jf::SQL ( "SELECT `TP`.* FROM {$this->TablePrefix()}rbac_rolepermissions AS `TR` 
					RIGHT JOIN {$this->TablePrefix()}rbac_roles AS `TP` ON (`TR`.RoleID=`TP`.ID)
					WHERE PermissionID=?", $Permission );
	}
}
class RBACUserManager extends Model
{
	/**
	 * Checks to see whether a user has a role or not
	 *
	 * @param integer $Role ID
	 * @param integer $User ID optional
	 * @return boolean success
	 */
	function HasRole($Role,$User = null)
	{
		if ($User === null)
			$User = jf::CurrentUser ();
		$R = jf::SQL ( "SELECT * FROM {$this->TablePrefix()}rbac_userroles WHERE
				UserID=? AND RoleID=?", $User, $Role );
		return $R===null;
	}
	/**
	 * Assigns a role to a user
	 *
	 * @param integer $Role ID
	 * @param integer $UserID ID
	 *        	optional, UserID or the current user would be used (use 0 for
	 *        	guest)
	 * @return inserted or existing
	 */
	function Assign($Role, $UserID = null)
	{
		if ($UserID === null)
			$UserID = jf::CurrentUser ();
		return jf::SQL ( "INSERT INTO {$this->TablePrefix()}rbac_userroles
			(UserID,RoleID,AssignmentDate)
			VALUES (?,?,?)
			", $UserID, $Role, jf::time () )>1;
	}
	/**
	 * Unassigns a role from a user
	 *
	 * @param integer $Role ID
	 * @param integer $UserID
	 *        	optional, UserID or the current user would be used (use 0 for
	 *        	guest)
	 * @return boolean success
	 */
	function Unassign($Role, $UserID = null)
	{
		if ($UserID === null)
			$UserID = jf::CurrentUser ();
		return jf::SQL ( "DELETE FROM {$this->TablePrefix()}rbac_userroles
			WHERE UserID=? AND RoleID=?", $UserID, $Role )>=1;
	}
	
	/**
	 * Returns all roles of a user
	 * @param integer $UserID optional
	 * @return array|null
	 */
	function AllRoles($UserID)
	{
		if ($UserID === null)
			$UserID = jf::CurrentUser ();

		return jf::SQL ( "SELECT TR.*
					FROM
					{$this->TablePrefix()}rbac_userroles AS `TRel`
					JOIN {$this->TablePrefix()}rbac_roles AS `TR` ON
					(`TRel`.RoleID=`TR`.ID)
					WHERE TRel.UserID=?",$UserID );
	}
	/**
	 * Return count of roles for a user
	 * @param integer $UserID optional
	 * @return integer
	 */
	function RoleCount($UserID=null)
	{
		if ($UserID === null)
			$UserID = jf::CurrentUser ();
		$Res = jf::SQL ( "SELECT COUNT(*) AS Result FROM {$this->TablePrefix()}rbac_userroles WHERE UserID=?" );
		return $Res [0] ['Result'];
	}
}
class RBACManager extends Model
{
	function __construct()
	{
		$this->Users = new RBACUserManager ();
		$this->Roles = new RoleManager ();
		$this->Permissions = new PermissionManager ();
	}
	/**
	 *
	 * @var \jf\PermissionManager
	 */
	public $Permissions;
	/**
	 *
	 * @var \jf\RoleManager
	 */
	public $Roles;
	/**
	 *
	 * @var \jf\RBACUserManager
	 */
	public $Users;
	



	
	
	private $ps_Check = null;
	/**
	 * Checks whether a user has a permission or not.
	 *
	 * @param string|integer $Permission you can provide a path like /some/permission, a title, or the permission ID.
	 * 					in case of ID, don't forget to provide integer (not a string containing a number)
	 * @param integer $UserID optional
	 * @return boolean
	 */
	function Check($Permission, $UserID = null)
	{
		if (is_int ( $Permission ))
		{
			$PermissionID = $Permission;
		}
		else
		{
			if (substr($Permission,0,1)=="/")
				$PermissionID=$this->Permissions->PathID($Permission);
			else
				$PermissionID=$this->Permissions->TitleID($Permission);
		}
		if ($UserID === null)
			$UserID = jf::CurrentUser ();
		if ($this->ps_Check===null)
		{
			$this->ps_Check= jf::db ()->prepare ( "SELECT COUNT(*) AS Result
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
				TPdirect.ID=?
			" );
		}
		$this->ps_Check->execute ( $UserID, $Permission );
		$Res = $this->ps_Check [$Index]->fetchAll ();
		
		return $Res [0] ['Result']>=1;
	}
	function Enforce($Permission)
	{
		if (! $this->Check ( $Permission ))
		{
			if (jf::CurrentUser ())
				jf::run ( "view/_internal/error/403", array ("Permission" => $Permission ) );
			else
				jf::run ( "view/_internal/error/401", array ("Permission" => $Permission ) );
			exit ();
		}
	}
}
