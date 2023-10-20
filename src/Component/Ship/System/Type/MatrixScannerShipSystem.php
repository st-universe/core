<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class MatrixScannerShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public const SCAN_EPS_COST = 10;

    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_MATRIX_SCANNER;
    }

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        //passive system
    }

    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        //passive system
    }
}
