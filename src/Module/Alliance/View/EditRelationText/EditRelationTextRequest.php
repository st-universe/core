<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\EditRelationText;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditRelationTextRequest implements EditRelationTextRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getRelationId(): int
    {
        return $this->queryParameter('relationid')->int()->defaultsTo(0);
    }
}
