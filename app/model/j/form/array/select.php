<?php
/**
 * Container for an array of dropdown lists
 * @author abiusx
 *
 */
class jFormArraySelect extends jFormArrayBase
{
	/**
	 * If set, forces associative $Items behavior
	 * @var boolean
	 */
	private $ForceAssoc=false;
	/**
	 * Holds the radio options as key/value pairs
	 * @var array
	 */
	public $Items=array();
	/**
	 * Construct a radio button set
	 * @param jWidget $Parent
	 * @param string $Label
	 * @param array $Items the first element is the default one. If keys are omitted, values serve as both text and data of options.
	*/
	function __construct(jWidget $Parent,$Label=null,array $Items)
	{
		parent::__construct($Parent,$Label);
		$this->Items=$Items;
		$IsAssoc=$this->IsAssociative($Items);
		$this->SetValidation(function ($Data) use ($Items,$IsAssoc) {
			if ($IsAssoc)
			{
				foreach ($Data as $v)
					if (!isset($Items[$v])) return false;
				return true;
			}
			else
				foreach ($Data as $v)
					if (!array_search($v,$Items)) return false;
		});
	}
	protected function IsAssociative($array)
	{
		if ($this->ForceAssoc)
			return true;
		if (array_values($array)===$array) return false;
		return true;
	}
	
	protected function DumpField($Index=null,$Value=null)
	{
		?><select name='<?php echo $this->Name()?>[]' class='<?php
		 echo $this->Class;?>' ><?php 
		 $isAssoc=$this->IsAssociative($this->Items);
		 foreach ($this->Items as $value=>$text)
		 {
		 	if (!$isAssoc) $value=$text;
		 	$selected="";
		 	if ($value==$Value && $Value!==null) $selected=" selected='selected'"
		 	?><option<?php echo $selected;?> value='<?php echo $value;?>' id='<?php echo $this->Name()."_".$value;?>'><?php echo $text;?></option><?php
		 }?></select><?php
	}
}