<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class MatrixScannerShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public const int SCAN_EPS_COST = 10;

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_MATRIX_SCANNER;
    }

    #[Override]
    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        //passive system
    }

    #[Override]
    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        //passive system
    }
}
