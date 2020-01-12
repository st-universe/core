<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity
 * @Table(
 *     name="stu_database_user",
 *     options={"engine":"InnoDB"},
 *     uniqueConstraints={@UniqueConstraint(name="entry_user_idx", columns={"database_id", "user_id"})}
 * )
 * @Entity(repositoryClass="Stu\Orm\Repository\DatabaseUserRepository")
 **/
class DatabaseUser implements DatabaseUserInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $database_id;

    /** @Column(type="integer") * */
    private $user_id;

    /** @Column(type="integer") * */
    private $date;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\DatabaseEntry")
     * @JoinColumn(name="database_id", referencedColumnName="id")
     */
    private $databaseEntry;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function setDatabaseEntry(DatabaseEntryInterface $databaseEntry): DatabaseUserInterface
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }

    public function getDatabaseEntry(): DatabaseEntryInterface
    {
        return $this->databaseEntry;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUser(UserInterface $user): DatabaseUserInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setDate(int $date): DatabaseUserInterface
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
