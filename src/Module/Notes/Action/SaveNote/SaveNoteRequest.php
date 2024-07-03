<?php

declare(strict_types=1);

namespace Stu\Module\Notes\Action\SaveNote;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class SaveNoteRequest implements SaveNoteRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getNoteId(): int
    {
        return $this->queryParameter('note')->int()->defaultsTo(0);
    }

    #[Override]
    public function getTitle(): string
    {
        return $this->tidyString(
            $this->queryParameter('title')->string()->trim()->defaultsToIfEmpty('')
        );
    }

    #[Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter('text')->string()->trim()->defaultsToIfEmpty('')
        );
    }
}
