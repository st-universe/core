<?php

declare(strict_types=1);

namespace Stu\Component\Admin;

use Stu\Component\Admin\Notification\FailureEmailSender;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Component\Admin\Reset\Alliance\AllianceReset;
use Stu\Component\Admin\Reset\Alliance\AllianceResetInterface;
use Stu\Component\Admin\Reset\Communication\PmReset;
use Stu\Component\Admin\Reset\Communication\PmResetInterface;
use Stu\Component\Admin\Reset\EntityReset;
use Stu\Component\Admin\Reset\Fleet\FleetReset;
use Stu\Component\Admin\Reset\Fleet\FleetResetInterface;
use Stu\Component\Admin\Reset\ResetManager;
use Stu\Component\Admin\Reset\ResetManagerInterface;
use Stu\Component\Admin\Reset\SequenceReset;
use Stu\Component\Admin\Reset\SequenceResetInterface;
use Stu\Component\Admin\Reset\Ship\ShipReset;
use Stu\Component\Admin\Reset\Ship\ShipResetInterface;
use Stu\Component\Admin\Reset\User\UserReset;
use Stu\Component\Admin\Reset\User\UserResetInterface;

use function DI\autowire;

return [
    AllianceResetInterface::class => autowire(AllianceReset::class),
    FailureEmailSenderInterface::class => autowire(FailureEmailSender::class),
    FleetResetInterface::class => autowire(FleetReset::class),
    PmResetInterface::class => autowire(PmReset::class),
    SequenceResetInterface::class => autowire(SequenceReset::class),
    ShipResetInterface::class => autowire(ShipReset::class),
    UserResetInterface::class => autowire(UserReset::class),
    ResetManagerInterface::class => autowire(ResetManager::class)
        ->constructorParameter('entityReset', autowire(EntityReset::class))
];
