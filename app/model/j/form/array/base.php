<?php
/**
 * Base container for an array of inputs
 * @author abiusx
 *
 */
abstract class jFormArrayBase extends jFormWidget
{

	public $AllowAdd=true;
	public $AllowRemove=true;
	public $StartCount=1;	
	abstract protected function DumpField($Index=null,$Value=null);
	/**
	 * Sets value of this widget
	 * @param array|mixed $FormData associative array of form data, or a variable providing only value of this instance
	 */
	function SetValue($FormData)
	{
		if (is_array($FormData) && isset($FormData[$this->Name()]) && is_array($FormData[$this->Name()]))
		{
			if (isset($FormData[$this->Name()])) //this widget value is provided
				$this->Value=$FormData[$this->Name()];
			if (!$this->IsTerminal())
			foreach ($this->Children as $child)
				$child->SetValue($FormData); //call setValue on all children
		}
		else
			$this->Value=$FormData;
	}
		
		
	private function DumpFieldBox($Index=null,$Value=null)
	{
		?><div class='dynamic_field'><?php 
		echo $this->DumpField($Index,$Value);
		if ($this->AllowRemove){
		?><img src='<?php echo jf::url();?>/img/jwidget/minus.png' class='icon jFormArrayField_remove' /><?php }?></div><?php
	}
	function Present()
	{
		?><fieldset id='<?php echo $this->Name();?>_container' class='dynamic_container'>
		<legend><?php echo $this->Label;?></legend>
		
		<?php 
		$n=0;
		if (count($this->Value))
			foreach ($this->Value as $v)
				echo $this->DumpFieldBox($n++,$v);
		else
			for ($i=0;$i<$this->StartCount;++$i)
				echo $this->DumpFieldBox($n++);
		?><?php if ($this->AllowAdd){?>
		<button id='<?php echo $this->Name();?>_add' ><img src='<?php echo jf::url();?>/img/jwidget/plus.png'  width='24' class='icon'/> Add field</button>
		<?php }?>
		</fieldset>
		<?php
				
	}
	
	function JS()
	{
		if ($this->IsFirstTime(__CLASS__))
		{
		?>
function removeArrayInput(e)
{
	$(e.target).parent().remove();
}
		<?php
		}
		?>
function addArrayInput_<?php echo $this->Name();?>(e)
{
	$(e.target).before("<?php $this->DumpFieldBox();?>");
	$(".jFormArrayField_remove").bind("click",removeArrayInput);
	return false;
}
$(function(){
	$(".jFormArrayField_remove").bind("click",removeArrayInput);
	$("#<?php echo $this->Name();?>_add").bind("click",addArrayInput_<?php echo $this->Name();?>);
});
<?php 	
	}
	function CSS()
	{
		if ($this->IsFirstTime(__CLASS__))
		{
			?>
.dynamic_container img.icon {
	vertical-align:middle;
	width:20px;
	cursor:pointer;			
}
			<?php
		}
	}
}