<?php

namespace Stu\Module\Communication\Lib;

use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\UserInterface;

interface KnTalFactoryInterface
{
    public function createKnPostTal(KnPostInterface $post, UserInterface $user): KnPostTalInterface;
}