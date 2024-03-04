<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

interface UserCharactersInterface
{
    public function getId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): UserCharactersInterface;

    public function getName(): string;

    public function setName(string $name): UserCharactersInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): UserCharactersInterface;

    public function getAvatar(): ?string;

    public function setAvatar(?string $avatar): UserCharactersInterface;

    public function getFormerUserId(): ?int;

    public function setFormerUserId(?int $formerUserId): UserCharactersInterface;
}
