<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\DeleteCharacter;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteCharacterRequest implements DeleteCharacterRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getCharacterId(): int
    {
        return $this->parameter('character_id')->int()->required();
    }
}
