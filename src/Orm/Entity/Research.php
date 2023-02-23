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
use Stu\Component\Colony\ColonyTypeEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ResearchRepository")
 * @Table(name="stu_research")
 **/
class Research implements ResearchInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="string")
     */
    private ?string $name = null;

    /**
     * @Column(type="text")
     */
    private ?string $description = null;

    /**
     * @Column(type="smallint")
     */
    private ?int $sort = null;

    /**
     * @Column(type="integer")
     */
    private ?int $rumps_id = null;

    /**
     * @Column(type="json")
     *
     * @var array<int>
     */
    private array $database_entries = [];

    /**
     * @Column(type="smallint")
     */
    private ?int $points = null;

    /**
     * @Column(type="integer")
     */
    private ?int $commodity_id = null;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $reward_buildplan_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $award_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $needed_award;

    /**
     * @Column(type="smallint", nullable=true)
     *
     * @var int|null
     */
    private $upper_limit_colony_type;

    /**
     * @Column(type="smallint", nullable=true)
     *
     * @var int|null
     */
    private $upper_limit_colony_amount;

    /**
     * @var CommodityInterface
     *
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Commodity")
     * @JoinColumn(name="commodity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    /**
     * @var null|ShipBuildplanInterface
     *
     * @ManyToOne(targetEntity="ShipBuildplan")
     * @JoinColumn(name="reward_buildplan_id", referencedColumnName="id")
     */
    private $rewardBuildplan;

    /**
     * @var null|AwardInterface
     *
     * @ManyToOne(targetEntity="Award")
     * @JoinColumn(name="award_id", referencedColumnName="id")
     */
    private $award;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ResearchInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): ResearchInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): ResearchInterface
    {
        $this->sort = $sort;

        return $this;
    }

    public function getRumpId(): int
    {
        return $this->rumps_id;
    }

    public function setRumpId(int $rumpId): ResearchInterface
    {
        $this->rumps_id = $rumpId;

        return $this;
    }

    public function getDatabaseEntryIds(): array
    {
        return $this->database_entries;
    }

    public function setDatabaseEntryIds(array $databaseEntryIds): ResearchInterface
    {
        $this->database_entries = $databaseEntryIds;

        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): ResearchInterface
    {
        $this->points = $points;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setCommodityId(int $commodityId): ResearchInterface
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    public function getUpperPlanetLimit(): int
    {
        return $this->upper_limit_colony_type === ColonyTypeEnum::COLONY_TYPE_PLANET
            && $this->upper_limit_colony_amount !== null ? $this->upper_limit_colony_amount : 0;
    }

    public function getUpperMoonLimit(): int
    {
        return $this->upper_limit_colony_type === ColonyTypeEnum::COLONY_TYPE_MOON
            && $this->upper_limit_colony_amount !== null ? $this->upper_limit_colony_amount : 0;
    }

    public function getUpperAsteroidLimit(): int
    {
        return $this->upper_limit_colony_type === ColonyTypeEnum::COLONY_TYPE_ASTEROID
            && $this->upper_limit_colony_amount !== null ? $this->upper_limit_colony_amount : 0;
    }

    public function getRewardBuildplanId(): ?int
    {
        return $this->reward_buildplan_id;
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    public function getRewardBuildplan(): ?ShipBuildplanInterface
    {
        return $this->rewardBuildplan;
    }

    public function getAward(): ?AwardInterface
    {
        return $this->award;
    }

    public function getNeededAwardId(): ?int
    {
        return $this->needed_award;
    }
}
