<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use function DI\autowire;

return [
    DatabaseBackup::class => autowire(DatabaseBackup::class),
    MapCycle::class => autowire(MapCycle::class),
    IdleUserDeletion::class => autowire(IdleUserDeletion::class),
    ExpiredInvitationTokenDeletion::class => autowire(ExpiredInvitationTokenDeletion::class),
    OldTachyonScanDeletion::class => autowire(OldTachyonScanDeletion::class),
    OldTradeOffersDeletion::class => autowire(OldTradeOffersDeletion::class),
    CorruptFleetDeletion::class => autowire(CorruptFleetDeletion::class),
    OldFlightSignatureDeletion::class => autowire(OldFlightSignatureDeletion::class),
    ColonyCorrector::class => autowire(ColonyCorrector::class)
    //TODO handler to delete knPlots older than a week with no postings -> repo query
    // -> PMs to plot owner and participants
];
