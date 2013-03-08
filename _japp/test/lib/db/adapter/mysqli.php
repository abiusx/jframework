<?php
class LibDbMysqliTest extends JDbTest
{
	function TablePrefix()
	{
		return "jf_";
	}
	function testLastID()
	{
		$insDb=jf::db();
		jf::SQL("INSERT INTO {$this->TablePrefix()}users (Username, Password, Salt, Protocol) VALUES (?,?,?,?);",
								"some_name","some_pass", "some_salt", 1);
		$first=$insDb->LastID();
		jf::SQL("INSERT INTO {$this->TablePrefix()}users (Username, Password, Salt, Protocol) VALUES (?,?,?,?);",
								"another_name","another_pass", "another_salt", 1);
		$second=$insDb->LastID();
		$this->assertEquals($first+1,$second);
	}
	function testQuote()
	{	
		$insDb=jf::db();
		$this->assertEquals($insDb->quote("'some-text'"),"\'some-text\'");
	}
	function testQuery()
	{
		$insDb=jf::db();		
		$this->assertNotNull($insDb->query("INSERT INTO {$this->TablePrefix()}users (Username, Password, Salt, Protocol) VALUES('some_name','some_pass', 'some_salt', 1)"));
	}
	function testPrepare()
	{
		$insDb=jf::db();
		$r=$insDb->prepare("INSERT INTO {$this->TablePrefix()}users (Username, Password, Salt, Protocol) VALUES (?,?,?,?);");
		$this->assertInstanceOf("\\jf\\BaseDatabaseStatement", $r);
		
		try
		{
			$r=$insDb->prepare("INSERT INTO {$this->TablePrefix()}users (Username, Pasword, Salt, Protocol) VALUES (?,?,?,?);");
			$this->assertInstanceOf("\\jf\\BaseDatabaseStatement", $r);
			$this->fail();
		} catch(Exception $e) { }
	}
	function testExec()
	{
		$insDb=jf::db();
		for ($i=0; $i<2; $i++)
			jf::SQL("INSERT INTO {$this->TablePrefix()}users (Username, Password, Salt, Protocol) VALUES (?,?,?,?);",
								"some_name{$i}","some_pass{$i}", "some_salt", 1);
		$r=$insDb->exec("UPDATE {$this->TablePrefix()}users SET Protocol=10 WHERE Salt='some_salt';");
		$this->assertEquals($r,2);

		$r=$insDb->exec("UPDATE {$this->TablePrefix()}users SET Protocol=9 WHERE Salt='another_salt';");
		$this->assertEquals($r,0);
	}
	/**
	 * @depends testExec
	 */
	function testInitialize()
	{
		$insDb=jf::db();
		$config= \jf\DatabaseManager::Configuration(1);
		
		$tableList=$insDb->GetListTables($config->DatabaseName);
		$insDb->Initialize($config->DatabaseName);	
		foreach($tableList as $table)
			$this->assertLessThanOrEqual($insDb->exec("SELECT * FROM {$table};"),0);
	}
	function testInitializeDate()
	{
		$insDb=jf::db();
		$config= \jf\DatabaseManager::Configuration(1);
		
		$insDb->InitializeData($config->DatabaseName);
		$r=jf::SQL("SELECT count(*) AS Num FROM {$this->TablePrefix()}users;");
		$this->assertLessThan($r[0]['Num'],0);
	}
}

class LibDbStatementMysqliTest extends JDbTest
{
	function TablePrefix()
	{
		return "jf_";
	}	
	function testExecute()
	{
		$insDb=jf::db();
		
		$r=$insDb->exec("INSERT INTO {$this->TablePrefix()}users (Username, Password, Salt, Protocol) VALUES('some_name','some_pass', 'some_salt', 1);");
		$myStatement=$insDb->prepare("INSERT INTO {$this->TablePrefix()}users (Username, Password, Salt, Protocol) VALUES (?,?,?,?);");
		$insDb->exec("DELETE FROM {$this->TablePrefix()}users WHERE Username='some_name'");
		$this->assertEquals($myStatement->execute("some_name","some_pass", "some_salt",1), $r);				
	}
	/**
	 * @depends testExecute
	 */
	function testBindAll()
	{
		$insDb=jf::db();
		$myStatement=$insDb->prepare("INSERT INTO {$this->TablePrefix()}users (Username, Password, Salt, Protocol) VALUES (?,?,?,?);");
		
		$myStatement->bindAll("some_name","some_pass", "some_salt", 1);
		$this->assertEquals($myStatement->execute(),1);
	}
	function testRowCount()
	{
		$insDb=jf::db();
		
		$myStatement=$insDb->prepare("INSERT INTO {$this->TablePrefix()}users (Username, Password, Salt, Protocol) VALUES (?,?,?,?);");
		$r=$myStatement->execute("some_name","some_pass", "some_salt",1);
		$this->assertEquals($r, $myStatement->rowCount());
	}
	function testFetch()
	{
		$insDb=jf::db();
		
		$r=jf::SQL("SELECT * FROM {$this->TablePrefix()}users;");
		$myStatement=$insDb->prepare("SELECT * FROM {$this->TablePrefix()}users;");
		$myStatement->execute();
		
		$this->assertEquals($myStatement->fetch(),$r[0]);
	}
	function testFetchAll()
	{
		$insDb=jf::db();
		$insDb->exec("INSERT INTO {$this->TablePrefix()}users (Username, Password, Salt, Protocol) VALUES('some_name','some_pass', 'some_salt', 1);");
		
		$r=jf::SQL("SELECT * FROM {$this->TablePrefix()}users;");
		$myStatement=$insDb->prepare("SELECT * FROM {$this->TablePrefix()}users;");
		$myStatement->execute();
		
		$this->assertEquals($myStatement->fetchAll(),$r);
	}
}