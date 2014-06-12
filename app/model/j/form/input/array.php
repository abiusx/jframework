<?php
/**
 * Container for an array of inputs
 * @author abiusx
 *
 */
class jFormInputArray extends jWidget
{

	function __construct(jWidget $Parent,$Label=null)
	{
		parent::__construct($Parent,$Label);
		$this->SetValidation('/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/');
		
	}
}