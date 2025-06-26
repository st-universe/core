<?php

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\AstronomicalEntry;

interface EntityWithAstroEntryInterface
{
    /** @return Collection<int, AstronomicalEntry> */
    public function getAstronomicalEntries(): Collection;
}
