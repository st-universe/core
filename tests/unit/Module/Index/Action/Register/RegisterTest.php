<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Register;

use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Override;
use Stu\Component\ErrorHandling\ErrorCodeEnum;
use Stu\Component\Player\Register\Exception\RegistrationException;
use Stu\Component\Player\Register\PlayerCreatorInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Index\View\ShowFinishRegistration\ShowFinishRegistration;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\StuTestCase;

class RegisterTest extends StuTestCase
{
    private Register $subject;

    private MockInterface&RegisterRequestInterface $registerRequest;

    private MockInterface&FactionRepositoryInterface $factionRepository;

    private MockInterface&PlayerCreatorInterface $playerCreator;

    private MockInterface&ConfigInterface $config;

    private MockInterface&GameControllerInterface $game;

    private MockInterface&Faction $faction;

    #[Override]
    public function setUp(): void
    {
        $this->registerRequest = $this->mock(RegisterRequestInterface::class);
        $this->factionRepository = $this->mock(FactionRepositoryInterface::class);
        $this->playerCreator = $this->mock(PlayerCreatorInterface::class);
        $this->config = $this->mock(ConfigInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->faction = $this->mock(Faction::class);

        $this->subject = new Register(
            $this->registerRequest,
            $this->factionRepository,
            $this->playerCreator,
            $this->config
        );
    }

    public function testHandleDoNothingIfRegistrationDisabled(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->once()
            ->andReturn(false);

        $this->subject->handle($this->game);
    }

    public function testHandleDoNothingIfFactioWasNotFound(): void
    {
        $factionId = 5;

        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->andReturn(true);

        $this->registerRequest->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);

        $this->factionRepository->shouldReceive('getPlayableFactionsPlayerCount')
            ->withNoArgs()
            ->once()
            ->andReturn([]);

        $this->subject->handle($this->game);
    }

    public function testHandleDoNothingIfNoFreeFactionSlots(): void
    {
        $factionId = 5;

        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->andReturn(true);

        $this->registerRequest->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);

        $this->factionRepository->shouldReceive('getPlayableFactionsPlayerCount')
            ->withNoArgs()
            ->once()
            ->andReturn([$factionId => ['faction' => $this->faction, 'count' => 1]]);

