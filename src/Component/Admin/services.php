<?php

declare(strict_types=1);

namespace Stu\Component\Admin;

use Stu\Component\Admin\Notification\FailureEmailSender;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Component\Admin\Reset\Alliance\AllianceReset;
use Stu\Component\Admin\Reset\Alliance\AllianceResetInterface;
use Stu\Component\Admin\Reset\Communication\KnReset;
use Stu\Component\Admin\Reset\Communication\KnResetInterface;
use Stu\Component\Admin\Reset\Communication\PmReset;
use Stu\Component\Admin\Reset\Communication\PmResetInterface;
use Stu\Component\Admin\Reset\Crew\CrewReset;
use Stu\Component\Admin\Reset\Crew\CrewResetInterface;
use Stu\Component\Admin\Reset\Fleet\FleetReset;
use Stu\Component\Admin\Reset\Fleet\FleetResetInterface;
use Stu\Component\Admin\Reset\Map\MapReset;
use Stu\Component\Admin\Reset\Map\MapResetInterface;
use Stu\Component\Admin\Reset\ResetManager;
use Stu\Component\Admin\Reset\ResetManagerInterface;
use Stu\Component\Admin\Reset\SequenceReset;
use Stu\Component\Admin\Reset\SequenceResetInterface;
use Stu\Component\Admin\Reset\Ship\ShipReset;
use Stu\Component\Admin\Reset\Ship\ShipResetInterface;
use Stu\Component\Admin\Reset\Storage\StorageReset;
use Stu\Component\Admin\Reset\Storage\StorageResetInterface;
use Stu\Component\Admin\Reset\Trade\TradeReset;
use Stu\Component\Admin\Reset\Trade\TradeResetInterface;
use Stu\Component\Admin\Reset\User\UserReset;
use Stu\Component\Admin\Reset\User\UserResetInterface;

use function DI\autowire;

return [
    AllianceResetInterface::class => autowire(AllianceReset::class),
    CrewResetInterface::class => autowire(CrewReset::class),
    FailureEmailSenderInterface::class => autowire(FailureEmailSender::class),
    FleetResetInterface::class => autowire(FleetReset::class),
    KnResetInterface::class => autowire(KnReset::class),
    MapResetInterface::class => autowire(MapReset::class),
    PmResetInterface::class => autowire(PmReset::class),
    SequenceResetInterface::class => autowire(SequenceReset::class),
    ResetManagerInterface::class => autowire(ResetManager::class),
    ShipResetInterface::class => autowire(ShipReset::class),
    StorageResetInterface::class => autowire(StorageReset::class),
    TradeResetInterface::class => autowire(TradeReset::class),
    UserResetInterface::class => autowire(UserReset::class)
];
