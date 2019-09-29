<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Common\Register;

use Mockery\MockInterface;
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

    public function setUp(): void
    {
        $this->jsonSchemaRequest = $this->mock(JsonSchemaRequestInterface::class);
        $this->playerCreator = $this->mock(PlayerCreatorInterface::class);
        $this->factionRepository = $this->mock(FactionRepositoryInterface::class);

        $this->setUpApiHandler(
            new Register(
                $this->jsonSchemaRequest,
                $this->playerCreator,
                $this->factionRepository
            )
        );
    }

    public function testActionThrowsErrorOnInvalidFaction(): void
    {
        $factionId = 666;

        $faction = $this->mock(FactionInterface::class);

        $faction->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->jsonSchemaRequest->shouldReceive('getData')
            ->With($this->handler)
            ->once()
            ->andReturn((object) ['factionId' => $factionId]);

        $this->factionRepository->shouldReceive('getByChooseable')
            ->with(true)
            ->once()
            ->andReturn([$faction]);

        $this->response->shouldReceive('withError')
            ->With(
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

        $faction->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);
        $faction->shouldReceive('hasFreePlayerSlots')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->jsonSchemaRequest->shouldReceive('getData')
            ->With($this->handler)
            ->once()
            ->andReturn((object) ['factionId' => $factionId]);

        $this->factionRepository->shouldReceive('getByChooseable')
            ->with(true)
            ->once()
            ->andReturn([$faction]);

        $this->response->shouldReceive('withError')
            ->With(
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

        $faction = $this->mock(FactionInterface::class);

        $faction->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);
        $faction->shouldReceive('hasFreePlayerSlots')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->jsonSchemaRequest->shouldReceive('getData')
            ->With($this->handler)
            ->once()
            ->andReturn((object) [
                'factionId' => $factionId,
                'loginName' => $loginName,
                'emailAddress' => $emailAddress
            ]);

        $this->factionRepository->shouldReceive('getByChooseable')
            ->with(true)
            ->once()
            ->andReturn([$faction]);

        $this->playerCreator->shouldReceive('create')
            ->with($loginName, $emailAddress, $faction)
            ->once()
            ->andThrow(new LoginNameInvalidException());

        $this->response->shouldReceive('withError')
            ->With(
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

        $faction = $this->mock(FactionInterface::class);

        $faction->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);
        $faction->shouldReceive('hasFreePlayerSlots')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->jsonSchemaRequest->shouldReceive('getData')
            ->With($this->handler)
            ->once()
            ->andReturn((object) [
                'factionId' => $factionId,
                'loginName' => $loginName,
                'emailAddress' => $emailAddress
            ]);

        $this->factionRepository->shouldReceive('getByChooseable')
            ->with(true)
            ->once()
            ->andReturn([$faction]);

        $this->playerCreator->shouldReceive('create')
            ->with($loginName, $emailAddress, $faction)
            ->once();

        $this->response->shouldReceive('withData')
            ->With(true)
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }
}
