<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowByPosition;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowByPositionRequest implements ShowByPositionRequestInterface
{
    use CustomControllerHelperTrait;

    public function getXCoordinate(): int
    {
        return $this->getCoordinate(
            $this->queryParameter('x')->int()->required(),
            MAP_MAX_X
        );
    }

    public function getYCoordinate(): int
    {
        return $this->getCoordinate(
            $this->queryParameter('y')->int()->required(),
            MAP_MAX_Y
        );
    }

    private function getCoordinate(int $value, int $max_value): int {
        return max(1, min($value, $max_value));
    }
}