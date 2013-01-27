<?php
class RbacDeleteroleController extends BaseControllerClass
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
                $this->App->RBAC->Role_Remove($ID,$Recursive);
            }
            $View->Result=count($_POST['pid']);
        }
		$View->Roles=$this->App->RBAC->Role_All();
        
		$this->Present();
	}
}
?>