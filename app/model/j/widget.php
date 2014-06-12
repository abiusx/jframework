<?php
require_once(__DIR__."/__base/autoload.php");

class NameAlreadyUsedException extends Exception
{
}

class TerminalAddChildException extends Exception
{
}

/**
 * Base class for jframework widget set
 * @author abiusx
 * @version 0.5
 */
abstract class jWidget extends jWidget_HTML
{
	function __invoke($args)
	{
		$this->Present($args);
	}

	public $Parent = null;

	/**
	 * Is this a terminal widget, i.e it can not have children and can not be used as other's parent?
	 */
	protected abstract function IsTerminal();
	/**
	 * Is this a rootable widget, i.e it can have no parent and be stand-alone?
	 */
	protected abstract function IsRootable();

	/**
	 * Is this the root widget, i.e its parent is null?
	 * @var boolean
	 */
	final protected function IsRoot()
	{
		return $this->Parent === null;
	}

	/**
	 * Used when in manual naming mode
	 * Usually used before calling parent constructor
	 * @param string $Name
	 */
	final protected function __setname($Name)
	{
		if (isset(self::$widgetInfo[$Name]))
			throw new NameAlreadyUsedException(
					"Name used for widget object at {$file}:{$line} already used for another widget at " . self::$widgetInfo[$Name]['file'] . ":" . self::$widgetInfo[$Name]['line']);
		$this->m_name = $Name;
		$t = debug_backtrace();
		self::$widgetInfo[$Name] = array("class" => $t[1]['class'] ? : "", "file" => $t[1]['file'], "line" => $t[1]['line']);
	}
	/**
	 * Construct and set parent widget
	 * $Parent can be null if its a rootable widget, but it is not default so that derived classes don't forget to
	 * call this constructor
	 * @param jWidget $Parent or null
	 */
	function __construct($Parent)
	{
		if ($Parent !== null and !is_a($Parent, "jWidget"))
			throw new Exception("Argument 1 passed to jWidget::__construct() must be an instance of jWidget or null.");
		$this->Name(); //detect the name
		$this->Parent = $Parent;
		if (is_subclass_of($Parent, "jWidget"))
		{
			$this->Parent->AddChild($this);
		}
		else
		{
			$this->Parent = null;
			$this->IsRoot = true;
		}
		$this->Name(); //generate and cache name

		$this->Class = get_class($this);

	}

	/**
	 * Remove from widget inventory static array, so that this name can be used again
	 */
	function __destruct()
	{
		//it can not be destructed as long as it has any children pointing to it as their parent
		unset(self::$widgetInfo[$this->Name()]);
	}
	/**
	 * Deletes the object references, causing destructor to be called after the variable is invalidated.
	 * The common way of using this is to call it on a widget, then unset the widget.
	 * Do not use the widget after calling this. May result in unknown errors.
	 */
	function Destroy()
	{
		$this->Parent = null;
		foreach ($this->Children as $child)
		{
			$child->Destroy();
		}
		$this->Children = null;
	}
	/**
	 * Children of this widget
	 *
	 * @var array of jWidget
	 */
	protected $Children = array();
	/**
	 * Add a child to this widget. 
	 * This function should not be called directly, instead send this widget as parameter to constructor
	 * of new widgets, and they use this function to register them in their parent. 
	 * @param jWidget $Widget
	 */
	final protected function AddChild(jWidget $Widget)
	{
		$t = (debug_backtrace());
		if (!($t[1]['class'] == "jWidget" and $t[1]['function'] == "__construct"))
			throw new Exception("Add child is implicitly called by jWidget constructors. You should not manually call it.");
		if ($this->IsTerminal())
			throw new TerminalAddChildException("Widget {$this->Name()} of type " . get_class($this) . " is terminal, i.e it can not contain children.");
		$this->Children[$Widget->Name()] = $Widget;
	}

