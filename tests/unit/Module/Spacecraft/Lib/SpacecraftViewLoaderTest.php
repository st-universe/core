<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Exception\UnallowedUplinkOperationException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Game\Lib\View\ViewComponentLoaderInterface;
use Stu\StuTestCase;
use Throwable;

final class SpacecraftViewLoaderTest extends StuTestCase
{
    public function testAuthorizedViewsKeepTheStrictLoaderAndItsUplinkAndReadonlyFlags(): void
    {
        $loader = $this->mock(SpacecraftLoaderInterface::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $loader->shouldReceive('getWrapperByIdAndUser')->once()->with(92708, 485, true, false)->andReturn($wrapper);
        $views = $this->mock(ViewComponentLoaderInterface::class);
        $views->shouldNotReceive('registerViewComponents');
        $game = $this->mock(ViewControllerContext::class);
        $game->shouldNotReceive('getInfo', 'setViewTemplate');

        self::assertSame($wrapper, new SpacecraftViewLoader($loader, $views)->getWrapper(92708, 485, $game));
    }

    /** @return iterable<string, array{ModuleEnum}> */
    public static function modules(): iterable
    {
        yield 'ship list' => [ModuleEnum::SHIP];
        yield 'station list' => [ModuleEnum::STATION];
    }

    #[DataProvider('modules')]
    public function testOwnershipLossOnlyShowsTheCurrentUsersListAndNeverForeignShipDetails(ModuleEnum $module): void
    {
        $loader = $this->mock(SpacecraftLoaderInterface::class);
        $loader->shouldReceive('getWrapperByIdAndUser')->once()->with(92708, 485, true, false)
            ->andThrow(new AccessViolationException('Spacecraft owned by another user (19)! Fool: 485'));
        $game = $this->mock(ViewControllerContext::class);
        $information = new InformationWrapper();
        $game->shouldReceive('getInfo')->once()->andReturn($information);
        $game->shouldReceive('getModule')->once()->andReturn($module);
        $game->shouldReceive('setPageTitle')->once()->with($module->getTitle());
        $game->shouldReceive('setViewTemplate')->once()->with($module->getTemplate());
        $game->shouldNotReceive('setTemplateVar');
        $views = $this->mock(ViewComponentLoaderInterface::class);
        $views->shouldReceive('registerViewComponents')->once()->with($module, $game);

        self::assertNull(new SpacecraftViewLoader($loader, $views)->getWrapper(92708, 485, $game));

        self::assertSame(['Dieses Schiff ist nicht mehr verfügbar oder gehört nicht zu deinem Account'], $information->getInformations());
        self::assertStringNotContainsString('19', $information->getInformationsAsString());
    }

    /** @return iterable<string, array{Throwable}> */
    public static function existingFallbacks(): iterable
    {
        yield 'deleted ship' => [new SpacecraftDoesNotExistException('Raumfahrzeug existiert nicht!')];
        yield 'forbidden uplink' => [new UnallowedUplinkOperationException('Uplink is not activated!')];
    }

    #[DataProvider('existingFallbacks')]
    public function testExistingFallbackRoutesKeepHandlingMissingShipsAndForbiddenUplinks(Throwable $exception): void
    {
        $loader = $this->mock(SpacecraftLoaderInterface::class);
        $loader->shouldReceive('getWrapperByIdAndUser')->once()->with(92708, 485, true, false)->andThrow($exception);
        $views = $this->mock(ViewComponentLoaderInterface::class);
        $views->shouldNotReceive('registerViewComponents');
        $game = $this->mock(ViewControllerContext::class);
        $game->shouldNotReceive('getInfo', 'setViewTemplate');
        $this->expectExceptionObject($exception);

        new SpacecraftViewLoader($loader, $views)->getWrapper(92708, 485, $game);
    }
}
