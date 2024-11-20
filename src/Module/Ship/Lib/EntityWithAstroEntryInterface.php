<?php

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\AstronomicalEntryInterface;

interface EntityWithAstroEntryInterface
{
    /** @return Collection<int, AstronomicalEntryInterface> */
    public function getAstronomicalEntries(): Collection;
}
