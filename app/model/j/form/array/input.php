<?php
/**
 * Container for an array of inputs
 * @author abiusx
 *
 */
class jFormArrayInput extends jFormArrayBase
{
	public $Labels=array();
	
	/**
	 * 
	 * @param jWidget $Parent
	 * @param string $Label
	 * @param array $Labels array of values depicting labels
	 */
	function __construct(jWidget $Parent,$Label=null,array $Labels=array())
	{
		parent::__construct($Parent,$Label);
		$this->Labels=$Labels;
		if (count($Labels)>1)
			$this->StartCount=count($Labels);
	}
	protected function DumpField($Index=null,$Value=null)
	{
		if (isset($this->Labels[$Index])) { ?><label class='jWidget_label'><?php exho ($this->Labels[$Index]);?></label><?php }?><input  type='text' name='<?php echo $this->Name()?>[]' <?php $this->DumpClass(); $this->DumpStyle(); $this->DumpValue($Index)?> /><?php
	}
	/**
	 * Dumps value of a form input
	 */
	protected function DumpValue()
	{
		$Index=func_get_arg(0);
		if (is_array($this->Value) && isset($this->Value[$Index]))
		{
		?>	value='<?php exho($this->Value[$Index]);?>' <?php
		}
		elseif ($this->Value()!==null && is_string($this->Value()))
			{
		?>	value='<?php exho($this->Value());?>' <?php
			}
	}		
}