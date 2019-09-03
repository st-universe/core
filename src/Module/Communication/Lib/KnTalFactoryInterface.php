<?php

namespace Stu\Module\Communication\Lib;

use Stu\Orm\Entity\KnPostInterface;
use UserData;

interface KnTalFactoryInterface
{
    public function createKnPostTal(KnPostInterface $post, UserData $user): KnPostTalInterface;
}