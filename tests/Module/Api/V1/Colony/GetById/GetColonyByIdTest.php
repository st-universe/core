<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Colony\GetById;

use Mockery\MockInterface;
use Stu\Component\ErrorHandling\ErrorCodeEnum;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\StuApiV1TestCase;

class GetColonyByIdTest extends StuApiV1TestCase
{
    /**
     * @var null|MockInterface|ColonyRepositoryInterface
     */
    private $colonyRepository;

    /**
     * @var null|MockInterface|SessionInterface
     */
    private $session;

    public function setUp(): void
    {
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->session = $this->mock(SessionInterface::class);

        $this->setUpApiHandler(
            new GetColonyById(
                $this->colonyRepository,
                $this->session
            )
        );
    }

    public function testActionReturnsErrorIfNotFound(): void
    {
        $colonyId = 666;

        $this->args = ['colonyId' => (string)$colonyId];

        $this->colonyRepository->shouldReceive('find')
            ->with($colonyId)
            ->once()
            ->andReturnNull();

        $this->response->shouldReceive('withError')
            ->with(
                ErrorCodeEnum::NOT_FOUND,
                'Not found'
            )
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }

    public function testActionReturnsErrorIfWrongUser(): void
    {
        $colonyId = 666;
        $userId = 42;

        $this->args = ['colonyId' => (string)$colonyId];

        $colony = $this->mock(ColonyInterface::class);

        $this->colonyRepository->shouldReceive('find')
            ->with($colonyId)
            ->once()
            ->andReturn($colony);

        $colony->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(33);

        $this->session->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->response->shouldReceive('withError')
            ->with(
                ErrorCodeEnum::NOT_FOUND,
                'Not found'
            )
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }

    public function testActionReturnsData(): void
    {
        $colonyId = 666;
        $userId = 42;
        $colonyName = 'some-name';

        $this->args = ['colonyId' => (string)$colonyId];

        $colony = $this->mock(ColonyInterface::class);

        $this->colonyRepository->shouldReceive('find')
            ->with($colonyId)
            ->once()
            ->andReturn($colony);

        $colony->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($colonyId);
        $colony->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($colonyName);

        $this->session->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->response->shouldReceive('withData')
            ->with([
                'colonyId' => $colonyId,
                'name' => $colonyName
            ])
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }
}
