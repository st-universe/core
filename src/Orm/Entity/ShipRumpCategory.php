<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipRumpCategoryRepository")
 * @Table(
 *     name="stu_rumps_categories",
 *     indexes={
 *     }
 * )
 **/
class ShipRumpCategory implements ShipRumpCategoryInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    private $name = '';

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $database_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $points = 0;

    /**
     * @var null|DatabaseEntryInterface
     *
     * @ManyToOne(targetEntity="DatabaseEntry")
     * @JoinColumn(name="database_id", referencedColumnName="id")
     */
    private $databaseEntry;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ShipRumpCategoryInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getDatabaseId(): int
    {
        return $this->database_id;
    }

    public function setDatabaseId(int $databaseId): ShipRumpCategoryInterface
    {
        $this->database_id = $databaseId;

        return $this;
    }

    //@deprecated
    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): ShipRumpCategoryInterface
    {
        $this->points = $points;

        return $this;
    }

    public function getDatabaseEntry(): ?DatabaseEntryInterface
    {
        return $this->databaseEntry;
    }

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): ShipRumpCategoryInterface
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }
}
