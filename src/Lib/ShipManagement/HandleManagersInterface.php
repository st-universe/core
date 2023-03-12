<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement;

use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface HandleManagersInterface
{
    /**
     * @param array<string, array<int|string, mixed>> $values
     * 
     * @return array<string>
     */
    public function handle(ShipWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array;
}
