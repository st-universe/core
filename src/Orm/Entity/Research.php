<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Component\Colony\ColonyTypeEnum;
use Stu\Orm\Repository\ResearchRepository;

#[Table(name: 'stu_research')]
#[Entity(repositoryClass: ResearchRepository::class)]
class Research implements ResearchInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $name;

    #[Column(type: 'text')]
    private string $description;

    #[Column(type: 'smallint')]
    private int $sort;

    #[Column(type: 'integer')]
    private int $rump_id;

    /**
     * @var int[]|null
     */
    #[Column(type: 'json')]
    private ?array $database_entries = null;

    #[Column(type: 'smallint')]
    private int $points;

    #[Column(type: 'integer')]
    private int $commodity_id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $reward_buildplan_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $award_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $needed_award = null;

    #[Column(type: 'smallint', nullable: true)]
    private ?int $upper_limit_colony_type = null;

    #[Column(type: 'smallint', nullable: true)]
    private ?int $upper_limit_colony_amount = null;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $commodity;

    #[ManyToOne(targetEntity: SpacecraftBuildplan::class)]
    #[JoinColumn(name: 'reward_buildplan_id', referencedColumnName: 'id')]
    private ?SpacecraftBuildplanInterface $rewardBuildplan = null;

    #[ManyToOne(targetEntity: Award::class)]
    #[JoinColumn(name: 'award_id', referencedColumnName: 'id')]
    private ?AwardInterface $award = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): ResearchInterface
    {
        $this->name = $name;

        return $this;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function setDescription(string $description): ResearchInterface
    {
        $this->description = $description;

        return $this;
    }

    #[Override]
    public function getSort(): int
    {
        return $this->sort;
    }

    #[Override]
    public function setSort(int $sort): ResearchInterface
    {
        $this->sort = $sort;

        return $this;
    }

    #[Override]
    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    #[Override]
    public function getDatabaseEntryIds(): array
    {
        return $this->database_entries;
    }

    #[Override]
    public function setDatabaseEntryIds(array $databaseEntryIds): ResearchInterface
    {
        $this->database_entries = $databaseEntryIds;

        return $this;
    }

    #[Override]
    public function getPoints(): int
    {
        return $this->points;
    }

    #[Override]
    public function setPoints(int $points): ResearchInterface
    {
        $this->points = $points;

        return $this;
    }

    #[Override]
    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    #[Override]
    public function setCommodityId(int $commodityId): ResearchInterface
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    #[Override]
    public function getUpperPlanetLimit(): int
    {
        return $this->upper_limit_colony_type === ColonyTypeEnum::COLONY_TYPE_PLANET
            && $this->upper_limit_colony_amount !== null ? $this->upper_limit_colony_amount : 0;
    }

    #[Override]
    public function getUpperMoonLimit(): int
    {
        return $this->upper_limit_colony_type === ColonyTypeEnum::COLONY_TYPE_MOON
            && $this->upper_limit_colony_amount !== null ? $this->upper_limit_colony_amount : 0;
    }

    #[Override]
    public function getUpperAsteroidLimit(): int
    {
        return $this->upper_limit_colony_type === ColonyTypeEnum::COLONY_TYPE_ASTEROID
            && $this->upper_limit_colony_amount !== null ? $this->upper_limit_colony_amount : 0;
    }

    #[Override]
    public function getRewardBuildplanId(): ?int
    {
        return $this->reward_buildplan_id;
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    #[Override]
    public function getRewardBuildplan(): ?SpacecraftBuildplanInterface
    {
        return $this->rewardBuildplan;
    }

    #[Override]
    public function getAward(): ?AwardInterface
    {
        return $this->award;
    }

    #[Override]
    public function getNeededAwardId(): ?int
    {
        return $this->needed_award;
    }
}
