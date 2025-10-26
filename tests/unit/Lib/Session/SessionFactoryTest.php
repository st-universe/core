<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use DateTime;
use Mockery;
use Mockery\MockInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\SessionString;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\StuTestCase;

class SessionFactoryTest extends StuTestCase
{
    private MockInterface&SessionStringRepositoryInterface $sessionStringRepository;
    private MockInterface&StuTime $stuTime;

    private SessionStringFactoryInterface $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->sessionStringRepository = $this->mock(SessionStringRepositoryInterface::class);
        $this->stuTime = $this->mock(StuTime::class);

        $this->subject = new SessionStringFactory(
            $this->sessionStringRepository,
            $this->stuTime
        );
    }

    public function testCreateSessionString(): void
    {
        $user = $this->mock(User::class);
        $sessionString = $this->mock(SessionString::class);
        $dateTime = $this->mock(DateTime::class);

        $this->sessionStringRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($sessionString);
        $this->sessionStringRepository->shouldReceive('save')
            ->with($sessionString)
            ->once();

        $this->stuTime->shouldReceive('dateTime')
            ->withNoArgs()
            ->once()
            ->andReturn($dateTime);

        $sessionString->shouldReceive('setUser')
            ->with($user)
            ->once()
            ->andReturnSelf();
        $sessionString->shouldReceive('setDate')
            ->with($dateTime)
            ->once()
            ->andReturnSelf();

        $string = '';
        $sessionString->shouldReceive('setSessionString')
            ->with(Mockery::on(function ($param) use (&$string): bool {
                $string = $param;
                return true;
            }))
            ->once()
            ->andReturnSelf();

        $result = $this->subject->createSessionString($user);

        $this->assertEquals($string, $result);
    }
}
