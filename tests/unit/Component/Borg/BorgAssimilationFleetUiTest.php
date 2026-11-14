<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Ship\View\ShowAvailableShips\ShowAvailableShips;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class BorgAssimilationFleetUiTest extends StuTestCase
{
    /** @return iterable<string, array{bool}> */
    public static function captureStates(): iterable
    {
        yield 'active capture' => [true];
        yield 'freed partial assimilation' => [false];
    }

    #[DataProvider('captureStates')]
    public function testShiplistKeepsReadOnlyNavigationButHidesFleetCreationDuringCapture(bool $captured): void
    {
        $ship = $this->ship($captured);
        $html = $this->twig()->createTemplate("{% import 'html/shiplistmacros.twig' as ships %}{{ ships.shiplist_body(wrapper) }}")
            ->render(['wrapper' => ['get' => $ship]]);

        self::assertStringContainsString('/ship.php?SHOW_SPACECRAFT=1&id=77', $html);
        self::assertSame(!$captured, str_contains($html, 'B_NEW_FLEET=1'));
    }

    #[DataProvider('captureStates')]
    public function testFleetSelectionNeverOffersACapturedShipOrAnEmptyJoinAction(bool $captured): void
    {
        $html = $this->twig()->render('html/ship/showavailableships.twig', [
            'AVAILABLE_SHIPS' => [$this->ship($captured)], 'FLEET' => ['getId' => 10], 'SESSIONSTRING' => 'token'
        ]);

        self::assertSame(!$captured, str_contains($html, 'name="chosen[]"'));
        self::assertSame(!$captured, str_contains($html, 'joinFleetInShiplist(10)'));
        self::assertSame($captured, str_contains($html, 'Keine Schiffe vorhanden'));
    }

    #[DataProvider('captureStates')]
    public function testNbsJoinShortcutIsHiddenWhileTheSourceShipIsCaptured(bool $captured): void
    {
        $ship = $this->ship($captured);
        $fleet = ['getId' => 10, 'getName' => 'Flotte', 'showManagement' => true, 'getVisibleShipsCount' => 0, 'getVisibleShips' => [], 'getLeadShip' => $ship];
        $html = $this->twig()->createTemplate("{% import 'html/shipmacros.twig' as ships %}{{ ships.nbsblock(wrapper, true, false, [], [fleet], [], [], false) }}")
            ->render(['wrapper' => ['get' => $ship], 'fleet' => $fleet]);

        self::assertSame(!$captured, str_contains($html, 'B_JOIN_FLEET_NBS=1'));
    }

    #[DataProvider('captureStates')]
    public function testFleetHeaderCannotOfferAddingShipsWhileItsLeaderIsCaptured(bool $captured): void
    {
        $fleet = ['getId' => 10, 'getName' => 'Flotte', 'getCrewSum' => 0, 'getLeadShip' => $this->ship($captured)];
        $html = $this->twig()->render('html/shiplistFleetform.twig', [
            'FLEETWRAPPER' => ['get' => $fleet, 'getShipWrappers' => []], 'MAX_CREW_PER_FLEET' => 5000, 'IS_ADMIN' => false
        ]);

        self::assertSame(!$captured, str_contains($html, 'showAvailableShips(this, 10)'));
    }

    public function testAvailableShipsViewFiltersActiveCapturesButRetainsFreedShips(): void
    {
        request::setMockVars(['fleetid' => 10]);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(261);
        $leader = new Ship();
        $fleet = $this->mock(Fleet::class);
        $fleet->shouldReceive('getUserId')->once()->andReturn(261);
        $fleet->shouldReceive('getLeadShip')->once()->andReturn($leader);
        $free = new Ship();
        $freed = new Ship();
        new SpacecraftAssimilated()->setSpacecraft($freed)->setProgress(2500);
        $source = $this->mock(Ship::class);
        $source->shouldReceive('getUser->getId')->once()->andReturn(19);
        $captured = new Ship();
        new SpacecraftAssimilated()->setSpacecraft($captured)->start($source, 1000, 900);
        $fleets = $this->mock(FleetRepositoryInterface::class);
        $fleets->shouldReceive('find')->once()->with(10)->andReturn($fleet);
        $ships = $this->mock(ShipRepositoryInterface::class);
        $ships->shouldReceive('getPossibleFleetMembers')->once()->with($leader)->andReturn([$free, $captured, $freed]);
        $context = $this->mock(ViewControllerContext::class);
        $context->shouldReceive('getUser')->once()->andReturn($user);
        $context->shouldReceive('setPageTitle')->once()->with('Schiff(e) hinzufügen');
        $context->shouldReceive('setMacroInAjaxWindow')->once()->with('html/ship/showavailableships.twig');
        $context->shouldReceive('setTemplateVar')->once()->with('FLEET', $fleet);
        $context->shouldReceive('setTemplateVar')->once()->with('AVAILABLE_SHIPS', [0 => $free, 2 => $freed]);

        new ShowAvailableShips($fleets, $ships)->handle($context);
    }

    /** @return iterable<string, array{bool, bool}> */
    public static function manualAssimilationTargets(): iterable
    {
        yield 'eligible target' => [true, false];
        yield 'unavailable target or foreign source' => [false, false];
        yield 'target in warp' => [true, true];
    }

    #[DataProvider('manualAssimilationTargets')]
    public function testNbsOffersManualAssimilationOnlyForAnEligibleUnwarpedTarget(bool $eligible, bool $warped): void
    {
        $twig = $this->twig();
        $twig->addFunction(new TwigFunction('canAssimilateTarget', static fn (): bool => $eligible));
        $twig->addGlobal('SESSIONSTRING', 'token');
        $source = array_merge($this->ship(false), ['getId' => 11, 'displayNbsActions' => true]);
        $target = array_merge($this->ship(false), ['getId' => 22, 'getName' => 'Zielschiff', 'isWarped' => $warped]);
        $html = $twig->createTemplate("{% import 'html/shipmacros.twig' as ships %}{{ ships.nbslist_body(wrapper, target, false) }}")
            ->render(['wrapper' => ['get' => $source], 'target' => $target, 'SESSIONSTRING' => 'token']);

        self::assertSame($eligible && !$warped, str_contains($html, 'B_BORG_ASSIMILATE_TARGET'));
        if ($eligible && !$warped) {
            self::assertStringContainsString("actionToInnerContent('B_BORG_ASSIMILATE_TARGET', 'id=11&target=22&sstr=token');", $html);
            self::assertStringContainsString('title="Zielschiff assimilieren"', $html);
        }
    }

    /** @return array<string, mixed> */
    private function ship(bool $captured): array
    {
        return [
            'getId' => 77, 'getName' => 'Testschiff', 'getRumpId' => 4101, 'getRumpName' => 'K\'t\'inga',
            'getType' => SpacecraftTypeEnum::SHIP, 'isBeingAssimilated' => $captured, 'isStation' => false,
            'getCondition' => ['getHull' => 1000, 'getShield' => 0], 'getMaxHull' => 1000, 'getMaxShield' => 0,
            'getMap' => ['getCx' => 1, 'getCy' => 2], 'getLocation' => ['getLayer' => ['getId' => 2, 'getName' => 'Trench']]
        ];
    }

    private function twig(): Environment
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__, 4) . '/src'));
        $twig->addFunction(new TwigFunction('getBorgAssimilationState', static fn (): array => ['progress' => 0, 'active' => false, 'image' => null]));
        $twig->registerUndefinedFunctionCallback(static fn (string $name): TwigFunction => new TwigFunction($name, static fn (): null => null));
        $twig->registerUndefinedFilterCallback(static fn (string $name): TwigFilter => new TwigFilter($name, static fn (mixed $value): mixed => $value));
        return $twig;
    }
}
