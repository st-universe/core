<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\NewPost;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class NewPostRequest implements NewPostRequestInterface
{
    use CustomControllerHelperTrait;

    public function getBoardId(): int
    {
        return $this->queryParameter('bid')->int()->required();
    }

    public function getTopicId(): int
    {
        return $this->queryParameter('tid')->int()->required();
    }
}