<?php
class LoginController extends BaseControllerClass
{
    function Start ()
    {
        $App = $this->App;
        $View = $this->View;
        $View->UserID = $App->Session->UserID;
        $View->Username = $App->Session->Username();
        if (isset($_COOKIE["jFramework_Login_Remember"]))
        {
            $temp = $_COOKIE["jFramework_Login_Remember"];
            $Username = explode("\n", $temp);
            $Password = $Username[1];
            $Username = $Username[0];
            $View->Result = $App->Session->Login($Username, $Password);
            $View->Username = $Username;
        }
        if (isset($_POST["Username"]))
        {
            $View->Result = $App->Session->Login($_POST["Username"], $_POST["Password"]);
        }
        if ($View->Result) //Login Successful
        {
        	if ($_POST["Remember"])
            {
                setcookie("jFramework_Login_Remember", $_POST["Username"] . "\n" . $_POST["Password"], time()+60*60*24*7, "/", null, null);
            }
            else
            {
                setcookie("jFramework_Login_Remember", null);
            }
            if (isset($_GET["return"]))
                $Return = $_GET["return"];
            else
                $Return = "sys/main";
            $this->Redirect(SiteRoot."".$Return,true,1);
        }
        $this->Present("jFramework System Interface Login");
    }
}
?>