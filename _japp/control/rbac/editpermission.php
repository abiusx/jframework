<?php
class RbacEditpermissionController extends BaseControllerClass
{
	function Start()
	{
		$View=$this->View;
        
        if (isset($_POST['pid']))
        {
            $View->Result=$this->App->RBAC->Permission_Edit($_POST['pid'],$_POST['Title'],$_POST['Description']);
        }
		$View->Permissions=$this->App->RBAC->Permission_All();
        
		$this->Present();
	}
}
?>