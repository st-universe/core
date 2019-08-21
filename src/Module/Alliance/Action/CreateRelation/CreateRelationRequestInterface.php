<?php

namespace Stu\Module\Alliance\Action\CreateRelation;

interface CreateRelationRequestInterface
{
    public function getOpponentId(): int;

    public function getRelationType(): int;
}