<?php
class XuserLoginController extends JControl
{
    function Start ()
    {
    	$this->Username=jf::$XUser->Username();
    	if (isset($_COOKIE["jframework_rememberme"]))
        {
        	$rememberMeToken= $_COOKIE["jframework_rememberme"];
        	$userID=jf::LoadGeneralSetting("rememberme_".$rememberMeToken);
			if ($userID>0)
			{
				$Result=jf::$XUser->ForceLogin($userID);
				$Logged=true;
			}
        }
        if (isset($_POST["Username"]))
        {
        	$Username=$_POST['Username'];
        	$Password=$_POST['Password'];
			$loginResult=jf::$XUser->Login($Username, $Password);
			if ($loginResult==false)
			{
				$res=jf::$XUser->LastError;
				if ($res==\jf\ExtendedUserErrors::Inactive)
					$ErrorString="Your account is not activated.";
				elseif ($res==\jf\ExtendedUserErrors::InvalidCredentials or $res==\jf\ExtendedUserErrors::NotFound)
					$ErrorString="Invalid Credentials.";
				elseif ($res==\jf\ExtendedUserErrors::Locked)
					$ErrorString="Your account is locked. Try again in ".(jf::$XUser->LockTime($Username)/60)." minutes.";
				elseif ($res==\jf\ExtendedUserErrors::PasswordExpired)
				{
					$ErrorString="Your password is expired. You should set a new password.";
					$ChangePass=true;
				}
				elseif ($res==\jf\ExtendedUserErrors::TemporaryValidPassword)
				{
					$ErrorString="This is a temporary password. You should set your permanent password now.";
					$ChangePass=true;
				}
				$Logged=false;
				$this->Error=$ErrorString;
			}
			else //logged in successfully
			{
				$Logged=true;
				if (isset($_POST['Remember']))
				{
					$timeout=60*60*24*30;
					$rememberMeToken=jf::$Security->RandomToken();
					jf::SaveGeneralSetting("rememberme_".$rememberMeToken,jf::CurrentUser(),$timeout);
					setcookie('jframework_rememberme',$rememberMeToken,jf::time()+$timeout);
				}
			}
        }
        if ($Logged==true)
        {
        	if (isset($_GET['return']))
        		$this->Redirect($_GET['return']);
        	$this->Success=true;
        }

        return $this->Present();
    }
}
?>