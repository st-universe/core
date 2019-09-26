<?php

use Noodlehaus\ConfigInterface;
use Stu\Lib\Generators;

require_once __DIR__ . '/../../Config/Bootstrap.php';

Generators::generate(
    $container->get(ConfigInterface::class)
);
