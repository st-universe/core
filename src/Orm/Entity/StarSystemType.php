<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\StarSystemTypeRepository")
 * @Table(
 *     name="stu_system_types",
 *     indexes={
 *     }
 * )
 **/
class StarSystemType implements StarSystemTypeInterface
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
    private $description = '';

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $database_id = null;

    /**
     * @var null|DatabaseEntryInterface
     *
     * @OneToOne(targetEntity="DatabaseEntry")
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

    public function setDescription(string $description): StarSystemTypeInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getDatabaseEntryId(): ?int
    {
        return $this->database_id;
    }

    public function setDatabaseEntryId(?int $databaseEntryId): StarSystemTypeInterface
    {
        $this->database_id = $databaseEntryId;

        return $this;
    }

    public function getDatabaseEntry(): ?DatabaseEntryInterface
    {
        return $this->databaseEntry;
    }

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): StarSystemTypeInterface
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }
}
