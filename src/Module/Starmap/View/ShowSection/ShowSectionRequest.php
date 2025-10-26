<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowSection;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowSectionRequest implements ShowSectionRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getLayerId(): int
    {
        return $this->parameter('layerid')->int()->required();
    }

    #[\Override]
    public function getSection(): int
    {
        return $this->parameter('section')->int()->required();
    }

    #[\Override]
    public function getDirection(): ?int
    {
        return $this->parameter('direction')->int()->defaultsTo(null);
    }
}
