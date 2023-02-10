<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Stu\Component\Map\MapEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\StarSystemMapRepository")
 * @Table(
 *     name="stu_sys_map",
 *     uniqueConstraints={@UniqueConstraint(name="system_coordinates_idx", columns={"sx", "sy", "systems_id"})}
 * )
 **/
class StarSystemMap implements StarSystemMapInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $sx = 0;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $sy = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $systems_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $field_id = 0;

    /**
     * @var StarSystemInterface
     *
     * @ManyToOne(targetEntity="StarSystem", inversedBy="fields")
     * @JoinColumn(name="systems_id", referencedColumnName="id")
     */
    private $starSystem;

    /**
     * @var null|ColonyInterface
     *
     * @OneToOne(targetEntity="Colony", mappedBy="starsystem_map")
     */
    private $colony;

    /**
     * @var MapFieldTypeInterface
     *
     * @ManyToOne(targetEntity="MapFieldType")
     * @JoinColumn(name="field_id", referencedColumnName="id")
     */
    private $mapFieldType;

    /**
     * @var ArrayCollection<int, ShipInterface>
     *
     * @OneToMany(targetEntity="Ship", mappedBy="starsystem_map", fetch="EXTRA_LAZY")
     */
    private $ships;

    /**
     * @var ArrayCollection<int, FlightSignatureInterface>
     *
     * @OneToMany(targetEntity="FlightSignature", mappedBy="starsystem_map")
     * @OrderBy({"time" = "DESC"})
     */
    private $signatures;

    /**
     * @var ArrayCollection<int, WormholeEntryInterface>
     *
     * @OneToMany(targetEntity="WormholeEntry", mappedBy="systemMap")
     */
    private $wormholeEntries;

    public function __construct()
    {
        $this->ships = new ArrayCollection();
        $this->signatures = new ArrayCollection();
        $this->wormholeEntries = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSx(): int
    {
        return $this->sx;
    }

    public function setSx(int $sx): StarSystemMapInterface
    {
        $this->sx = $sx;

        return $this;
    }

    public function getSy(): int
    {
        return $this->sy;
    }

    public function setSy(int $sy): StarSystemMapInterface
    {
        $this->sy = $sy;

        return $this;
    }

    public function getSystemId(): int
    {
        return $this->systems_id;
    }

    public function setSystemId(int $systemId): StarSystemMapInterface
    {
        $this->systems_id = $systemId;

        return $this;
    }

    public function getSystem(): StarSystemInterface
    {
        return $this->starSystem;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function setFieldId(int $fieldId): StarSystemMapInterface
    {
        $this->field_id = $fieldId;

        return $this;
    }

    public function getFieldType(): MapFieldTypeInterface
    {
        return $this->mapFieldType;
    }

    public function getColony(): ?ColonyInterface
    {
        return $this->colony;
    }

    public function getMapRegion(): ?MapRegionInterface
    {
        return null;
    }

    public function getAdministratedRegion(): ?MapRegionInterface
    {
        return null;
    }

    public function getInfluenceArea(): ?StarSystemInterface
    {
        return null;
    }

    public function getFieldStyle(): string
    {
        return "background-image: url('/assets/map/" . $this->getFieldId() . ".png');";
    }

    public function getShips(): Collection
    {
        return $this->ships;
    }

    public function getSignatures(): Collection
    {
        return $this->signatures;
    }

    public function getRandomWormholeEntry(): ?WormholeEntryInterface
    {
        if ($this->wormholeEntries->isEmpty()) {
            return null;
        }

        $usableEntries =  array_filter(
            $this->wormholeEntries->toArray(),
            function (WormholeEntryInterface $entry): bool {
                $type = $entry->getType();

                return $entry->isUsable() && ($type === MapEnum::WORMHOLE_ENTRY_TYPE_BOTH ||
                    $type === MapEnum::WORMHOLE_ENTRY_TYPE_OUT);
            }
        );

        return empty($usableEntries) ? null : $usableEntries[array_rand($usableEntries)];
    }

    public function getSectorString(): string
    {
        return sprintf(
            '%d|%d (%s-%s)',
            $this->getSx(),
            $this->getSy(),
            $this->getSystem()->getName(),
            $this->getSystem()->isWormhole() ? 'Wurmloch' : 'System'
        );
    }
}
