<?php
class LibMainAdapterTest extends JDbTest
{
	function __construct()
	{
		switch (getAdapter())
		{
			case "mysqli":
				$this->add("jf/test/lib/db/adapter/mysqli");
				break;
			case "pdo_mysql":
				$this->add("jf/test/lib/db/adapter/pdo_mysql");
				break;
			case "pdo_sqlite":
				$this->add("jf/test/lib/db/adapter/pdo_sqlite");
				break;			
		}
	}
	function getAdapter()
	{
		$userConfig= \jf\DatabaseManager::Configuration(1);
		return $userConfig->Adapter;
	}
}