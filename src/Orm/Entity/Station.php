<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Orm\Repository\StationRepository;

#[Table(name: 'stu_station')]
#[Index(name: 'station_influence_area_idx', columns: ['influence_area_id'])]
#[Entity(repositoryClass: StationRepository::class)]
class Station extends Spacecraft implements StationInterface
{
    #[Column(type: 'integer', nullable: true)]
    private ?int $influence_area_id = null;

    #[OneToOne(targetEntity: 'TradePost', mappedBy: 'station')]
    private ?TradePostInterface $tradePost = null;

    #[OneToOne(targetEntity: 'ConstructionProgress', mappedBy: 'station')]
    private ?ConstructionProgressInterface $constructionProgress = null;

    /**
     * @var ArrayCollection<int, ShipInterface>
     */
    #[OneToMany(targetEntity: 'Ship', mappedBy: 'dockedTo', indexBy: 'id')]
    #[OrderBy(['fleet_id' => 'DESC', 'is_fleet_leader' => 'DESC'])]
    private Collection $dockedShips;

    /**
     * @var ArrayCollection<int, DockingPrivilegeInterface>
     */
    #[OneToMany(targetEntity: 'DockingPrivilege', mappedBy: 'station')]
    private Collection $dockingPrivileges;

    #[OneToOne(targetEntity: 'StarSystem')]
    #[JoinColumn(name: 'influence_area_id', referencedColumnName: 'id')]
    private ?StarSystemInterface $influenceArea = null;

    public function __construct()
    {
        parent::__construct();
        $this->dockedShips = new ArrayCollection();
        $this->dockingPrivileges = new ArrayCollection();
    }

    #[Override]
    public function getType(): SpacecraftTypeEnum
    {
        return SpacecraftTypeEnum::STATION;
    }

    #[Override]
    public function getFleet(): ?FleetInterface
    {
        return null;
    }

    #[Override]
    public function getTradePost(): ?TradePostInterface
    {
        return $this->tradePost;
    }

    #[Override]
    public function setTradePost(?TradePostInterface $tradePost): StationInterface
    {
        $this->tradePost = $tradePost;

        return $this;
    }

    #[Override]
    public function getInfluenceArea(): ?StarSystemInterface
    {
        return $this->influenceArea;
    }

    #[Override]
    public function setInfluenceArea(?StarSystemInterface $influenceArea): StationInterface
    {
        $this->influenceArea = $influenceArea;
        return $this;
    }

    #[Override]
    public function getConstructionProgress(): ?ConstructionProgressInterface
    {
        return $this->constructionProgress;
    }

    #[Override]
    public function getModules(): array
    {
        $constructionProgress = $this->getConstructionProgress();
        if ($constructionProgress === null) {
            return [];
        }

        return parent::getModules() +
            $constructionProgress
            ->getSpecialModules()
            ->map(fn(ConstructionProgressModuleInterface $progressModule): ModuleInterface => $progressModule->getModule())
            ->toArray();
    }

    #[Override]
    public function getDockPrivileges(): Collection
    {
        return $this->dockingPrivileges;
    }

    #[Override]
    public function getDockingSlotCount(): int
    {
        return ($this->getState() === SpacecraftStateEnum::SHIP_STATE_UNDER_CONSTRUCTION)
            || ($this->getState() === SpacecraftStateEnum::SHIP_STATE_UNDER_SCRAPPING)
            ? 50 : $this->getRump()->getDockingSlots();
    }

    #[Override]
    public function hasFreeDockingSlots(): bool
    {
        return $this->getDockingSlotCount() > $this->getDockedShipCount();
    }

    #[Override]
    public function getFreeDockingSlotCount(): int
    {
        return $this->getDockingSlotCount() - $this->getDockedShipCount();
    }

    #[Override]
    public function getDockedShipCount(): int
    {
        return $this->dockedShips->count();
    }

    #[Override]
    public function getDockedShips(): Collection
    {
        return $this->dockedShips;
    }

    #[Override]
    public function getDockedWorkbeeCount(): int
    {
        $count = 0;

        foreach ($this->getDockedShips() as $ships) {
            if ($ships->getRump()->getCategoryId() === SpacecraftRumpEnum::SHIP_CATEGORY_SHUTTLE) {
                $count += 1;
            }
        }

        return $count;
    }

    #[Override]
    public function getConstructionHubState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::SYSTEM_CONSTRUCTION_HUB);
    }

    #[Override]
    public function isAggregationSystemHealthy(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM);
    }

    #[Override]
    public function getTransferEntityType(): TransferEntityTypeEnum
    {
        return TransferEntityTypeEnum::STATION;
    }
}
