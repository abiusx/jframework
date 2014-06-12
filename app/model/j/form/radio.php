<?php
class jFormRadio extends jFormDropdown
{
	
	function Present()
	{
		
		$this->DumpLabel();
		$index=0;
		$isAssoc=$this->IsAssociative($this->Items);
		foreach ($this->Items as $value=>$text)
		{
			if (!$isAssoc) $value=$text;
			if ($this->Value()==$value)
				$checked=true;
			elseif ($this->Value()===null && $index==0) 
				$checked=true;
			else
				$checked=false;
			$index++;
			?>	<input type='radio' <?php $this->DumpClass();?> <?php $this->DumpName();?> id='<?php echo $this->Name()."_".$value;
			?>' value='<?php echo $value;?>'<?php if ($checked) echo " checked='checked' ";?><?php $this->DumpStyle();?>/><label for="<?php echo $this->Name(); 
			?>"><?php echo $text;?></label>
			
<?php
		} 
		$this->DumpDescription();
	}
}

