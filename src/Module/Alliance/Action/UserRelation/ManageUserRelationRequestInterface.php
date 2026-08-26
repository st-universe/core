<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\UserRelation;

interface ManageUserRelationRequestInterface
{
    public function getAction(): string;

    public function getRelationId(): int;

    public function getUserId(): int;

    public function getRelationType(): int;
}
