<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Exception;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class MoveShipRequest implements MoveShipRequestInterface
{
    use CustomControllerHelperTrait;

    public function getShipId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

    /**
     * @return int<1, 9>
     */
    public function getFieldCount(): int
    {
        return $this->queryParameter('navapp')->int()->between(1, 9)->defaultsTo(1);
    }

    public function getDestinationPosX(): int
    {
        return $this->queryParameter('posx')->int()->required();
    }

    public function getDestinationPosY(): int
    {
        return $this->queryParameter('posy')->int()->required();
    }
}
