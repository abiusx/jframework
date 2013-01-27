<?php
class HttpRequestMethod
{
	const GET=1;
	const POST=2;
	const PUT=3;
	const DELETE=4;
		
}

/**
 * in this context, request refers to part of URI after the SiteRoot,
 * e.g if jf is in http://jframework.info/jf/
 * and uri is http://jframework.info/jf/some/uri/file
 * request would be some/uri/file
 */
class jFrameworkRequest 
{
	/**
	 * Raw request provided by user
	 */
	public $Raw="";	
	
	/**
	 * This holds the processed request,
	 * e.g main added to the end
	 * @var string
	 */
	public $Processed="";
	
	/**
	 * defines type of request
	 * @var unknown_type
	 */
	public $Type;
	
	
	/**
	 * type of the requested file for files
	 * @var unknown_type
	 */
	public $FileType=false;
	
	/**
	 * holds the requested jFramework module name
	 * @var unknown_type
	 */
	public $Module="";
	
}
final class HttpRequest
{
	public $UID="";
	
	public $Method = HttpRequestMethod::GET;
	public $Params = array();
	public $Cookies = array();
	public $Host = "";
	public $URI = "";
	
	
			

	/**
	 * 
	 * @var jFrameworkRequest
	 */
	public $Request;
	
	function __construct()
	{
		$this->Request=new jFrameworkRequest();
	}
}

class HttpResponseStatus
{
	const OK=200;
	const Created=201;
	const Accepted=202;
	const NonAuthoritative=203;
	const NoContent=204;
	const ResetContent=205;
	const PartialContent=206;
	
	const MovedPermanently=301;
	const Found=302;
	const SeeOther=303;
	const NotModified=304;
	const TemporaryRedirect=307;
	
	const BadRequest=400;
	const Unauthorized=401;
	const Forbidden=403;
	const NotFound=404;
	
	const InternalError=505;
	const NotImplemented=501;
	
	
}
class HTMLDoctype 
{
	const HTML5="<!DOCTYPE html>";
	const HTML41_Strict='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
	const HTML41_Transitional='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
	const HTML41_Frameset='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
	
	
}
class jfHTMLDocument
{
	public $Doctype=HTMLDoctype::HTML41_Transitional;
	public $Direction=null;
	public $Title=null;
	#public $Charset="UTF-8";
	public $Style=array();
	public $Script=array();
	public $CSS=array();
	public $JS=array();
	public $Meta=array();
	public $MetaHttp=array();
	public $ExtraHeaders=array();
	public $Body="";
	#public $Link=array();
	
	function HTML()
	{
		$Direction=$this->Direction?" dir='{$this->Direction}'":"";
		$res=$this->Doctype."\n";
		$res.="<html{$Direction}>\n";
		$res.="<head>\n";
		#$res.="<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />\n";
		$res.=implode("\n",$this->ExtraHeaders);
		if ($this->Title) 
			$res.="<title>{$this->Title}</title>\n";
		foreach ($this->MetaHttp as $k=>$v)
			$res.="<meta http-equiv='{$k}' content='{$v}' />\n";
		foreach ($this->Meta as $k=>$v)
			$res.="<meta name='{$k}' content='{$v}' />\n";
		foreach ($this->CSS as  $css)
				$res.="<link rel='stylesheet' href='{$css}' type='text/css' />\n";
		foreach ($this->JS as $js )
				$res.="<script src='{$js}' type='text/javascript'></script>\n";
		if (count($this->Style))
		{
			$styles="<style type='text/css'>\n";
			foreach ($this->Style as  $style)
					$styles.=$style; 
			$styles.="</style>\n";
			$res.=$this->Compress($styles);
		}
		if (count($this->Script))
		{
			$scripts="<script type='text/javascript'>\n";
			foreach ($this->Script as $script)
				$scripts.="{$script}\n";
			$scripts.="</script>\n";
			$res.=$this->Compress($scripts);
		}
		
		foreach ($this->ExtraHeaders as $header)
				$res.="{$header}\n";
		$res.="</head>\n";
		$res.="<body>\n";
		$res.=$this->Body;
		$res.="</body>\n";
		$res.="</html>";
		return $res;
	}
	public $Compression=false;
	private function Compress($Data)
	{
		if (!$this->Compression) return $Data;
		$Data=str_replace("\n\n","\n",$Data);
		$Data=str_replace("	"," ",$Data);
// 		$Data=str_replace("\n"," ",$Data);
		$Data=str_replace("  "," ",$Data);
		return $Data;
	}
	
}
class HttpResponse 
{
	public $Status=HttpResponseStatus::OK;
	public $ContentType="";
	
