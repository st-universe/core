<?php

declare(strict_types=1);

namespace Stu\Component\Communication\Kn;

use Stu\Orm\Entity\KnPostArchiv;
use Stu\Orm\Entity\RpgPlotArchiv;
use Stu\Orm\Entity\User;

interface KnArchiveFactoryInterface
{
    public function createKnArchiveItem(KnPostArchiv $post, User $user, ?RpgPlotArchiv $plot = null): KnArchiveItemInterface;
}
