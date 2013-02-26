<?php
class LibPasswordTest extends JTest
{
	
	function testStrength()
	{
		print_(\jf\Password::Strength("123aB456"));
		print_(\jf\Password::Strength("123456aB"));
		print_(\jf\Password::Strength("123456abde"));
		print_(\jf\Password::Strength("123456abcde"));
		print_(\jf\Password::Strength("19881989"));
		print_(\jf\Password::Strength("19880308"));
		print_(\jf\Password::Strength("09123874634"));
		print_(\jf\Password::Strength("12345678"));
		print_(\jf\Password::Strength("qwerty"));
		
		$pass=\jf\Password::Generate();
		print_($pass);
		print_(\jf\Password::Strength($pass));
	}
}