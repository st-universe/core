<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AdminDeleteKnPost;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class AdminDeleteKnPostRequest implements AdminDeleteKnPostRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getKnId(): int
    {
        return $this->parameter('knid')->int()->required();
    }

    #[\Override]
    public function getReason(): string
    {
        return $this->parameter('reason')->string()->defaultsTo('');
    }
}
