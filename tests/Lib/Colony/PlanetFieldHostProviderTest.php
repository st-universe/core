<?php

declare(strict_types=1);

namespace Stu\Lib;

use Mockery\MockInterface;
use request;
use RuntimeException;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Colony\PlanetFieldHostProvider;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Colony\PlanetFieldHostTypeEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class PlanetFieldHostProviderTest extends StuTestCase
{
    /** @var MockInterface|ColonySandboxRepositoryInterface */
    private MockInterface $colonySandboxRepository;

    /** @var MockInterface|PlanetFieldRepositoryInterface */
    private MockInterface $planetFieldRepository;

    /** @var MockInterface|ColonyLoaderInterface */
    private MockInterface $colonyLoader;

    private PlanetFieldHostProviderInterface $subject;

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

        $user = $this->mock(UserInterface::class);

        $this->subject->loadHostViaRequestParameters($user);
    }

    public function testLoadHostViaRequestParametersExpectExceptionIfHosttypeIsMissing(): void
    {
        static::expectExceptionMessage('request param "hosttype" is missing');
        static::expectException(RuntimeException::class);

        $user = $this->mock(UserInterface::class);

        request::setMockVars(['id' => 42]);

        $this->subject->loadHostViaRequestParameters($user);
    }

    public function testLoadHostViaRequestParametersExpectColony(): void
    {
        $user = $this->mock(UserInterface::class);
        $colony = $this->mock(ColonyInterface::class);

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

        $user = $this->mock(UserInterface::class);

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

        $user = $this->mock(UserInterface::class);
        $sandbox = $this->mock(ColonySandboxInterface::class);

        request::setMockVars(['id' => 42, 'hosttype' => PlanetFieldHostTypeEnum::SANDBOX->value]);

        $sandbox->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(UserInterface::class));

        $this->colonySandboxRepository->shouldReceive('find')
            ->with(42)
            ->once()
            ->andReturn($sandbox);

        $this->subject->loadHostViaRequestParameters($user);
    }

    public function testLoadHostViaRequestParametersExpectSandbox(): void
    {
        $user = $this->mock(UserInterface::class);
        $sandbox = $this->mock(ColonySandboxInterface::class);

        request::setMockVars(['id' => 42, 'hosttype' => PlanetFieldHostTypeEnum::SANDBOX->value]);

        $sandbox->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->colonySandboxRepository->shouldReceive('find')
            ->with(42)
            ->once()
            ->andReturn($sandbox);

        $result = $this->subject->loadHostViaRequestParameters($user);

        $this->assertEquals($sandbox, $result);
    }
}
