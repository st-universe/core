<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowBorgSphereAttackConfirmation;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use request;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\SourceAndTargetWrappers;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\StuTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class ShowBorgSphereAttackConfirmationTest extends StuTestCase
{
    private ViewControllerContext&MockInterface $game;
    private SessionStorageInterface&MockInterface $session;
    private BorgSphereRepositoryInterface&MockInterface $events;
    /** @var SpacecraftLoaderInterface<SpacecraftWrapperInterface>&MockInterface */
    private SpacecraftLoaderInterface&MockInterface $loader;
    private BorgSphereEvent $event;
    private Ship $target;
    private InformationWrapper $information;
    private ShowBorgSphereAttackConfirmation $subject;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        request::setMockVars(['id' => 167, 'target' => 42, 'sstr' => 'old-session', 'borg_confirmation' => 'nonce', 'borg_target_system' => 'transwarp']);
        $this->game = $this->mock(ViewControllerContext::class);
        $this->session = $this->mock(SessionStorageInterface::class);
        $this->events = $this->mock(BorgSphereRepositoryInterface::class);
        $this->loader = $this->mock(SpacecraftLoaderInterface::class);
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->andReturn(1000);
        $this->subject = new ShowBorgSphereAttackConfirmation($this->loader, $this->events, $this->session, $time);
        $this->information = new InformationWrapper();
        $this->target = new Ship();
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($this->target, 42);
        $this->target->setLocation(new StarSystemMap());
        $this->target->setCondition(new SpacecraftCondition($this->target)->setDisabled(true));
        $this->event = new BorgSphereEvent()->setConfiguration(new BorgSphereConfiguration()->setQuest(new NPCQuest()->setStart(1))->setEndTime(2000));
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->once()->andReturn(261);
        $this->game->shouldReceive('getUser')->once()->andReturn($user);
        $this->game->shouldReceive('setPageTitle')->once()->with('Angriff bestätigen');
        $this->game->shouldReceive('setMacroInAjaxWindow')->once()->with('');
        $this->session->shouldNotReceive('deleteSessionData', 'storeSessionData');
        $this->events->shouldReceive('getByShip')->once()->with($this->target)->andReturn($this->event);
    }

    /** @return iterable<string, array{bool}> */
    public static function sourceTypes(): iterable
    {
        yield 'ship' => [false];
        yield 'station' => [true];
    }

    #[DataProvider('sourceTypes')]
    public function testValidConfirmationKeepsTheNonceAndUsesTheCurrentSession(bool $station): void
    {
        $this->source($station ? new Station() : new Ship());
        $this->session->shouldReceive('getSessionValue')->once()->with('borg_sphere_attack_167_42')->andReturn(['nonce' => 'nonce', 'expires' => 1000]);
        $this->game->shouldReceive('getSessionString')->once()->andReturn('new-session');
        $this->game->shouldReceive('setTemplateVar')->once()->with('BORG_ATTACK_PARAMETERS', 'id=167&target=42&sstr=new-session&borg_confirmation=nonce&borg_target_system=transwarp');
        $this->game->shouldReceive('setMacroInAjaxWindow')->once()->with('html/ship/borgSphereAttackConfirmation.twig');

        $this->subject->handle($this->game);
        $this->assertSkinPopup();
    }

    /** @return iterable<string, array{mixed}> */
    public static function invalidConfirmations(): iterable
    {
        yield 'missing nonce' => [null];
        yield 'wrong nonce' => [['nonce' => 'another', 'expires' => 1100]];
        yield 'expired nonce' => [['nonce' => 'nonce', 'expires' => 999]];
        yield 'invalid expiration type' => [['nonce' => 'nonce', 'expires' => '1100']];
    }

    #[DataProvider('invalidConfirmations')]
    public function testInvalidConfirmationShowsInformationWithoutAnAttackButton(mixed $stored): void
    {
        $this->source(new Ship());
        $this->session->shouldReceive('getSessionValue')->once()->with('borg_sphere_attack_167_42')->andReturn($stored);
        $this->game->shouldReceive('getInfo')->once()->andReturn($this->information);
        $this->game->shouldNotReceive('setTemplateVar', 'getSessionString');

        $this->subject->handle($this->game);

        self::assertSame(['Diese Angriffsbestätigung ist ungültig oder abgelaufen. Starte den Angriff erneut'], $this->information->getInformations());
    }

    public function testAFinishedEventDoesNotOfferFurtherFire(): void
    {
        $this->source(new Ship());
        $this->event->setPhase(BorgSpherePhaseEnum::ESCAPED);
        $this->session->shouldNotReceive('getSessionValue');
        $this->game->shouldReceive('getInfo')->once()->andReturn($this->information);
        $this->game->shouldNotReceive('setTemplateVar', 'getSessionString');

        $this->subject->handle($this->game);

        self::assertSame(['Diese Borg-Sphäre ist nicht mehr für diesen Angriff verfügbar'], $this->information->getInformations());
    }

    private function assertSkinPopup(): void
    {
        $twig = new Environment(new FilesystemLoader('src'), ['strict_variables' => true, 'autoescape' => 'html']);
        $html = $twig->render('html/ship/borgSphereAttackConfirmation.twig', ['BORG_ATTACK_PARAMETERS' => 'id=167&target=42&sstr=session&borg_confirmation=nonce']);

        self::assertSame(1, substr_count($html, 'actionToInnerContent('));
        self::assertStringContainsString("actionToInnerContent('B_ATTACK_SPACECRAFT', 'id", $html);
        self::assertStringContainsString('borg_confirmation', $html);
        self::assertStringContainsString('closeAjaxWindow();', $html);
        self::assertStringContainsString('Danach kann niemand mehr Naniten von ihr sammeln', $html);
        self::assertStringContainsString('class="button"', $html);
        self::assertStringContainsString('padding: 0 10px', $html);
        self::assertStringNotContainsString('confirm(', $html);
    }

    private function source(Spacecraft $source): void
    {
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($source, 167);
        $source->setLocation($this->target->getLocation());
        $sourceWrapper = $this->mock(SpacecraftWrapperInterface::class);
        $sourceWrapper->shouldReceive('get')->once()->andReturn($source);
        $targetWrapper = $this->mock(SpacecraftWrapperInterface::class);
        $targetWrapper->shouldReceive('get')->once()->andReturn($this->target);
        $wrappers = new SourceAndTargetWrappers($sourceWrapper);
        $wrappers->setTarget($targetWrapper);
        $this->loader->shouldReceive('getWrappersBySourceAndUserAndTarget')->once()->with(167, 261, 42, false, false)->andReturn($wrappers);
    }
}
