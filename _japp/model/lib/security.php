<?php
namespace jf;
class SecurityManager extends Model
{
	static $AccessControlFile="__rbac";
	function LoadRbac($RbacModule)
	{
		try {
			jf::run($RbacModule);
		}
		catch (ImportException $e)
		{
			return false;
		}
		return true;
		
	}
	public function AccessControl(Controller $ControllerObject)
	{
		$modulename=$this->ModuleName($ControllerObject);
		$Parts=explode("/",$modulename);
		$n = 0;
		while ( $Parts  )
		{
			$Part=array_pop($Parts);
			$rbac_meta=implode("/",$Parts);
			if (count($Parts)==0) break; //non 
			$rbac_meta .= "/". self::$AccessControlFile;
			if ($this->LoadRbac ( $rbac_meta )) return true;
			if ($rbac_meta == "control") break;
		}
		return false;
		
	}
	
	
	function RandomToken($Length=64)
	{
		return substr(hash("sha512",mt_rand()),0,$Length);
	}
}
?>