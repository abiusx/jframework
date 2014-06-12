<?php
/**
 * One-line text input field for a form
 * @author abiusx
 *
 */
class jFormTextarea extends jFormWidget
{
	
	function Present()
	{
		
		$this->DumpLabel();
		?>	<textarea <?php $this->DumpAttributes();?>><?php exho ($this->Value())?></textarea>
<?php $this->DumpDescription();
	}
	
		
}