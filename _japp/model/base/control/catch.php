<?php
namespace jf;
abstract class CatchController extends Controller
{
	function Start()
	{
		#FIXME: this should send relative request instead of the whole request
		return $this->Handle(jf::$Request);
	}
	/**
	 * Catched requests are delivered here, with their relative paths
	 * @param string $RelativeRequest
	 */
	abstract function Handle($RelativeRequest);

	/*function Present()
	 {
	$arg=func_get_arg(0); //view module
	#TODO: present the given view
	}*/
}
