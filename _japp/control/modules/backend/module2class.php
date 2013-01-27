<?php
class ModulesBackendModule2classController extends BaseControllerClass 
{
    function Start()
    {
        $Class=new jpModule2ClassName($_GET["module"]);
        $x=array_shift(explode(".",$_GET['module']));

        $m=new SystemModulesModel($this->App);
        $s=$m->Template($x,$Class);
        if (!$s)
            $s="Invalid Module or This kind of module doesn't have a template!";   
        
        
        $this->BarePresentString(highlight_string($s,true));
    }
    
}
?>