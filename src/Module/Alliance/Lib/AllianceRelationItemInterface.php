<?php

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelationInterface;

interface AllianceRelationItemInterface
{
    public function getRelation(): AllianceRelationInterface;

    public function getOpponent(): AllianceInterface;

    public function offerIsSend(): bool;
}
