<?php 
class FluentInterfaceException extends Exception
{
}
/**
 * Allows FluentInterface chaining and setting and instantiation.
 * e.g if a class inherits this interface, you can use:
 * class::newInstance($a,$b)->SetProperty("123")->SetProperty2("345");
 * 
 * @author abiusx
 * @version 1.0
 */
class FluentInterface
{
	function __invoke($args)
	{
		throw new FluentInterfaceException("Object of ".get_class($this)." can not be evaluated.");
	}
	/**
	 * Creates a new instance and returns it
	 * @return object
	 */
	public static function construct()
	{
		$args = func_get_args();
		$myClass = get_called_class();
		$refl = new ReflectionClass($myClass);
		return $refl->newInstanceArgs($args);
	}
	/**
	 * 
	 * @see FluentInterface::construct
	 * @return mixed
	 */
	public static function newInstance()
	{
		$args = func_get_args();
		$myClass = get_called_class();
		$refl = new ReflectionClass($myClass);
		return $refl->newInstanceArgs($args);
	}
	/**
	 * Fluent Interface chaining magic
	 * @param string $name
	 * @param array $args
	 * @return FluentInterface
	 */
	function __call($name, $args)
	{
		if (strlen($name) > 3 && substr(strtolower($name), 0, 3) == "set")
		{
			$property = substr($name, 3);
			if (count($args)<1)
				throw new FluentInterfaceException(get_class($this) . "::{$name}() called without argument.");
			$this->$property = $args[0];
		}
		return $this;
	}
}