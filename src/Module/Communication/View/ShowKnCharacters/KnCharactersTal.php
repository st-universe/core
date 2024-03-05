<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnCharacters;

use Stu\Orm\Entity\UserCharactersInterface;
use Stu\Orm\Entity\UserInterface;

final class KnCharactersTal implements KnCharactersTalInterface
{
    private UserCharactersInterface $character;
    private UserInterface $currentUser;

    public function __construct(
        UserCharactersInterface $character,
        UserInterface $currentUser
    ) {
        $this->character = $character;
        $this->currentUser = $currentUser;
    }

    public function getId(): int
    {
        return $this->character->getId();
    }

    public function getName(): string
    {
        return $this->character->getName();
    }

    public function getDescription(): ?string
    {
        return $this->character->getDescription();
    }

    public function getAvatar(): ?string
    {
        return $this->character->getAvatar();
    }

    public function getUserName(): string
    {
        return $this->character->getUser()->getName();
    }

    public function isOwnedByCurrentUser(): bool
    {
        return $this->character->getUser() === $this->currentUser;
    }
}
