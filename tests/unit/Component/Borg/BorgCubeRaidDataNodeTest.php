<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\BorgSphereRaid;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\StuTestCase;

final class BorgCubeRaidDataNodeTest extends StuTestCase
{
    /** @return iterable<string, array{bool, int, int, bool, bool, bool}> */
    public static function raids(): iterable
    {
        yield 'cube with precisely enough room awards both rewards' => [true, 26, 5, true, true, true];
        yield 'cube node roll outside five percent awards only nanites' => [true, 26, 6, true, false, true];
        yield 'cube reserves a node slot before consuming the attempt' => [true, 25, 0, false, false, false];
        yield 'sphere preserves old capacity and never rolls a node' => [false, 25, 0, true, false, true];
        yield 'failed cube raid never rolls for a data node' => [true, 26, 0, true, false, false];
    }

    #[DataProvider('raids')]
    public function testSuccessfulRaidsReserveAndAwardTheAdditionalNodeOnlyForTheCube(bool $cube, int $capacity, int $nodeRoll, bool $expectedAttempt, bool $expectedNode, bool $success): void
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(261);
        $ship = $this->mock(Ship::class)->makePartial();
        $ship->__construct();
        $ship->setUser($user)->setName('Vor’cha')->setRump(new SpacecraftRump()->setStorage($capacity))->setCondition(new SpacecraftCondition($ship));
        $ship->shouldReceive('getExcessCrewCount')->times($expectedAttempt && !$success ? 2 : 1)->andReturn(1);
        $ship->getCrewAssignments()->add(new CrewAssignment()->setSlot(CrewTypeEnum::CREWMAN));
        $target = new Ship();
        $target->setCondition(new SpacecraftCondition($target)->setDisabled(true));
        $configuration = new BorgSphereConfiguration()->setQuest(new NPCQuest()->setStart(0))->setEndTime(2000)->setSettings([
            'assimilation_enabled' => $cube ? 1 : 0,
            'data_node_commodity_id' => 93,
            'raid_cooldown_seconds' => 7200,
            'raid_team_size' => 1,
            'raid_reward_min' => 3,
            'raid_reward_max' => 25,
            'raid_success_disabled' => 90,
            'raid_loss_min' => 0,
            'raid_loss_max' => 0,
            'nanite_commodity_id' => 76
        ]);
        $event = new BorgSphereEvent()->setConfiguration($configuration)->setPhase(BorgSpherePhaseEnum::REGENERATING);
        $events = $this->mock(BorgSphereRepositoryInterface::class);
        $events->shouldReceive('getByShip')->once()->with($target)->andReturn($event);
        $events->shouldReceive('lockEvent')->once()->with($event);
        $raid = new BorgSphereRaid()->setUser($user);
        $events->shouldReceive('getRaid')->once()->with($user, true)->andReturn($raid);
        $checks = $this->mock(InteractionCheckerBuilderFactoryInterface::class);
        $checks->shouldReceive('createInteractionChecker->setSource->setTarget->setCheckTypes->check')->once()->andReturnTrue();
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $nanite = $this->commodity(76, 'Borg-Naniten');
        $commodities->shouldReceive('find')->once()->with(76)->andReturn($nanite);
        $node = $this->commodity(93, 'Borg-Datenknoten');
        if ($cube) {
            $commodities->shouldReceive('find')->once()->with(93)->andReturn($node);
        } else {
            $commodities->shouldNotReceive('find')->with(93);
        }
        $storage = $this->mock(StorageManagerInterface::class);
        $random = $this->mock(StuRandom::class);
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->andReturn(1000);
        if ($expectedAttempt) {
            $events->shouldReceive('saveRaid')->once()->with($raid);
            $random->shouldReceive('rand')->with(1, 100)->once()->ordered()->andReturn($success ? 1 : 100);
            if ($success) {
                $random->shouldReceive('rand')->with(3, 25)->once()->ordered()->andReturn(25);
                $storage->shouldReceive('upperStorage')->once()->with($ship, $nanite, 25)->andReturnUsing(static function () use ($ship, $nanite): void {
                    $ship->getStorage()->set(76, new Storage()->setCommodity($nanite)->setAmount(25));
                });
            } else {
                $random->shouldReceive('rand')->with(0, 0)->once()->ordered()->andReturn(0);
                $storage->shouldNotReceive('upperStorage');
            }
            if ($cube && $success) {
                $random->shouldReceive('rand')->with(1, 100)->once()->ordered()->andReturn($nodeRoll);
            }
            $messages->shouldReceive('send')->once()->with(UserConstants::USER_NOONE, 261, Mockery::on(static fn (string $text): bool => str_contains($text, '25 Borg-Naniten') === $success && str_contains($text, '1 Borg-Datenknoten') === $expectedNode), PrivateMessageFolderTypeEnum::SPECIAL_SHIP, $ship);
        } else {
            $events->shouldNotReceive('saveRaid');
            $random->shouldNotReceive('rand');
            $storage->shouldNotReceive('upperStorage');
            $messages->shouldNotReceive('send');
        }
        if ($expectedNode) {
            $storage->shouldReceive('upperStorage')->once()->with($ship, $node, 1)->andReturnUsing(static function () use ($ship, $node): void {
                $ship->getStorage()->set(93, new Storage()->setCommodity($node)->setAmount(1));
            });
        }
        $team = new BorgSphereRaidTeam($this->mock(CrewRepositoryInterface::class), $this->mock(CrewAssignmentRepositoryInterface::class), $this->mock(SpacecraftShutdownInterface::class), $this->mock(SpacecraftMovementPublisherInterface::class), $random);
        $service = new BorgSphereRaidService($events, $team, $checks, $commodities, $storage, $messages, $random, $time);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($ship);
        $information = new InformationWrapper();

        self::assertSame($expectedAttempt, $service->collect($wrapper, $target, $information));

        self::assertSame($expectedAttempt ? 1 : 0, $raid->getAttempts());
        self::assertSame($success ? 1000 : 0, $raid->getLastAttemptAt());
        self::assertSame($success ? 25 + ($expectedNode ? 1 : 0) : 0, $ship->getStorageSum());
        self::assertLessThanOrEqual($capacity, $ship->getStorageSum());
        if (!$expectedAttempt) {
            self::assertSame(['Für die Außenmission werden 26 freie Lagerplätze benötigt'], $information->getInformations());
        }
    }

    private function commodity(int $id, string $name): Commodity
    {
        $commodity = $this->mock(Commodity::class);
        $commodity->shouldReceive('getId')->andReturn($id);
        $commodity->shouldReceive('getName')->andReturn($name);
        return $commodity;
    }
}
