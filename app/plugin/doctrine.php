<?php

class DoctrinePlugin extends JPlugin
{
	protected $classLoader;
	protected $cache;
	protected $config;
	public $eventManager;
	/**
	 *	@var Doctrine\ORM\EntityManager
	 * 
	 */
	public $entityManager;
	public function AutoloadSetup()
	{
		require __DIR__.'/doctrine/Doctrine/Common/ClassLoader.php';
		$this->classLoader = new \Doctrine\Common\ClassLoader('Doctrine', __DIR__."/doctrine");
		$this->classLoader->register(); // register on SPL autoload stack

	}
	public function Config($prefix="app_")
	{
		$this->config = new Doctrine\ORM\Configuration(); // (2)

		// Proxy Configuration (3)
		$this->config->setProxyDir(__DIR__."/doctrine/proxy_cache");
		$this->config->setProxyNamespace(reg("app/name").'\Proxies');
		$this->config->setAutoGenerateProxyClasses((reg("app/state") == "develop"));

		// Mapping Configuration (4)
		//$driverImpl = new Doctrine\ORM\Mapping\Driver\XmlDriver(__DIR__."/entities/xml");
		//$driverImpl = new Doctrine\ORM\Mapping\Driver\XmlDriver(__DIR__."/config/mappings/yml");
		$driverImpl = $this->config->newDefaultAnnotationDriver(__DIR__."/../model");
		$this->config->setMetadataDriverImpl($driverImpl);

		// Caching Configuration (5)
		if (reg("app/state")== "deploy" and function_exists("apc_exists")) {
			$this->cache = new \Doctrine\Common\Cache\ApcCache();
		} else {
			$this->cache = new \Doctrine\Common\Cache\ArrayCache();
		}
		$this->config->setMetadataCacheImpl($this->cache);
		$this->config->setQueryCacheImpl($this->cache);

		// database configuration parameters (6)
		$adapter=reg("app/db/default/adapter");
		if ($adapter=="mysql" or $adapter=="mysqli" or !$adapter)
			$adapter="pdo_mysql";
		$host=reg("app/db/default/host");
		if ($host=="localhost")
			$host="127.0.0.1";
		$conn = array(
    'driver' => $adapter,
    'user' => reg("app/db/default/user"),
    'password' => reg("app/db/default/pass"),
    'host' => $host,
    'dbname' => reg("app/db/default/name"),
    'charset' => 'utf8',
    'path' => reg("app/db/default/sqlite/folder") . '/'.reg("app/db/default/name"),
		);

		$this->config->setSQLLogger(new Doctrine\DBAL\Logging\jFrameworkSQLLogger());

		// obtaining the entity manager (7)
		$this->eventManager = new Doctrine\Common\EventManager();

		$this->tablePrefix = new \Doctrine\Extensions\TablePrefix($prefix);
		$this->eventManager->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $this->tablePrefix);
		$this->entityManager = \Doctrine\ORM\EntityManager::create($conn, $this->config, $this->eventManager);
	}

	public function Init($Prefix="app_")
	{
	}

	public function CreateSchema()
	{
		$x=shell_exec("
		cd ".__DIR__."/doctrine ;
		php bin/doctrine.php orm:schema-tool:create
		");
		return $x;
	}
	public function UpdateSchema()
	{
		$metadata=$this->entityManager->getMetadataFactory()->getAllMetadata();
		$schemaTool=new Doctrine\ORM\Tools\SchemaTool($this->entityManager);
		$schemaTool->updateSchema($metadata,true);
		return ;
		
		$x=shell_exec("
		cd ".__DIR__."/doctrine ;
		php bin/doctrine.php orm:schema-tool:update --force;
		");
		if (strpos($x,"Database schema updated successfully!")===false)
			trigger_error($x);
		return $x;
	}


	function __construct($prefix="app_")
	{
		if (!defined("DoctrineCommandLine") or !constant("DoctrineCommandLine"))
			$this->AutoloadSetup();

		$this->Config($prefix);

		if (!defined("DoctrineCommandLine") or !constant("DoctrineCommandLine"))
			if (reg("app/state")=="develop")
				$r=$this->UpdateSchema();
		require_once(__DIR__."/doctrine/helper.php");
	}
	function __destruct()
	{
		$this->flush();
	}
	function flush()
	{
		$this->entityManager->flush();
		
	}

}