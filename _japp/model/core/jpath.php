<?php
# the way jPath works, consumes lots of memory and CPU. think of sth
/*
	- We have 4 kinds of jPaths:
	
	1. Request Path e.g app.main.ai
	2. Resource Path e.g script.jquery.132.js
	3. Module Path e.g control.main.ai
	4. FileSystem Path e.g /etc/htdocs/file1.php
	
	- The first two are used to recognize jFramework through the outside world,
	Module Path is used to point to jFramework modules
	FileSystem paths are used to refer directly to files in the filesystem of the web server.

	- Along with these paths, we have a ClassName convention based on ModulePaths, e.g
	
	control.main.ai maps to MainAiController
					and 
	model.scripting maps to ScriptingModel

	- The following classes and constants are used to handle these four paths and their conversions
	
 */
/**
 * jPath Fundumental Class
 * for a basic understanding of jFramework paths, browse /_japp/model/core/jpath.php file
 * @version 1.9
 */
define ( "jf_jPath_System_Folder", "_japp" );
define ( "jf_jPath_Application_Folder", "app" );
define ( "jf_jPath_Module_Extension", ".php" );
# General Delimiters
define ( "jf_jPath_Request_Delimiter", "/" ); # e.g app.main.ai, can't use "/" yet due to a bug XXX 
define ( "jf_jPath_Resource_Delimiter", "/" ); # e.g script.jquery.132.js
define ( "jf_jPath_Module_Delimiter", "." ); # e.g model.main.hello

if (isset($_SERVER['SERVER_SOFTWARE']))
{
	if (strpos($_SERVER['SERVER_SOFTWARE'],"Win"))
		define ( "jf_jPath_FileSystem_Delimiter", "\\" ); # e.g /file/1 
	else //unix
		define ( "jf_jPath_FileSystem_Delimiter", "/" ); # e.g /file/1 
}
else
{
		define ( "jf_jPath_FileSystem_Delimiter", "/" ); # e.g /file/1 
}
/**
 * Note:
 * Since you might want to change these Delimiters on different applications or even different deployments,
 * There are custom Delimiters for each of the above. For example you might like to use "." in one case as Module
 * Delimiter and "::" in another case, but you'll have to change all your code whenever you change that, since
 * you might have done e.g LoadModule("model::main::hello"); and you should change it to LoadModule("model.main.hello");
 * 
 * That's really inconvenient, So there's an interface to that, You change your desired custom Delimiter below, and do
 * this:
 * LoadModule(jpCustom2Module("model.main.hello","."));
 *
 */
define ( "jf_jPath_Request_Custom_Delimiter", "." );
define ( "jf_jPath_Resource_Custom_Delimiter", "." );
define ( "jf_jPath_Module_Custom_Delimiter", "." ); # e.g model.main.hello

/**
 * Abstract Base jPath Convertion Class
 * @copyright AbiusX
 * @author abiusx@jframework.info
 * @version 2.3
 */
abstract class jpBase
{
	private static $_Root;
	public static $FileEscaping = array(
		"_colon_"=>":",
		"_leftp_"=>"(",
		"_rightp_"=>")",
//		"_dot_php"=>".php",
//		"_slash_"=>"/",
		"_backslash_"=>"\\",
		"_tilde_"=>"~",
	);
	public static $ClassnameEscaping = array(
		"_colon_"=>":",
		"_dash_"=>"-",
		"_leftp_"=>"(",
		"_rightp_"=>")",
		"_underline_"=>"_",
		"_plus_"=>"+",
		"_at_"=>"@",
		"_sharp_"=>"#",
		"_exclaimation_"=>"!",
//		"_dot_php"=>".php",
		"_slash_"=>"/",
		"_backslash_"=>"\\",
		"_tilde_"=>"~",
	);
	public static $ClassnameEscapingReversed;
	public static $FileEscapingReversed;
	
	
	public function FileUnescape(&$String)
	{
		$String=strtr($String, jpRoot::$FileEscaping);	
	}
	public function FileEscape(&$String)
	{
		$String=strtr($String, jpRoot::$FileEscapingReversed);	
	}
	public function ClassnameUnescape(&$String)
	{
		$String=strtr($String, jpRoot::$ClassnameEscaping);	
	}
	public function ClassnameEscape(&$String)
	{
		$String=strtr($String, jpRoot::$ClassnameEscapingReversed);	
	}
	
