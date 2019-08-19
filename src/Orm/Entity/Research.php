<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use GoodData;
use ResearchDependency;
use ResearchUser;

/**
 * @Entity
 * @Table(name="stu_research")
 * @Entity(repositoryClass="Stu\Orm\Repository\ResearchRepository")
 **/
final class Research implements ResearchInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
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

    private $state;

    private $excludes;

    private $positiveDependencies;

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

    public function getGood(): GoodData
    {
        return ResourceCache()->getObject('good', $this->getGoodId());
    }

    public function getResearchState()
    {
        if ($this->state === null) {
            $this->state = ResearchUser::getByResearch($this->getId(), currentUser()->getId());
        }
        return $this->state;
    }

    public function getExcludes(): array
    {
        if ($this->excludes === null) {
            $this->excludes = ResearchDependency::getExcludesByResearch($this->getId());
        }
        return $this->excludes;
    }

    public function hasExcludes(): bool
    {
        return count($this->getExcludes()) > 0;
    }

    public function getPositiveDependencies(): array
    {
        if ($this->positiveDependencies === null) {
            $this->positiveDependencies = ResearchDependency::getPositiveDependenciesByResearch($this->getId());
        }
        return $this->positiveDependencies;
    }

    public function hasPositiveDependencies(): bool
    {
        return count($this->getPositiveDependencies()) > 0;
    }

    public function getDonePoints(): int
    {
        return $this->getPoints() - $this->getResearchState()->getActive();
    }

    public function isStartResearch(): bool
    {
        return in_array($this->getId(), getDefaultTechs());
    }
}
