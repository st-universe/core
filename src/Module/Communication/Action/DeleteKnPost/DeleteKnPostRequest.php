<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnPost;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteKnPostRequest implements DeleteKnPostRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getPostId(): int
    {
        return $this->parameter('knid')->int()->required();
    }
}
