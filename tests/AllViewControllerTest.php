<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowRegionInfo;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Stu\Config\Init;
use Stu\Module\Control\ViewControllerInterface;
use Stu\TwigTestCase;

class AllViewControllerTest extends TwigTestCase
{
    private string $snapshotKey = '';

    #[Override]
    protected function getViewControllerClass(): string
    {
        return 'PROVIDED_BY_DATA_PROVIDER';
    }

    #[Override]
    protected function getSnapshotId(): string
    {
        return (new ReflectionClass($this))->getShortName() . '--' .
            $this->snapshotKey;
    }

    public static function getAllViewControllerDataProvider(): array
    {
        $definedImplementations =  Init::getContainer(TwigTestCase::$INTTEST_CONFIG_PATH)
            ->getDefinedImplementationsOf(ViewControllerInterface::class, true);

        return $definedImplementations
            ->map(fn(ViewControllerInterface $viewController): array => [$definedImplementations->indexOf($viewController), $viewController])
            ->toArray();
    }

    #[DataProvider('getAllViewControllerDataProvider')]
    public function testHandle(string $key, ViewControllerInterface $viewController): void
    {
        $this->snapshotKey = $key;

        $this->renderSnapshot($viewController);
    }
}
