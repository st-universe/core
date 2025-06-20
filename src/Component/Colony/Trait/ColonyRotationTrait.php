<?php

namespace Stu\Component\Colony\Trait;

use Stu\Component\Game\TimeConstants;

trait ColonyRotationTrait
{
    public function getTwilightZone(int $timestamp): int
    {
        if (array_key_exists($timestamp, $this->twilightZones)) {
            return $this->twilightZones[$timestamp];
        }

        $twilightZone = 0;

        $width = $this->getSurfaceWidth();
        $rotationTime = $this->getRotationTime();
        $colonyTimeSeconds = $this->getColonyTimeSeconds($timestamp);

        if ($this->getDayTimePrefix($timestamp) == 1) {
            $scaled = floor((((100 / ($rotationTime * 0.125)) * ($colonyTimeSeconds - $rotationTime * 0.25)) / 100) * $width);
            if ($scaled == 0) {
                $twilightZone = - (($width) - 1);
            } elseif ((int) - (($width) - ceil($scaled)) == 0) {
                $twilightZone = -1;
            } else {
                $twilightZone = (int) - (($width) - $scaled);
            }
        }
        if ($this->getDayTimePrefix($timestamp) == 2) {
            $twilightZone = $width;
        }
        if ($this->getDayTimePrefix($timestamp) == 3) {
            $scaled = floor((((100 / ($rotationTime * 0.125)) * ($colonyTimeSeconds - $rotationTime * 0.75)) / 100) * $width);
            $twilightZone = (int) ($width - $scaled);
        }
        if ($this->getDayTimePrefix($timestamp) == 4) {
            $twilightZone = 0;
        }

        $this->twilightZones[$timestamp] = $twilightZone;

        return $twilightZone;
    }

    public function getRotationTime(): int
    {
        return (int) (TimeConstants::ONE_DAY_IN_SECONDS * $this->getRotationFactor() / 100);
    }

    public function getColonyTimeHour(int $timestamp): ?string
    {
        $rotationTime = $this->getRotationTime();

        return sprintf("%02d", (int) floor(($rotationTime / 3600) * ($this->getColonyTimeSeconds($timestamp) / $rotationTime)));
    }

    public function getColonyTimeMinute(int $timestamp): ?string
    {
        $rotationTime = $this->getRotationTime();

        return sprintf("%02d", (int) floor(60 * (($rotationTime / 3600) * ($this->getColonyTimeSeconds($timestamp) / $rotationTime) - ((int) $this->getColonyTimeHour($timestamp)))));
    }

    private function getColonyTimeSeconds(int $timestamp): int
    {
        return $timestamp % $this->getRotationTime();
    }

    public function getDayTimePrefix(int $timestamp): ?int
    {
        $daytimeprefix = null;
        $daypercent = (int) (($this->getColonyTimeSeconds($timestamp) / $this->getRotationTime()) * 100);
        if ($daypercent > 25 && $daypercent <= 37.5) {
            $daytimeprefix = 1; //Sonnenaufgang
        }
        if ($daypercent > 37.5 && $daypercent <= 75) {
            $daytimeprefix = 2; //Tag
        }
        if ($daypercent > 75 && $daypercent <= 87.5) {
            $daytimeprefix = 3; //Sonnenuntergang
        }
        if ($daypercent > 87.5 || $daypercent <= 25) {
            $daytimeprefix = 4; //Nacht
        }
        return $daytimeprefix;
    }

    public function getDayTimeName(int $timestamp): ?string
    {
        $daytimename = null;
        if ($this->getDayTimePrefix($timestamp) == 1) {
            $daytimename = 'Morgen';
        }

        if ($this->getDayTimePrefix($timestamp) == 2) {
            $daytimename = 'Tag';
        }

        if ($this->getDayTimePrefix($timestamp) == 3) {
            $daytimename = 'Abend';
        }

        if ($this->getDayTimePrefix($timestamp) == 4) {
            $daytimename = 'Nacht';
        }
        return $daytimename;
    }
}
