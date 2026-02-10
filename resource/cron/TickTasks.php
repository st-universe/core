<?php

declare(strict_types=1);

use Crunz\Schedule;
use Psr\Container\ContainerInterface;
use Stu\Config\Init;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Tick\Colony\ColonyTickRunner;
use Stu\Module\Tick\History\HistoryTickRunner;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunner;
use Stu\Module\Tick\Manager\TickManagerRunner;
use Stu\Module\Tick\Pirate\PirateTickRunner;
use Stu\Module\Tick\Process\ProcessTickRunner;
use Stu\Module\Tick\Spacecraft\SpacecraftTickRunner;

$schedule = new Schedule();

//split colony tick into groups
$colonyTickGroupCount = Init::getContainer()
    ->get(StuConfigInterface::class)
    ->getGameSettings()
    ->getColonySettings()
    ->getTickWorker();

for ($groupId = 1; $groupId <= $colonyTickGroupCount; $groupId++) {
    $schedule
        ->run(function () use ($groupId, $colonyTickGroupCount): void {
            Init::run(function (ContainerInterface $dic) use ($groupId, $colonyTickGroupCount): void {
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
            $dic->get(SpacecraftTickRunner::class)->run(1, 1);
        });
    })
    ->hour(12, 15, 18, 21, 00)
    ->minute(00)
    ->description('SpacecraftTick');

$schedule
    ->run(function (): void {
        Init::run(function (ContainerInterface $dic): void {
            $dic->get(TickManagerRunner::class)->run(1, 1);
        });
    })
    ->hour(12, 15, 18, 21, 00)
    ->minute(00)
    ->description('TickManagerTick');

$schedule
    ->run(function (): void {
        Init::run(function (ContainerInterface $dic): void {
            $dic->get(MaintenanceTickRunner::class)->run(1, 1);
        });
    })
    ->dailyAt('03:00')
    ->description('MaintenanceTick');

$schedule
    ->run(function (): void {
        Init::run(function (ContainerInterface $dic): void {
            $dic->get(ProcessTickRunner::class)->run(1, 1);
        });
    })
    ->everyMinute()
    ->description('ProcessTick');

$schedule
    ->run(function (): void {
        Init::run(function (ContainerInterface $dic): void {
            $dic->get(PirateTickRunner::class)->run(1, 1);
        });
    })
    ->between('12:00', '23:59')
    ->minute(05, 15, 25, 35, 45, 55)
    ->description('PirateTick');

$schedule
    ->run(function (): void {
        Init::run(function (ContainerInterface $dic): void {
        $dic->get(HistoryTickRunner::class)->run(1, 1);
        });
    })
    ->hourlyAt(30)
    ->description('HistoryTick');

return $schedule;
