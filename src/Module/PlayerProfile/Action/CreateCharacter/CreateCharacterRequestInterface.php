<?php

namespace Stu\Module\PlayerProfile\Action\CreateCharacter;

interface CreateCharacterRequestInterface
{
    public function getName(): string;
    public function getDescription(): string;
    public function getAvatar(): array;
}
