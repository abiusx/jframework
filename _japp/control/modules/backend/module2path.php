<?php
class ModulesBackendModule2pathController extends BaseControllerClass 
{
    function Start()
    {
        if ($_GET['issystem']=="true") 
            $sys=true;
        else
             $sys=false;
        $x=new jpModule2FileSystem($_GET["module"],$sys);
        $this->BarePresentString($x);
    }
    
}
?>