<?php
class_exists("jWidget"); //autoload all classes

class jListException extends Exception
{
}

class jColumn extends FluentInterface
{
	public function __invoke($args)
	{

		return $this->Key;
	}

	const Type_Action = 1;
	const Type_Data = 0;
	function __construct($key, $title = null)
	{
		if ($title === null)
			$title = $key;
		$this->Key = $key;
		$this->Title = $title;
	}
	/**
	 * Is this column visible?
	 * @var boolean
	 */
	public $Visible = true;
	/**
	 * Is this column sortable?
	 * @var boolean
	 */
	public $Sortable = true;
	/**
	 * Is this column searchable?
	 * @var boolean
	 */
	public $Searchable = true;
	/**
	 * The column key
	 * @var string
	 */
	public $Key = null;
	/**
	 * The column title
	 * @var string
	 */
	public $Title = null;
	/**
	 * Column type. One of Type_ constants
	 * @var integer
	 */
	protected $Type = null;
	/**
	 * Column output filter
	 * @var callable
	 */
	public $Filter = null;
	/**
	 * Width of the column  in css style
	 * @var string
	 */
	public $Width;

	/**
	 * Alignment of the column
	 * @var unknown
	 */
	public $Alignment = "center";
	
	function DumpStyle()
	{
		?> style='text-align:<?php exho($this->Alignment);?>' <?php
		return $this;
	}
	function DumpHeaderStyle()
	{
		
		?> style='width:<?php exho($this->Width);?>' <?php
		return $this;
	}
}

class jDataColumn extends jColumn
{
	/**
	 * Useful for searching in joins, 
	 * where KEY might be duplicate in joined result
	 * @var string
	 */
	public $TableField;
	function __construct($key, $title = null)
	{
		parent::__construct($key, $title);
		$this->Type = jColumn::Type_Data;
		$this->TableField=$key;
	}
}

class jActionColumn extends jColumn
{
	function __construct($key, $title = null)
	{
		parent::__construct($key, $title);
		$this->Type = jColumn::Type_Action;
		$this->Sortable = false;
		$this->Searchable = false;
	}
}

class jList extends jWidget
{
	protected function IsTerminal()
	{
		return true;
	}
	protected function IsRootable()
	{
		return true;
	}

	function __construct($Parent)
	{
		parent::__construct($Parent);

	}

	public $Navigation=jList::Navigation_Range;
	public $Search=true;
	public $DefaultLimit = 20;

	/**
	 * Sets the SQL select statement for this table.
	 * The select should only contain the 'SELECT fields FROM tables' and no conditions or groups or limits 
	 * @param string $sql
	 */
	function SetDataSQL($sql)
	{
		$this->SQL = $sql;
		return $this;
	}
	protected $SQL = null;
	/**
	 * Sets a key as the key for primary column (also used for action columns)
	 * @param string|jColumn $key
	 */
	function SetPrimaryColumn($key)
	{
		if ($key instanceof jColumn)
			$key = $key->Key;
		$this->Primary = $key;
		return $this;
	}
	protected $Primary = null;

	function SetColumns(array $array)
	{
		$this->Columns = array();
		foreach ($array as $r)
			$this->AddColumn($r);
	}
	/**
	 * Holds columns for the list
	 * @var array ListColumn
	 */
	protected $Columns = array();
	function AddColumn(jColumn $column)
	{
		$this->Columns[$column->Key] = $column;
		return $this;
	}

