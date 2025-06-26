<?php

namespace Stu\Component\Communication\Kn;

use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\User;

interface KnFactoryInterface
{
    public function createKnItem(KnPost $knPost, User $currentUser): KnItemInterface;
}
