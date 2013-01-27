<?php
class MainTest extends JTest
{
	function __construct()
	{
		$this->add("jf/test/lib/main");
	}
	function testSomething()
	{
		$this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
	}
    public function testEmpty()
    {
        $stack = array();
        $this->assertEmpty($stack);
 
        return $stack;
    }
 
    function testSkipped()
    {
    	$this->markTestSkipped("not usable");
    }
    /**
     * @depends testEmpty
     */
    public function testPush(array $stack)
    {
        array_push($stack, 'foo');
        $this->assertEquals('foo', $stack[count($stack)-1]);
        $this->assertNotEmpty($stack);
 
        return $stack;
    }
 
    /**
     * @depends testPush
     */
    public function testPop(array $stack)
    {
        $this->assertEquals('foo', array_pop($stack));
        $this->assertEmpty($stack);
        $this->assertNotEmpty($stack); #fail
    }
}