<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Mockery\MockInterface;
use RuntimeException;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class ColonyCreationTest extends StuTestCase
{
    private MockInterface&ColonyRepositoryInterface $colonyRepository;

    private MockInterface&UserRepositoryInterface $userRepository;

    private MockInterface&StuRandom $stuRandom;

    private ColonyCreationInterface $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);

        $this->subject = new ColonyCreation(
            $this->colonyRepository,
            $this->userRepository,
            $this->stuRandom
        );
    }

    public function testCreateExpectExceptionWhenNoColonyOnSystemMap(): void
    {
        static::expectExceptionMessage('colony class can not be null');
        static::expectException(RuntimeException::class);

        $systemMap = $this->mock(StarSystemMap::class);

        $systemMap->shouldReceive('getFieldType->getColonyClass')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->create($systemMap, 'foo');
    }

    public function testCreateExpectCreationOfNewColony(): void
    {
        $systemMap = $this->mock(StarSystemMap::class);
        $colonyClass = $this->mock(ColonyClass::class);
        $colony = $this->mock(Colony::class);
        $user = $this->mock(User::class);

        $systemMap->shouldReceive('getFieldType->getColonyClass')
            ->withNoArgs()
            ->once()
            ->andReturn($colonyClass);

        $colonyClass->shouldReceive('getMinRotation')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $colonyClass->shouldReceive('getMaxRotation')
            ->withNoArgs()
            ->once()
            ->andReturn(55);

        $this->stuRandom->shouldReceive('rand')
            ->with(5, 55, true)
            ->once()
            ->andReturn(42);

        $this->colonyRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($colony);
        $this->colonyRepository->shouldReceive('save')
            ->with($colony)
            ->once();

        $this->userRepository->shouldReceive('getFallbackUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $colony->shouldReceive('setColonyClass')
            ->with($colonyClass)
            ->once();
        $colony->shouldReceive('setUser')
            ->with($user)
            ->once();
        $colony->shouldReceive('setStarsystemMap')
            ->with($systemMap)
            ->once();
        $colony->shouldReceive('setPlanetName')
            ->with('foo')
            ->once();
        $colony->shouldReceive('setRotationFactor')
            ->with(42)
            ->once();

        $result = $this->subject->create($systemMap, 'foo');

        $this->assertSame($colony, $result);
    }
}
