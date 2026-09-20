<?php

declare(strict_types=1);

namespace Stu\Config;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Stu\Component\Game\ModuleEnum;

final class GameRunner
{

    public static function runModule(ModuleEnum $module): void
    {
        Init::run(function (ContainerInterface $dic) use ($module): void {
            $dic->get(EntityManagerInterface::class)
                ->wrapInTransaction(
                    fn() => $dic->get(GameRequestRunnerInterface::class)->run($module)
                );
        });
    }
}
