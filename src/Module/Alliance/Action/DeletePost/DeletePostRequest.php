<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeletePost;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeletePostRequest implements DeletePostRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPostId(): int
    {
        return $this->queryParameter('pid')->int()->required();
    }
}