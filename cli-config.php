<?php

declare(strict_types=1);

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Config\Init;

require_once __DIR__ . '/vendor/autoload.php';

$config = new PhpFile('dist/db/migrations/migrations.php'); // Or use one of the Doctrine\Migrations\Configuration\Configuration\* loaders

return DependencyFactory::fromEntityManager(
    $config,
    new ExistingEntityManager(Init::getContainer(false)
        ->get(EntityManagerInterface::class))
);
