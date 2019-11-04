<?php

namespace Stu\Component\Communication\Kn;

use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\UserInterface;

interface KnFactoryInterface {

    public function createKnItem(KnPostInterface $knPost, UserInterface $currentUser): KnItemInterface;
}