	/**
	 * Returns count of total data
	 * @return integer
	 */
	protected function Count()
	{
		static $count = null;
		if ($count !== null)
			return $count;

		$From = substr($this->SQL, strpos(strtoupper($this->SQL), "FROM") + 5);
		$count = jf::SQL("SELECT COUNT(*) AS Result FROM {$From}");
		$count = $count[0]['Result'];
		return $count;
	}
	/**
	 * Returns the field the list is to be sorted by
	 * default is Primary
	 * @return string
	 */
	protected function SortField($InDatabase=false)
	{
		$r = isset($_GET[$this->Name() . "sort"]) ? $_GET[$this->Name() . "sort"] : $this->Primary;
		if (count(array_filter($this->Columns, function ($column) use ($r)
		{
			if ($column->Key == $r)
				return true;
			else
				return false;
		})) != 1) //validation
			$r = $this->Primary;
		if ($InDatabase)
			return $this->Columns[$r]->TableField;
		else
			return $r;
	}
	public $DefaultOrder="ASC";
	/**
	 * Returns ASC by default, unless the list submitted DESC via post
	 * 
	 */
	protected function SortOrder()
	{
		$r = isset($_GET[$this->Name() . "ord"]) ? strtoupper($_GET[$this->Name() . "ord"]) : $this->DefaultOrder;
		if (!in_array($r, array("ASC", "DESC")))
			$r = $this->DefaultOrder;
		return $r;

	}
	/**
	 * Returns number of rows to return in one query
	 */
	protected function Limit()
	{
		$r = isset($_GET[$this->Name() . "lim"]) ? min($_GET[$this->Name() . "lim"] * 1, 32000) : $this->DefaultLimit;
		return $r * 1;
	}
	/**
	 * Returns starting offset of returned data
	 */
	protected function Offset()
	{
		$r = isset($_GET[$this->Name() . "off"]) ? max(0,min($_GET[$this->Name() . "off"] * 1, $this->Count() - $this->Limit())) : 0;
		return $r * 1;

	}
	/**
	 * Returns search string
	 * @return string|null
	 */
	protected function Search()
	{
		$r = isset($_GET[$this->Name() . "search"]) ? $_GET[$this->Name() . "search"]  : null;
		return $r;
	}
	/**
	 * Return search field
	 * @param $InDatabase if set, returns the field in database (For sql queries)
	 * @return string
	 */
	protected function SearchField($InDatabase=false)
	{
		$r = isset($_GET[$this->Name() . "field"]) ? $_GET[$this->Name() . "field"] : $this->Primary;
		if (count(array_filter($this->Columns, function ($column) use ($r)
		{
			if ($column->Key == $r)
				return true;
			else
				return false;
		})) != 1) //validation
			$r = $this->Primary;
		if ($InDatabase)
			return $this->Columns[$r]->TableField;
		else
			return $r;
	}
	/**
	 * Returns data to feed the list
	 */
	protected function Data()
	{
		if ($this->__data) return $this->__data;
		$sql = $this->SQL;
		if ($r=$this->Search())
		{
			$sql .= " WHERE {$this->SearchField(true)} LIKE ? ORDER BY {$this->SortField(true)} {$this->SortOrder()} LIMIT {$this->Offset()},{$this->Limit()}";
			return $this->__data=jf::SQL($sql,str_replace("*","%",$r));
		}
		else
		{
			$sql .= " ORDER BY {$this->SortField(true)} {$this->SortOrder()} LIMIT {$this->Offset()},{$this->Limit()}";
			return $this->__data=jf::SQL($sql);
		}
	}
	
	
	
	protected function Row($data)
	{
?>
<tr>
	<?php foreach ($this->Columns as $key => $Column)
		{
	?>
	<td <?php $Column->DumpStyle(); ?>><?php
			if ($Column->Visible)
			{
				if ($Column instanceof jDataColumn)
				{
					if (!isset($data[$Column->Key]) && $data[$Column->Key]!==null)
						throw new jListException("Key not found in data: " . exho($Column->Key, true));
					if ($Column->Filter)
					{
						$t = $Column->Filter;
						echo ($t(exho($data[$Column->Key],true)));
					}
					else
						exho($data[$Column->Key]);
				}
				elseif ($Column instanceof jActionColumn)
				{
					if (!$Column->Filter)
						throw new jListException("Filter not set for action column: " . exho($Column->Key, true));
					$t = $Column->Filter;
					echo ($t(exho($data[$this->Primary],true)));
				}
			}

									   ?></td>
	<?php
											   }
	?>
</tr>
<?php
	}
	
	const Navigation_Pages=1;
	const Navigation_PageSteps=3;
	const Navigation_Range=2;
	
