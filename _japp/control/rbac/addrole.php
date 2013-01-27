<?php
class RbacAddroleController extends BaseControllerClass
{
	function Start()
	{
	    $App=$this->App;
		$View=$this->View;
        
        if (isset($_POST['pid']))
        {
            $View->Result=$this->App->RBAC->Role_Add($_POST['Title'],$_POST['Description'],$_POST['pid']);
        }
		$View->Roles=$this->App->RBAC->Role_All();
        
		$this->Present();
	}
}
?>