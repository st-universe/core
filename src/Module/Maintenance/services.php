<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use function DI\autowire;

return [
    DatabaseBackup::class => autowire(DatabaseBackup::class),
    MapCycle::class => autowire(MapCycle::class),
    ExpiredInvitationTokenDeletion::class => autowire(ExpiredInvitationTokenDeletion::class),
    OldTachyonScanDeletion::class => autowire(OldTachyonScanDeletion::class),
    OldTradeOffersDeletion::class => autowire(OldTradeOffersDeletion::class),
    CorruptFleetDeletion::class => autowire(CorruptFleetDeletion::class),
    OldFlightSignatureDeletion::class => autowire(OldFlightSignatureDeletion::class),
    ColonyCorrectorHandler::class => autowire(ColonyCorrectorHandler::class),
    EmptyPlotDeletion::class => autowire(EmptyPlotDeletion::class),
    IdleUserDeletion::class => autowire(IdleUserDeletion::class)
];
