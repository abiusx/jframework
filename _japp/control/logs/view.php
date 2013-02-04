<?php
class LogsViewController extends JControl
{
    function Start()
    {
    	if (isset($_POST['DelSeverity']))
    	{
    		jf::SQL("DELETE FROM {$this->TablePrefix()}logs WHERE Severity<=?",$_POST['DelSeverity']);
    		
    	}
    	if (isset($_POST['DelSubject']))
    	{
    		jf::SQL("DELETE FROM {$this->TablePrefix()}logs WHERE Subject=?",$_POST['DelSubject']);
    		
    	}
    	if (is_array($_POST['Log']))
    	{
    		foreach ($_POST['Log'] as $L)
    			jf::SQL("DELETE FROM {$this->TablePrefix()}logs WHERE ID=?",$L);
    	}

    	if (isset($_POST['DelOffset']))
    	{
    			jf::SQL("DELETE FROM {$this->TablePrefix()}logs LIMIT {$_POST['DelOffset']},{$_POST['DelLimit']}");
    	}
    	
    	$lim=100;
    	$off=0;
    	if (isset($_GET['lim']))
    		$lim=$_GET['lim']*1;
    	if (isset($_GET['off']))
    		$off=$_GET['off']*1;
    	$this->Logs=jf::SQL("SELECT * FROM {$this->TablePrefix()}logs ORDER BY Timestamp DESC LIMIT {$off},{$lim}");
    	$this->Offset=$off;
    	$this->Limit=$lim;
        $this->Present();
    }
}
?>
