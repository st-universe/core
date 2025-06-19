<?php

declare(strict_types=1);

namespace Stu\Module\Index\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Orm\Entity\FactionInterface;
use Stu\StuTestCase;

class FactionItemTest extends StuTestCase
{
    private MockInterface&FactionInterface  $faction;

    private int $currentPlayerCount = 666;

    private FactionItem $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->faction = $this->mock(FactionInterface::class);

        $this->subject = new FactionItem(
            $this->faction,
            $this->currentPlayerCount
        );
    }

    public function testGetPlayerCountReturnsValue(): void
    {
        static::assertSame(
            $this->currentPlayerCount,
            $this->subject->getPlayerCount()
        );
    }

    public function testHasFreePlayerSlotsReturnsTrueIfPlayerLimitIsZero(): void
    {
        $this->faction->shouldReceive('getPlayerLimit')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        static::assertTrue(
            $this->subject->hasFreePlayerSlots()
        );
    }

    public function testHasFreePlayerSlotsReturnsTrueIfPlayerLimitIsAboveCurrentCount(): void
    {
        $this->faction->shouldReceive('getPlayerLimit')
            ->withNoArgs()
            ->once()
            ->andReturn($this->currentPlayerCount + 1);

        static::assertTrue(
            $this->subject->hasFreePlayerSlots()
        );
    }

    public function testHasFreePlayerSlotsReturnsFalseIfPlayerLimitCount(): void
    {
        $this->faction->shouldReceive('getPlayerLimit')
            ->withNoArgs()
            ->once()
            ->andReturn($this->currentPlayerCount);

        static::assertFalse(
            $this->subject->hasFreePlayerSlots()
        );
    }

    public function testGetColorReturnsFactionColor(): void
    {
        $color = 'redblacksomthing';

        $this->faction->shouldReceive('getDarkerColor')
            ->withNoArgs()
            ->once()
            ->andReturn($color);

        static::assertSame(
            $color,
            $this->subject->getColor()
        );
    }

    public function testGetIdReturnsValue(): void
    {
        $value = 666;

        $this->faction->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->subject->getId()
        );
    }

    public function testGetNameReturnsValue(): void
    {
        $value = 'some-name';

        $this->faction->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->subject->getName()
        );
    }

    public function testGetPlayerLimitReturnsValue(): void
    {
        $value = 666;

        $this->faction->shouldReceive('getPlayerLimit')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->subject->getPlayerLimit()
        );
    }

    public function testGetDescriptionReturnsValue(): void
    {
        $value = 'some-description';

        $this->faction->shouldReceive('getDescription')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->subject->getDescription()
        );
    }
}
