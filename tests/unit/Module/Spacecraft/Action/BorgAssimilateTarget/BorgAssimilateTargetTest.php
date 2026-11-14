<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\BorgAssimilateTarget;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Borg\BorgAssimilationCapture;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SourceAndTargetWrappers;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgAssimilationRepositoryInterface;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;

final class BorgAssimilateTargetTest extends ActionControllerTestCase
{
    private BorgSphereRepositoryInterface&MockInterface $events;
    private BorgAssimilationRepositoryInterface&MockInterface $assimilations;
    private LeaveFleetInterface&MockInterface $fleets;
    private PrivateMessageSenderInterface&MockInterface $messages;
    private BorgAssimilateTarget $subject;
    private Ship $source;
    private Ship $target;
    private BorgSphereEvent $event;
    private InformationWrapper $information;
    /** @var SourceAndTargetWrappers<ShipWrapperInterface> */
    private SourceAndTargetWrappers $selection;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        request::setMockVars(['id' => 11, 'target' => 22]);
        $this->source = $this->ship(11, 19);
        $this->target = $this->ship(22, 261);
        $this->target->setName('Gewähltes Schiff');
        $this->target->setLocation($this->source->getLocation());
        $this->activate($this->source, SpacecraftSystemTypeEnum::TRACTOR_BEAM, SpacecraftSystemModeEnum::MODE_OFF);
        $this->event = new BorgSphereEvent()->setShipId(11)->setConfiguration(new BorgSphereConfiguration()
            ->setUser($this->source->getUser())->setQuest(new NPCQuest()->setStart(0))->setEndTime(2000)->setSettings(['assimilation_enabled' => 1, 'nanite_commodity_id' => 76]));
        $this->events = $this->mock(BorgSphereRepositoryInterface::class);
        $this->assimilations = $this->mock(BorgAssimilationRepositoryInterface::class);
        $this->fleets = $this->mock(LeaveFleetInterface::class);
        $this->messages = $this->mock(PrivateMessageSenderInterface::class);
        $relations = $this->mock(PlayerRelationDeterminatorInterface::class);
        $relations->shouldReceive('isFriend')->andReturn(false);
        $random = $this->mock(StuRandom::class);
        $random->shouldNotReceive('rand', 'array_rand');
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->andReturn(1000);
        $capture = new BorgAssimilationCapture($this->assimilations, $relations, $this->fleets, $this->messages, $random, $time);
        $this->selection = new SourceAndTargetWrappers($this->wrapper($this->source));
        $this->selection->setTarget($this->wrapper($this->target));
        $ships = $this->mock(ShipLoaderInterface::class);
        $ships->shouldReceive('getWrappersBySourceAndUserAndTarget')->once()->with(11, 19, 22)->andReturn($this->selection);
        $this->subject = new BorgAssimilateTarget($ships, $this->events, $capture, $time);
        $this->information = new InformationWrapper();
        $this->game->shouldReceive('setView')->once()->with(ShowSpacecraft::VIEW_IDENTIFIER);
        $this->game->shouldReceive('getUser')->once()->andReturn($this->source->getUser());
        $this->game->shouldReceive('getInfo')->once()->andReturn($this->information);
    }

    public function testTheOwnerCapturesExactlyTheSelectedShipWithoutARandomRoll(): void
    {
        $unselected = $this->ship(23, 261);
        $unselected->setLocation($this->source->getLocation());
        $this->events->shouldReceive('getByShip')->once()->with($this->source)->andReturn($this->event);
        $this->events->shouldReceive('lockEvent')->once()->with($this->event);
        $this->assimilations->shouldReceive('isAssimilateable')->twice()->with($this->target->getRump())->andReturn(true);
        $this->assimilations->shouldReceive('lock')->once()->with($this->source);
        $this->assimilations->shouldReceive('lock')->once()->with($this->target);
        $this->assimilations->shouldReceive('save')->once();
        $this->fleets->shouldReceive('leaveFleet')->once()->with($this->target)->andReturn(false);
        $this->messages->shouldReceive('send')->once();

        $this->subject->handle($this->game);

        self::assertSame($this->target, $this->source->getTractoredShip());
        self::assertTrue($this->target->isBeingAssimilated());
        self::assertFalse($unselected->isBeingAssimilated());
        self::assertSame(['Die Assimilation der Gewähltes Schiff wurde begonnen'], $this->information->getInformations());
        self::assertTrue($this->subject->performSessionCheck());
    }

    /** @return iterable<string, array{string}> */
    public static function invalidSelections(): iterable
    {
        foreach (['foreign source', 'different event owner', 'no event', 'not a cube', 'expired', 'future quest', 'finished event', 'no target', 'warped target', 'wrong category', 'not whitelisted'] as $state) {
            yield $state => [$state];
        }
    }

    #[DataProvider('invalidSelections')]
    public function testForgedOrUnavailableSelectionsCannotStartAssimilation(string $state): void
    {
        if ($state === 'foreign source') {
            $this->source->setUser($this->target->getUser());
        } elseif ($state === 'different event owner') {
            $this->event->getConfiguration()->setUser($this->target->getUser());
        } elseif ($state === 'not a cube') {
            $this->event->getConfiguration()->setSettings(['assimilation_enabled' => 0]);
        } elseif ($state === 'expired') {
            $this->event->getConfiguration()->setEndTime(900);
        } elseif ($state === 'future quest') {
            $this->event->getConfiguration()->getQuest()->setStart(1100);
        } elseif ($state === 'finished event') {
            $this->event->setPhase(BorgSpherePhaseEnum::DESTROYED);
        } elseif ($state === 'no target') {
            $this->selection->setTarget(null);
        } elseif ($state === 'warped target') {
            $this->activate($this->target, SpacecraftSystemTypeEnum::WARPDRIVE);
        } elseif ($state === 'wrong category') {
            $this->target->getRump()->setCategoryId(SpacecraftRumpCategoryEnum::FREIGHT);
        } elseif ($state === 'not whitelisted') {
            $this->assimilations->shouldReceive('isAssimilateable')->once()->with($this->target->getRump())->andReturn(false);
        }
        $this->events->shouldReceive('getByShip')->once()->with($this->source)->andReturn($state === 'no event' ? null : $this->event);
        if (!in_array($state, ['foreign source', 'different event owner', 'no event'], true)) {
            $this->events->shouldReceive('lockEvent')->once()->with($this->event);
        } else {
            $this->events->shouldNotReceive('lockEvent');
        }
        $this->assimilations->shouldNotReceive('save', 'lock');
        $this->fleets->shouldNotReceive('leaveFleet');
        $this->messages->shouldNotReceive('send');

        $this->subject->handle($this->game);

        self::assertFalse($this->target->isBeingAssimilated());
        self::assertNull($this->source->getTractoredShip());
        self::assertCount(1, $this->information->getInformations());
    }

    private function ship(int $id, int $owner): Ship
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn($owner);
        $user->shouldReceive('isVacationRequestOldEnough')->andReturn(false);
        $ship = new Ship();
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, $id);
        $ship->setUser($user)->setLocation(new StarSystemMap())->setRump(new SpacecraftRump()->setCategoryId(SpacecraftRumpCategoryEnum::CRUISER));
        $ship->setCondition(new SpacecraftCondition($ship));
        return $ship;
    }

    private function wrapper(Ship $ship): ShipWrapperInterface&MockInterface
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($ship);
        return $wrapper;
    }

    private function activate(Ship $ship, SpacecraftSystemTypeEnum $type, SpacecraftSystemModeEnum $mode = SpacecraftSystemModeEnum::MODE_ON): void
    {
        $ship->getSystems()->set($type->value, new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type)->setStatus(100)->setMode($mode));
    }
}
