<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditKnPost;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditKnPostRequest implements EditKnPostRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPostId(): int
    {
        return $this->queryParameter('knid')->int()->required();
    }

    public function getPlotId(): int
    {
        return $this->queryParameter('plotid')->int()->defaultsTo(0);
    }

    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter('text')->string()->trim()->required()
        );
    }

    public function getTitle(): string
    {
        return $this->tidyString(
            $this->queryParameter('title')->string()->trim()->defaultsToIfEmpty('')
        );
    }

    public function getCharacterIds(): string
    {
        return $this->queryParameter('characterids')->string()->trim()->required();
    }
}