	function ParseHTML($HtmlData)
	{
		$this->Body=new jfHTMLDocument();
		$h=new HTMLParser($HtmlData, $this->Body);
	}
	/**
	 * 
	 * @var jfHTMLDocument
	 */
	public $Body=null;
	
	
}
class HTMLParser
{
	/**
	 * 
	 * @var jfHTMLDocument
	 */
	public $doc;
	public $html="";
	function __construct($HTMLData,jfHTMLDocument $doc)
	{
		$this->doc=$doc;
		$this->html=$HTMLData;
		$this->Parse();
		$this->StartTS=j::$Tracker->GetTime();
	}
	function __destruct()
	{
		echo j::$Tracker->GetTime()-$this->StartTS.BR;
	}
	function Parse()
	{
		$this->FixLinks();
		$this->doc->Title=$this->GetTitle();
		$this->doc->Script=$this->GetScript();
		$this->doc->JS=$this->GetJS();
		$this->doc->Style=$this->GetStyle();
		$this->doc->CSS=$this->GetCSS();
		$this->doc->Meta=$this->GetMeta();
		$this->doc->MetaHttp=$this->GetMetaHttp();
		$this->doc->ExtraHeaders=$this->GetOtherHeaders();
		
		preg_match("/<head>(.*?)<\\/head>/is", $this->html, $matches);
		$head=$matches[1];
		preg_match("/<body>(.*?)<\\/body>/is", $this->html, $matches);
		$body=$matches[1];
		$this->doc->Body=$body;

		
		preg_match("/<html\\sdir=[\"'](.*?)[\"']/is",$this->html,$match);
		if ($match)
			$this->doc->Direction=$match[1];
		
	}
	function RegexExtract($Regex,$Index,$KeyIndex=null)
	{
		$data=array();
		preg_match_all($Regex,$this->html, $matches, PREG_SET_ORDER);
		if (is_array($matches))
		foreach ($matches as $m)
		{
			if ($KeyIndex)
				$data[$m[$KeyIndex]]=$m[$Index];
			else
				$data[]=$m[$Index];
			
			$this->html=str_replace($m[0],"",$this->html,$count);
		}
		return $data;
	}
	function GetTitle ()
	{
		$title=null;
		preg_match_all("/<title>(.*?)<\\/title>/i", $this->html, $matches);
		if (is_array($matches))
			$title=end($matches[1]); //first parantes
		$this->html=preg_replace("/<title>(.*?)<\\/title>/i", "", $this->html);
		return $title;
	}
	function GetScript()
	{
		return $this->RegexExtract("/<script\\s*(type=[\"']text\\/javascript[\"'])?\\s*>(.*?)<\\/script>/is",2);
	}
	function GetJS()
	{		
		
		return $this->RegexExtract("/<script\\s*(type=[\"']text\\/javascript[\"'])?\\s*src=[\"'](.*?)[\"']\\s*(type=[\"']text\\/javascript[\"'])?><\\/script>/is",2);
	}
	function GetCSS()
	{
		return $this->RegexExtract("/<link\\s*rel=[\"']stylesheet[\"']\\s*href=[\"'](.*?)[\"'].*?\\/>/is",1);
	}
	function GetStyle()
	{
		return $this->RegexExtract("/<style\\s*(type=[\"']text\\/css[\"'])?\\s*>(.*?)<\\/style>/is",2);
	}
	
	function GetMeta ()
	{
		return $this->RegexExtract("/<meta\\s*name=[\"'](.*?)[\"']\\s*content=[\"'](.*?)[\"']\\s*\\/>/is",2,1);
	}
	function GetMetaHttp ()
	{
		return $this->RegexExtract("/<meta\\s*http-equiv=[\"'](.*?)[\"']\\s*content=[\"'](.*?)[\"']\\s*\\/>/is",2,1);
	}
	function GetOtherHeaders()
	{
		return $this->RegexExtract("/(<link.*?\\/>)/is",1);
		
	}
	function FixLinks ()
	{
		// href
		$this->html = preg_replace("/(href=[\"'])(\\/.*?[\"'])/is", "$1" . SiteRoot .
		 "$2", $this->html);
		$this->html = preg_replace("/(src=[\"'])(\\/.*?[\"'])/is", "$1" . SiteRoot . "$2", 
		$this->html);
		$this->html = preg_replace("/(background-image\\s*:\\s*url\\s*\\([\"']?)\\//is", 
		"$1" . SiteRoot . "/", $this->html);
	}
}

jf::$App->HttpRequest=new HttpRequest();
jf::$App->HttpResponse=new HttpResponse();

?>