	/**
	 * Filesystem Root of the jFramework on the local file system (without trailing slash)
	 *
	 * @return String RootFolder
	 */
	protected function Root()
	{
		if (! jpBase::$_Root)
		{
			$Folder = dirname ( __FILE__ );
			$fa = explode ( constant("jf_jPath_FileSystem_Delimiter"), $Folder );
			$n=0;
			do
			{
				$n++;
				$x = array_pop ( $fa );
			} while ( $x != constant ( "jf_jPath_System_Folder" ) && $n<10000);
			if ($n>=10000)
				trigger_error("jFramework jPath core error: can not determine your OS type! jFramework should not work properly.");
			jpBase::$_Root = implode ( constant("jf_jPath_FileSystem_Delimiter"), $fa );
		}
		return jpBase::$_Root;
	}
	protected $Path;

	public function __toString()
	{
		return $this->Path;
	}

	function __construct()
	{
		if (!is_array(jpRoot::$FileEscapingReversed))
			jpRoot::$FileEscapingReversed=array_flip(jpRoot::$FileEscaping);
		if (!is_array(jpRoot::$ClassnameEscapingReversed))
			jpRoot::$ClassnameEscapingReversed=array_flip(jpRoot::$ClassnameEscaping);
		$a = func_get_args ();
		return call_user_func_array ( array (
			$this, "Convert" 
		), $a );
	}
	/**
	 * Spilits a $Path using $Del1 and merges it using $Del2, then returns the result appended by $Append
	 * $Path can itself be a spilited array
	 * 
	 * @param String $Path or Splited Array
	 * @param String $DelSplit split delimiter
	 * @param String $DelMerge merge delimiter
	 * @param String $Append
	 * @param String $Prepend
	 * @return String
	 */
	protected function _Convert($Path, $DelSplit = "", $DelMerge = "", $Append = null, $Prepend = null)
	{
		if (! is_array ( $Path ))
		{
			if ($DelSplit==$DelMerge)
				return $this->Path = $Prepend . $Path  . $Append;
			else
			{
				return $this->Path=$Prepend. str_replace($DelSplit,$DelMerge,$Path).$Append;
			}
		}
		else return $this->Path = $Prepend . implode ( $DelMerge, $Path ) . $Append;
	}
}


class jpCustom2Module extends jpBase
{
	function Convert($CustomPath, $CustomPathDelimiter = jf_jPath_Module_Custom_Delimiter)
	{
		return $this->_Convert ( $CustomPath, $CustomPathDelimiter, constant ( "jf_jPath_Module_Delimiter" ) );
	}
}


class jpCustom2Resource extends jpBase
{

	function Convert($CustomPath, $CustomPathDelimiter = jf_jPath_Resource_Custom_Delimiter)
	{
		//fixing file extension 
		if ($CustomPathDelimiter == ".")
		{
			$a = explode ( ".", $CustomPath );
			$ext = array_pop ( $a );
			$ext = "." . $ext;
			$CustomPath = $a;
		}
		return $this->_Convert ( $CustomPath, $CustomPathDelimiter, constant ( "jf_jPath_Resource_Delimiter" ), $ext );
	}
}


class jpCustom2Request extends jpBase
{

	function Convert($CustomPath, $CustomPathDelimiter = jf_jPath_Request_Custom_Delimiter)
	{
		return $this->_Convert ( $CustomPath, $CustomPathDelimiter, constant ( "jf_jPath_Request_Delimiter" ) );
	}
}


class jpResource2FileSystem extends jpBase
{
	public static $Prefix = array (
		"img" => "images", "file" => "files", "script" => "script", "style" => "style" 
	);

	function Convert($ResourcePath)
	{
		$this->FileEscape($ResourcePath);
		if (constant ( "jf_jPath_Resource_Delimiter" ) == ".")
		{
			$a = explode ( ".", $ResourcePath );
			$ext = array_pop ( $a );
			$ext = "." . $ext;
		}
		else
			$a = explode ( constant ( "jf_jPath_Resource_Delimiter" ), $ResourcePath );
		
		if (array_key_exists ( $a [0], jpResource2FileSystem::$Prefix )) $a [0] = jpResource2FileSystem::$Prefix [$a [0]];
		return $this->_Convert ( $a, "", constant("jf_jPath_FileSystem_Delimiter"), "", $this->Root () . constant("jf_jPath_FileSystem_Delimiter") );
	}
}


class jpRequest2Module extends jpBase
{
	public static $Prefix = array (
		"app" => "control", "sys" => "control", "service" => "service" 
	);

