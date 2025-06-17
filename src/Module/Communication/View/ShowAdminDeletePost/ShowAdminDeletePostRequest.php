<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowAdminDeletePost;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowAdminDeletePostRequest implements ShowAdminDeletePostRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getPostId(): int
    {
        return $this->parameter('postid')->int()->required();
    }
}
