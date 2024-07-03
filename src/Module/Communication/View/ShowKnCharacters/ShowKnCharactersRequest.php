<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnCharacters;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowKnCharactersRequest implements ShowKnCharactersRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getCharacterId(): int
    {
        return $this->queryParameter('character')->int()->required();
    }
}
