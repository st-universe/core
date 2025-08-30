<?php

declare(strict_types=1);

namespace Stu\Lib;

use Mockery\MockInterface;
use Override;
use request;
use RuntimeException;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Colony\PlanetFieldHostProvider;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Colony\PlanetFieldHostTypeEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class PlanetFieldHostProviderTest extends StuTestCase
{
    private MockInterface&ColonySandboxRepositoryInterface $colonySandboxRepository;
    private MockInterface&PlanetFieldRepositoryInterface $planetFieldRepository;
    private MockInterface&ColonyLoaderInterface $colonyLoader;

    private PlanetFieldHostProviderInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->colonySandboxRepository = $this->mock(ColonySandboxRepositoryInterface::class);
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->colonyLoader = $this->mock(ColonyLoaderInterface::class);

        $this->subject = new PlanetFieldHostProvider(
            $this->colonySandboxRepository,
            $this->planetFieldRepository,
            $this->colonyLoader
        );
    }

    public function testLoadHostViaRequestParametersExpectExceptionIfIdIsMissing(): void
    {
        static::expectExceptionMessage('request param "id" is missing');
        static::expectException(RuntimeException::class);

        $user = $this->mock(User::class);

        $this->subject->loadHostViaRequestParameters($user);
    }

    public function testLoadHostViaRequestParametersExpectExceptionIfHosttypeIsMissing(): void
    {
        static::expectExceptionMessage('request param "hosttype" is missing');
        static::expectException(RuntimeException::class);

        $user = $this->mock(User::class);

        request::setMockVars(['id' => 42]);

        $this->subject->loadHostViaRequestParameters($user);
    }

    public function testLoadHostViaRequestParametersExpectColony(): void
    {
        $user = $this->mock(User::class);
        $colony = $this->mock(Colony::class);

        request::setMockVars(['id' => 42, 'hosttype' => PlanetFieldHostTypeEnum::COLONY->value]);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $this->colonyLoader->shouldReceive('loadWithOwnerValidation')
            ->with(42, 666, true)
            ->once()
            ->andReturn($colony);

        $result = $this->subject->loadHostViaRequestParameters($user);

        $this->assertEquals($colony, $result);
    }

    public function testLoadHostViaRequestParametersExpectExceptionWhenSandboxNonExistent(): void
    {
        static::expectExceptionMessage('sandbox with following id does not exist: 42');
        static::expectException(RuntimeException::class);

        $user = $this->mock(User::class);

        request::setMockVars(['id' => 42, 'hosttype' => PlanetFieldHostTypeEnum::SANDBOX->value]);

        $this->colonySandboxRepository->shouldReceive('find')
            ->with(42)
            ->once()
            ->andReturn(null);

        $this->subject->loadHostViaRequestParameters($user);
    }

    public function testLoadHostViaRequestParametersExpectExceptionWhenSandboxOfOtherUser(): void
    {
        static::expectExceptionMessage('sandbox does belong to other user');
        static::expectException(SanityCheckException::class);

        $user = $this->mock(User::class);
        $sandbox = $this->mock(ColonySandbox::class);

        request::setMockVars(['id' => 42, 'hosttype' => PlanetFieldHostTypeEnum::SANDBOX->value]);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(11111);

        $sandbox->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(77777);

        $this->colonySandboxRepository->shouldReceive('find')
            ->with(42)
            ->once()
            ->andReturn($sandbox);

        $this->subject->loadHostViaRequestParameters($user);
    }

    public function testLoadHostViaRequestParametersExpectSandbox(): void
    {
        $user = $this->mock(User::class);
        $sandbox = $this->mock(ColonySandbox::class);

        request::setMockVars(['id' => 42, 'hosttype' => PlanetFieldHostTypeEnum::SANDBOX->value]);

        $sandbox->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(55555);

        $this->colonySandboxRepository->shouldReceive('find')
            ->with(42)
            ->once()
            ->andReturn($sandbox);

        $result = $this->subject->loadHostViaRequestParameters($user);

        $this->assertEquals($sandbox, $result);
    }
}
