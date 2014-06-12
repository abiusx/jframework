<?php
/**
 * Hidden input
 * @author abiusx
 *
 */
class jFormHidden extends jFormWidget
{
	function Present()
	{
		
		?>	<input  type='hidden' <?php $this->DumpAttributes();?>/><?php
	}
	
		
}