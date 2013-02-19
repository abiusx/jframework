<?php
jf::import("jf/test/lib/rbac/base");
class LibRbacRolesTest extends LibRbacBaseTest
{
	/**
	 * 
	 * @return \jf\RoleManager
	 */
	protected function Instance()
	{
		return jf::$RBAC->Roles;
	}
	
	protected function Type()
	{
		return "role";
	}
	
	function testUnassignPermissions()
	{
		$this->markTestIncomplete();
	}
	
	function testUnassignUsers()
	{
		$this->markTestIncomplete();
	}

	function testHasPermission()
	{
		$this->markTestIncomplete();
	}
	
	function testPermissions()
	{
		$this->markTestIncomplete();
		
	}
	
	
}