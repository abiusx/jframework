<?php

/**
 * 
 * Outputs a translated version of an string
 * @param string $Phrase
 * @param optional $Lang string language
 * @param optional $Target desired language
 * @version 1.08
 */
function tr($Phrase,$Lang=null,$Target=null)
{
	echo trr($Phrase,$Lang,$Target);
}
/**
 * 
 * Returns a translated version of an string
 * @param string $Phrase
 * @param optional $Lang string language
 * @param optional $Target desired language
 * @version 1.08
 */
function trr($Phrase,$Lang=null,$Target=null)
{
	return jf::$i18n->Translate($Phrase,$Lang,$Target);
}

function print_($var)
{
	echo nl2br(str_replace(" ","&nbsp;",htmlspecialchars(print_r($var,true))));
}

function exho($data)
{
	if (defined("ENT_HTML401"))
		echo htmlspecialchars($data,ENT_QUOTES | ENT_HTML401,"UTF-8");
	else
		echo htmlspecialchars($data,ENT_QUOTES,"UTF-8");
		
}

?>