        $this->faction->shouldReceive('getPlayerLimit')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->subject->handle($this->game);
    }

    public function testHandleDoNothingIfRegistrationExceptionOccurs(): void
    {
        $factionId = 4;
        $mobileNumber = ' 12345 ';
        $password = 'ValidPass1!';

        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->andReturn(true);

        $this->registerRequest->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);
        $this->registerRequest->shouldReceive('getLoginName')
            ->withNoArgs()
            ->once()
            ->andReturn(' LOGIN ');
        $this->registerRequest->shouldReceive('getEmailAddress')
            ->withNoArgs()
            ->once()
            ->andReturn(' EMAIL ');
        $this->registerRequest->shouldReceive('getMobileNumber')
            ->withNoArgs()
            ->once()
            ->andReturn($mobileNumber);
        $this->registerRequest->shouldReceive('getCountryCode')
            ->withNoArgs()
            ->once()
            ->andReturn('+49');
        $this->registerRequest->shouldReceive('getReferer')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->registerRequest->shouldReceive('getPassword')
            ->withNoArgs()
            ->once()
            ->andReturn($password);
        $this->registerRequest->shouldReceive('getPasswordReEntered')
            ->withNoArgs()
            ->once()
            ->andReturn($password);

        $this->factionRepository->shouldReceive('getPlayableFactionsPlayerCount')
            ->withNoArgs()
            ->once()
            ->andReturn([$factionId => ['faction' => $this->faction, 'count' => 1]]);

        $this->faction->shouldReceive('getPlayerLimit')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $this->playerCreator->shouldReceive('createWithMobileNumber')
            ->with('login', 'email', $this->faction, '+4912345', $password, null)
            ->once()
            ->andThrow(new RegistrationException(ErrorCodeEnum::LOGIN_NAME_INVALID));

        static::expectException(RegistrationException::class);
        static::expectExceptionMessage('The provided login name is invalid (invalid characters or invalid length)');

        $this->subject->handle($this->game);
    }

    public function testHandleDoNothingIfMobileNumberIsEmpty(): void
    {
        $factionId = 4;

        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->andReturn(true);
        $this->config->shouldReceive('get')
            ->with('game.registration.sms_code_verification.enabled')
            ->andReturn(true);

        $this->registerRequest->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);
        $this->registerRequest->shouldReceive('getLoginName')
            ->withNoArgs()
            ->once()
            ->andReturn(' LOGIN ');
        $this->registerRequest->shouldReceive('getEmailAddress')
            ->withNoArgs()
            ->once()
            ->andReturn(' EMAIL ');
        $this->registerRequest->shouldReceive('getMobileNumber')
            ->withNoArgs()
            ->once()
            ->andReturn('');
        $this->registerRequest->shouldReceive('getCountryCode')
            ->withNoArgs()
            ->once()
            ->andReturn('');
        $this->registerRequest->shouldReceive('getReferer')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->factionRepository->shouldReceive('getPlayableFactionsPlayerCount')
            ->withNoArgs()
            ->once()
            ->andReturn([$factionId => ['faction' => $this->faction, 'count' => 1]]);

        $this->faction->shouldReceive('getPlayerLimit')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $this->subject->handle($this->game);
    }

    public function testHandleDoNothingIfPasswordIsEmpty(): void
    {
        $factionId = 4;
        $mobileNumber = ' 12345 ';

        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->andReturn(true);

        $this->registerRequest->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);
        $this->registerRequest->shouldReceive('getLoginName')
            ->withNoArgs()
            ->once()
            ->andReturn(' LOGIN ');
        $this->registerRequest->shouldReceive('getEmailAddress')
            ->withNoArgs()
            ->once()
            ->andReturn(' EMAIL ');
        $this->registerRequest->shouldReceive('getMobileNumber')
            ->withNoArgs()
            ->once()
            ->andReturn($mobileNumber);
        $this->registerRequest->shouldReceive('getCountryCode')
            ->withNoArgs()
            ->once()
            ->andReturn('+49');
        $this->registerRequest->shouldReceive('getReferer')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->registerRequest->shouldReceive('getPassword')
            ->withNoArgs()
            ->once()
            ->andReturn('');

        $this->factionRepository->shouldReceive('getPlayableFactionsPlayerCount')
            ->withNoArgs()
            ->once()
            ->andReturn([$factionId => ['faction' => $this->faction, 'count' => 1]]);

        $this->faction->shouldReceive('getPlayerLimit')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $this->registerRequest->shouldReceive('getPasswordReEntered')
            ->withNoArgs()
            ->once()
            ->andReturn('');

        $this->subject->handle($this->game);
    }

    public function testHandleDoNothingIfPasswordIsInvalid(): void
    {
        $factionId = 4;
        $mobileNumber = ' 12345 ';
        $invalidPassword = 'weak';

        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->andReturn(true);

        $this->registerRequest->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);
        $this->registerRequest->shouldReceive('getLoginName')
            ->withNoArgs()
            ->once()
            ->andReturn(' LOGIN ');
        $this->registerRequest->shouldReceive('getEmailAddress')
            ->withNoArgs()
            ->once()
            ->andReturn(' EMAIL ');
        $this->registerRequest->shouldReceive('getMobileNumber')
            ->withNoArgs()
            ->once()
            ->andReturn($mobileNumber);
        $this->registerRequest->shouldReceive('getCountryCode')
            ->withNoArgs()
            ->once()
            ->andReturn('+49');
        $this->registerRequest->shouldReceive('getReferer')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->registerRequest->shouldReceive('getPassword')
            ->withNoArgs()
            ->once()
            ->andReturn($invalidPassword);

        $this->factionRepository->shouldReceive('getPlayableFactionsPlayerCount')
            ->withNoArgs()
            ->once()
            ->andReturn([$factionId => ['faction' => $this->faction, 'count' => 1]]);

        $this->faction->shouldReceive('getPlayerLimit')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $this->registerRequest->shouldReceive('getPasswordReEntered')
            ->withNoArgs()
            ->once()
            ->andReturn($invalidPassword);


        $this->subject->handle($this->game);
    }

    public function testHandleDoNothingIfPasswordsDoNotMatch(): void
    {
        $factionId = 4;
        $mobileNumber = ' 12345 ';
        $password = 'ValidPass1!';
        $differentPassword = 'DifferentPass1!';

        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->andReturn(true);

        $this->registerRequest->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);
        $this->registerRequest->shouldReceive('getLoginName')
            ->withNoArgs()
            ->once()
            ->andReturn(' LOGIN ');
        $this->registerRequest->shouldReceive('getEmailAddress')
            ->withNoArgs()
            ->once()
            ->andReturn(' EMAIL ');
        $this->registerRequest->shouldReceive('getMobileNumber')
            ->withNoArgs()
            ->once()
            ->andReturn($mobileNumber);
        $this->registerRequest->shouldReceive('getCountryCode')
            ->withNoArgs()
            ->once()
            ->andReturn('+49');
        $this->registerRequest->shouldReceive('getReferer')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->registerRequest->shouldReceive('getPassword')
            ->withNoArgs()
            ->once()
            ->andReturn($password);
        $this->registerRequest->shouldReceive('getPasswordReEntered')
            ->withNoArgs()
            ->once()
            ->andReturn($differentPassword);

        $this->factionRepository->shouldReceive('getPlayableFactionsPlayerCount')
            ->withNoArgs()
            ->once()
            ->andReturn([$factionId => ['faction' => $this->faction, 'count' => 1]]);

        $this->faction->shouldReceive('getPlayerLimit')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $this->subject->handle($this->game);
    }

    public function testHandleShowFinishRegistrationIfSmsRegistrationSuccessful(): void
    {
        $factionId = 123;
        $mobileNumber = ' 12345';
        $password = 'ValidPass1!';

        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->andReturn(true);

        $this->registerRequest->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);
        $this->registerRequest->shouldReceive('getLoginName')
            ->withNoArgs()
            ->once()
            ->andReturn(' LOGIN ');
        $this->registerRequest->shouldReceive('getEmailAddress')
            ->withNoArgs()
            ->once()
            ->andReturn(' EMAIL ');
        $this->registerRequest->shouldReceive('getMobileNumber')
            ->withNoArgs()
            ->once()
            ->andReturn($mobileNumber);
        $this->registerRequest->shouldReceive('getCountryCode')
            ->withNoArgs()
            ->once()
            ->andReturn('+49');
        $this->registerRequest->shouldReceive('getReferer')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->registerRequest->shouldReceive('getPassword')
            ->withNoArgs()
            ->once()
            ->andReturn($password);
        $this->registerRequest->shouldReceive('getPasswordReEntered')
            ->withNoArgs()
            ->once()
            ->andReturn($password);

        $this->factionRepository->shouldReceive('getPlayableFactionsPlayerCount')
            ->withNoArgs()
            ->once()
            ->andReturn([$factionId => ['faction' => $this->faction, 'count' => 1]]);

        $this->faction->shouldReceive('getPlayerLimit')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $this->game->shouldReceive('setView')
            ->with(ShowFinishRegistration::VIEW_IDENTIFIER)
            ->once();

        $this->playerCreator->shouldReceive('createWithMobileNumber')
            ->with('login', 'email', $this->faction, '+4912345', $password, null)
            ->once();
        $this->subject->handle($this->game);
    }

    public function testPerformSessionCheckReturnsFalse(): void
    {
        static::assertFalse(
            $this->subject->performSessionCheck()
        );
    }
}
