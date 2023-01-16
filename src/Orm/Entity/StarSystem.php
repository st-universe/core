<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\StarSystemRepository")
 * @Table(
 *     name="stu_systems",
 *     indexes={
 *         @Index(name="coordinate_idx", columns={"cx","cy"})
 *     }
 * )
 **/
class StarSystem implements StarSystemInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="smallint") * */
    private $cx = 0;

    /** @Column(type="smallint") * */
    private $cy = 0;

    /** @Column(type="integer") * */
    private $type = 0;

    /** @Column(type="string") */
    private $name = '';

    /** @Column(type="smallint") * */
    private $max_x = 0;

    /** @Column(type="smallint") * */
    private $max_y = 0;

    /** @Column(type="smallint") * */
    private $bonus_fields = 0;

    /** @Column(type="integer", nullable=true) * */
    private $database_id = 0;

    /** @Column(type="boolean") * */
    private $is_wormhole;

    /**
     * @ManyToOne(targetEntity="StarSystemType")
     * @JoinColumn(name="type", referencedColumnName="id", onDelete="CASCADE")
     */
    private $systemType;

    /**
     * @OneToOne(targetEntity="Map", mappedBy="starSystem")
     */
    private $map;

    /**
     * @ManyToOne(targetEntity="DatabaseEntry")
     * @JoinColumn(name="database_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $databaseEntry;

    /**
     * @OneToOne(targetEntity="Ship", mappedBy="influenceArea")
     */
    private $base;

    private $fields;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCx(): int
    {
        return $this->cx;
    }

    public function setCx(int $cx): StarSystemInterface
    {
        $this->cx = $cx;

        return $this;
    }

    public function getCy(): int
    {
        return $this->cy;
    }

    public function setCy(int $cy): StarSystemInterface
    {
        $this->cy = $cy;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): StarSystemInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): StarSystemInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getMaxX(): int
    {
        return $this->max_x;
    }

    public function setMaxX(int $maxX): StarSystemInterface
    {
        $this->max_x = $maxX;

        return $this;
    }

    public function getMaxY(): int
    {
        return $this->max_y;
    }

    public function setMaxY(int $maxY): StarSystemInterface
    {
        $this->max_y = $maxY;

        return $this;
    }

    public function getBonusFieldAmount(): int
    {
        return $this->bonus_fields;
    }

    public function setBonusFieldAmount(int $bonusFieldAmount): StarSystemInterface
    {
        $this->bonus_fields = $bonusFieldAmount;

        return $this;
    }

    public function getSystemType(): StarSystemTypeInterface
    {
        return $this->systemType;
    }

    public function getDatabaseEntry(): ?DatabaseEntryInterface
    {
        return $this->databaseEntry;
    }

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): StarSystemInterface
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }

    public function getLayer(): ?LayerInterface
    {
        if ($this->isWormhole()) {
            return null;
        }

        return $this->getMapField()->getLayer();
    }

    public function getMapField(): ?MapInterface
    {
        return $this->map;
    }

    public function getBase(): ?ShipInterface
    {
        return $this->base;
    }

    public function getFields(): array
    {
        if ($this->fields === null) {
            // @todo refactor
            global $container;

            $this->fields = $container->get(StarSystemMapRepositoryInterface::class)->getBySystemOrdered($this->getId());
        }
        return $this->fields;
    }

    public function isWormhole(): bool
    {
        return $this->is_wormhole;
    }
}
