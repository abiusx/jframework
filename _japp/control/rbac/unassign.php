<?php
class RbacUnassignController extends BaseControllerClass
{
	function Start()
	{
		$View=$this->View;

		if (isset($_POST['a']))
		{
            foreach($_POST['a'] as $A)
            {
                $A=explode("_",$A);
                if ($A[0]=='0' && $A[1]=='0')
                    $View->Result="Can not unassign root.";
                else
                    $this->App->RBAC->Unassign($A[0],$A[1]);
            }
		}
		$View->Assignments=$this->App->RBAC->Assignments_All(false,$_GET['sort'],$_GET['offset'],$_GET['limit']);
        $View->Count=$this->App->RBAC->Assignments_Count();
        
        $this->Present();
	}
}
?>