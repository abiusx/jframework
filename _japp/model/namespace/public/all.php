<?php
#
# this serves as a means of defining classes in public namespace
#
abstract class JControl extends \jf\Controller {}
abstract class JCatchControl extends \jf\CatchController {}
abstract class JController extends JControl {}
abstract class JCatchController extends JCatchControl {}

class JModel extends \jf\Model {}
class JView extends \jf\View {}
class JPlugin extends \jf\Plugin {}

//class JService extends \jf\Service {}

class HttpRequest extends \jf\HttpRequest {}

class RunModes
{
	const Develop=1;
	const Deploy=2;
	const CLI=3;
}


define("TIMESTAMP_MINUTE",60);
define("TIMESTAMP_HOUR",TIMESTAMP_MINUTE*60);
define("TIMESTAMP_DAY",TIMESTAMP_HOUR*24);
define("TIMESTAMP_WEEK",TIMESTAMP_DAY*7);
define("TIMESTAMP_MONTH",TIMESTAMP_DAY*30);
define("TIMESTAMP_YEAR",TIMESTAMP_DAY*365);
define("TIMESTAMP_FOREVER",TIMESTAMP_YEAR*128);

