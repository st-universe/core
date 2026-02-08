<?php

namespace Stu\Module\Control;

use RuntimeException;

/**
 * This class adds the possibility to inject a random generator
 */
class StuRandom
{
    public function rand(
        int $min,
        int $max,
        bool $useStandardNormalDistribution = false,
        ?int $mean = null,
        float $skewness = 0
    ): int {
        if ($skewness != 0 && $useStandardNormalDistribution) {
            return $this->generateRandomValueAsymmetricDistribution($min, $max, $mean, $skewness);
        }

        if ($useStandardNormalDistribution) {
            return $this->generateRandomValueStandardNormalDistribution($min, $max, $mean);
        }


        return random_int($min, $max);
    }

    /**
     * Return a random key from the given array.
     *
     * @template TKey of int|string
     * @param array<TKey, mixed> $array The array to pick a key from.
     * @return TKey A randomly selected key from the array.
     */
    public function array_rand(array $array): string|int
    {
        if ($array === []) {
            throw new RuntimeException('Cannot pick a random key from an empty array');
        }

        /** @var TKey of int|string */
        $result = array_rand($array);

        return $result;
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

    public function uniqid(): string
    {
        return uniqid();
    }

    private function generateRandomValueStandardNormalDistribution(int $min, int $max, ?int $mean): int
    {
        $usedMean = $mean ?? ($min + $max) / 2; // MW
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

    private function generateRandomValueAsymmetricDistribution(int $min, int $max, ?int $mean = null, float $skewness = 0): int
    {
        $usedMean = $mean ?? ($min + $max) / 2;
        $stdDeviation = ($max - $min) / 6;

        do {
            $value = random_int($min, $max);
            $probability = exp(-0.5 * (($value - $usedMean) / $stdDeviation) ** 2);

            if ($skewness !== 0.0) {
                $skewFactor = 1 + $skewness * (($value - $usedMean) / ($max - $min));
                $probability *= max(0, $skewFactor);
            }

            $randomProbability = random_int(0, mt_getrandmax()) / mt_getrandmax();

            if ($randomProbability <= $probability) {
                return $value;
            }
        } while (true);
    }
}
