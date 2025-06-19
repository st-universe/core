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
use Override;
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Repository\ModuleRepository;

#[Table(name: 'stu_modules')]
#[Index(name: 'ship_rump_role_type_idx', columns: ['rumps_role_id', 'type'])]
#[Entity(repositoryClass: ModuleRepository::class)]
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

    #[Column(type: 'integer', enumType: SpacecraftModuleTypeEnum::class)]
    private SpacecraftModuleTypeEnum $type = SpacecraftModuleTypeEnum::HULL;

    #[Column(type: 'integer', nullable: true)]
    private ?int $research_id = 0;

    #[Column(type: 'integer')]
    private int $commodity_id = 0;

    #[Column(type: 'boolean')]
    private bool $viewable = false;

    #[Column(type: 'enum', enumType: SpacecraftRumpRoleEnum::class, nullable: true)]
    private ?SpacecraftRumpRoleEnum $rumps_role_id = null;

    #[Column(type: 'smallint')]
    private int $ecost = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $faction_id = null;

    #[Column(type: 'integer', enumType: SpacecraftSystemTypeEnum::class, nullable: true)]
    private ?SpacecraftSystemTypeEnum $system_type = null;

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
    #[OneToMany(targetEntity: 'ModuleSpecial', mappedBy: 'module', indexBy: 'special_id', fetch: 'EXTRA_LAZY')]
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

    public function __construct()
    {
        $this->moduleSpecials = new ArrayCollection();
        $this->buildingCosts = new ArrayCollection();
        $this->torpedoHull = new ArrayCollection();
        $this->weaponShield = new ArrayCollection();
    }

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
    public function setName(string $name): ModuleInterface
    {
        $this->name = $name;

        return $this;
    }

    #[Override]
    public function getLevel(): int
    {
        return $this->level;
    }

    #[Override]
    public function setLevel(int $level): ModuleInterface
    {
        $this->level = $level;

        return $this;
    }

    #[Override]
    public function getUpgradeFactor(): int
    {
        return $this->upgrade_factor;
    }

    #[Override]
    public function setUpgradeFactor(int $upgradeFactor): ModuleInterface
    {
        $this->upgrade_factor = $upgradeFactor;

        return $this;
    }

    #[Override]
    public function getDefaultFactor(): int
    {
        return $this->default_factor;
    }

    #[Override]
    public function setDefaultFactor(int $defaultFactor): ModuleInterface
    {
        $this->default_factor = $defaultFactor;

        return $this;
    }

    #[Override]
    public function getDowngradeFactor(): int
    {
        return $this->downgrade_factor;
    }

    #[Override]
    public function setDowngradeFactor(int $downgradeFactor): ModuleInterface
    {
        $this->downgrade_factor = $downgradeFactor;

        return $this;
    }

    #[Override]
    public function getCrew(): int
    {
        return $this->crew;
    }

    #[Override]
    public function getCrewByFactionAndRumpLvl(FactionInterface $faction, SpacecraftRumpInterface $rump): int
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

    #[Override]
    public function setCrew(int $crew): ModuleInterface
    {
        $this->crew = $crew;

        return $this;
    }

    #[Override]
    public function getType(): SpacecraftModuleTypeEnum
    {
        return $this->type;
    }

    #[Override]
    public function setType(SpacecraftModuleTypeEnum $type): ModuleInterface
    {
        $this->type = $type;

        return $this;
    }

    #[Override]
    public function getResearchId(): ?int
    {
        return $this->research_id;
    }

    #[Override]
    public function setResearchId(int $researchId): ModuleInterface
    {
        $this->research_id = $researchId;

        return $this;
    }

    #[Override]
    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    #[Override]
    public function setCommodityId(int $commodityId): ModuleInterface
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    #[Override]
    public function getViewable(): bool
    {
        return $this->viewable;
    }

    #[Override]
    public function setViewable(bool $viewable): ModuleInterface
    {
        $this->viewable = $viewable;

        return $this;
    }

    #[Override]
    public function getShipRumpRoleId(): ?SpacecraftRumpRoleEnum
    {
        return $this->rumps_role_id;
    }

    #[Override]
    public function getWeapon(): ?WeaponInterface
    {
        return $this->weapon;
    }

    #[Override]
    public function getEcost(): int
    {
        return $this->ecost;
    }

    #[Override]
    public function setEcost(int $energyCosts): ModuleInterface
    {
        $this->ecost = $energyCosts;

        return $this;
    }

    #[Override]
    public function getFactionId(): ?int
    {
        return $this->faction_id;
    }

    #[Override]
    public function setFactionId(int $factionId): ?ModuleInterface
    {
        $this->faction_id = $factionId;

        return $this;
    }

    #[Override]
    public function getSystemType(): ?SpacecraftSystemTypeEnum
    {
        return $this->system_type;
    }

    #[Override]
    public function getSpecials(): Collection
    {
        return $this->moduleSpecials;
    }

    #[Override]
    public function hasSpecial(ModuleSpecialAbilityEnum $ability): bool
    {
        return $this->moduleSpecials->containsKey($ability->value);
    }

    #[Override]
    public function getCost(): Collection
    {
        return $this->buildingCosts;
    }

    #[Override]
    public function getCostSorted(): array
    {
        $array = $this->getCost()->getValues();

        usort(
            $array,
            fn(ModuleCostInterface $a, ModuleCostInterface $b): int => $a->getCommodity()->getSort() <=> $b->getCommodity()->getSort()
        );

        return $array;
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->getType()->getDescription();
    }

    #[Override]
    public function getTorpedoHull(): Collection
    {
        return $this->torpedoHull;
    }

    #[Override]
    public function getWeaponShield(): Collection
    {
        return $this->weaponShield;
    }

    #[Override]
    public function getFaction(): ?FactionInterface
    {
        return $this->faction;
    }
}
