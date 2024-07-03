<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Application;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use Override;
use Psr\Container\ContainerInterface;
use Stu\CliInteractorHelper;
use Stu\Component\Faction\FactionEnum;
use Stu\Component\Player\Register\LocalPlayerCreator;
use Stu\Component\Player\Register\PlayerCreatorInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\StuTestCase;

class UserCreateCommandTest extends StuTestCase
{
    /** @var MockInterface&ContainerInterface */
    private MockInterface $dic;

    private UserCreateCommand $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->dic = $this->mock(ContainerInterface::class);

        $this->subject = new UserCreateCommand(
            $this->dic
        );
    }

    public function testExecuteErrorsDueToInvalidEmailAddress(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The provided email address is invalid');

        $this->subject->execute(
            'snafu',
            'roedlbroem',
            'some-faction'
        );
    }

    public function testExecuteErrorsDueToUnknownFaction(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The provided faction is invalid');

        $app = $this->mock(Application::class);
        $interactor = $this->mock(CliInteractorHelper::class);

        $this->subject->bind($app);

        $app->shouldReceive('io')
            ->withNoArgs()
            ->once()
            ->andReturn($interactor);

        $interactor->shouldReceive('info')
            ->zeroOrMoreTimes();

        $this->subject->execute(
            'snafu',
            'roedlbroem@example.com',
            'some-faction'
        );
    }

    public function testExecuteErrorsDueToInvalidPassword(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Password does not meet requirements (6-20 alphanumerical characters)');

        $app = $this->mock(Application::class);
        $interactor = $this->mock(CliInteractorHelper::class);

        $userName = 'some-username';
        $emailAddress = 'some@example.com';
        $password = 'ö';

        $this->subject->bind($app);

        $app->shouldReceive('io')
            ->withNoArgs()
            ->once()
            ->andReturn($interactor);

        $interactor->shouldReceive('promptHidden')
            ->with(
                'Password',
                Mockery::on(function (callable $validator) use ($password): bool {
                    $validator($password);

                    return false;
                }),
                2
            );

        $interactor->shouldReceive('info')
            ->zeroOrMoreTimes();

        $this->subject->execute(
            $userName,
            $emailAddress,
            'klingon'
        );
    }

    public function testExecuteErrorsDueToUnMappableFaction(): void
    {
        $app = $this->mock(Application::class);
        $interactor = $this->mock(CliInteractorHelper::class);
        $factionRepository = $this->mock(FactionRepositoryInterface::class);
        $playerCreator = $this->mock(PlayerCreatorInterface::class);
        $faction = $this->mock(FactionInterface::class);
        $user = $this->mock(UserInterface::class);

        $userName = 'some-username';
        $emailAddress = 'some@example.com';
        $password = 'somepassword';
        $userId = 666;

        $this->subject->bind($app);

        $app->shouldReceive('io')
            ->withNoArgs()
            ->once()
            ->andReturn($interactor);

        $interactor->shouldReceive('promptHidden')
            ->with(
                'Password',
                Mockery::on(function (callable $validator) use ($password): bool {
                    $validator($password);

                    return true;
                }),
                2
            )
            ->once()
            ->andReturn($password);
        $interactor->shouldReceive('ok')
            ->with(
                sprintf(
                    'Player with ID %s has been created',
                    $userId
                ),
                true
            )
            ->once();

        $interactor->shouldReceive('info')
            ->zeroOrMoreTimes();

        $this->dic->shouldReceive('get')
            ->with(FactionRepositoryInterface::class)
            ->once()
            ->andReturn($factionRepository);
        $this->dic->shouldReceive('get')
            ->with(LocalPlayerCreator::class)
            ->once()
            ->andReturn($playerCreator);

        $factionRepository->shouldReceive('find')
            ->with(FactionEnum::FACTION_KLINGON)
            ->once()
            ->andReturn($faction);

        $playerCreator->shouldReceive('createPlayer')
            ->with(
                $userName,
                $emailAddress,
                $faction,
                $password
            )
            ->once()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->subject->execute(
            $userName,
            $emailAddress,
            'klingon'
        );
    }
}
