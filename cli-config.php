<?php

declare(strict_types=1);
require_once __DIR__.'/src/Config/Bootstrap.php';

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet(
    $container->get(\Doctrine\ORM\EntityManagerInterface::class)
);
