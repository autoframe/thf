<?php

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/src/_demo_config.php');


use Autoframe\Core\Socket\AfrCacheSocketServer;



$oAfrCacheSocketServer = AfrCacheSocketServer::getInstance();
$oAfrCacheSocketConfig = $oAfrCacheSocketServer->selectSocketConfig(AfrCacheSocketServer::DEFAULT_CONFIG_NAME);
$oAfrCacheSocketServer->run();
