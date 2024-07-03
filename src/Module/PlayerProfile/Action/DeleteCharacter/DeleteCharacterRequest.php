<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\DeleteCharacter;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteCharacterRequest implements DeleteCharacterRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getCharacterId(): int
    {
        return $this->queryParameter('character_id')->int()->required();
    }
}
