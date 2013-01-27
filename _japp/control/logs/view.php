<?php
class LogsViewController extends BaseControllerClass
{
    function Start()
    {
    	if (isset($_POST['DelSeverity']))
    	{
    		j::SQL("DELETE FROM `".reg("jf/log/table/name")."` WHERE `".reg("jf/log/table/Severity")."`<=?",$_POST['DelSeverity']);
    		
    	}
    	if (isset($_POST['DelSubject']))
    	{
    		j::SQL("DELETE FROM `".reg("jf/log/table/name")."` WHERE `".reg("jf/log/table/Subject")."`=?",$_POST['DelSubject']);
    		
    	}
    	if (is_array($_POST['Log']))
    	{
    		foreach ($_POST['Log'] as $L)
    			j::SQL("DELETE FROM `".reg("jf/log/table/name")."` WHERE `".reg("jf/log/table/LogID")."`=?",$L);
    	}

    	if (isset($_POST['DelOffset']))
    	{
    			j::SQL("DELETE FROM `".reg("jf/log/table/name")."` LIMIT {$_POST['DelOffset']},{$_POST['DelLimit']}");
    	}
    	
    	$lim=100;
    	$off=0;
    	if (isset($_GET['lim']))
    		$lim=$_GET['lim']*1;
    	if (isset($_GET['off']))
    		$off=$_GET['off']*1;
    	$this->Logs=j::SQL("SELECT * FROM `".reg("jf/log/table/name")."` ORDER BY `".reg("jf/log/table/Timestamp")."` DESC LIMIT {$off},{$lim}");
    	$this->Offset=$off;
    	$this->Limit=$lim;
        $this->Present();
    }
}
?>
