<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddKnPost;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class AddKnPostRequest implements AddKnPostRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getPostMark(): int
    {
        return $this->queryParameter('markposting')->int()->defaultsTo(0);
    }

    #[Override]
    public function getPlotId(): int
    {
        return $this->queryParameter('plotid')->int()->defaultsTo(0);
    }

    #[Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter('text')->string()->trim()->required()
        );
    }

    #[Override]
    public function getTitle(): string
    {
        return $this->tidyString(
            $this->queryParameter('title')->string()->trim()->required()
        );
    }

    #[Override]
    public function getCharacterIds(): string
    {
        return $this->queryParameter('characterids')->string()->defaultsTo('');
    }
}
