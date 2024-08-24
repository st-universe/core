<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

interface UserCharacterInterface
{
    public function getId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): UserCharacterInterface;

    public function getName(): string;

    public function setName(string $name): UserCharacterInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): UserCharacterInterface;

    public function getAvatar(): ?string;

    public function setAvatar(?string $avatar): UserCharacterInterface;

    public function getFormerUserId(): ?int;

    public function setFormerUserId(?int $formerUserId): UserCharacterInterface;
}
