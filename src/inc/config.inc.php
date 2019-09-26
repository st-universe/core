<?php

use Noodlehaus\ConfigInterface;
use Psr\Container\ContainerInterface;

require_once __DIR__.'/../../vendor/autoload.php';



/**
 * @var ContainerInterface $container
 */
$container = require_once __DIR__ . '/../Config/Bootstrap.php';

require_once __DIR__ . '/../Config/ErrorHandler.php';

$config = $container->get(ConfigInterface::class);

$webrootPath = $config->get('game.webroot');

ini_set('date.timezone', 'Europe/Berlin');
set_include_path(get_include_path() . PATH_SEPARATOR . $webrootPath);


require_once 'func.inc.php';
include_once("generated/fieldtypesname.inc.php");
