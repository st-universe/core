<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player\Research;

use Mockery\MockInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuApiV1TestCase;

class CancelResearchTest extends StuApiV1TestCase
{

    /**
     * @var null|MockInterface|SessionInterface
     */
    private $session;

    /**
     * @var null|MockInterface|ResearchedRepositoryInterface
     */
    private $researchedRepository;

    public function setUp(): void
    {
        $this->session = $this->mock(SessionInterface::class);
        $this->researchedRepository = $this->mock(ResearchedRepositoryInterface::class);

        $this->setUpApiHandler(
            new CancelResearch(
                $this->session,
                $this->researchedRepository
            )
        );
    }

    public function testActionDeletesState(): void
    {
        $state = $this->mock(ResearchedInterface::class);

        $userId = 666;

        $this->session->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($userId)
            ->once()
            ->andReturn($state);

        $this->researchedRepository->shouldReceive('delete')
            ->with($state)
            ->once();

        $this->response->shouldReceive('withData')
            ->with(true)
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }
}
