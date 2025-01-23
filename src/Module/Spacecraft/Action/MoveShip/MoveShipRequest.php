<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\MoveShip;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class MoveShipRequest implements MoveShipRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getShipId(): int
    {
        return $this->parameter('id')->int()->required();
    }

    /**
     * @return int<1, 9>
     */
    #[Override]
    public function getFieldCount(): int
    {
        return $this->parameter('navapp')->int()->between(1, 9)->defaultsTo(1);
    }

    #[Override]
    public function getDestinationPosX(): int
    {
        return $this->parameter('posx')->int()->required();
    }

    #[Override]
    public function getDestinationPosY(): int
    {
        return $this->parameter('posy')->int()->required();
    }
}
