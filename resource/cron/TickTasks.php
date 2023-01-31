<?php

declare(strict_types=1);

use Crunz\Schedule;
use Psr\Container\ContainerInterface;
use Stu\Config\Init;
use Stu\Module\Tick\Colony\ColonyTickRunner;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunner;
use Stu\Module\Tick\Manager\TickManagerRunner;
use Stu\Module\Tick\Process\ProcessTickRunner;
use Stu\Module\Tick\Ship\ShipTickRunner;

$schedule = new Schedule();

$schedule
    ->run(function(): void {
        Init::run(function (ContainerInterface $dic): void {
            /** @todo remove after magic container-calls have been purged from the game */
            global $container;
            $container = $dic;

            $dic->get(ColonyTickRunner::class)->run();
        });
    })
    ->everyThreeHours()
    ->between('12:00', '0:00')
    ->description('ColonyTick');

$schedule
    ->run(function(): void {
        Init::run(function (ContainerInterface $dic): void {
            /** @todo remove after magic container-calls have been purged from the game */
            global $container;
            $container = $dic;

            $dic->get(ShipTickRunner::class)->run();
        });
    })
    ->everyThreeHours()
    ->between('12:00', '0:00')
    ->description('ShipTick');

$schedule
    ->run(function(): void {
        Init::run(function (ContainerInterface $dic): void {
            /** @todo remove after magic container-calls have been purged from the game */
            global $container;
            $container = $dic;

            $dic->get(TickManagerRunner::class)->run();
        });
    })
    ->everyThreeHours()
    ->between('12:00', '0:00')
    ->description('TickManagerTick');

$schedule
    ->run(function(): void {
        Init::run(function (ContainerInterface $dic): void {
            /** @todo remove after magic container-calls have been purged from the game */
            global $container;
            $container = $dic;

            $dic->get(MaintenanceTickRunner::class)->run();
        });
    })
    ->dailyAt('03:00')
    ->description('MaintenanceTick');

$schedule
    ->run(function(): void {
        Init::run(function (ContainerInterface $dic): void {
            /** @todo remove after magic container-calls have been purged from the game */
            global $container;
            $container = $dic;

            $dic->get(ProcessTickRunner::class)->run();
        });
    })
    ->everyMinute()
    ->description('ProcessTick');

return $schedule;
