<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\AttackBorgTractor;

use Doctrine\Common\Collections\ArrayCollection;
use LogicException;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Borg\BorgAssimilationLifecycle;
use Stu\Component\Borg\BorgSphereAttackContext;
use Stu\Component\Borg\BorgSphereCombat;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Component\Borg\BorgTractorRescue;
use Stu\Component\Borg\BorgTractorRescueParticipants;
use Stu\Component\Borg\BorgTractorRescueResponse;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\AttackedBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\Party\AttackingBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\MixedBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycleInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactory;
use Stu\Module\Spacecraft\Lib\SourceAndTargetWrappers;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;

final class AttackBorgTractorTest extends ActionControllerTestCase
{
    private ShipLoaderInterface&MockInterface $ships;
    private BorgSphereRepositoryInterface&MockInterface $events;
    private SpacecraftAttackCycleInterface&MockInterface $cycle;
    private SpacecraftWrapperFactoryInterface&MockInterface $wrappers;
    private BattlePartyFactoryInterface&MockInterface $parties;
    private InteractionCheckerBuilderFactoryInterface&MockInterface $checks;
    private StuRandom&MockInterface $random;
    private AttackBorgTractor $subject;
    private InformationWrapper $information;
    private Ship $source;
    private Ship $cube;
    private ShipWrapperInterface&MockInterface $sourceWrapper;
    private ShipWrapperInterface&MockInterface $cubeWrapper;
    /** @var SourceAndTargetWrappers<ShipWrapperInterface> */
    private SourceAndTargetWrappers $selection;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        request::setMockVars(['id' => 167, 'target' => 42]);
        $this->ships = $this->mock(ShipLoaderInterface::class);
        $this->events = $this->mock(BorgSphereRepositoryInterface::class);
        $this->cycle = $this->mock(SpacecraftAttackCycleInterface::class);
        $this->wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->parties = $this->mock(BattlePartyFactoryInterface::class);
        $this->checks = $this->mock(InteractionCheckerBuilderFactoryInterface::class);
        $this->random = $this->mock(StuRandom::class);
        $context = new BorgSphereAttackContext();
        $messages = $this->mock(DistributedMessageSenderInterface::class);
        $messages->shouldReceive('distributeMessageCollection');
        $fight = $this->mock(FightLibInterface::class);
        $fight->shouldReceive('canAttackTarget')->andReturn(true);
        $webs = $this->mock(TholianWebUtilInterface::class);
        $webs->shouldReceive('isTargetOutsideFinishedTholianWeb', 'isTargetInsideFinishedTholianWeb')->andReturn(false);
        $response = new BorgTractorRescueResponse($this->wrappers, $this->parties, $this->cycle, $messages, $fight, $context, $webs);
        $rescue = new BorgTractorRescue($this->events, new BorgSphereCombat($this->events, $context, $this->mock(StuTime::class)), $response, new BorgAssimilationLifecycle(static fn () => throw new LogicException('No successful rescue expected')), $this->mock(SpacecraftSystemManagerInterface::class), $this->checks, $this->random, new BorgTractorRescueParticipants($this->wrappers, $webs));
        $this->subject = new AttackBorgTractor($this->ships, $rescue);
        $location = new Map();
        $this->source = $this->ship(167, 261, $location);
        $this->source->setName('Rettungsschiff');
        $this->cube = $this->ship(42, 10, $location);
        $this->cube->setName('Kubus');
        $this->sourceWrapper = $this->wrapper($this->source);
        $this->cubeWrapper = $this->wrapper($this->cube);
        $this->selection = new SourceAndTargetWrappers($this->sourceWrapper);
        $this->selection->setTarget($this->cubeWrapper);
        $this->ships->shouldReceive('getWrappersBySourceAndUserAndTarget')->once()->with(167, 261, 42)->andReturn($this->selection);
        $this->information = new InformationWrapper();
        $this->game->shouldReceive('getUser')->once()->andReturn($this->source->getUser());
        $this->game->shouldReceive('getInfo')->andReturn($this->information);
        $this->game->shouldReceive('setView')->once()->with(ShowSpacecraft::VIEW_IDENTIFIER)->ordered();
    }

    public function testMissingTargetDoesNotCreateAReport(): void
    {
        $this->selection->setTarget(null);
        $this->events->shouldNotReceive('getByShip');
        $this->game->shouldNotReceive('setTemplateVar');

        $this->subject->handle($this->game);

        self::assertTrue($this->information->isEmpty());
    }

    public function testValidationErrorsRemainOrdinaryInformation(): void
    {
        $this->events->shouldReceive('getByShip')->once()->with($this->cube)->andReturnNull();
        $this->game->shouldNotReceive('setTemplateVar');

        $this->subject->handle($this->game);

        self::assertSame(['Hier ist kein assimilierender Borg-Traktorstrahl erreichbar'], $this->information->getInformations());
    }

    /** @return iterable<string, array{bool}> */
    public static function counterfireOutcomes(): iterable
    {
        yield 'surviving rescuer' => [false];
        yield 'destroyed rescuer' => [true];
    }

    #[DataProvider('counterfireOutcomes')]
    public function testSalvosAndCounterfireFormOneReport(bool $destroyed): void
    {
        $event = new BorgSphereEvent()->setShipId(42)->setPhase(BorgSpherePhaseEnum::ROAMING)
            ->setConfiguration(new BorgSphereConfiguration()->setSettings(['assimilation_enabled' => 1, 'tractor_rescue_chance_percent' => 25]));
        $captured = $this->ship(43, 261, $this->cube->getLocation());
        new SpacecraftAssimilated()->setSpacecraft($captured)->start($this->cube, 1000, 900);
        $this->cube->setTractoredShip($captured);
        $this->activate($this->cube, SpacecraftSystemTypeEnum::TRACTOR_BEAM);
        $this->activate($this->source, SpacecraftSystemTypeEnum::PHASER);
        $this->events->shouldReceive('getByShip')->once()->with($this->cube)->andReturn($event);
        $this->events->shouldReceive('lockEvent')->once()->with($event);
        $this->checks->shouldReceive('createInteractionChecker->setSource->setTarget->setCheckTypes->check')->once()->andReturn(true);
        $this->random->shouldReceive('rand')->once()->with(1, 100)->andReturn(100);
        $eps = $this->mock(EpsSystemData::class);
        $eps->shouldReceive('getEps')->andReturn(10);
        $eps->shouldReceive('lowerEps')->once()->with(1)->andReturnSelf();
        $eps->shouldReceive('update')->once();
        $this->sourceWrapper->shouldReceive('getEpsSystemData')->andReturn($eps);
        $this->wrappers->shouldReceive('wrapShip')->with($this->cube)->andReturn($this->cubeWrapper);
        $this->wrappers->shouldReceive('wrapShip')->with($this->source)->andReturn($this->sourceWrapper);
        $sourceParty = $this->mock(AttackingBattleParty::class);
        $attackers = $this->mock(MixedBattleParty::class);
        $targetParty = $this->mock(AttackedBattleParty::class);
        $defenders = $this->mock(MixedBattleParty::class);
        $sourceParty->shouldReceive('getActiveMembers')->with(true)->andReturn(new ArrayCollection([42 => $this->cubeWrapper]));
        $targetParty->shouldReceive('getActiveMembers')->once()->withNoArgs()->andReturn(new ArrayCollection([167 => $this->sourceWrapper]));
        $this->parties->shouldReceive('createAttackingBattleParty')->once()->with($this->cubeWrapper, false)->andReturn($sourceParty);
        $this->parties->shouldReceive('createMixedBattleParty')->twice()->andReturn($attackers, $defenders);
        $this->parties->shouldReceive('createAttackedBattleParty')->once()->with($this->sourceWrapper)->andReturn($targetParty);
        $messages = new MessageFactory()->createMessageCollection();
        $messages->addInformation('Der Kubus feuert auf das Rettungsschiff');
        $messages->addInformation('- Schildschaden: 40');
        $this->cycle->shouldReceive('cycle')->once()->with($attackers, $defenders, SpacecraftAttackCauseEnum::BORG_TRACTOR_RESCUE_RESPONSE)->andReturnUsing(function () use ($messages, $destroyed): mixed {
            $this->source->getCondition()->setIsDestroyed($destroyed);
            return $messages;
        });
        $expected = ['Die Rettungsschiff beschießt den Traktoremitter des Schiffs Kubus mit rotierend modulierten Energiewaffenfrequenzen', 'Die modulierte Salve wird von den Schilden abgefangen. Der Traktorstrahl bleibt aktiv', 'Der Kubus feuert auf das Rettungsschiff', '- Schildschaden: 40'];
        if ($destroyed) {
            $this->game->shouldReceive('setView')->once()->with(ModuleEnum::SHIP)->ordered();
            $this->game->shouldNotReceive('setTemplateVar');
        } else {
            $this->game->shouldReceive('setTemplateVar')->once()->with('FIGHT_RESULTS', $expected);
        }

        $this->subject->handle($this->game);

        self::assertSame($destroyed ? $expected : ['Rettungsangriff durchgeführt'], $this->information->getInformations());
        self::assertTrue($this->subject->performSessionCheck());
    }

    private function ship(int $id, int $userId, Location $location): Ship
    {
        $user = new User();
        new ReflectionProperty(User::class, 'id')->setValue($user, $userId);
        $ship = new Ship();
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, $id);
        $ship->setUser($user)->setLocation($location)->setBuildplan(new SpacecraftBuildplan()->setCrew(0))
            ->setCondition(new SpacecraftCondition($ship))->setRump(new SpacecraftRump()->setCategoryId(SpacecraftRumpCategoryEnum::WARSHIP)->setPhaserVolleys(1));
        return $ship;
    }

    private function wrapper(Ship $ship): ShipWrapperInterface&MockInterface
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($ship);
        $wrapper->shouldReceive('canFire')->andReturn(true);
        return $wrapper;
    }

    private function activate(Ship $ship, SpacecraftSystemTypeEnum $type): void
    {
        $ship->getSystems()->set($type->value, new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type)->setMode(SpacecraftSystemModeEnum::MODE_ON));
    }
}
