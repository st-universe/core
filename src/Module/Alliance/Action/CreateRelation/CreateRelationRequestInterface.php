<?php

namespace Stu\Module\Alliance\Action\CreateRelation;

interface CreateRelationRequestInterface
{
    public function getCounterpartId(): int;

    public function getRelationType(): int;
}
