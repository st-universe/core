<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowSection;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowSectionRequest implements ShowSectionRequestInterface
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

    public function getSectionId(): int
    {
        return $this->queryParameter('sec')->int()->required();
    }

    private function getCoordinate(int $value, int $max_value): int {
        return max(1, min($value, $max_value));
    }
}