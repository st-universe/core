<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\EditSection;

use Stu\Lib\Request\CustomControllerHelperTrait;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;

final class EditSectionRequest implements EditSectionRequestInterface
{
    use CustomControllerHelperTrait;

    private LayerRepositoryInterface $layerRepository;

    private ?LayerInterface $layer = null;

    public function __construct(LayerRepositoryInterface $layerRepository)
    {
        $this->layerRepository = $layerRepository;
    }

    public function getLayer(): LayerInterface
    {
        if ($this->layer === null) {
            $layerId = $this->queryParameter('layerid')->int()->required();
            $this->layer = $this->layerRepository->find($layerId);
        }

        return $this->layer;
    }

    public function getXCoordinate(): int
    {
        return $this->getCoordinate(
            $this->queryParameter('x')->int()->required(),
            true
        );
    }

    public function getYCoordinate(): int
    {
        return $this->getCoordinate(
            $this->queryParameter('y')->int()->required(),
            false
        );
    }

    public function getSectionId(): int
    {
        return $this->queryParameter('sec')->int()->required();
    }

    private function getCoordinate(int $value, bool $isWidth): int
    {
        $max_value = $isWidth ? $this->getLayer()->getWidth() : $this->getLayer()->getHeight();

        return max(1, min($value, $max_value));
    }
}
