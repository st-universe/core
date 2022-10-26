<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Common\Register;

use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Stu\Component\ErrorHandling\ErrorCodeEnum;
use Stu\Component\Player\Register\Exception\LoginNameInvalidException;
use Stu\Component\Player\Register\PlayerCreatorInterface;
use Stu\Module\Api\Middleware\Request\JsonSchemaRequestInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\StuApiV1TestCase;

class RegisterTest extends StuApiV1TestCase
{

    /**
     * @var null|MockInterface|JsonSchemaRequestInterface
     */
    private $jsonSchemaRequest;

    /**
     * @var null|MockInterface|PlayerCreatorInterface
     */
    private $playerCreator;

    /**
     * @var null|MockInterface|FactionRepositoryInterface
     */
    private $factionRepository;

    /**
     * @var null|MockInterface|ConfigInterface
     */
    private $config;

    public function setUp(): void
    {
        $this->jsonSchemaRequest = $this->mock(JsonSchemaRequestInterface::class);
        $this->playerCreator = $this->mock(PlayerCreatorInterface::class);
        $this->factionRepository = $this->mock(FactionRepositoryInterface::class);
        $this->config = $this->mock(ConfigInterface::class);

        $this->setUpApiHandler(
            new Register(
                $this->jsonSchemaRequest,
                $this->playerCreator,
                $this->factionRepository,
                $this->config
            )
        );
    }

    public function testActionThrowsErrorIgRegistrationIsDisabled(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->once()
            ->andReturnFalse();

        $this->response->shouldReceive('withError')
            ->with(
                ErrorCodeEnum::REGISTRATION_NOT_PERMITTED,
                'The registration of new player is disabled'
            )
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }

    public function testActionThrowsErrorOnInvalidFaction(): void
    {
        $factionId = 666;

        $faction = $this->mock(FactionInterface::class);

        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->once()
            ->andReturnTrue();

        $faction->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->jsonSchemaRequest->shouldReceive('getData')
            ->with($this->handler)
            ->once()
            ->andReturn((object) ['factionId' => $factionId]);

        $this->factionRepository->shouldReceive('getByChooseable')
            ->with(true)
            ->once()
            ->andReturn([$faction]);

        $this->response->shouldReceive('withError')
            ->with(
                ErrorCodeEnum::INVALID_FACTION,
                'No suitable faction transmitted'
            )
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }

    public function testActionThrowsErrorIfFactionIsNotUseable(): void
    {
        $factionId = 666;

        $faction = $this->mock(FactionInterface::class);

        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->once()
            ->andReturnTrue();

        $faction->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);
        $faction->shouldReceive('hasFreePlayerSlots')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->jsonSchemaRequest->shouldReceive('getData')
            ->with($this->handler)
            ->once()
            ->andReturn((object) ['factionId' => $factionId]);

        $this->factionRepository->shouldReceive('getByChooseable')
            ->with(true)
            ->once()
            ->andReturn([$faction]);

        $this->response->shouldReceive('withError')
            ->with(
                ErrorCodeEnum::INVALID_FACTION,
                'No suitable faction transmitted'
            )
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }

    public function testActionThrowsErrorOnCreationError(): void
    {
        $factionId = 666;
        $loginName = 'some-name';
        $emailAddress = 'some-email-address';
        $token = 'some-token';

        $faction = $this->mock(FactionInterface::class);

        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->once()
            ->andReturnTrue();

        $faction->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);
        $faction->shouldReceive('hasFreePlayerSlots')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->jsonSchemaRequest->shouldReceive('getData')
            ->with($this->handler)
            ->once()
            ->andReturn((object) [
                'factionId' => $factionId,
                'loginName' => $loginName,
                'emailAddress' => $emailAddress,
                'token' => $token
            ]);

        $this->factionRepository->shouldReceive('getByChooseable')
            ->with(true)
            ->once()
            ->andReturn([$faction]);

        $this->playerCreator->shouldReceive('createViaToken')
            ->with($loginName, $emailAddress, $faction, $token)
            ->once()
            ->andThrow(new LoginNameInvalidException());

        $this->response->shouldReceive('withError')
            ->with(
                ErrorCodeEnum::LOGIN_NAME_INVALID,
                'The provided login name is invalid (invalid characters or invalid length)'
            )
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }

    public function testActionTrueOnSuccess(): void
    {
        $factionId = 666;
        $loginName = 'some-name';
        $emailAddress = 'some-email-address';
        $token = 'some-token';

        $faction = $this->mock(FactionInterface::class);

        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->once()
            ->andReturnTrue();

        $faction->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);
        $faction->shouldReceive('hasFreePlayerSlots')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->jsonSchemaRequest->shouldReceive('getData')
            ->with($this->handler)
            ->once()
            ->andReturn((object) [
                'factionId' => $factionId,
                'loginName' => $loginName,
                'emailAddress' => $emailAddress,
                'token' => $token
            ]);

        $this->factionRepository->shouldReceive('getByChooseable')
            ->with(true)
            ->once()
            ->andReturn([$faction]);

        $this->playerCreator->shouldReceive('createViaToken')
            ->with($loginName, $emailAddress, $faction, $token)
            ->once();

        $this->response->shouldReceive('withData')
            ->with(true)
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }

    //TOTEST with createWithMobileNumber
}
