<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnComment;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteKnCommentRequest implements DeleteKnCommentRequestInterface
{
    use CustomControllerHelperTrait;

    public function getCommentId(): int
    {
        return $this->queryParameter('comment')->int()->required();
    }
}
