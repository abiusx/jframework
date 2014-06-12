<?php
class jElement extends jWidget
{
	public $Attributes="";
	public $Data=null;
	public $Tag=null;
	function __construct($Parent,$Tag,$Data=null,$Attributes=array())
	{
		parent::__construct($Parent);
		$this->Tag=$Tag;
		$this->Data=$Data;
		$this->Attributes=$Attributes;
	}
	function Present()
	{
		$attribStrings=array();
		if (count($this->Attributes))
			foreach($this->Attributes as $k=>$a)
		{
			$attribStrings[]="{$k}='{$a}'";
		} 
		$attribString=implode(" ",$attribStrings);
		if ($attribString)
			$attribString=" ".$attribString;
		echo "<{$this->Tag}{$attribString}>";
		$this->PresentChildren(false);
		echo $this->Data;
		echo "</{$this->Tag}>";
			
	}
	function IsTerminal(){
		return false;
	}
	function IsRootable(){
		return false;
	}
}