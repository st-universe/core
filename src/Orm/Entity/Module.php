<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ModuleRepository")
 * @Table(
 *     name="stu_modules",
 *     indexes={
 *         @Index(name="ship_rump_role_type_idx", columns={"rumps_role_id", "type"})
 *     }
 * )
 **/
class Module implements ModuleInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="string") */
    private $name = '';

    /** @Column(type="smallint") * */
    private $level = 0;

    /** @Column(type="smallint") * */
    private $upgrade_factor = 0;

    /** @Column(type="smallint") * */
    private $downgrade_factor = 0;

    /** @Column(type="smallint") * */
    private $crew = 0;

    /** @Column(type="integer") * */
    private $type = 0;

    /** @Column(type="integer", nullable=true) * */
    private $research_id = 0;

    /** @Column(type="integer") * */
    private $goods_id = 0;

    /** @Column(type="boolean") * */
    private $viewable = false;

    /** @Column(type="integer", nullable=true) * */
    private $rumps_role_id = 0;

    /** @Column(type="smallint") * */
    private $ecost = 0;

    /**
     * @ManyToOne(targetEntity="Research")
     * @JoinColumn(name="research_id", referencedColumnName="id")
     */
    private $research;

    /**
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="goods_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    /**
     * @ManyToOne(targetEntity="ShipRumpRole")
     * @JoinColumn(name="rumps_role_id", referencedColumnName="id")
     */
    private $shipRumpRole;

    /**
     * @OneToMany(targetEntity="ModuleSpecial", mappedBy="module")
     * @OrderBy({"special_id" = "ASC"})
     */
    private $moduleSpecials;

    /**
     * @OneToMany(targetEntity="ModuleCost", mappedBy="module")
     */
    private $buildingCosts;

    /**
     * @var null|array
     */
    private $specialAbilities;

    public function __construct()
    {
        $this->moduleSpecials = new ArrayCollection();
        $this->buildingCosts = new ArrayCollection();
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

    public function setCrew(int $crew): ModuleInterface
    {
        $this->crew = $crew;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): ModuleInterface
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
        return $this->goods_id;
    }

    public function setCommodityId(int $commodityId): ModuleInterface
    {
        $this->goods_id = $commodityId;

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

    public function getEcost(): int
    {
        return $this->ecost;
    }

    public function setEcost(int $energyCosts): ModuleInterface
    {
        $this->ecost = $energyCosts;

        return $this;
    }

    public function hasSpecial($special_id): bool
    {
        if ($this->specialAbilities === null) {
            $this->specialAbilities = array_map(
                function (ModuleSpecialInterface $moduleSpecial): int {
                    return (int)$moduleSpecial->getSpecialId();
                },
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
            function (ModuleCostInterface $a, ModuleCostInterface $b): int {
                if ($a->getCommodity()->getSort() == $b->getCommodity()->getSort()) {
                    return 0;
                }
                return ($a->getCommodity()->getSort() < $b->getCommodity()->getSort()) ? -1 : 1;
            }
        );

        return array_values($array);
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    public function getDescription(): string
    {
        return ModuleTypeDescriptionMapper::getDescription($this->getType());
    }
}
