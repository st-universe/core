<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Stu\Component\Player\Register\Exception\LoginNameInvalidException;
use Stu\Component\Player\Register\PlayerCreatorInterface;
use Stu\Module\Index\Action\Register\Register;
use Stu\Module\Index\Action\Register\RegisterRequestInterface;
use Stu\Module\Index\View\ShowFinishRegistration\ShowFinishRegistration;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\StuTestCase;

class RegisterTest extends StuTestCase
{
    private Register $register;

    //Mocks
    private MockInterface $requestMock;
    private MockInterface $factionRepositoryMock;
    private MockInterface $playerCreatorMock;
    private MockInterface $configMock;
    private MockInterface $gameMock;
    private MockInterface $factionMock;

    public function setUp(): void
    {
        $this->requestMock = $this->mock(RegisterRequestInterface::class);
        $this->factionRepositoryMock = $this->mock(FactionRepositoryInterface::class);
        $this->playerCreatorMock = $this->mock(PlayerCreatorInterface::class);
        $this->configMock = $this->mock(ConfigInterface::class);
        $this->gameMock = $this->mock(GameControllerInterface::class);
        $this->factionMock = $this->mock(FactionInterface::class);

        $this->register = new Register(
            $this->requestMock,
            $this->factionRepositoryMock,
            $this->playerCreatorMock,
            $this->configMock
        );
    }

    public function testHandleDoNothingIfRegistrationDisabled(): void
    {
        $this->configMock->shouldReceive('get')
            ->with('game.registration.enabled')
            ->andReturn(false);

        $this->register->handle($this->gameMock);

        $this->gameMock->shouldNotHaveBeenCalled();
    }

    public function testHandleDoNothingIfNoFreeFactionSlots(): void
    {
        $this->configMock->shouldReceive('get')
            ->with('game.registration.enabled')
            ->andReturn(true);

        //request
        $this->requestMock->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        //faction
        $this->factionRepositoryMock->shouldReceive('getByChooseable')
            ->with(true)
            ->once()
            ->andReturn([$this->factionMock]);
        $this->factionMock->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $this->factionMock->shouldReceive('hasFreePlayerSlots')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->register->handle($this->gameMock);

        $this->gameMock->shouldNotHaveBeenCalled();
    }

    public function testHandleDoNothingIfRegistrationExceptionOccurs(): void
    {
        //config
        $this->configMock->shouldReceive('get')
            ->with('game.registration.enabled')
            ->andReturn(true);
        $this->configMock->shouldReceive('get')
            ->with('game.registration.sms_code_verification.enabled')
            ->andReturn(true);

        //request
        $this->requestMock->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $this->requestMock->shouldReceive('getLoginName')
            ->withNoArgs()
            ->once()
            ->andReturn(' LOGIN ');
        $this->requestMock->shouldReceive('getEmailAddress')
            ->withNoArgs()
            ->once()
            ->andReturn(' EMAIL ');
        $this->requestMock->shouldReceive('getMobileNumber')
            ->withNoArgs()
            ->once()
            ->andReturn(' 12345 ');

        //faction
        $this->factionRepositoryMock->shouldReceive('getByChooseable')
            ->with(true)
            ->once()
            ->andReturn([$this->factionMock]);
        $this->factionMock->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $this->factionMock->shouldReceive('hasFreePlayerSlots')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        //player creator
        $this->playerCreatorMock->shouldReceive('createWithMobileNumber')
            ->with('login', 'email', $this->factionMock, '12345')
            ->once()
            ->andThrow(new LoginNameInvalidException());

        $this->register->handle($this->gameMock);

        $this->gameMock->shouldNotHaveBeenCalled();
    }

    public function testHandleShowFinishRegistrationIfSmsRegistrationSuccessful(): void
    {
        //config
        $this->configMock->shouldReceive('get')
            ->with('game.registration.enabled')
            ->andReturn(true);
        $this->configMock->shouldReceive('get')
            ->with('game.registration.sms_code_verification.enabled')
            ->andReturn(true);

        //request
        $this->requestMock->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $this->requestMock->shouldReceive('getLoginName')
            ->withNoArgs()
            ->once()
            ->andReturn(' LOGIN ');
        $this->requestMock->shouldReceive('getEmailAddress')
            ->withNoArgs()
            ->once()
            ->andReturn(' EMAIL ');
        $this->requestMock->shouldReceive('getMobileNumber')
            ->withNoArgs()
            ->once()
            ->andReturn(' 12345 ');

        //faction
        $this->factionRepositoryMock->shouldReceive('getByChooseable')
            ->with(true)
            ->once()
            ->andReturn([$this->factionMock]);
        $this->factionMock->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $this->factionMock->shouldReceive('hasFreePlayerSlots')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        //game
        $this->gameMock->shouldReceive('setView')
            ->with(ShowFinishRegistration::VIEW_IDENTIFIER)
            ->once();

        //player creator
        $this->playerCreatorMock->shouldReceive('createWithMobileNumber')
            ->with('login', 'email', $this->factionMock, '12345')
            ->once();

        $this->register->handle($this->gameMock);
    }

    public function testHandleShowFinishRegistrationIfTokenRegistrationSuccessful(): void
    {
        //config
        $this->configMock->shouldReceive('get')
            ->with('game.registration.enabled')
            ->andReturn(true);
        $this->configMock->shouldReceive('get')
            ->with('game.registration.sms_code_verification.enabled')
            ->andReturn(false);

        //request
        $this->requestMock->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $this->requestMock->shouldReceive('getLoginName')
            ->withNoArgs()
            ->once()
            ->andReturn(' LOGIN ');
        $this->requestMock->shouldReceive('getEmailAddress')
            ->withNoArgs()
            ->once()
            ->andReturn(' EMAIL ');
        $this->requestMock->shouldReceive('getToken')
            ->withNoArgs()
            ->once()
            ->andReturn('token');

        //faction
        $this->factionRepositoryMock->shouldReceive('getByChooseable')
            ->with(true)
            ->once()
            ->andReturn([$this->factionMock]);
        $this->factionMock->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $this->factionMock->shouldReceive('hasFreePlayerSlots')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        //game
        $this->gameMock->shouldReceive('setView')
            ->with(ShowFinishRegistration::VIEW_IDENTIFIER)
            ->once();

        //player creator
        $this->playerCreatorMock->shouldReceive('createViaToken')
            ->with('login', 'email', $this->factionMock, 'token')
            ->once();

        $this->register->handle($this->gameMock);
    }
}
