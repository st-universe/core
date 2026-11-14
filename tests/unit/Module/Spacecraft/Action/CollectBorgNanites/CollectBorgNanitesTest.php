<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\CollectBorgNanites;

use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Borg\BorgSphereRaidService;
use Stu\Component\Borg\BorgSphereRaidTeam;
use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Module\Spacecraft\Lib\SourceAndTargetWrappers;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

final class CollectBorgNanitesTest extends ActionControllerTestCase
{
    public function testTheActionReturnsTheShipPageWithItsInformation(): void
    {
        request::setMockVars(['id' => 167, 'target' => 42]);
        $source = $this->mock(ShipWrapperInterface::class);
        $source->shouldReceive('get')->once()->andReturn(new Ship());
        $target = new Ship();
        $targetWrapper = $this->mock(SpacecraftWrapperInterface::class);
        $targetWrapper->shouldReceive('get')->once()->andReturn($target);
        $wrappers = new SourceAndTargetWrappers($source);
        $wrappers->setTarget($targetWrapper);
        $ships = $this->mock(ShipLoaderInterface::class);
        $ships->shouldReceive('getWrappersBySourceAndUserAndTarget')->once()->with(167, 261, 42)->andReturn($wrappers);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->once()->andReturn(261);
        $information = new InformationWrapper();
        $this->game->shouldReceive('setView')->once()->with(ShowSpacecraft::VIEW_IDENTIFIER);
        $this->game->shouldReceive('getUser')->once()->andReturn($user);
        $this->game->shouldReceive('getInfo')->once()->andReturn($information);
        $events = $this->mock(BorgSphereRepositoryInterface::class);
        $events->shouldReceive('getByShip')->once()->with($target)->andReturnNull();
        $action = new CollectBorgNanites($ships, $this->raids($events));

        $action->handle($this->game);

        self::assertSame(['Diese Borg-Sphäre ist nicht mehr für eine Außenmission verfügbar'], $information->getInformations());
        self::assertTrue($action->performSessionCheck());
    }

    public function testThePopupButtonUpdatesTheShipPageInOneRequest(): void
    {
        $html = $this->renderPopup();

        self::assertStringContainsString("actionToInnerContent('B_COLLECT_BORG_NANITES', 'id=167&target=42&sstr=test-session');", $html);
        self::assertStringNotContainsString('updatePopup(', $html);
        self::assertStringContainsString('<th>Benötigte freie Crew</th><td>5</td>', $html);
        self::assertStringContainsString('<th>Transwarpantrieb</th><td>10%</td>', $html);
        self::assertStringNotContainsString('10,00%', $html);
        self::assertStringNotContainsString('erhöhte Gefahr', $html);
        self::assertStringContainsString("<button type=\"button\" class=\"button\" onclick=\"actionToInnerContent('B_ATTACK_BORG_TRACTOR', 'id=167&target=42&sstr=test-session');\">Traktoremitter beschießen</button>", $html);
        self::assertStringContainsString("<button type=\"button\" class=\"button\" onclick=\"actionToInnerContent('B_ATTACK_SPACECRAFT', 'id=167&target=42&borg_target_system=transwarp&sstr=test-session');\">Transwarpantrieb beschießen</button>", $html);
        self::assertStringNotContainsString('class="divbody"', $html);
        self::assertStringNotContainsString('<a class="button"', $html);
        self::assertStringContainsString('<table class="tcal"', $html);
        self::assertMatchesRegularExpression('/Erfasstes Schiff wird assimiliert.*Rotierende Frequenzmodulation: 25% Erfolgschance pro Energiesalve.*Traktoremitter beschießen/s', $html);
    }

    /** @return iterable<string, array{bool, bool, int, bool}> */
    public static function awayTeamAvailability(): iterable
    {
        yield 'active cube' => [false, false, 5, false];
        yield 'disabled cube' => [true, true, 5, true];
        yield 'disabled cube without free crew' => [true, true, 0, false];
        yield 'active sphere' => [true, false, 5, true];
    }

    #[DataProvider('awayTeamAvailability')]
    public function testThePopupOnlyOffersAvailableAwayTeams(bool $canSend, bool $disabled, int $crew, bool $expected): void
    {
        $html = $this->renderPopup(['BORG_CAN_SEND_TEAM' => $canSend, 'BORG_DISABLED' => $disabled, 'BORG_TEAM_SIZE' => $crew]);

        self::assertSame($expected, str_contains($html, 'Außenteam senden'));
        self::assertStringNotContainsString('Zusätzliche Crew fehlt', $html);
        self::assertStringContainsString('<th>Benötigte freie Crew</th><td>5</td>', $html);
    }

    /** @param array<string, mixed> $overrides */
    private function renderPopup(array $overrides = []): string
    {
        $event = new BorgSphereEvent()->setConfiguration(new BorgSphereConfiguration()->setSettings([
            'raid_cooldown_seconds' => 7200,
            'raid_team_size' => 5,
            'raid_reward_max' => 5,
            'tractor_rescue_chance_percent' => 25
        ]))->setTranswarpProgress(1000);
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__, 6) . '/src'), ['strict_variables' => true]);
        $twig->addFilter(new TwigFilter('bbcode', static fn (string $text): string => $text));
        return $twig->render('html/ship/borgSphere.twig', array_replace([
            'INFORMATION' => null,
            'BORG_DISABLED' => false,
            'BORG_LABEL' => 'Borg-Sphäre',
            'BORG_CAPTURED_SHIP' => ['isBeingAssimilated' => true, 'getName' => 'Erfasstes Schiff'],
            'BORG_SPHERE' => ['getId' => 42, 'getCondition' => ['getHull' => 10, 'getShield' => 10], 'getMaxHull' => 100, 'getMaxShield' => 100],
            'BORG_MAX_SHIELD' => 100,
            'BORG_EVENT' => $event,
            'BORG_SUCCESS' => 90,
            'BORG_CAN_SEND_TEAM' => true,
            'BORG_TEAM_SIZE' => 5,
            'BORG_CREW_REQUIREMENT' => 'Zusätzliche Crew fehlt',
            'BORG_COOLDOWN' => null,
            'SHIP' => ['getId' => 167],
            'SESSIONSTRING' => 'test-session'
        ], $overrides));
    }

    private function raids(BorgSphereRepositoryInterface $events): BorgSphereRaidService
    {
        $random = $this->mock(StuRandom::class);
        $team = new BorgSphereRaidTeam(
            $this->mock(CrewRepositoryInterface::class),
            $this->mock(CrewAssignmentRepositoryInterface::class),
            $this->mock(SpacecraftShutdownInterface::class),
            $this->mock(SpacecraftMovementPublisherInterface::class),
            $random
        );
        return new BorgSphereRaidService(
            $events,
            $team,
            $this->mock(InteractionCheckerBuilderFactoryInterface::class),
            $this->mock(CommodityRepositoryInterface::class),
            $this->mock(StorageManagerInterface::class),
            $this->mock(PrivateMessageSenderInterface::class),
            $random,
            $this->mock(StuTime::class)
        );
    }
}
