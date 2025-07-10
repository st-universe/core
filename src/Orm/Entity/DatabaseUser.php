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
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\DatabaseUserRepository;

#[Table(name: 'stu_database_user')]
#[Entity(repositoryClass: DatabaseUserRepository::class)]
#[TruncateOnGameReset]
class DatabaseUser
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
    #[JoinColumn(name: 'database_id', nullable: false, referencedColumnName: 'id')]
    private DatabaseEntry $databaseEntry;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function setDatabaseEntry(DatabaseEntry $databaseEntry): DatabaseUser
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }

    public function getDatabaseEntry(): DatabaseEntry
    {
        return $this->databaseEntry;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUser(User $user): DatabaseUser
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setDate(int $date): DatabaseUser
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function getDatabaseEntryId(): int
    {
        return $this->database_id;
    }
}
