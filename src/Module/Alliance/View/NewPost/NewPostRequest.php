<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\NewPost;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class NewPostRequest implements NewPostRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getBoardId(): int
    {
        return $this->queryParameter('bid')->int()->required();
    }

    #[Override]
    public function getTopicId(): int
    {
        return $this->queryParameter('tid')->int()->required();
    }
}
