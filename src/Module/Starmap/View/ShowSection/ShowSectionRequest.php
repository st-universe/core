<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowSection;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowSectionRequest implements ShowSectionRequestInterface
{
    use CustomControllerHelperTrait;

    public function getLayerId(): int
    {
        return $this->queryParameter('layerid')->int()->required();
    }

    public function getSection(): int
    {
        return $this->queryParameter('section')->int()->required();
    }

    public function getDirection(): ?int
    {
        return $this->queryParameter('direction')->int()->defaultsTo(null);
    }
}
