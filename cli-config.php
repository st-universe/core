<?php

declare(strict_types=1);

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Stu\Config\Init;

require_once __DIR__ . '/vendor/autoload.php';

$entityManager = null;

Init::run(function (ContainerInterface $dic) use (&$entityManager): void {
    $entityManager = $dic->get(EntityManagerInterface::class);
});

$config = new PhpFile('dist/db/migrations/migrations.php'); // Or use one of the Doctrine\Migrations\Configuration\Configuration\* loaders

return DependencyFactory::fromEntityManager($config, new ExistingEntityManager($entityManager));
