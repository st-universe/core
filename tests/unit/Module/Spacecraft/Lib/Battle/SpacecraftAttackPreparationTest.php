<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class SpacecraftAttackPreparationTest extends StuTestCase
{
    private MockInterface&FightLibInterface $fightLib;
    private MockInterface&MessageFactoryInterface $messageFactory;

    private SpacecraftAttackPreparationInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->fightLib = $this->mock(FightLibInterface::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        $this->subject = new SpacecraftAttackPreparation(
            $this->fightLib,
            $this->messageFactory
        );
    }

    public static function provideGetReadyData(): array
    {
        return [
            [false],
            [true],
        ];
    }

    #[DataProvider('provideGetReadyData')]
    public function testGetReady(bool $isOneWay): void
    {
        $attackers = $this->mock(BattlePartyInterface::class);
        $defenders = $this->mock(BattlePartyInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $attacker1 = $this->mock(ShipWrapperInterface::class);
        $attacker2 = $this->mock(ShipWrapperInterface::class);
        $defender1 = $this->mock(ShipWrapperInterface::class);
        $defender2 = $this->mock(ShipWrapperInterface::class);

        $ship1 = $this->mock(Ship::class);
        $ship2 = $this->mock(Ship::class);
        $ship3 = $this->mock(Ship::class);
        $ship4 = $this->mock(Ship::class);

        $message1 = $this->mock(MessageInterface::class);
        $message2 = $this->mock(MessageInterface::class);
        $message3 = $this->mock(MessageInterface::class);
        $message4 = $this->mock(MessageInterface::class);

        $attackers->shouldReceive('getActiveMembers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([$attacker1, $attacker2]));

        if (!$isOneWay) {
            $defenders->shouldReceive('getActiveMembers')
                ->withNoArgs()
                ->andReturn(new ArrayCollection([$defender1, $defender2]));
            $defenders->shouldReceive('isActive')
                ->withNoArgs()
                ->once()
                ->andReturn(false);
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

        $messages->shouldReceive('add')
            ->with($message1)
            ->once();
        $messages->shouldReceive('add')
            ->with($message2)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->with(1)
            ->once()
            ->andReturn($message1);
        $this->messageFactory->shouldReceive('createMessage')
            ->with(2)
            ->once()
            ->andReturn($message2);

        $this->fightLib->shouldReceive('ready')
            ->with($attacker1, true, $message1)
            ->once();
        $this->fightLib->shouldReceive('ready')
            ->with($attacker2, true, $message2)
            ->once();

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

            $messages->shouldReceive('add')
                ->with($message3)
                ->once();
            $messages->shouldReceive('add')
                ->with($message4)
                ->once();

            $this->messageFactory->shouldReceive('createMessage')
                ->with(3)
                ->once()
                ->andReturn($message3);
            $this->messageFactory->shouldReceive('createMessage')
                ->with(4)
                ->once()
                ->andReturn($message4);

            $this->fightLib->shouldReceive('ready')
                ->with($defender1, false, $message3)
                ->once();
            $this->fightLib->shouldReceive('ready')
                ->with($defender2, false, $message4)
                ->once();
        }

        $this->subject->getReady($attackers, $defenders, $isOneWay, $messages);
    }
}
