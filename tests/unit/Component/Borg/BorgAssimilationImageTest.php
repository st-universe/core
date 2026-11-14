<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\StuTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class BorgAssimilationImageTest extends StuTestCase
{
    /** @return iterable<string, array{int}> */
    public static function progress(): iterable
    {
        yield 'partial assimilation' => [25];
        yield 'complete assimilation' => [100];
    }

    #[DataProvider('progress')]
    public function testShipMacroRendersTheRightSideOverlayAndOnlyTheAssimilatedImageAtCompletion(int $progress): void
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__, 4) . '/src'));
        $twig->addFunction(new TwigFunction('getBorgAssimilationState', static fn (): array => [
            'progress' => $progress, 'active' => false, 'image' => '/assets/ships/assimilated/4203.png',
            'started' => 0, 'initial' => 0, 'duration' => 900, 'now' => 1000
        ]));
        $twig->addFunction(new TwigFunction('isBorgSphere', static fn (): bool => false));
        $twig->addFunction(new TwigFunction('canAttackTarget', static fn (): bool => false));
        $twig->addFilter(new TwigFilter('bbcode', static fn (string $text): string => $text));
        $twig->addFilter(new TwigFilter('bbcode2txt', static fn (string $text): string => $text));
        $ship = new Ship()->setRump(new SpacecraftRump()->setId(4203)->setName('K\'t\'inga'));

        $html = $twig->createTemplate("{% import 'html/shipmacros.twig' as images %}{{ images.spacecraftImageWithoutWarp(ship) }}")->render(['ship' => $ship]);

        self::assertStringContainsString('/assets/ships/assimilated/4203.png', $html);
        if ($progress === 100) {
            self::assertStringNotContainsString('src="/assets/ships/4203.png"', $html);
            self::assertStringNotContainsString('borg-assimilation-overlay', $html);
        } else {
            self::assertStringContainsString('src="/assets/ships/4203.png"', $html);
            self::assertStringContainsString('clip-path:inset(0 0 0 75%)', $html);
        }
    }
}
