<?php
/**
 * One-line password input field for a form
 * @author abiusx
 *
 */
class jFormInputPassword extends jFormInput
{
	function Present()
	{
	
		$this->DumpLabel();
		?>	<input  type='password' <?php $this->DumpAttributes();?>/>
	<?php $this->DumpDescription();
	}
}