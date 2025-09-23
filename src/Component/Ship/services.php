<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

use Stu\Component\Ship\Mining\CancelMining;
use Stu\Component\Ship\Mining\CancelMiningInterface;
use Stu\Component\Ship\Retrofit\CancelRetrofit;
use Stu\Component\Ship\Retrofit\CancelRetrofitInterface;
use Stu\Component\Ship\Wormhole\WormholeEntryPrivilegeUtility;
use Stu\Component\Ship\Wormhole\WormholeEntryPrivilegeUtilityInterface;

use function DI\autowire;

return [
    CancelMiningInterface::class => autowire(CancelMining::class),
    CancelRetrofitInterface::class => autowire(CancelRetrofit::class),
    WormholeEntryPrivilegeUtilityInterface::class => autowire(WormholeEntryPrivilegeUtility::class)
];
