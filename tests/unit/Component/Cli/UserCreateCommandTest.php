<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Application;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use Stu\CliInteractorHelper;
use Stu\Component\Faction\FactionEnum;
use Stu\Component\Player\Register\LocalPlayerCreator;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\StuTestCase;

class UserCreateCommandTest extends StuTestCase
{
    private MockInterface&FactionRepositoryInterface $factionRepository;

    private MockInterface&LocalPlayerCreator $playerCreator;

    private UserCreateCommand $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->factionRepository = $this->mock(FactionRepositoryInterface::class);
        $this->playerCreator = $this->mock(LocalPlayerCreator::class);

        $this->subject = new UserCreateCommand(
            $this->playerCreator,
            $this->factionRepository
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
        static::expectExceptionMessage('no faction defined for name some-faction');

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
        $faction = $this->mock(Faction::class);
        $user = $this->mock(User::class);

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

        $this->factionRepository->shouldReceive('find')
            ->with(FactionEnum::FACTION_KLINGON->value)
            ->once()
            ->andReturn($faction);

        $this->playerCreator->shouldReceive('createPlayer')
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
