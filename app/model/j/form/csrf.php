<?php
class jFormCsrf extends jFormWidget
{
	
	
	const SettingNamePrefix="jWidget_CSRFGuard_";
	
	protected $Token=null;
	/**
	 * Construct a CSRF guard hidden field. You should provide the name of the csrf guard as second parameter here.
	 * @param jWidget $Parent
	 */
	function __construct(jWidget $Parent,$Name)
	{
		$this->__setname($Name);
		parent::__construct($Parent);
		$OldToken=jf::LoadSessionSetting(jFormCsrf::SettingNamePrefix.$Name);
		$this->Token=jf::$Security->RandomToken();
		jf::SaveSessionSetting(jFormCsrf::SettingNamePrefix.$this->Name(), $this->Token,\jf\Timeout::HOUR);
		
		$this->SetValidation(
				function ($Data)  use ($Name,$OldToken){ 
					return $OldToken==$Data;
				}
		);
	}
	
	
	function Present()
	{
		//only update the csrf token on the session when outputting the field.
		
		echo "<input class='jWidget jFormCSRF' type='hidden' name='{$this->Name()}' value='{$this->Token}' />\n";
	}
}