<?php

namespace Stu\Module\PlayerProfile\Action\CreateCharacter;

interface CreateCharacterRequestInterface
{
    public function getName(): string;
    public function getDescription(): string;

    /**
     * @return array<string, mixed>
     */
    public function getAvatar(): array;
}
