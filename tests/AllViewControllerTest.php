<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowRegionInfo;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Config\Init;
use Stu\Module\Control\ViewControllerInterface;
use Stu\TestUser;
use Stu\TwigTestCase;

class AllViewControllerTest extends TwigTestCase
{
    #[Override]
    protected function getViewControllerClass(): string
    {
        return 'PROVIDED_BY_DATA_PROVIDER';
    }

    public static function getAllViewControllerDataProvider(): array
    {
        $stuContainer = Init::getContainer(TwigTestCase::$INTTEST_CONFIG_PATH);

        $result =  $stuContainer
            ->getDefinedImplementationsOf(ViewControllerInterface::class, true)
            ->map(fn(ViewControllerInterface $viewController): array => [$viewController])
            ->toArray();

        return $result;
    }

    #[DataProvider('getAllViewControllerDataProvider')]
    public function testHandle(ViewControllerInterface $viewController): void
    {
        //$userId = $this->loadTestData(new TestUser());

        $this->renderSnapshot($viewController);
    }
}
