<?php

namespace Stu\Module\Starmap\Lib;

use Stu\Orm\Entity\MapBorderTypeInterface;
use Stu\Orm\Entity\MapRegionInterface;
use Stu\Orm\Entity\StarSystemInterface;

interface ExploreableStarMapInterface
{
    public function getId(): int;

    public function getCx(): int;

    public function getCy(): int;

    public function getFieldId(): int;

    public function getLayer(): int;

    public function getBordertypeId(): ?int;

    public function getUserId(): ?int;

    public function getMapped(): ?int;

    public function getSystemName(): ?string;

    public function getTradePostId(): ?int;

    public function getRegionDescription(): ?string;

    public function setRegionDescription(string $regiondescription): ExploreableStarMapInterface;

    public function getMapBorderType(): ?MapBorderTypeInterface;

    public function getAdminRegion(): ?MapRegionInterface;

    public function getInfluenceArea(): ?StarSystemInterface;
}
