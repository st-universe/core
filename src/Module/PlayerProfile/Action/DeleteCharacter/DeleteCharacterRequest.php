<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\DeleteCharacter;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteCharacterRequest implements DeleteCharacterRequestInterface
{
    use CustomControllerHelperTrait;

    public function getCharacterId(): int
    {
        return $this->queryParameter('character_id')->int()->required();
    }
}