	function Convert($RequestPath)
	{
		$a = explode ( constant ( "jf_jPath_Request_Delimiter" ), $RequestPath );
		if (array_key_exists ( $a [0], jpRequest2Module::$Prefix )) $a [0] = jpRequest2Module::$Prefix [$a [0]];
		return $this->_Convert ( $a, "", constant ( "jf_jPath_Module_Delimiter" ) );
	}
}


class jpModule2FileSystem extends jpBase
{

	function Convert($ModulePath, $System = null)
	{
		$this->FileEscape($ModulePath);
		if (function_exists ("reg") && reg("jf/mode")=="system" && $System===null)
			$System=true;
		elseif ($System===null)
			$System=false;
		if (! $System)
			$f = constant ( "jf_jPath_Application_Folder" );
		else
			$f = constant ( "jf_jPath_System_Folder" );
		return $this->_Convert ( $ModulePath, constant ( "jf_jPath_Module_Delimiter" ), constant("jf_jPath_FileSystem_Delimiter"), constant ( "jf_jPath_Module_Extension" ), $this->Root () . constant("jf_jPath_FileSystem_Delimiter") . $f . constant("jf_jPath_FileSystem_Delimiter") );
	}
}


class jpModule2ClassName extends jpBase
{
	public static $Prefix = Array (
		"control" => "Controller", "model" => "Model", "service" => "Service", "Plugin" => "Plugin", "lib" => "Library"
		,"test"=>"Test" 
	);

	function Convert($ModulePath, $System = false)
	{
		$this->ClassnameEscape($ModulePath);
		$a = explode ( constant ( "jf_jPath_Module_Delimiter" ), $ModulePath );
		if (array_key_exists ( $a [0], jpModule2ClassName::$Prefix ))
		{
			$a [] = jpModule2ClassName::$Prefix [$a [0]];
			array_shift ( $a );
		}
		foreach ( $a as &$v )
			if ($v [0] >= 'a' && $v [0] <= "z") $v [0] = strtoupper ( $v [0] );
		return $this->_Convert ( $a, "", "", "", "" );
	}
}


class jpClassName2Module extends jpBase
{
	public static $Prefix = array (
		"Controller" => "control", "Model" => "model", "Service" => "service", "Plugin" => "plugin"
		,"Test"=>"test" 
	);

	function Convert($Classname)
	{
		$this->ClassnameUnescape($Classname);
		for($i = strlen ( $Classname ) - 1; $i >= 0; -- $i)
			if ($Classname [$i] >= 'A' and $Classname [$i] <= 'Z')
			{
				$Postfix = substr ( $Classname, $i );
				break;
			}
			
		if (!array_key_exists ( $Postfix, jpClassName2Module::$Prefix )) //no discriminator, default to model
		{
			$Classname.="Model";
			$Postfix="Model";
		}
		$x = substr ( $Classname, 0, strlen ( $Classname ) - strlen ( $Postfix ) );
		//finding uppercase letters and forming module path
		for($i = 1; $i < strlen ( $x ); ++ $i)
			if ($x [$i] >= 'A' and $x [$i] <= 'Z')
			{
				$x = substr ( $x, 0, $i ) . constant ( "jf_jPath_Module_Delimiter" ) . substr ( $x, $i );
				$i ++;
			}
		if (array_key_exists ( $Postfix, jpClassName2Module::$Prefix ))
		{
			$Prefix = jpClassName2Module::$Prefix [$Postfix];
		}
		//lower case the result
		return $this->_Convert ( strtolower ( $x ), "", "", "", $Prefix . constant ( "jf_jPath_Module_Delimiter" ) );
	}
}


class jpTrimEnd extends jpBase
{

	function Convert($Path, $Delimiter = jf_jPath_Module_Delimiter, $TrimCount = 1)
	{
		$Path = explode ( $Delimiter, $Path );
		for($i = 0; $i < $TrimCount; ++ $i)
			array_pop ( $Path );
		return $this->_Convert ( $Path, $Delimiter, $Delimiter );
	}
}


class jpRoot extends jpBase
{

	function Convert()
	{
		return $this->_Convert( "", "", "", $this->Root () );
	}
}
class jpTrimStart extends jpBase
{
    function Convert ($Path, $Delimiter = jf_jPath_Module_Delimiter, $TrimCount = 1)
    {
        $Path = explode($Delimiter, $Path);
        for ($i = 0; $i < $TrimCount; ++ $i)
            array_shift($Path);
        return $this->_Convert($Path, $Delimiter, $Delimiter);
    }
}

?>