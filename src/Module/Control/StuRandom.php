<?php

namespace Stu\Module\Control;

use RuntimeException;

/**
 * This class adds the possibility to inject a random generator
 */
class StuRandom
{
    public function rand(int $min, int $max, bool $useStandardNormalDistribution = false, int $mean = null): int
    {
        if ($useStandardNormalDistribution) {
            return $this->generateRandomValueStandardNormalDistribution($min, $max, $mean);
        }

        return random_int($min, $max);
    }

    public function array_rand(array $array): string|int
    {
        return array_rand($array);
    }

    /** @param array<int, int> $probabilities */
    public function randomKeyOfProbabilities(array $probabilities): int
    {
        $totalProbability = array_sum($probabilities);

        $randomNumber = random_int(1, $totalProbability);
        $cumulativeProbability = 0;

        foreach ($probabilities as $key => $prob) {
            $cumulativeProbability += $prob;
            if ($randomNumber <= $cumulativeProbability) {
                return $key;
            }
        }

        throw new RuntimeException('this should not happen');
    }

    private function generateRandomValueStandardNormalDistribution(int $min, int $max, ?int $mean): int
    {
        $usedMean = $mean === null ? (($min + $max) / 2) : $mean; // MW
        $stdDeviation = $usedMean / 2.5; // FWHM

        do {
            $value = random_int($min, $max);
            $probability = exp(-0.5 * (($value - $usedMean) / $stdDeviation) ** 2); // normal distribution
            $randomProbability = random_int(0, mt_getrandmax()) / mt_getrandmax();

            if ($randomProbability <= $probability) {
                return $value;
            }
        } while (true);
    }
}
