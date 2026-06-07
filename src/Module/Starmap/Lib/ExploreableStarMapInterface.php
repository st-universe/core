<?php

namespace Stu\Module\Starmap\Lib;

use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Orm\Entity\MapBorderType;
use Stu\Orm\Entity\MapRegion;
use Stu\Orm\Entity\StarSystem;

interface ExploreableStarMapInterface
{
    public function getId(): int;

    public function getCx(): int;

    public function getCy(): int;

    public function getFieldId(): int;

    public function getFieldName(): string;

    public function getPassable(): bool;

    /** @return array<FieldTypeEffectEnum> */
    public function getEffects(): array;

    public function getLayer(): int;

    public function getBordertypeId(): ?int;

    public function getUserId(): ?int;

    public function getMapped(): ?int;

    public function getSystemName(): ?string;

    public function getTradePostId(): ?int;

    public function getRegionDescription(): ?string;

    public function setRegionDescription(string $regiondescription): ExploreableStarMap;

    public function getMapBorderType(): ?MapBorderType;

    public function getAdminRegion(): ?MapRegion;

    public function getInfluenceArea(): ?StarSystem;
}
