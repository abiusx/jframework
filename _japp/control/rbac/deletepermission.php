<?php
class RbacDeletepermissionController extends BaseControllerClass
{
	function Start()
	{
		$View=$this->View;
        
        if (isset($_POST['pid']))
        {
            $Recursive=$_POST['Recursive'];
            foreach ($_POST['pid'] as $ID)
            {
                if ($ID==0)
                    $View->Error="Can not delete root node!";
                $this->App->RBAC->Permission_Remove($ID,$Recursive);
            }
            $View->Result=count($_POST['pid']);
        }
		$View->Permissions=$this->App->RBAC->Permission_All();
        
		$this->Present();
	}
}
?>