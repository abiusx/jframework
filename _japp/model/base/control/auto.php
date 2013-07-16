<?php
namespace jf;
/**
 * AutoController automatically presents the corresponding view
 * it is intended for controllers with no code.
 *
 */
class AutoController extends Controller
{

	function __construct()
	{
		$this->View=new View();
	}
	/**
	 * Provide the view module to this and it presents it.
	 * @see \jf\Controller::Start()
	 */
	function Start()
	{
		$arg = func_get_arg ( 0 );
		return $this->View->Present ( $this->ViewModule($arg) );
	}
}