<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\UserRelation;

interface UserRelationRequestInterface
{
    public function getRelationId(): int;
}
