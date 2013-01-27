<?php
class JformListPlugin extends BasePluginClass
{
	public $Columns;
	public $ColumnWidth;
	public $PresentHeaders		=	true;
	public $PresentNavigation	=	true;
	public $EditableFields		=	true;
	public $CheckableFields		=	true;
	
	private $UID;
	function __construct($App=null)
	{	
		parent::__construct($App);
		$this->UID=rand(0,100000);
		
	}
	function PresentViaSQL($Table,$Offset=0,$Limit=10000)
	{
		$Offset*=1;
		$Limit*=1;
		return $this->Present(j::SQL("SELECT * FROM {$Table} LIMIT {$Offset},{$Limit}"));
	}
	function Present($List)
	{
		if (!is_array($List))
		return false;
		if ($this->CheckableFields)
			echo "<form method='post'>\n";
		echo "<table width='100%' border='1' cellspacing='0' cellpadding='0'>";
		
		//header
		if ($this->PresentHeaders)
		foreach ($List as $HeadList)
		{
			if (!is_array($HeadList)) return false;
			echo "<thead><tr>\n";
			$n=0;
			if ($this->CheckableFields)
				echo "<th width='10'><input type='checkbox' id='CheckboxPresident_".$this->UID."' onclick=\"\$('.Checkbox_".$this->UID."').attr('checked',\$('#CheckboxPresident_".$this->UID."').attr('checked'))\" /></th>\n";
			foreach ($HeadList as $k=>$H)
			{
				if (is_array($this->ColumnWidth))
					$Width=" width='".$this->ColumnWidth[$n++]."' ";	
				if (is_array($this->Columns))
					echo "<th{$Width}>".strtr($k,$this->Columns)."</th>\n";
				else
					echo "<th{$Width}>{$k}</th>\n";
			}
			if ($this->EditableFields)
				echo "<th>".strtr("Operation",$this->Columns)."</th>\n";
			echo "</tr></thead>\n";
			break;
		}
		
		//content
		foreach ($List as $ListItem)
		{
			echo "<tr align='center'>\n";
			if ($this->CheckableFields)
				echo "<td><input type='checkbox' class='Checkbox_".$this->UID."' name='Item[]' /></td>\n";
			if (is_array($ListItem)) foreach($ListItem as $k=>$L)
			{
				if ($this->EditableFields)
					echo "<td><input style='width:100%;border:0px solid;' type='text' value='".htmlspecialchars($L)."' name='{$k}' /></td>\n";
				else
					echo "<td>".htmlspecialchars($L)."</td>\n";
			}
			if ($this->EditableFields)
				echo "<td><input type='submit' value='".strtr("Submit",$this->Columns)."' /></td></form>\n";
			echo "</tr>\n";
		}
		
		echo "</table>\n";	
		if ($this->CheckableFields)
			echo "<input type='submit'  value='".strtr("Submit",$this->Columns)."'/></form>\n";
	}
	
}

