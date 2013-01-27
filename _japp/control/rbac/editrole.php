<?php
class RbacEditroleController extends BaseControllerClass
{
	function Start()
	{
		$View=$this->View;
        
        if (isset($_POST['pid']))
        {
            $View->Result=$this->App->RBAC->Role_Edit($_POST['pid'],$_POST['Title'],$_POST['Description']);
        }
		$View->Roles=$this->App->RBAC->Role_All();
        
		$this->Present();
	}
}
?>