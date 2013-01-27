<?php
class RbacAddpermissionController extends BaseControllerClass
{
	function Start()
	{
	    $App=$this->App;
		$View=$this->View;
		# Put your logic here
        
        if (isset($_POST['pid']))
        {
            $View->Result=$this->App->RBAC->Permission_Add($_POST['Title'],$_POST['Description'],$_POST['pid']);
        }
		$View->Permissions=$this->App->RBAC->Permission_All();
        
		$this->Present();
	}
}
?>