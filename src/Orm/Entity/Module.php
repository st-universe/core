<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;

#[Table(name: 'stu_modules')]
#[Index(name: 'ship_rump_role_type_idx', columns: ['rumps_role_id', 'type'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\ModuleRepository')]
class Module implements ModuleInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'smallint')]
    private int $level = 0;

    #[Column(type: 'smallint')]
    private int $upgrade_factor = 0;

    #[Column(type: 'smallint')]
    private int $default_factor = 0;

    #[Column(type: 'smallint')]
    private int $downgrade_factor = 0;

    #[Column(type: 'smallint')]
    private int $crew = 0;

    #[Column(type: 'integer', enumType: ShipModuleTypeEnum::class)]
    private ShipModuleTypeEnum $type = ShipModuleTypeEnum::HULL;

    #[Column(type: 'integer', nullable: true)]
    private ?int $research_id = 0;

    #[Column(type: 'integer')]
    private int $commodity_id = 0;

    #[Column(type: 'boolean')]
    private bool $viewable = false;

    #[Column(type: 'integer', nullable: true)]
    private ?int $rumps_role_id = 0;

    #[Column(type: 'smallint')]
    private int $ecost = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $faction_id = null;

    #[Column(type: 'integer', enumType: ShipSystemTypeEnum::class, nullable: true)]
    private ?ShipSystemTypeEnum $system_type = null;

    /**
     * @var ResearchInterface
     */
    #[ManyToOne(targetEntity: 'Research')]
    #[JoinColumn(name: 'research_id', referencedColumnName: 'id')]
    private $research;

    #[ManyToOne(targetEntity: 'Commodity')]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $commodity;

    /**
     * @var FactionInterface
     */
    #[ManyToOne(targetEntity: 'Faction')]
    #[JoinColumn(name: 'faction_id', referencedColumnName: 'id')]
    private $faction;

    /**
     * @var ShipRumpRoleInterface
     */
    #[ManyToOne(targetEntity: 'ShipRumpRole')]
    #[JoinColumn(name: 'rumps_role_id', referencedColumnName: 'id')]
    private $shipRumpRole;

    /**
     * @var ArrayCollection<int, ModuleSpecialInterface>
     */
    #[OneToMany(targetEntity: 'ModuleSpecial', mappedBy: 'module')]
    #[OrderBy(['special_id' => 'ASC'])]
    private Collection $moduleSpecials;

    /**
     * @var ArrayCollection<int, ModuleCostInterface>
     */
    #[OneToMany(targetEntity: 'ModuleCost', mappedBy: 'module')]
    private Collection $buildingCosts;

    /**
     * @var ArrayCollection<int, TorpedoHullInterface>
     */
    #[OneToMany(targetEntity: 'TorpedoHull', mappedBy: 'module', indexBy: 'torpedo_type')]
    #[OrderBy(['torpedo_type' => 'ASC'])]
    private Collection $torpedoHull;

    /**
     * @var ArrayCollection<int, WeaponShieldInterface>
     */
    #[OneToMany(targetEntity: 'WeaponShield', mappedBy: 'module', indexBy: 'weapon_id')]
    #[OrderBy(['weapon_id' => 'ASC'])]
    private Collection $weaponShield;

    #[OneToOne(targetEntity: 'Weapon', mappedBy: 'module')]
    private ?WeaponInterface $weapon = null;

    /** @var null|array<int> */
    private ?array $specialAbilities = null;

    public function __construct()
    {
        $this->moduleSpecials = new ArrayCollection();
        $this->buildingCosts = new ArrayCollection();
        $this->torpedoHull = new ArrayCollection();
        $this->weaponShield = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ModuleInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): ModuleInterface
    {
        $this->level = $level;

        return $this;
    }

    public function getUpgradeFactor(): int
    {
        return $this->upgrade_factor;
    }

    public function setUpgradeFactor(int $upgradeFactor): ModuleInterface
    {
        $this->upgrade_factor = $upgradeFactor;

        return $this;
    }

    public function getDefaultFactor(): int
    {
        return $this->default_factor;
    }

    public function setDefaultFactor(int $defaultFactor): ModuleInterface
    {
        $this->default_factor = $defaultFactor;

        return $this;
    }

    public function getDowngradeFactor(): int
    {
        return $this->downgrade_factor;
    }

    public function setDowngradeFactor(int $downgradeFactor): ModuleInterface
    {
        $this->downgrade_factor = $downgradeFactor;

        return $this;
    }

    public function getCrew(): int
    {
        return $this->crew;
    }

    public function getCrewByFactionAndRumpLvl(FactionInterface $faction, ShipRumpInterface $rump): int
    {
        $result = $this->getCrew();

        if (
            $this->getFaction() !== null
            && $this->getFaction() !== $faction
        ) {
            $result += 1;
        }

        if ($this->getLevel() > $rump->getModuleLevel()) {
            $result += 1;
        }

        return $result;
    }

    public function setCrew(int $crew): ModuleInterface
    {
        $this->crew = $crew;

        return $this;
    }

    public function getType(): ShipModuleTypeEnum
    {
        return $this->type;
    }

    public function setType(ShipModuleTypeEnum $type): ModuleInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getResearchId(): int
    {
        return $this->research_id;
    }

    public function setResearchId(int $researchId): ModuleInterface
    {
        $this->research_id = $researchId;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setCommodityId(int $commodityId): ModuleInterface
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    public function getViewable(): bool
    {
        return $this->viewable;
    }

    public function setViewable(bool $viewable): ModuleInterface
    {
        $this->viewable = $viewable;

        return $this;
    }

    public function getShipRumpRoleId(): int
    {
        return $this->rumps_role_id;
    }

    public function setShipRumpRoleId(int $shipRumpRoleId): ModuleInterface
    {
        $this->rumps_role_id = $shipRumpRoleId;

        return $this;
    }

    public function getWeapon(): ?WeaponInterface
    {
        return $this->weapon;
    }

    public function getEcost(): int
    {
        return $this->ecost;
    }

    public function setEcost(int $energyCosts): ModuleInterface
    {
        $this->ecost = $energyCosts;

        return $this;
    }

    public function getFactionId(): ?int
    {
        return $this->faction_id;
    }

    public function setFactionId(int $factionId): ?ModuleInterface
    {
        $this->faction_id = $factionId;

        return $this;
    }

    public function getSystemType(): ?ShipSystemTypeEnum
    {
        return $this->system_type;
    }

    public function hasSpecial($special_id): bool
    {
        if ($this->specialAbilities === null) {
            $this->specialAbilities = array_map(
                fn (ModuleSpecialInterface $moduleSpecial): int => $moduleSpecial->getSpecialId(),
                $this->getSpecials()->toArray()
            );
        }
        return in_array((int)$special_id, $this->specialAbilities);
    }

    public function getSpecials(): Collection
    {
        return $this->moduleSpecials;
    }

    public function getCost(): Collection
    {
        return $this->buildingCosts;
    }

    public function getCostSorted(): array
    {
        $array = $this->getCost()->getValues();

        usort(
            $array,
            fn (ModuleCostInterface $a, ModuleCostInterface $b): int => $a->getCommodity()->getSort() <=> $b->getCommodity()->getSort()
        );

        return array_values($array);
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    public function getDescription(): string
    {
        return $this->getType()->getDescription();
    }

    public function getTorpedoHull(): Collection
    {
        return $this->torpedoHull;
    }

    public function getWeaponShield(): Collection
    {
        return $this->weaponShield;
    }

    public function getFaction(): ?FactionInterface
    {
        return $this->faction;
    }
}
