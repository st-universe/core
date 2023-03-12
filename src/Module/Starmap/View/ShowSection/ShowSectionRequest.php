<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowSection;

use Stu\Lib\Request\CustomControllerHelperTrait;
use Stu\Orm\Entity\LayerInterface;

final class ShowSectionRequest implements ShowSectionRequestInterface
{
    use CustomControllerHelperTrait;

    public function getLayerId(): int
    {
        return $this->queryParameter('layerid')->int()->required();
    }

    public function getXCoordinate(LayerInterface $layer): int
    {
        return $this->getCoordinate(
            $layer,
            $this->queryParameter('x')->int()->required(),
            true
        );
    }

    public function getYCoordinate(LayerInterface $layer): int
    {
        return $this->getCoordinate(
            $layer,
            $this->queryParameter('y')->int()->required(),
            false
        );
    }

    public function getSectionId(): int
    {
        return $this->queryParameter('sec')->int()->required();
    }

    private function getCoordinate(LayerInterface $layer, int $value, bool $isWidth): int
    {
        $max_value = $isWidth ? $layer->getWidth() : $layer->getHeight();

        return max(1, min($value, $max_value));
    }
}
