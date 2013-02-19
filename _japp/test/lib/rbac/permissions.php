<?php
jf::import("jf/test/lib/rbac/base");
class LibRbacPermissionsTest extends LibRbacBaseTest
{
	/**
	 * 
	 * @return \jf\PermissionManager
	 */
	protected function Instance()
	{
		return jf::$RBAC->Permissions;
	}
	
	protected function Type()
	{
		return "permissions";
	}
	
	
	function testUnassignRoles()
	{
		$this->markTestIncomplete();
	}
	
	function testRoles()
	{
		$this->markTestIncomplete();
	}
}