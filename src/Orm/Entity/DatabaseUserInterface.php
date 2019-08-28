<?php

namespace Stu\Orm\Entity;

interface DatabaseUserInterface
{
    public function getId(): int;

    public function setDatabaseEntry(DatabaseEntryInterface $databaseEntry): DatabaseUserInterface;

    public function getDatabaseEntry(): DatabaseEntryInterface;

    public function setUserId(int $userId): DatabaseUserInterface;

    public function getUserId(): int;

    public function setDate(int $date): DatabaseUserInterface;

    public function getDate(): int;

    public function getDatabaseEntryId(): int;
}