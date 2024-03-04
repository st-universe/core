<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\ChangeCharacter;

interface ChangeCharacterRequestInterface
{
    public function getCharacterId(): int;
    public function getName(): string;
    public function getDescription(): string;

    /**
     * @return array<string, mixed>
     */
    public function getAvatar(): array;
}
