<?php
$Request=null;
if (isset ( $_GET ['__r'] )) //HTTP
{
	$Request = ($_GET ['__r']);
	unset ( $_GET ['__r'] );
}
if (isset($argc) && $argc>1) //SAPI
{
	$Request=$argv[1];
	if (strpos($Request, "?")!==false)
	{
		parse_str(substr($Request,strpos($Request,"?")+1),$_GET); //get params from CLI
		$Request=substr($Request, 0,strpos($Request,"?"));
	}
}

if ($Request!==null)
{
	require_once (dirname ( __FILE__ ) . "/model/j.php"); // jf static
	// module, shortcut
	// to all
	// jframework
	// functionality
	class jf extends \jf\jf
	{
	} // make jf static class known both in jf namespace
	// and in public
	jf::import ( "jf/model/frontcontroller" ); // front controller module
	$jFramework = \jf\FrontController::GetSingleton ();
	try
	{
		$jFramework->Init ( $Request );
	} catch ( Exception $e )
	{
		try
		{
			if (class_exists("\jf\ErrorHandler",/* dont autoload */false) && \jf\ErrorHandler::$Enabled) // if error handler object initiated
			{
				jf::$ErrorHandler->HandleException ( $e );
			}
			else // basic error dump, jf error handler is disabled
			{
				echo $e->getTraceAsString ();
				trigger_error ( $e->getMessage () );
			}
		} catch ( Exception $e ) // error handler popped errors itself
		{
			trigger_error ( $e->getTraceAsString () );
		}
	}
}
else
{
	echo "Nothing to run.\n";
}
