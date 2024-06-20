<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\Message\MessageCollection;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class ShipAttackPreparationTest extends StuTestCase
{
    /** @var MockInterface|FightLibInterface */
    private $fightLib;

    private ShipAttackPreparationInterface $subject;

    public function setUp(): void
    {
        $this->fightLib = $this->mock(FightLibInterface::class);

        $this->subject = new ShipAttackPreparation(
            $this->fightLib
        );
    }

    public static function provideGetReadyData()
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider provideGetReadyData
     */
    public function testGetReady(bool $isOneWay): void
    {
        $attackers = $this->mock(BattlePartyInterface::class);
        $defenders = $this->mock(BattlePartyInterface::class);
        $messages = new MessageCollection();

        $attacker1 = $this->mock(ShipWrapperInterface::class);
        $attacker2 = $this->mock(ShipWrapperInterface::class);
        $defender1 = $this->mock(ShipWrapperInterface::class);
        $defender2 = $this->mock(ShipWrapperInterface::class);

        $ship1 = $this->mock(ShipInterface::class);
        $ship2 = $this->mock(ShipInterface::class);
        $ship3 = $this->mock(ShipInterface::class);
        $ship4 = $this->mock(ShipInterface::class);

        $attackers->shouldReceive('getActiveMembers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([$attacker1, $attacker2]));

        if (!$isOneWay) {
            $defenders->shouldReceive('getActiveMembers')
                ->withNoArgs()
                ->andReturn(new ArrayCollection([$defender1, $defender2]));
        }

        $attacker1->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship1);
        $attacker2->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship2);
        $ship1->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $ship2->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $this->fightLib->shouldReceive('ready')
            ->with($attacker1)
            ->once()
            ->andReturn(new InformationWrapper(['ready1']));
        $this->fightLib->shouldReceive('ready')
            ->with($attacker2)
            ->once()
            ->andReturn(new InformationWrapper(['ready2']));

        if (!$isOneWay) {

            $ship3->shouldReceive('getUser->getId')
                ->withNoArgs()
                ->once()
                ->andReturn(3);
            $ship4->shouldReceive('getUser->getId')
                ->withNoArgs()
                ->once()
                ->andReturn(4);
            $defender1->shouldReceive('get')
                ->withNoArgs()
                ->andReturn($ship3);
            $defender2->shouldReceive('get')
                ->withNoArgs()
                ->andReturn($ship4);

            $this->fightLib->shouldReceive('ready')
                ->with($defender1)
                ->once()
                ->andReturn(new InformationWrapper(['ready3']));
            $this->fightLib->shouldReceive('ready')
                ->with($defender2)
                ->once()
                ->andReturn(new InformationWrapper(['ready4']));
        }

        $this->subject->getReady($attackers, $defenders, $isOneWay, $messages);

        if ($isOneWay) {
            $this->assertEquals(
                new InformationWrapper(['ready1', 'ready2']),
                $messages->getInformationDump()
            );
        } else {
            $this->assertEquals(
                new InformationWrapper(['ready1', 'ready2', 'ready3', 'ready4']),
                $messages->getInformationDump()
            );
        }
    }
}
