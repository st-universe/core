<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class MatrixScannerShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public const SCAN_EPS_COST = 10;

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        //passive system
    }

    public function deactivate(ShipInterface $ship): void
    {
        //passive system
    }
}
