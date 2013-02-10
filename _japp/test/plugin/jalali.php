<?php
class PluginJalaliTest extends JTest
{
	function setUp()
	{
		$this->Timestamp=1359929366;
		#1359929366 is timestamp of 4 feb 2013 1:39:26 AM GMT 3:30
		#it is 15 Bahman 1391 in Jalali Calendar
		new JalaliPlugin();
	}
	function testYear()
	{
		$this->assertEquals(Jalali::Year($this->Timestamp),1391);
	}
	function testMonth()
	{
		$this->assertEquals(Jalali::Month($this->Timestamp),11);
	}
	function testDay()
	{
		$this->assertEquals(Jalali::Day($this->Timestamp),15);
	}
	
	function testTime()
	{
		$this->assertEquals(Jalali::Hour($this->Timestamp),1);
		$this->assertEquals(Jalali::Minute($this->Timestamp),39);
		$this->assertEquals(Jalali::Second($this->Timestamp),26);
	}
	function testStrings()
	{
		$this->assertEquals(Jalali::DateString($this->Timestamp),"1391-11-15");
		$this->assertEquals(Jalali::TimeString($this->Timestamp),"01:39:26");
		$this->assertEquals(new Jalali($this->Timestamp),"1391-11-15 01:39:26");

		$this->assertEquals(new Jalali(),new Jalali(jf::time()));
	}
	function testToTimestamp()
	{
		$this->assertEquals(Jalali::ToTimestamp(1391, 11, 15),strtotime(date("Y-m-d",$this->Timestamp)));
		$this->assertEquals(Jalali::ToTimestamp(1391, 11, 15,1),strtotime(date("Y-m-d",$this->Timestamp))+3600);
		$this->assertEquals(Jalali::ToTimestamp(1391, 11, 15,1,39),strtotime(date("Y-m-d",$this->Timestamp))+3600+39*60);
		
		
		$this->assertEquals(Jalali::ToTimestamp(1391, 11, 15,1,39,26),strtotime(date("Y-m-d",$this->Timestamp))+3600+39*60+26);
		$this->assertEquals(Jalali::ToTimestamp("1391-11-15 1:39:26"),strtotime(date("Y-m-d",$this->Timestamp))+3600+39*60+26);
	}

}