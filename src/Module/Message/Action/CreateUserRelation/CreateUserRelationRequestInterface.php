<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\CreateUserRelation;

interface CreateUserRelationRequestInterface
{
    public function getTargetId(): int;

    public function getTargetType(): int;

    public function getRelationType(): int;
}
