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

    public function getTooltip(): string;

    public function getIcon(): ?string;

    public function getHref(): ?string;

    public function setHide(bool $hide): ExplorableStarMapItemInterface;

    public function getFieldImagePath(): string;

    public function getBorderStyle(): string;

    public function getTerritoryStyle(): string;

    public function hasTerritory(): bool;

    public function hasEffects(): bool;

    public function isImpassable(): bool;

    public function getFieldStyle(): string;
}
