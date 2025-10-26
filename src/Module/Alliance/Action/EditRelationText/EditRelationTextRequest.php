<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\EditRelationText;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditRelationTextRequest implements EditRelationTextRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getRelationId(): int
    {
        return $this->queryParameter('relationid')->int()->defaultsTo(0);
    }

    #[\Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter('text')->string()->defaultsToIfEmpty('')
        );
    }
}
