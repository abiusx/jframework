<?php
namespace jf;
class View extends Model
{

	static $IterativeTemplates=true;
	static $TemplateFolder="_template";
	/**
	 * Presents a template file, e.g _template/head or _template/foot
	 * @param string $ViewModule
	 * @param string $Template name
	 * @return boolean
	 */
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
				return jf::run($templateModule,array("Append"=>$this->HeadDataAppend,"Prepend"=>$this->HeadDataPrepend));
			}
		}
		return false;
	}
	
	/**
	 * Loads the header from _template/head.php
	 *
	 * @param String $ViewModule
	 * @return boolean
	 */
	function PresentHeader($ViewModule)
	{
		return $this->PresentTemplate($ViewModule,"head");
	}
	/**
	 * Presents the _template/foot.php
	 * @param unknown_type $ViewModule
	 * @return boolean
	 */
	function PresentFooter($ViewModule)
	{
		return $this->PresentTemplate($ViewModule,"foot");
	}

	/**
	 * Starts output buffering for main content
	 */
	private function StartBuffering()
	{
		ob_start ();
	}
	
	/**
	 * end and return output buffering
	 */
	private function EndBuffering()
	{
		return ob_get_clean ();
	}

	private $ViewModule;
	
	/**
	 * These variables hold extra data that are appended and prepended to the head template, for dynamic titles, etc.
	 * @var array
	 */
	protected $HeadDataAppend=array();
	protected $HeadDataPrepend=array();
	/**
	 * Add something to template header
	 * @param string $DataString
	 * @param boolea $Append if true, appends to head, otherwise adds at beginning
	 */
	function AddToHead($DataString,$Append=true)
	{
		if ($Append)
			$this->HeadDataAppend[]=$DataString;
		else
			$this->HeadDataPrepend[]=$DataString;
	}
	
	/**
	 * Presents the view with its templates
	 * @param string $ViewModule
	 * @return boolean
	 */
	function Present($ViewModule)
	{
		if (file_exists ( $this->ModuleFile($ViewModule) ))
		{
			$this->ViewModule=$ViewModule;
			
			$this->StartBuffering ();
			include $this->ModuleFile($ViewModule);
			$MainContent = $this->EndBuffering ();

			$this->PresentHeader ($ViewModule);

			echo $MainContent;

			$this->PresentFooter ($ViewModule);
			
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
		$Parts=explode("/",$this->ViewModule);
		array_pop($Parts);
		$NewParts=explode("/",$RelativePath);
		$Parts=array_merge($Parts,$NewParts);
		$NewModule=implode("/",$Parts);
		include $this->ModuleFile($NewModule);
	}
	
	
}

?>