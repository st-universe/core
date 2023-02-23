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
    ->run(function (): void {
        Init::run(function (ContainerInterface $dic): void {
            $dic->get(ColonyTickRunner::class)->run();
        });
    })
    ->hour(12, 15, 18, 21, 00)
    ->minute(00)
    ->description('ColonyTick');

$schedule
    ->run(function (): void {
        Init::run(function (ContainerInterface $dic): void {
            $dic->get(ShipTickRunner::class)->run();
        });
    })
    ->hour(12, 15, 18, 21, 00)
    ->minute(00)
    ->description('ShipTick');

$schedule
    ->run(function (): void {
        Init::run(function (ContainerInterface $dic): void {
            $dic->get(TickManagerRunner::class)->run();
        });
    })
    ->hour(12, 15, 18, 21, 00)
    ->minute(00)
    ->description('TickManagerTick');

$schedule
    ->run(function (): void {
        Init::run(function (ContainerInterface $dic): void {
            $dic->get(MaintenanceTickRunner::class)->run();
        });
    })
    ->dailyAt('03:00')
    ->description('MaintenanceTick');

$schedule
    ->run(function (): void {
        Init::run(function (ContainerInterface $dic): void {
            $dic->get(ProcessTickRunner::class)->run();
        });
    })
    ->everyMinute()
    ->description('ProcessTick');

return $schedule;
