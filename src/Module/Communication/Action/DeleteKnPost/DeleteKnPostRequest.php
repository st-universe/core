<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnPost;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteKnPostRequest implements DeleteKnPostRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPostId(): int
    {
        return $this->queryParameter('knid')->int()->required();
    }
}
