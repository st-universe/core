<?php

namespace Stu\Migrations;

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Config\ConfigStageEnum;
use Stu\Config\Init;

class StuDependencyFactory extends DependencyFactory
{
    public function __construct() {}

    public static function createDependencyFactory(): DependencyFactory
    {
        /** @var array<string> */
        $argv = $_SERVER['argv'];

        $configStage = ConfigStageEnum::from(self::popArgument('--stage=', $argv, ConfigStageEnum::PRODUCTION->value));
        $configuration = self::popArgument('--configuration=', $argv, 'dist/db/migrations/production.php');

        return DependencyFactory::fromEntityManager(
            new PhpFile($configuration),
            new ExistingEntityManager(Init::getContainer($configStage)
                ->get(EntityManagerInterface::class))
        );
    }

    private static function popArgument(string $name, array $argv, string $default): string
    {
        $filtered = array_filter($argv, fn(string $token): bool => str_starts_with($token, $name));
        if (count($filtered) !== 1) {
            return $default;
        }

        foreach ($filtered as $key => $value) {
            unset($_SERVER['argv'][$key]);
        }

        return str_replace($name, '', current($filtered));
    }
}
