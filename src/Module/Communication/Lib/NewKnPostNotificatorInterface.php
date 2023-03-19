<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;

interface NewKnPostNotificatorInterface
{
    public function notify(KnPostInterface $post, RpgPlotInterface $plot): void;
}
