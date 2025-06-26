<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use function DI\autowire;
use function DI\get;

return [
    ColonyCorrectorHandler::class => autowire(ColonyCorrectorHandler::class),
    CorruptFleetDeletion::class => autowire(CorruptFleetDeletion::class),
    CreateInterstellarMedia::class => autowire(CreateInterstellarMedia::class),
    CreateMissingUserAwards::class => autowire(CreateMissingUserAwards::class),
    DatabaseBackup::class => autowire(DatabaseBackup::class),
    EmptyPlotDeletion::class => autowire(EmptyPlotDeletion::class),
    EndLotteryPeriod::class => autowire(EndLotteryPeriod::class),
    IdleUserDeletion::class => autowire(IdleUserDeletion::class),
    IdleUserWarning::class => autowire(IdleUserWarning::class),
    MapCycle::class => autowire(MapCycle::class),
    OldFlightSignatureDeletion::class => autowire(OldFlightSignatureDeletion::class),
    OldTachyonScanDeletion::class => autowire(OldTachyonScanDeletion::class),
    OldTradeLicenseDeletion::class => autowire(OldTradeLicenseDeletion::class),
    OldTradeOffersDeletion::class => autowire(OldTradeOffersDeletion::class),
    TopFlightsReward::class => autowire(TopFlightsReward::class),
    BeginPirateRound::class => autowire(BeginPirateRound::class),
    UserInformation::class => autowire(UserInformation::class),
    MaintenanceHandlerInterface::class => [
        get(DatabaseBackup::class),
        get(IdleUserWarning::class),
        get(IdleUserDeletion::class),
        get(CreateInterstellarMedia::class),
        get(MapCycle::class),
        get(CreateMissingUserAwards::class),
        get(OldTachyonScanDeletion::class),
        get(OldTradeOffersDeletion::class),
        get(CorruptFleetDeletion::class),
        get(OldFlightSignatureDeletion::class),
        get(ColonyCorrectorHandler::class),
        get(SpacecraftCorrectorHandler::class),
        get(EmptyPlotDeletion::class),
        get(OldTradeLicenseDeletion::class),
        get(TopFlightsReward::class),
        get(BeginPirateRound::class),
        get(EndLotteryPeriod::class),
        get(PirateWrathDecreaser::class),
        get(UserInformation::class),
        get(GameRequestCleanUp::class)
    ]
];
