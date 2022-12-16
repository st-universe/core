<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

final class EpsSystemData
{
    private int $maxBatt = 0;
    private int $batt = 0;
    private int $battWait = 0;

    public function getMaxBatt(): int
    {
        return $this->maxBatt;
    }

    public function setMaxBatt(int $maxBatt): EpsSystemData
    {
        $this->maxBatt = $maxBatt;
        return $this;
    }

    public function getBatt(): int
    {
        return $this->batt;
    }

    public function setBatt(int $batt): EpsSystemData
    {
        $this->batt = $batt;
        return $this;
    }

    public function getBattWait(): int
    {
        return $this->battWait;
    }

    public function setBattWait(int $battWait): EpsSystemData
    {
        $this->battWait = $battWait;
        return $this;
    }
}
