<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\UserRelation;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class UserRelationRequest implements UserRelationRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getRelationId(): int
    {
        return $this->parameter('ur')->int()->required();
    }
}
