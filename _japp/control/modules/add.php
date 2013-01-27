<?php
class ModulesAddController extends BaseControllerClass
{
    function Start ()
    {
        $App = $this->App;
        $View = $this->View;
        if (isset($_POST['file']))
        {
            $File = $_POST['file'];
            $Module = $_POST['module'];
            $Class=new jpModule2ClassName($Module);
            $m = new SystemModulesModel($App);
            $data = $m->Template(array_shift(explode(".", $Module)), $Class);
            if (! is_writable($File) && ($_POST['save_username'] != "" or $_POST['save_password'] != ""))
            {
                echo "* It seems I don't have the permissions to create the file, so I'm gonna grant it to myself using the
                credentials you provided: ";
                $Username = $_POST['save_username'];
                $Password = $_POST['save_password'];
                $x = shell_exec("su $Username << __eof
$Password
__eof;
echo>>$File;
chmod 777 $File;
whoami;
");
                $x = trim($x); //trim newline and space after whoami result
                if (strtolower($x) !== strtolower($Username))
                    echo "<b>Invalid credentials!</b>" . BR;
                else
                {
                    echo nl2br("<div style='border:2px inset;background-color:black;color:white;padding:5px;'><b>su $Username << __eof
" . str_repeat("*", strlen($Password)) . "
__eof;
echo>>$File;
chmod 777 $File;</b></div>") . BR;
                    $sudo = true;
                }
            }
            if (is_writable($File))
            {
                $f = fopen($File, "wt");
                fwrite($f, $data);
                fclose($f);
                echo "* $File <b>successfully created.</b>" . BR;
            }
            else
            {
                echo "* I have no permission to save this file: $File" . BR;
                $sudo = false;
            }
            if ($sudo)
            {
                echo "* Now setting file permission back to safe:" . BR;
                shell_exec("su $Username << __eof
$Password
__eof;
chmod 755 $File;
");
                echo nl2br("<div style='border:2px inset;background-color:black;color:white;padding:5px;'><b>su $Username << __eof
" . str_repeat("*", strlen($Password)) . "
__eof;
chmod 755 $File;</b></div>") . BR;
            }
            echo "* All done.";
            echo "<hr/>";
        }
        $this->Present();
    }
}
?>