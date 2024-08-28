<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnCharacter;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowKnCharacterRequest implements ShowKnCharacterRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getCharacterId(): int
    {
        return $this->queryParameter('character')->int()->required();
    }
}
