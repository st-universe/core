<?php

namespace Stu\Module\Communication\View\ShowKnCharacters;

interface KnCharactersTalInterface
{
    public function getId(): int;
    public function getName(): string;
    public function getDescription(): ?string;
    public function getAvatar(): ?string;
    public function getUserName(): string;
    public function isOwnedByCurrentUser(): bool;
}
