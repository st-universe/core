<?php

declare(strict_types=1);

namespace Stu\Module\Notes\Action\SaveNote;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class SaveNoteRequest implements SaveNoteRequestInterface
{
    use CustomControllerHelperTrait;

    public function getNoteId(): int
    {
        return $this->queryParameter('note')->int()->defaultsTo(0);
    }

    public function getTitle(): string
    {
        return $this->tidyString(
            $this->queryParameter('title')->string()->trim()->defaultsToIfEmpty('')
        );
    }

    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter('text')->string()->trim()->defaultsToIfEmpty('')
        );
    }

}
