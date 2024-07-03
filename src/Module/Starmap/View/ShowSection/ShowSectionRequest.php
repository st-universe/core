<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowSection;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowSectionRequest implements ShowSectionRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getLayerId(): int
    {
        return $this->queryParameter('layerid')->int()->required();
    }

    #[Override]
    public function getSection(): int
    {
        return $this->queryParameter('section')->int()->required();
    }

    #[Override]
    public function getDirection(): ?int
    {
        return $this->queryParameter('direction')->int()->defaultsTo(null);
    }
}
