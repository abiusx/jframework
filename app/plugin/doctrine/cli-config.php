<?php
define("DoctrineCommandLine",true);
require_once __DIR__."/../../../jf.php";
//require_once __DIR__."/../doctrine.php";
$jFramework = jfFrontController::GetSingleton();
$jFramework->Start();
$d=new DoctrinePlugin();
$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($d->entityManager)
));