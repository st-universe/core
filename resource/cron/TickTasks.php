<?php

declare(strict_types=1);

use Crunz\Schedule;
use Noodlehaus\ConfigInterface;
use Psr\Container\ContainerInterface;
use Stu\Config\Init;
use Stu\Module\Tick\Colony\ColonyTickRunner;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunner;
use Stu\Module\Tick\Manager\TickManagerRunner;
use Stu\Module\Tick\Process\ProcessTickRunner;
use Stu\Module\Tick\Ship\ShipTickRunner;

/** @todo remove after magic container-calls have been purged from the game */
global $container;

/**
 * @var ConfigInterface
 */
$config = $container->get(ConfigInterface::class);

$schedule = new Schedule();

//split colony tick into groups
$colonyTickGroupCount = (int)$config->get('game.colony.tick_worker');
for ($groupId = 1; $groupId <= $colonyTickGroupCount; $groupId++) {
    $schedule
        ->run(function () use ($groupId, $colonyTickGroupCount): void {
            Init::run(function (ContainerInterface $dic) use ($groupId, $colonyTickGroupCount): void {
                /** @todo remove after magic container-calls have been purged from the game */
                global $container;
                $container = $dic;

                $dic->get(ColonyTickRunner::class)->run($groupId, $colonyTickGroupCount);
            });
        })
        ->hour(12, 15, 18, 21, 00)
        ->minute(00)
        ->description(sprintf('ColonyTick (group %d/%d)', $groupId, $colonyTickGroupCount));
}

$schedule
    ->run(function (): void {
        Init::run(function (ContainerInterface $dic): void {
            /** @todo remove after magic container-calls have been purged from the game */
            global $container;
            $container = $dic;

            $dic->get(ShipTickRunner::class)->run(1, 1);
        });
    })
    ->hour(12, 15, 18, 21, 00)
    ->minute(00)
    ->description('ShipTick');

$schedule
    ->run(function (): void {
        Init::run(function (ContainerInterface $dic): void {
            /** @todo remove after magic container-calls have been purged from the game */
            global $container;
            $container = $dic;

            $dic->get(TickManagerRunner::class)->run(1, 1);
        });
    })
    ->hour(12, 15, 18, 21, 00)
    ->minute(00)
    ->description('TickManagerTick');

$schedule
    ->run(function (): void {
        Init::run(function (ContainerInterface $dic): void {
            /** @todo remove after magic container-calls have been purged from the game */
            global $container;
            $container = $dic;

            $dic->get(MaintenanceTickRunner::class)->run(1, 1);
        });
    })
    ->dailyAt('03:00')
    ->description('MaintenanceTick');

$schedule
    ->run(function (): void {
        Init::run(function (ContainerInterface $dic): void {
            /** @todo remove after magic container-calls have been purged from the game */
            global $container;
            $container = $dic;

            $dic->get(ProcessTickRunner::class)->run(1, 1);
        });
    })
    ->everyMinute()
    ->description('ProcessTick');

return $schedule;
