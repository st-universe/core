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
use Stu\Component\Faction\FactionEnum;
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Repository\ModuleRepository;

#[Table(name: 'stu_modules')]
#[Index(name: 'ship_rump_role_type_idx', columns: ['rumps_role_id', 'type'])]
#[Entity(repositoryClass: ModuleRepository::class)]
class Module
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

    #[Column(type: 'integer', enumType: SpacecraftModuleTypeEnum::class)]
    private SpacecraftModuleTypeEnum $type = SpacecraftModuleTypeEnum::HULL;

    #[Column(type: 'integer', nullable: true)]
    private ?int $research_id = 0;

    #[Column(type: 'integer')]
    private int $commodity_id = 0;

    #[Column(type: 'boolean')]
    private bool $viewable = false;

    #[Column(type: 'integer', enumType: SpacecraftRumpRoleEnum::class, nullable: true)]
    private ?SpacecraftRumpRoleEnum $rumps_role_id = null;

    #[Column(type: 'smallint')]
    private int $ecost = 0;

    #[Column(type: 'integer', nullable: true, enumType: FactionEnum::class)]
    private ?FactionEnum $faction_id = null;

    #[Column(type: 'integer', enumType: SpacecraftSystemTypeEnum::class, nullable: true)]
    private ?SpacecraftSystemTypeEnum $system_type = null;

    #[Column(type: 'boolean', nullable: true)]
    private ?bool $is_npc = false;

    /**
     * @var Research
     */
    #[ManyToOne(targetEntity: Research::class)]
    #[JoinColumn(name: 'research_id', nullable: false, referencedColumnName: 'id')]
    private $research;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $commodity;

    /**
     * @var Faction
     */
    #[ManyToOne(targetEntity: Faction::class)]
    #[JoinColumn(name: 'faction_id', nullable: false, referencedColumnName: 'id')]
    private $faction;

    #[ManyToOne(targetEntity: ShipRumpRole::class)]
    #[JoinColumn(name: 'rumps_role_id', nullable: false, referencedColumnName: 'id')]
    private ShipRumpRole $shipRumpRole;

    /**
     * @var ArrayCollection<int, ModuleSpecial>
     */
    #[OneToMany(targetEntity: ModuleSpecial::class, mappedBy: 'module', indexBy: 'special_id', fetch: 'EXTRA_LAZY')]
    #[OrderBy(['special_id' => 'ASC'])]
    private Collection $moduleSpecials;

    /**
     * @var ArrayCollection<int, ModuleCost>
     */
    #[OneToMany(targetEntity: ModuleCost::class, mappedBy: 'module')]
    private Collection $buildingCosts;

    /**
     * @var ArrayCollection<int, TorpedoHull>
     */
    #[OneToMany(targetEntity: TorpedoHull::class, mappedBy: 'module', indexBy: 'torpedo_type')]
    #[OrderBy(['torpedo_type' => 'ASC'])]
    private Collection $torpedoHull;

    /**
     * @var ArrayCollection<int, WeaponShield>
     */
    #[OneToMany(targetEntity: WeaponShield::class, mappedBy: 'module', indexBy: 'weapon_id')]
    #[OrderBy(['weapon_id' => 'ASC'])]
    private Collection $weaponShield;

    #[OneToOne(targetEntity: Weapon::class, mappedBy: 'module')]
    private ?Weapon $weapon = null;

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

    public function setName(string $name): Module
    {
        $this->name = $name;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): Module
    {
        $this->level = $level;

        return $this;
    }

    public function getUpgradeFactor(): int
    {
        return $this->upgrade_factor;
    }

    public function setUpgradeFactor(int $upgradeFactor): Module
    {
        $this->upgrade_factor = $upgradeFactor;

        return $this;
    }

    public function getDefaultFactor(): int
    {
        return $this->default_factor;
    }

    public function setDefaultFactor(int $defaultFactor): Module
    {
        $this->default_factor = $defaultFactor;

        return $this;
    }

    public function getDowngradeFactor(): int
    {
        return $this->downgrade_factor;
    }

    public function setDowngradeFactor(int $downgradeFactor): Module
    {
        $this->downgrade_factor = $downgradeFactor;

        return $this;
    }

    public function getCrew(): int
    {
        return $this->crew;
    }

    public function getCrewByFactionAndRumpLvl(Faction $faction, SpacecraftRump $rump): int
    {
        $result = $this->getCrew();

        if (
            $this->getFaction() !== null
            && $this->getFaction()->getId() !== $faction->getId()
        ) {
            $result += 1;
        }

        if ($this->getLevel() > $rump->getBaseValues()->getModuleLevel()) {
            $result += 1;
        }

        return $result;
    }

    public function setCrew(int $crew): Module
    {
        $this->crew = $crew;

        return $this;
    }

    public function getType(): SpacecraftModuleTypeEnum
    {
        return $this->type;
    }

    public function setType(SpacecraftModuleTypeEnum $type): Module
    {
        $this->type = $type;

        return $this;
    }

    public function getResearchId(): ?int
    {
        return $this->research_id;
    }

    public function setResearchId(int $researchId): Module
    {
        $this->research_id = $researchId;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setCommodityId(int $commodityId): Module
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    public function getViewable(): bool
    {
        return $this->viewable;
    }

    public function setViewable(bool $viewable): Module
    {
        $this->viewable = $viewable;

        return $this;
    }

    public function getShipRumpRoleId(): ?SpacecraftRumpRoleEnum
    {
        return $this->rumps_role_id;
    }

    public function getWeapon(): ?Weapon
    {
        return $this->weapon;
    }

    public function getEcost(): int
    {
        return $this->ecost;
    }

    public function setEcost(int $energyCosts): Module
    {
        $this->ecost = $energyCosts;

        return $this;
    }

    public function getFactionId(): ?FactionEnum
    {
        return $this->faction_id;
    }

    public function setFactionId(FactionEnum $factionId): ?Module
    {
        $this->faction_id = $factionId;

        return $this;
    }

    public function getSystemType(): ?SpacecraftSystemTypeEnum
    {
        return $this->system_type;
    }

    /**
     * @return Collection<int, ModuleSpecial>
     */
    public function getSpecials(): Collection
    {
        return $this->moduleSpecials;
    }

    public function hasSpecial(ModuleSpecialAbilityEnum $ability): bool
    {
        return $this->moduleSpecials->containsKey($ability->value);
    }

    /**
     * @return Collection<int, ModuleCost>
     */
    public function getCost(): Collection
    {
        return $this->buildingCosts;
    }

    /**
     * @return array<int, ModuleCost>
     */
    public function getCostSorted(): array
    {
        $array = $this->getCost()->getValues();

        usort(
            $array,
            fn (ModuleCost $a, ModuleCost $b): int => $a->getCommodity()->getSort() <=> $b->getCommodity()->getSort()
        );

        return $array;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }

    public function getDescription(): string
    {
        return $this->getType()->getDescription();
    }

    /**
     * @return Collection<int, TorpedoHull>
     */
    public function getTorpedoHull(): Collection
    {
        return $this->torpedoHull;
    }

    /**
     * @return Collection<int, WeaponShield>
     */
    public function getWeaponShield(): Collection
    {
        return $this->weaponShield;
    }

    public function getFaction(): ?Faction
    {
        return $this->faction;
    }

    public function getisNpc(): ?bool
    {
        return $this->is_npc;
    }
}
