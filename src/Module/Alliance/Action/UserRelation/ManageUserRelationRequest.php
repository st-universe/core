<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\UserRelation;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ManageUserRelationRequest implements ManageUserRelationRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getAction(): string
    {
        return $this->parameter('ura')->string()->defaultsToIfEmpty('');
    }

    #[\Override]
    public function getRelationId(): int
    {
        return $this->parameter('ur')->int()->defaultsTo(0);
    }

    #[\Override]
    public function getUserId(): int
    {
        return $this->parameter('uid')->int()->defaultsTo(0);
    }

    #[\Override]
    public function getRelationType(): int
    {
        return $this->parameter('type')->int()->defaultsTo(0);
    }
}
