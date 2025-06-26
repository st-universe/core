<?php

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceRelation;

interface AllianceRelationItemInterface
{
    public function getRelation(): AllianceRelation;

    public function getOpponent(): Alliance;

    public function offerIsSend(): bool;
}
