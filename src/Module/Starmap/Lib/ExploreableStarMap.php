<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Override;
use Stu\Orm\Entity\MapBorderType;
use Stu\Orm\Entity\MapRegion;
use Stu\Orm\Entity\StarSystem;

#[Entity]
class ExploreableStarMap implements ExploreableStarMapInterface
{
    #[Id]
    #[Column(type: 'integer')]
    private int $id = 0;

    #[Column(type: 'integer')]
    private int $cx = 0;

    #[Column(type: 'integer')]
    private int $cy = 0;

    #[Column(type: 'integer')]
    private int $field_id = 0;

    #[Column(type: 'integer')]
    private int $layer_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $bordertype_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $user_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $mapped = 0;

    #[Column(type: 'string', nullable: true)]
    private ?string $system_name = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $influence_area_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $region_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $tradepost_id = null;

    #[Column(type: 'string', nullable: true)]
    private ?string $region_description = null;

    #[ManyToOne(targetEntity: MapBorderType::class)]
    #[JoinColumn(name: 'bordertype_id', referencedColumnName: 'id')]
    private ?MapBorderType $mapBorderType = null;

    #[ManyToOne(targetEntity: StarSystem::class)]
    #[JoinColumn(name: 'influence_area_id', referencedColumnName: 'id')]
    private ?StarSystem $influenceArea = null;

    #[ManyToOne(targetEntity: MapRegion::class)]
    #[JoinColumn(name: 'region_id', referencedColumnName: 'id')]
    private ?MapRegion $adminRegion = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getCx(): int
    {
        return $this->cx;
    }

    #[Override]
    public function getCy(): int
    {
        return $this->cy;
    }

    #[Override]
    public function getFieldId(): int
    {
        return $this->field_id;
    }

    #[Override]
    public function getLayer(): int
    {
        return $this->layer_id;
    }

    #[Override]
    public function getBordertypeId(): ?int
    {
        return $this->bordertype_id;
    }

    #[Override]
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    #[Override]
    public function getMapped(): ?int
    {
        return $this->mapped;
    }

    #[Override]
    public function getSystemName(): ?string
    {
        return $this->system_name;
    }

    #[Override]
    public function getTradePostId(): ?int
    {
        return $this->tradepost_id;
    }

    #[Override]
    public function getRegionDescription(): ?string
    {
        return $this->region_description;
    }

    #[Override]
    public function setRegionDescription(string $regiondescription): ExploreableStarMap
    {
        $this->region_description = $regiondescription;
        return $this;
    }

    #[Override]
    public function getMapBorderType(): ?MapBorderType
    {
        return $this->mapBorderType;
    }

    #[Override]
    public function getAdminRegion(): ?MapRegion
    {
        return $this->adminRegion;
    }

    #[Override]
    public function getInfluenceArea(): ?StarSystem
    {
        return $this->influenceArea;
    }
}
