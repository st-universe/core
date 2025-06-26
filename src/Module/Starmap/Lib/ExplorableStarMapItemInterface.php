<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Stu\Orm\Entity\Layer;

interface ExplorableStarMapItemInterface
{
    public function getCx(): int;

    public function getCy(): int;

    public function getFieldId(): int;

    public function getLayer(): Layer;

    public function getTitle(): ?string;

    public function getIcon(): ?string;

    public function getHref(): ?string;

    public function setHide(bool $hide): ExplorableStarMapItemInterface;

    public function getFieldStyle(): string;
}
