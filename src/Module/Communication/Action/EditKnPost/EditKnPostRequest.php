<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditKnPost;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditKnPostRequest implements EditKnPostRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getKnId(): int
    {
        return $this->parameter('knid')->int()->required();
    }

    #[Override]
    public function getPlotId(): int
    {
        return $this->parameter('plotid')->int()->defaultsTo(0);
    }

    #[Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->parameter('text')->string()->trim()->required()
        );
    }

    #[Override]
    public function getTitle(): string
    {
        return $this->tidyString(
            $this->parameter('title')->string()->trim()->defaultsToIfEmpty('')
        );
    }

    #[Override]
    public function getCharacterIds(): string
    {
        return $this->parameter('characterids')->string()->trim()->required();
    }
}
