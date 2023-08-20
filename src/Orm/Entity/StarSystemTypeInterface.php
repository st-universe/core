<?php

namespace Stu\Orm\Entity;

interface StarSystemTypeInterface
{
    public function getId(): int;

    public function getDescription(): string;

    public function setDescription(string $description): StarSystemTypeInterface;

    public function getDatabaseEntryId(): ?int;

    public function setDatabaseEntryId(?int $databaseEntryId): StarSystemTypeInterface;

    public function getDatabaseEntry(): ?DatabaseEntryInterface;

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): StarSystemTypeInterface;

    public function getFirstMassCenterType(): ?MassCenterTypeInterface;

    public function getSecondMassCenterType(): ?MassCenterTypeInterface;

    public function getIsGenerateable(): ?bool;
}
