<?php

declare(strict_types=1);

namespace Stu\Html;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Stu\Config\Init;
use Stu\Module\Control\ViewControllerInterface;
use Stu\TwigTestCase;

class DefaultViewsControllerTest extends TwigTestCase
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

    public static function getDefaultViewsControllerDataProvider(): array
    {
        $definedImplementations =  Init::getContainer()
            ->getDefinedImplementationsOf(ViewControllerInterface::class, true);

        return $definedImplementations
            ->map(fn(ViewControllerInterface $viewController): array => [$definedImplementations->indexOf($viewController)])
            ->filter(fn(array $array): bool => str_ends_with($array[0], '-DEFAULT_VIEW') && !str_starts_with($array[0], 'GAME_VIEWS'))
            ->toArray();
    }

    #[DataProvider('getDefaultViewsControllerDataProvider')]
    public function testHandle(string $key): void
    {
        $this->snapshotKey = $key;

        $viewValue = strtolower(explode('_', $key, 2)[0]);

        $this->renderSnapshot(
            [
                'view' => $viewValue,
                'switch' => 1
            ],
            Init::getContainer()
                ->getDefinedImplementationsOf(ViewControllerInterface::class, true)->get($key)
        );
    }
}
