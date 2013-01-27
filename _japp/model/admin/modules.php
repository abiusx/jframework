<?php
class SystemModulesModel extends BaseApplicationClass
{
    /* if you need a constructor, remove this line
	function __construct(ApplicationController $App)
	{
		parent::__construct($App);
	}

	/**/
    function Template ($folder, $Class)
    {
        $x = $folder;
        if ($x == "control")
        {
            $s = "<?php
class $Class extends BaseControllerClass
{
\tfunction Start()
\t{
\t\t\$App=\$this->App;
\t\t\$View=\$this->View;
\t\t# Put your logic here


\t\treturn \$this->Present('Page Title Here');
\t}
}
?>";
        }
        elseif ($x == "model" or $x == "plugin")
        {
            $s = "<?php
class $Class extends BaseApplicationClass
{
\t/* if you need a constructor, remove this line
\tfunction __construct(ApplicationController \$App)
\t{
\t\tparent::__construct(\$App);
\t}

\t/**/
}
?>";
        }
        elseif ($x == "service")
        {
            $s = "<?php
class $Class extends BaseServiceClass
{
\tfunction Execute(\$Params)
\t{
\t\t# check for params validity here
\t
\t\t# load modules and logic here
\t\t
\t\t# return result here
\t}
}
?>";
        }
        elseif ($x=="view")
        {
            $s="<?php
# \$this here is the view object, and all the data controller has gathered is attached to it.
?>
<!--
	Put all your HTML here
	and keep in mind that you dont need header and footer,
	i.e no <html>, <head> or <body> open and closes.
	-->
";
        }
        else
            return false;
        return $s;
    }
}
?>
