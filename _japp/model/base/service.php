<?php
abstract class BaseService  
{
    /**
     * Application
     *
     * @var ApplicationController
     */
    protected $App;
    function __construct($App=null)
    {
    	if ($App===null)
    		$App=j::$App;
        $this->App=$App;
    }
    abstract function Execute($Params);
    
}
abstract class BaseServiceClass extends BaseService {}
abstract class JService extends BaseServiceClass {}

?>