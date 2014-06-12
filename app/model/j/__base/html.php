<?php

abstract class jWidget_HTML extends FluentInterface
{

	function __get($name)
	{
		if (isset($this->Children[$name]))
			return $this->Children[$name];
	}
	/**
	 * The custom style of the widget
	 * @var string
	 */
	protected $Style;
	/**
	 * Sets custom style for widget
	 * @param string $Style
	 * @param boolean $Append or overwrite
	 */
	function SetStyle($Style, $Append = false)
	{
		if (substr($Style, -1) !== ";")
			$Style .= ";";
		if ($Append)
			$this->Style = $this->Style . $Style;
		else
			$this->Style = $Style;
		return $this;
	}
	/**
	 * Dumps the custom style of an element
	 * using $Style and SetStyle
	 */
	protected function DumpStyle()
	{
		if ($this->Style !== null)
			echo " style='{$this->Style}'";
	}
	/**
	 * Dumps the css section of a widget
	 * based on theme and etc.
	 */
	protected function DumpClass()
	{
		echo "class='jWidget {$this->Class}'";
	}
	/**
	 * Holds the class name of this instance, for convenience
	 * @var string
	 */
	protected $Class = null;

	/**
	 * Returns the header of widget set, e.g scripts and stylesheets
	 */
	final function Header()
	{
		ob_start();
		echo "<script type='text/javascript'>\n";
		$this->JS();
		foreach ($this->Children as $child)
			$child->JS();
		echo "</script>\n";
		echo "<style>\n";
		$this->CSS();
		foreach ($this->Children as $child)
			$child->CSS();
		echo "</style>\n";
		return ob_get_clean();
	}

	/**
	 * Holds the states of first timers
	 * @var array
	 */
	private static $firstTimeCSS = array();
	private static $firstTimeJS = array();
	/**
	 * Tells whether it is the first time this function is called on
	 * ANY CLASS object or not. Useful for one-time scripts and styles
	 *
	 * @param string $class name optional. Usually you should send __CLASS__ to this, otherwise the instance ($this) class would be used.
	 * @return boolean
	*/
	final protected function IsFirstTime($class = null)
	{
		$t = debug_backtrace();
		if ($t[1]['function'] == "JS")
			$arr = &self::$firstTimeJS;
		else
			$arr = &self::$firstTimeCSS;

		if ($class === null)
			$class = $this->Class;
		if (isset($arr[$class]))
			return false;
		else
		{
			$arr[$class] = true;
			return true;
		}
	}
	/**
	 * Called on every widget to dump their CSS and styles
	 */
	protected function CSS()
	{
		if (!$this->IsFirstTime(__CLASS__))
			return;
		?>
<?php
	}
	/**
	 * Called on every widget to dump their javascript code
	 */
	protected function JS()
	{
	}
}