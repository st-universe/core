<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\DeleteCharacter;

interface DeleteCharacterRequestInterface
{
    public function getCharacterId(): int;
}
