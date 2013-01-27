<?php

?>
<style>
label { 
	width:100px;
	float:left;
}
input[type="text"],input[type="password"] {
	width:150px;
	
}

#remember_container {
	text-align:center;
}
#login_container {
	
	margin:auto;
	text-align:center;
	width:300px;
	
}
form#login {
	border: 2px ridge;
	padding:10px;
	margin:5px;
	}

</style>
<div id="login_container">
<form id="login" method="post">
	<strong>Login to jFramework</strong>
	<br/>
	<?php if (isset($this->Result) and !$this->Result)
	{
	    ?><span style="color:red">Invalid credentials</span><?php
	    $this->Username=$_POST["Username"]; 
	}
	?>
	<br/>
	
	<label>Username: </label>
	<input type='text' value="<?php echo $this->Username?>" name="Username" />
	<br/>
	<br/>
	<label>Password:</label>
	<input type="password" name="Password" />
	<br/>
	<div id="remember_container"> 
	<input type="checkbox" value="yes" name="Remember" /> Remember me
	</div>
	
	
	<input type="submit" value="Login" />
	<input type="button" value="Back" onclick="history.back()" />
<?php if ($this->UserID) {?>
	<br/><a style="font-size:small" href="/sys/logout?return=sys.login">Sign in as a different user</a>
<?php } ?>
	
	</form>

</div>