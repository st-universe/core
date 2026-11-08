<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stu\Orm\Entity\TechnologyRecipe;

final class ModuleOptimizationChanceTest extends TestCase
{
    /** @return iterable<string, array{int, int, int, int}> */
    public static function chances(): iterable
    {
        yield 'first minimum' => [0, 5, 10, 2000];
        yield 'first maximum' => [0, 50, 100, 5000];
        yield 'modules independently' => [0, 50, 10, 3500];
        yield 'nanites independently' => [0, 5, 100, 3500];
        yield 'second minimum' => [1, 5, 10, 2100];
        yield 'second maximum' => [1, 50, 100, 5100];
        yield 'experienced minimum' => [30, 5, 10, 5000];
        yield 'experienced maximum' => [30, 50, 100, 8000];
        yield 'cap before final success' => [69, 50, 100, 8000];
        yield 'fractional chance' => [0, 6, 11, 2050];
    }

    #[DataProvider('chances')]
    public function testChanceUsesBothInputsAndExperience(int $steps, int $modules, int $nanites, int $expected): void
    {
        self::assertSame($expected, new ModuleOptimizationChance()->calculate($this->recipe(), $steps, $modules, $nanites));
    }

    /** @return iterable<string, array{int, int}> */
    public static function invalidAmounts(): iterable
    {
        yield 'modules below' => [4, 10];
        yield 'modules above' => [51, 10];
        yield 'nanites below' => [5, 9];
        yield 'nanites above' => [5, 101];
    }

    #[DataProvider('invalidAmounts')]
    public function testAmountsOutsideRecipeLimitsAreRejected(int $modules, int $nanites): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ModuleOptimizationChance()->calculate($this->recipe(), 0, $modules, $nanites);
    }

    private function recipe(): TechnologyRecipe
    {
        return new TechnologyRecipe()->setSettings([
            'min_modules' => 5, 'max_modules' => 50, 'min_nanites' => 10, 'max_nanites' => 100,
            'base_success' => 20, 'max_base_success' => 50, 'extra_success' => 30
        ]);
    }
}
