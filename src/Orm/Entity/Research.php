<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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
     */
    private $id;

    /** @Column(type="string") * */
    private $name;

    /** @Column(type="text") * */
    private $description;

    /** @Column(type="smallint") * */
    private $sort;

    /** @Column(type="integer") * */
    private $rumps_id;

    /** @Column(type="json") * */
    private $database_entries;

    /** @Column(type="smallint") * */
    private $points;

    /** @Column(type="integer") * */
    private $good_id;

    /** @Column(type="smallint") * */
    private $upper_planetlimit;

    /** @Column(type="smallint") * */
    private $upper_moonlimit;

    /** @Column(type="integer", nullable=true) * */
    private $reward_buildplan_id;

    /** @Column(type="integer", nullable=true) * */
    private $needed_award;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Commodity")
     * @JoinColumn(name="good_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $good;

    /**
     * @ManyToOne(targetEntity="ShipBuildplan")
     * @JoinColumn(name="reward_buildplan_id", referencedColumnName="id")
     */
    private $rewardBuildplan;

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

    public function getGoodId(): int
    {
        return $this->good_id;
    }

    public function setGoodId(int $good_id): ResearchInterface
    {
        $this->good_id = $good_id;

        return $this;
    }

    public function getUpperPlanetLimit(): int
    {
        return $this->upper_planetlimit;
    }

    public function setUpperPlanetLimit(int $upperPlanetLimit): ResearchInterface
    {
        $this->upper_planetlimit = $upperPlanetLimit;

        return $this;
    }

    public function getUpperMoonLimit(): int
    {
        return $this->upper_moonlimit;
    }

    public function setUpperMoonLimit(int $upperMoonLimit): ResearchInterface
    {
        $this->upper_moonlimit = $upperMoonLimit;

        return $this;
    }

    public function getRewardBuildplanId(): ?int
    {
        return $this->reward_buildplan_id;
    }

    public function getGood(): CommodityInterface
    {
        return $this->good;
    }

    public function getRewardBuildplan(): ?ShipBuildplanInterface
    {
        return $this->rewardBuildplan;
    }

    public function getNeededAward(): ?int
    {
        return $this->needed_award;
    }
}