	private function DumpPageLink($page)
	{
		?><a href='?<?php exho ($this->Name());?>sort=<?php exho ($this->SortField());?>&<?php exho ($this->Name());?>ord=<?php exho ($this->SortOrder());
						?>&<?php exho ($this->Name());?>off=<?php exho ($page*$this->Limit());?>&<?php exho($this->Name()); ?>lim=<?php exho ($this->Limit());?>'><?php exho ($page+1);?></a>
						<?php
	}
	function PresentNavigation($Mode=null)
	{
		if ($Mode===null)
			if (!$this->Navigation) return;
			else $Mode=$this->Navigation;
				
		
		?><div class='jListNavigation'><?php 
		if ($Mode==jList::Navigation_Pages)
		{
			for ($i=0;$i<=$this->Count()/$this->Limit();++$i)
			{
				if ($this->Offset()>=$i*$this->Limit() && $this->Offset()<($i+1)*$this->Limit())
				{
					?><span style='font-weight:bold;'><?php exho($i+1);?></span> <?php 
					
				}
				else
					$this->DumpPageLink($i);	
			}
		}
		elseif ($Mode==jList::Navigation_PageSteps)
		{
			$pageCount=floor($this->Count()/$this->Limit());
			$currentPage=floor($this->Offset()/$this->Limit());
			$range=5;
			
			if ($currentPage>0)
				$this->DumpPageLink(0);
			for ($i=1;$i<$currentPage;$i*=10)
			{
				for ($k=1;$k<=round($range/2);++$k)
				{
					$page=$k*$i-1;
					if ($page>=$currentPage or $page==0) continue;
					$this->DumpPageLink($page);
				}
				for ($k=10-round($range/2)-1;$k<10;++$k)
				{
					$page=$k*$i-1;
					if ($page>=$currentPage or $page==0) continue;
					$this->DumpPageLink($page);
				}
			}
			exho ($currentPage+1)." ";
			for ($i=1;$i<$pageCount-$currentPage;$i*=10)
			{
				for ($k=1;$k<=$range;++$k)
				{
					$page=$i*$k+$currentPage-1;
					if ($page<=$currentPage or $page>=$pageCount) continue;
					$this->DumpPageLink($page);
				}
			}
			if ($pageCount-1>$currentPage)
				$this->DumpPageLink($pageCount-1);
		}
		elseif ($this->Navigation==jList::Navigation_Range)
		{
			?>
			<form method='get'>
			<input type='hidden' name='<?php exho ($this->Name());?>sort' value='<?php exho ($this->SortField());?>' />
			<input type='hidden' name='<?php exho ($this->Name());?>ord' value='<?php exho ($this->SortOrder());?>' />
			<?php if ($this->Search()){?>
			<input type='hidden' name='<?php exho ($this->Name());?>search' value='<?php exho ($this->Search());?>' />
			<input type='hidden' name='<?php exho ($this->Name());?>field' value='<?php exho ($this->SearchField());?>' />
			<?php }?>
			Show 
			<input type='text' name='<?php exho ($this->Name());?>lim' value='<?php exho ($this->Limit());?>' />
			items starting from 
			<input type='text' name='<?php exho ($this->Name());?>off' value='<?php exho (max(0,min($this->Offset()+$this->Limit(),$this->Count()-$this->Limit())));?>' />
			<input type='submit' value='Go' />
			(Total <?php exho ($this->Count());?>)
			</form>
			<?php 
		}
		?></div><?php 

	}
	/**
	 * Outputs the search box
	 */
	function PresentSearch()
	{
		if (!$this->Search) return;
		?>
		<fieldset class='jListSearch'>
		<legend>Search</legend>
			<form method='get'>
			<input type='hidden' name='<?php exho ($this->Name());?>sort' value='<?php exho ($this->SortField());?>' />
			<input type='hidden' name='<?php exho ($this->Name());?>ord' value='<?php exho ($this->SortOrder());?>' />
			<input type='hidden' name='<?php exho ($this->Name());?>lim' value='<?php exho ($this->Limit());?>' />
			<input type='text' name='<?php exho ($this->Name());?>search' value='<?php exho ($this->Search());?>' />
			in
			<select name='<?php exho ($this->Name());?>field'>
		<?php
		foreach ($this->Columns as $column)
			if ($column->Searchable)
			{
				?>
				<option <?php
				if ($this->SearchField()==$column->Key)
				{
					?> selected='selected' <?php 
				}
				?>value='<?php exho ($column->Key);?>'><?php exho ($column->Title);?></option>
				<?php 
			}		
		?>
			</select>
			<input type='submit' value='Go' />
			<a href='?'>Reset</a>
			</form>	
		</fieldset><?php 
		
	}

	
	/**
	 * Outputs everything, search box, navigation box, data table
	 * @see jWidget::Present()
	 */
	function Present()
	{
		if ($this->Primary === null)
			throw new jListException("Primary column not set. Use SetPrimaryColumn()");
		if ($this->SQL === null)
			throw new jListException("SQL not set for this list. Use SetDataSQL()");
		$this->PresentSearch();
		if ($this->Limit()>=100)
			$this->PresentNavigation();
?>
<table class='jListTable'>
	<thead>
		<tr>
			<?php foreach ($this->Columns as $column)
		{
			?>
			<th <?php $column->DumpHeaderStyle();?>><?php
			if ($column->Sortable)
			{
				?> <a
				href='?<?php exho($this->Name()); ?>sort=<?php exho (urlencode($column->Key)); ?>&<?php exho($this->Name()); ?>ord=<?php
								if ($this->SortField() == $column->Key)
								{
									if ($this->SortOrder() == "ASC")
										exho ("desc");
									else
										exho ("asc");
								}
								else
									exho ("asc");
				?>&<?php exho($this->Name());?>off=<?php exho($this->Offset());
				?>&<?php exho($this->Name());?>lim=<?php exho($this->Limit());
				if ($this->Search())
				{
					?>&<?php exho($this->Name());?>search=<?php exho($this->Search());
					?>&<?php exho ($this->Name());?>field=<?php exho($this->SearchField());
				} 
				?>'><?php
				if ($this->SortField() == $column->Key)
				{
					?><span	style='font-style: italic;'><?php exho($column->Title); ?></span><?php
				}
				else																																								
				{
					exho($column->Title);
				}
				?></a><?php
			}
			else
				exho($column->Title);
				?></th>
			<?php
		}
			?>
		</tr>
	</thead>
	<tbody>
		<?php
		if (count($this->Data()))
		foreach ($this->Data() as $d)
			$this->Row($d);
		else
		{
			$visibleColumns=$this->VisibleCount();
			?><tr style='background-color:inherit;'><td colspan='<?php exho($visibleColumns);?>' style='text-align:center;padding:10px;' ><strong>No data available.</strong></td></tr>
			<?php 
		}
		?>
	</tbody>
</table>
<?php
		$this->PresentNavigation();
	}
	function VisibleCount()
	{
		return array_reduce($this->Columns,function($d1,$d2)
				{
					return $d1+($d2->Visible?1:0);
				},0);
	}
	function CSS()
	{
		if (!$this->IsFirstTime(__CLASS__)) return;
		?>
		.jListTable {
			border:1px solid;
			width:100%;
			border-spacing:0;
			border-collapse:collapse;
		}
		.jListTable td {
			padding:3px;
		}
		.jListTable th {
			background-color:black;
			color:white;
			padding-bottom:5px;
			border-bottom:1px solid;
		}
		.jListTable tr:nth-child(2n+1) {
			background-color:gray;
		}
		.jListNavigation {
			font-size:smaller;
			width:100%;
			text-align:center;
			margin:5px;
		}
		.jListNavigation input[type='text'] {
			border:0px;
			background-color:inherit;
			width:50px;
			text-align:center;
			color:inherit;
			font-weight:bold;
		}
		.jListSearch {
			padding:5px;
			margin:5px;
			width:auto;
			/*float:right;*/
		}
		.jListSearch input[type='text'] {
			border:1px solid gray;
			/*width:150px;*/
			text-align:center;
		
		}
		<?php 	
	}
		

}
