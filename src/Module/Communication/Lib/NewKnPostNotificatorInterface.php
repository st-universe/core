<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\RpgPlot;

interface NewKnPostNotificatorInterface
{
    public function notify(KnPost $post, RpgPlot $plot): void;
}
