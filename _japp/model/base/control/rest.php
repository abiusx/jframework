<?php
namespace jf;
abstract class RestController extends Controller
{
	final function Start()
	{
		$Method=HttpRequest::Method();
		if ($Method=="put")
			return $this->Put();
		elseif ($Method=="get")
			return $this->Get();
		elseif ($Method=="delete")
			return $this->Delete();
		elseif ($Method=="post")
			return $this->Post();
		else
			return false; //bad request
	}
	
	function Put()
	{
		return false;
	}
	function Post()
	{
		return false;
	}
	function Get()
	{
		return false;
	}
	function Delete()
	{
		return false;
	}
} 