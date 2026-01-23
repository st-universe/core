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
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Orm\Repository\StationRepository;

#[Table(name: 'stu_station')]
#[Index(name: 'station_influence_area_idx', columns: ['influence_area_id'])]
#[Entity(repositoryClass: StationRepository::class)]
class Station extends Spacecraft
{
    #[Column(type: 'integer', nullable: true)]
    private ?int $influence_area_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $ally_id = null;

    #[OneToOne(targetEntity: TradePost::class, mappedBy: 'station')]
    private ?TradePost $tradePost = null;

    #[OneToOne(targetEntity: ConstructionProgress::class, mappedBy: 'station')]
    private ?ConstructionProgress $constructionProgress = null;

    /**
     * @var ArrayCollection<int, Ship>
     */
    #[OneToMany(targetEntity: Ship::class, mappedBy: 'dockedTo')]
    #[OrderBy(['fleet_id' => 'DESC', 'isFleetLeader' => 'DESC'])]
    private Collection $dockedShips;

    /**
     * @var ArrayCollection<int, DockingPrivilege>
     */
    #[OneToMany(targetEntity: DockingPrivilege::class, mappedBy: 'station')]
    #[OrderBy(['privilege_mode' => 'DESC'])]
    private Collection $dockingPrivileges;

    #[OneToOne(targetEntity: StarSystem::class, inversedBy: 'station')]
    #[JoinColumn(name: 'influence_area_id', referencedColumnName: 'id')]
    private ?StarSystem $influenceArea = null;

    #[ManyToOne(targetEntity: Alliance::class, inversedBy: 'stations')]
    #[JoinColumn(name: 'ally_id', referencedColumnName: 'id')]
    private ?Alliance $alliance = null;


    public function __construct()
    {
        parent::__construct();
        $this->dockedShips = new ArrayCollection();
        $this->dockingPrivileges = new ArrayCollection();
    }

    #[\Override]
    public function getType(): SpacecraftTypeEnum
    {
        return SpacecraftTypeEnum::STATION;
    }

    #[\Override]
    public function getFleet(): ?Fleet
    {
        return null;
    }

    public function getTradePost(): ?TradePost
    {
        return $this->tradePost;
    }

    public function setTradePost(?TradePost $tradePost): Station
    {
        $this->tradePost = $tradePost;

        return $this;
    }

    public function getInfluenceArea(): ?StarSystem
    {
        return $this->influenceArea;
    }

    public function setInfluenceArea(?StarSystem $influenceArea): Station
    {
        $this->influenceArea = $influenceArea;
        return $this;
    }

    public function getAlliance(): ?Alliance
    {
        return $this->alliance;
    }

    public function setAlliance(?Alliance $alliance): Station
    {
        $this->alliance = $alliance;
        return $this;
    }

    public function getConstructionProgress(): ?ConstructionProgress
    {
        return $this->constructionProgress;
    }

    public function resetConstructionProgress(): Station
    {
        $this->constructionProgress = null;

        return $this;
    }

    #[\Override]
    public function getModules(): array
    {
        $constructionProgress = $this->getConstructionProgress();
        if ($constructionProgress === null) {
            return [];
        }

        $parentModules = parent::getModules();
        $parentModuleIds = array_map(fn(Module $module): int => $module->getId(), $parentModules);

        $specialModules = $constructionProgress
            ->getSpecialModules()
            ->filter(fn(ConstructionProgressModule $progressModule): bool => !in_array(
                $progressModule->getModule()->getId(),
                $parentModuleIds
            ))
            ->map(fn(ConstructionProgressModule $progressModule): Module => $progressModule->getModule())
            ->toArray();

        return $parentModules + $specialModules;
    }

    /**
     * @return Collection<int, DockingPrivilege>
     */
    public function getDockPrivileges(): Collection
    {
        return $this->dockingPrivileges;
    }

    public function getDockingSlotCount(): int
    {
        $state = $this->getCondition()->getState();

        return $state === SpacecraftStateEnum::UNDER_CONSTRUCTION
            || $state === SpacecraftStateEnum::UNDER_SCRAPPING
            ? 50
            : $this->getRump()->getDockingSlots();
    }

    public function hasFreeDockingSlots(): bool
    {
        return $this->getDockingSlotCount() > $this->getDockedShipCount();
    }

    public function getFreeDockingSlotCount(): int
    {
        return $this->getDockingSlotCount() - $this->getDockedShipCount();
    }

    public function getDockedShipCount(): int
    {
        return $this->dockedShips->count();
    }

    /**
     * @return Collection<int, Ship>
     */
    public function getDockedShips(): Collection
    {
        return $this->dockedShips;
    }

    public function getDockedWorkbeeCount(): int
    {
        return $this->getDockedShips()
            ->filter(fn(Ship $docked): bool => $docked->hasEnoughCrew()
                && !$docked->getUser()->isVacationRequestOldEnough()
                && $docked->getRump()->isWorkbee())
            ->count();
    }

    public function getConstructionHubState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::CONSTRUCTION_HUB);
    }

    public function isAggregationSystemHealthy(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM);
    }

    #[\Override]
    public function getTransferEntityType(): TransferEntityTypeEnum
    {
        return TransferEntityTypeEnum::STATION;
    }
}
