<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnPost;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteKnPostRequest implements DeleteKnPostRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getKnId(): int
    {
        return $this->parameter('knid')->int()->required();
    }
}
