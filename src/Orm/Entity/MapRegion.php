<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\MapRegionRepository")
 * @Table(
 *     name="stu_map_regions",
 *     indexes={
 *     }
 * )
 **/
class MapRegion implements MapRegionInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="string") */
    private $description = '';

    /** @Column(type="integer", nullable=true) * */
    private $database_id = 0;

    /** @Column(type="boolean", nullable=true) */
    private $is_administrated;

    /**
     * @ManyToOne(targetEntity="DatabaseEntry")
     * @JoinColumn(name="database_id", referencedColumnName="id")
     */
    private $databaseEntry;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): MapRegionInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getDatabaseEntry(): ?DatabaseEntryInterface
    {
        return $this->databaseEntry;
    }

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): MapRegionInterface
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }

    public function isAdministrated(): ?bool
    {
        return $this->is_administrated;
    }

    public function setIsAdministrated(?bool $isAdministrated): MapRegionInterface
    {
        $this->is_administrated = $isAdministrated;
        return $this;
    }
}
