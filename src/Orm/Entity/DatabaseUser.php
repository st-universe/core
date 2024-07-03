<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\DatabaseUserRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Table(name: 'stu_database_user', options: ['engine' => 'InnoDB'])]
#[UniqueConstraint(name: 'entry_user_idx', columns: ['database_id', 'user_id'])]
#[Entity(repositoryClass: DatabaseUserRepository::class)]
class DatabaseUser implements DatabaseUserInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $database_id;

    #[Column(type: 'integer')]
    private int $user_id;

    #[Column(type: 'integer')]
    private int $date;

    #[ManyToOne(targetEntity: DatabaseEntry::class)]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id')]
    private DatabaseEntryInterface $databaseEntry;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function setDatabaseEntry(DatabaseEntryInterface $databaseEntry): DatabaseUserInterface
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }

    #[Override]
    public function getDatabaseEntry(): DatabaseEntryInterface
    {
        return $this->databaseEntry;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function setUser(UserInterface $user): DatabaseUserInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setDate(int $date): DatabaseUserInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->date;
    }

    #[Override]
    public function getDatabaseEntryId(): int
    {
        return $this->database_id;
    }
}
