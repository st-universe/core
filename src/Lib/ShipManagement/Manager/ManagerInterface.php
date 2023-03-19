<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Manager;

use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface ManagerInterface
{
    /**
     * @param array<string, array<int|string, mixed>> $values
     *
     * @return array<string>
     */
    public function manage(
        ShipWrapperInterface $wrapper,
        array $values,
        ManagerProviderInterface $managerProvider
    ): array;
}
