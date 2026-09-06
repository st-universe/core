<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Relation;

interface AllianceRelationItemInterface
{
    public function getRelation(): Relation;

    public function getOpponent(): Alliance;

    public function offerIsSend(): bool;

    public function permissionChangeIsOfferedByCurrentParty(): bool;
}
