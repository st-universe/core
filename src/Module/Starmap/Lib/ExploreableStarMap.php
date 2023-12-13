<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Stu\Orm\Entity\MapBorderTypeInterface;
use Stu\Orm\Entity\MapRegionInterface;
use Stu\Orm\Entity\StarSystemInterface;

#[Entity]
class ExploreableStarMap implements ExploreableStarMapInterface
{
    #[Id]
    private int $id = 0;

    #[Column(type: 'integer')]
    private int $cx = 0;

    #[Column(type: 'integer')]
    private int $cy = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $field_id = 0;

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

    #[ManyToOne(targetEntity: 'Stu\Orm\Entity\MapBorderType')]
    #[JoinColumn(name: 'bordertype_id', referencedColumnName: 'id')]
    private ?MapBorderTypeInterface $mapBorderType = null;

    #[ManyToOne(targetEntity: 'Stu\Orm\Entity\StarSystem')]
    #[JoinColumn(name: 'influence_area_id', referencedColumnName: 'id')]
    private ?StarSystemInterface $influenceArea = null;

    #[ManyToOne(targetEntity: 'Stu\Orm\Entity\MapRegion')]
    #[JoinColumn(name: 'region_id', referencedColumnName: 'id')]
    private ?MapRegionInterface $adminRegion = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCx(): int
    {
        return $this->cx;
    }

    public function getCy(): int
    {
        return $this->cy;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function getLayer(): int
    {
        return $this->layer_id;
    }

    public function getBordertypeId(): ?int
    {
        return $this->bordertype_id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getMapped(): ?int
    {
        return $this->mapped;
    }

    public function getSystemName(): ?string
    {
        return $this->system_name;
    }

    public function getTradePostId(): ?int
    {
        return $this->tradepost_id;
    }

    public function getRegionDescription(): ?string
    {
        return $this->region_description;
    }

    public function setRegionDescription(string $regiondescription): ExploreableStarMapInterface
    {
        $this->region_description = $regiondescription;
        return $this;
    }

    public function getMapBorderType(): ?MapBorderTypeInterface
    {
        return $this->mapBorderType;
    }

    public function getAdminRegion(): ?MapRegionInterface
    {
        return $this->adminRegion;
    }

    public function getInfluenceArea(): ?StarSystemInterface
    {
        return $this->influenceArea;
    }
}
