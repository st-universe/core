<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowByPosition;

use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\Exception\AccessViolationException;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Exception\UnallowedUplinkOperationException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;
use Throwable;

final class ShowByPositionTest extends StuTestCase
{
    /** @return iterable<string, array{Throwable}> */
    public static function unavailableShips(): iterable
    {
        yield 'missing ship' => [new SpacecraftDoesNotExistException()];
        yield 'foreign ship' => [new AccessViolationException()];
        yield 'uplink not available' => [new UnallowedUplinkOperationException()];
    }

    #[DataProvider('unavailableShips')]
    public function testUnavailablePositionsHaveASafePopupTemplateWithoutRevealingAnyMap(Throwable $exception): void
    {
        request::setMockVars(['id' => 68741]);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->once()->andReturn(485);
        $information = new InformationWrapper();
        $game = $this->mock(ViewControllerContext::class);
        $game->shouldReceive('setMacroInAjaxWindow')->once()->with('');
        $game->shouldReceive('setPageTitle')->once()->with('Sternenkartenposition');
        $game->shouldReceive('getUser')->once()->andReturn($user);
        $game->shouldReceive('getInfo')->once()->andReturn($information);
        $game->shouldNotReceive('setTemplateVar', 'addExecuteJS');
        $loader = $this->mock(SpacecraftLoaderInterface::class);
        $loader->shouldReceive('getByIdAndUser')->once()->with(68741, 485, true, false)->andThrow($exception);
        $factory = $this->mock(StarmapUiFactoryInterface::class);
        $factory->shouldNotReceive('createMapSectionHelper');

        new ShowByPosition($loader, $factory)->handle($game);

        self::assertSame(['Die Schiffsposition ist nicht verfügbar'], $information->getInformations());
    }

    /** @return iterable<string, array{bool, ?int}> */
    public static function unmappableLocations(): iterable
    {
        yield 'wormhole without a layer' => [false, 1];
        yield 'location without a sector' => [true, null];
    }

    #[DataProvider('unmappableLocations')]
    public function testLocationsWithoutAWorldMapStayRenderableInsteadOfThrowingBeforeSettingATemplate(bool $hasLayer, ?int $sector): void
    {
        request::setMockVars(['id' => 68741]);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->once()->andReturn(485);
        $information = new InformationWrapper();
        $game = $this->mock(ViewControllerContext::class);
        $game->shouldReceive('setMacroInAjaxWindow')->once()->with('');
        $game->shouldReceive('setPageTitle')->once()->with('Sternenkartenposition');
        $game->shouldReceive('getUser')->once()->andReturn($user);
        $game->shouldReceive('getInfo')->once()->andReturn($information);
        $game->shouldNotReceive('setTemplateVar', 'addExecuteJS');
        $ship = $this->mock(Ship::class);
        $ship->shouldReceive('getLayer')->once()->andReturn($hasLayer ? new Layer() : null);
        $ship->shouldReceive('getLocation->getSectorId')->once()->andReturn($sector);
        $loader = $this->mock(SpacecraftLoaderInterface::class);
        $loader->shouldReceive('getByIdAndUser')->once()->with(68741, 485, true, false)->andReturn($ship);
        $factory = $this->mock(StarmapUiFactoryInterface::class);
        $factory->shouldNotReceive('createMapSectionHelper');

        new ShowByPosition($loader, $factory)->handle($game);

        self::assertSame(['An dieser Position ist keine Sternenkarte verfügbar'], $information->getInformations());
    }
}
