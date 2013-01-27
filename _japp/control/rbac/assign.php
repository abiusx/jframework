<?php
class RbacAssignController extends BaseControllerClass
{
	function Start()
	{
		$App=$this->App;
		$View=$this->View;
		# Put your logic here
        
        if (isset($_POST['pid']))
        {
            $Replace=$_POST['Replace'];
            foreach ($_POST['pid'] as $P)
            {
                foreach ($_POST['rid'] as $R)
                {
                    $this->App->RBAC->Assign($R,$P,$Replace);
                }
            }
            $View->Result=count($_POST['rid'])*count($_POST['pid']);
        }
		$View->Permissions=$this->App->RBAC->Permission_All();
		$View->Roles=$this->App->RBAC->Role_All();
		

		$this->Present();
	}
}
?>