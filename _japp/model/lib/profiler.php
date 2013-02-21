<?php
namespace jf;
class Profiler extends Model 
{
    function __construct ()
    {
        $this->Reset();
    }
    
    private $Start=null;
    private $End=null;
    private $TimeMicroseconds;
    private $Time;
    
    /**
     * Returns time in microseconds
     * @param boolean $ReturnMicroseconds
     * @return string
     */
    function GetTime ($ReturnMicroseconds = true)
    {
        $match=array();
        list($microsec,$sec)=explode(" ",microtime());
        $utime = $microsec +$sec;
        if ($ReturnMicroseconds) {
            $utime *= 1000000;
        }
        return sprintf("%d",$utime);
    }

    /**
     * Reset the timer
     */
    function Reset ()
    {
        $this->Start = $this->GetTime(true);
    }
    /**
     * Returns the time calculated
     * @param string $Start
     * @param string $End
     * @return string
     */
    function Timer ($Start=null,$End=null)
    {
        if ($Start===null) $Start=$this->Start;
        if ($End===null) 
        	if ($this->End===null)
        		$End=$this->GetTime(true);
        	else
        		$End=$this->End;
        $this->TimeMicroseconds = $End - $Start;
        return $this->Time = sprintf("%f",$this->TimeMicroseconds / 1000000.0);
    }
    
    /**
     * Stops the timer
     */
    function Stop()
    {
    	$this->End=$this->GetTime(true);
    }
}

?>