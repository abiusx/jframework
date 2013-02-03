<?php
namespace jf;
class View extends Model
{

	static $IterativeTemplates=true;
	static $TemplateFolder="_template";
	
	private function PresentTemplate($ViewModule,$Template="head")
	{
		if (self::$IterativeTemplates)
			$Iteration = 1000;
		else
			$Iteration = 1;
		
		$n = 0;
		$Parts=explode("/",$ViewModule);
		while (  $n <= $Iteration )
		{
			$Part=array_pop($Parts);
			$templateModule = implode("/",$Parts);
			if (count($Parts) == 0) break;
			$templateModule =  $templateModule . DIRECTORY_SEPARATOR. self::$TemplateFolder . DIRECTORY_SEPARATOR. $Template;
		
			if (file_exists ( $this->ModuleFile($templateModule) ))
			{
				return jf::import($templateModule);
			}
		}
		return false;
	}
	
	/**
	 * Loads the header from _template/head.php
	 *
	 * @param String $ViewModule
	 */
	function PresentHeader($ViewModule)
	{
		return $this->PresentTemplate($ViewModule,"head");
	}

	function PresentFooter($ViewModule)
	{
		return $this->PresentTemplate($ViewModule,"foot");
	}

	private function StartBuffering()
	{
// 		ob_start ();
	}

	private function EndBuffering()
	{
// 		return ob_get_clean ();
	}

	private $ViewModule;
	
	
	function Present($ViewModule)
	{
		if (file_exists ( $this->ModuleFile($ViewModule) ))
		{
			$this->ViewModule=$ViewModule;
			
// 			$this->StartBuffering ();
			$this->PresentHeader ($ViewModule);
// 			$HeadContent = $this->EndBuffering ();

// 			$this->StartBuffering ();
			include $this->ModuleFile($ViewModule);
// 			$MainContent = $this->EndBuffering ();
			
// 			$this->StartBuffering ();
			$this->PresentFooter ($ViewModule);
// 			$FootContent = $this->EndBuffering ();
			
			return true;
		}
		else
			return false;
	}


	/**
	 * Represents a portion of a view
	 * This is useful for huge views.
	 *
	 * @param String $RelativePath the path from here to the other portion of the view
	 */
	function Represent($RelativePath)
	{
		$x = new jpTrimEnd ( $this->ViewTitle );
		$x=$x->__toString();
		if ($x != "") $x .= constant ( "jf_jPath_Module_Delimiter" );
		return $this->OutputFile ( $x . $RelativePath );
	}
	
	
}

?>