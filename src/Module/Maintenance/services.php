<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use function DI\autowire;
use function DI\get;

return [
    DatabaseBackup::class => autowire(DatabaseBackup::class),
    MapCycle::class => autowire(MapCycle::class),
    OldTachyonScanDeletion::class => autowire(OldTachyonScanDeletion::class),
    OldTradeOffersDeletion::class => autowire(OldTradeOffersDeletion::class),
    CorruptFleetDeletion::class => autowire(CorruptFleetDeletion::class),
    OldFlightSignatureDeletion::class => autowire(OldFlightSignatureDeletion::class),
    ColonyCorrectorHandler::class => autowire(ColonyCorrectorHandler::class),
    EmptyPlotDeletion::class => autowire(EmptyPlotDeletion::class),
    IdleUserDeletion::class => autowire(IdleUserDeletion::class),
    OldTradeLicenseDeletion::class => autowire(OldTradeLicenseDeletion::class),
    TopFlightsReward::class => autowire(TopFlightsReward::class),
    EndLotteryPeriod::class => autowire(EndLotteryPeriod::class),
    'maintenance_handler' => [
        get(DatabaseBackup::class),
        get(MapCycle::class),
        get(OldTachyonScanDeletion::class),
        get(OldTradeOffersDeletion::class),
        get(CorruptFleetDeletion::class),
        get(OldFlightSignatureDeletion::class),
        get(ColonyCorrectorHandler::class),
        get(EmptyPlotDeletion::class),
        get(IdleUserDeletion::class),
        get(OldTradeLicenseDeletion::class),
        get(TopFlightsReward::class),
        get(EndLotteryPeriod::class)
    ]
];
