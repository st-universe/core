<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnCharacter;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowKnCharacterRequest implements ShowKnCharacterRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getCharacterId(): int
    {
        return $this->parameter('character')->int()->required();
    }
}