	/**
	 * Internal variables used by ParseName
	 * This one holds widget information, stored by their name as the key. Holds class, file and line of instantiation
	 * @var array
	 */
	public static $widgetInfo = array();
	/**
	 * This function gets the variable name of a widget, to be used as its name
	 * e.g when you use $form1=new jForm($this); , this function returns "form1" as the result
	 * @throws Exception
	 * @return string
	 */
	final private function ParseName()
	{
		$staticMode = false;
		//first lets find the file who created the widget
		$backtrace = debug_backtrace();
		$backtraceIndex = 2; //omit ParseName and Name on this
		//step over all subclasses of jWidget, who called parent constructor to get the name done. Also other methods of this class
		while ((isset($backtrace[$backtraceIndex]['class']) and is_a($backtrace[$backtraceIndex]['class'], "jWidget", true) and ($backtrace[$backtraceIndex]['function'] == "__construct"))
		//new class() 
 or ($backtrace[$backtraceIndex]['function'] == "newInstanceArgs" and $backtrace[$backtraceIndex]['class'] == "ReflectionClass")
		//class::newInstance()
		)
			$backtraceIndex++;
		if (($backtrace[$backtraceIndex - 1]['function'] == "newInstanceArgs" and $backtrace[$backtraceIndex - 1]['class'] == "ReflectionClass"))
			$staticMode = true;
		//instantiated using FluentInterface, not old school new object().

		if (!$staticMode)
			$backtraceIndex--;
		$file = $backtrace[$backtraceIndex]['file'];
		$line = $backtrace[$backtraceIndex]['line']; //used in error messages

		$classname = get_class($this);


		$php_code = file_get_contents($file);
		$lines = explode("\n", $php_code);
		$desiredLine = $lines[$line - 1];
		$desiredLine = "<" . "?php " . $desiredLine;
		$tokens = token_get_all($desiredLine);
		$count = count($tokens);
		$offset=null;
		for ($i = 0; $i < $count; $i++)
		{
			if ($staticMode) //object is instantiated in FluentInterface mode, not classic object instantiation. No NEW method available
			{
				if ($tokens[$i][0] === T_DOUBLE_COLON) //found ::
				{
					//go back to find the classname
					$j = 1;
					while ($tokens[$i - $j][0] == T_WHITESPACE)
						$j++;
					if ($tokens[$i - $j][1] == $classname) //if desired class found, set flag to find variable later
						$offset=$j+1;						
				}
			}
			else
			{
				if ($tokens[$i][0] === T_NEW) //found the "new" keyword
				{
					//go forth until you find the classname
					$j = 1;
					while ($tokens[$i + $j][0] == T_WHITESPACE)
						$j++;
					if ($tokens[$i + $j][1] == $classname) //if desired class found, set flag to find variable later
						$offset=1;
				}
			}
			if ($offset) //already found, extract variable name
			{

				//go back until you find the assignment sign
				$j = $offset;
				while (($i-$j)>0 and $tokens[$i - $j]!="=")
					$j++;
				if ($tokens[$i - $j] != "=") //go until you reach = sign
					throw new Exception("You should instantiate jWidget by assigning it to a variable, e.g \$someWidget=new jWidget(\$this);
							in file {$file} line {$line} ");
				//go furthur back until you find the variable name
				$j++;
				while ($tokens[$i - $j][0] == T_WHITESPACE) //omit whitespaces
					$j++;
				if ($tokens[$i - $j][0] != T_VARIABLE)
					throw new Exception(
							"Could not find variable: You should instantiate jWidget by assigning it to a variable, e.g \$someWidget=new jWidget(\$this);
							in file {$file} line {$line}");
				$variableName = $tokens[$i - $j][1];
				$variableName = substr($variableName, 1); //remove the $ sign
				//check if this name already used on another widget
				if (isset(self::$widgetInfo[$variableName]))
					throw new NameAlreadyUsedException(
							"Name used for widget object at {$file}:{$line} already used for another widget at " . self::$widgetInfo[$variableName]['file'] . ":"
									. self::$widgetInfo[$variableName]['line']);
				//store this new widget information
				self::$widgetInfo[$variableName] = array("class" => $classname, "file" => $file, "line" => $line);
				return $variableName;
			}

		}
		//this should not happen!
		throw new Exception("Could not find appropariate jWidget instanciation in {$file}:{$line} (Maybe you forgot to call parent::__construct(\$Parent) in your widget constructor?)");
	}

	/**
	 * Holds the name of a widget, used for caching
	 * because parsing of name from file is heavy
	 * @var string
	 */
	private $m_name = null;
	/**
	 * Returns name of this widget
	 * @return string
	 */
	final function Name()
	{
		if ($this->m_name === null)
		{
			return $this->m_name = $this->ParseName();
		}
		return $this->m_name;
	}

	/**
	 * Should output this widget and all its children
	 */
	abstract function Present();

	/**
	 * Present all children widgets by calling their present.
	 * This is a convenient function usually called within Present after outputing header and before footer of current
	 * widget
	 * @param boolean $Containers whether or not to wrap the children in a div container 
	 */
	final protected function PresentChildren($Containers = true)
	{
		foreach ($this->Children as $child)
		{
			if ($Containers)
				echo "<div class='jWidget_container' id='{$child->Name()}_container'>\n";
			$child->Present();
			if ($Containers)
				echo "</div><!-- {$child->Name()} container -->\n";
		}
	}
}
