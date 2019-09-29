<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player\Research;

use Mockery\MockInterface;
use Stu\Component\ErrorHandling\ErrorCodeEnum;
use Stu\Module\Api\Middleware\Request\JsonSchemaRequestInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuApiV1TestCase;

class StartResearchTest extends StuApiV1TestCase
{

    /**
     * @var null|MockInterface|SessionInterface
     */
    private $session;

    /**
     * @var null|MockInterface|ResearchedRepositoryInterface
     */
    private $researchedRepository;

    /**
     * @var null|MockInterface|JsonSchemaRequestInterface
     */
    private $jsonSchemaRequest;

    /**
     * @var null|MockInterface|TechlistRetrieverInterface
     */
    private $techlistRetriever;

    public function setUp(): void
    {
        $this->session = $this->mock(SessionInterface::class);
        $this->researchedRepository = $this->mock(ResearchedRepositoryInterface::class);
        $this->jsonSchemaRequest = $this->mock(JsonSchemaRequestInterface::class);
        $this->techlistRetriever = $this->mock(TechlistRetrieverInterface::class);

        $this->setUpApiHandler(
            new StartResearch(
                $this->session,
                $this->researchedRepository,
                $this->jsonSchemaRequest,
                $this->techlistRetriever
            )
        );
    }

    public function testActionFailsWithInvalidResearchId(): void
    {
        $userId = 666;
        $researchId = 42;

        $user = $this->mock(UserInterface::class);

        $this->session->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->jsonSchemaRequest->shouldReceive('getData')
            ->with($this->handler)
            ->once()
            ->andReturn((object) ['researchId' => $researchId]);

        $this->techlistRetriever->shouldReceive('getResearchList')
            ->with($userId)
            ->once()
            ->andReturn([]);

        $this->response->shouldReceive('withError')
            ->with(
                ErrorCodeEnum::NOT_FOUND,
                'Research not found'
            )
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }

    public function testActionStartsResearch(): void
    {
        $userId = 666;
        $researchId = 42;
        $points = 33;

        $user = $this->mock(UserInterface::class);
        $research = $this->mock(ResearchInterface::class);
        $currentResearch = $this->mock(ResearchedInterface::class);
        $newResearch = $this->mock(ResearchedInterface::class);

        $this->session->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->jsonSchemaRequest->shouldReceive('getData')
            ->with($this->handler)
            ->once()
            ->andReturn((object) ['researchId' => $researchId]);

        $this->techlistRetriever->shouldReceive('getResearchList')
            ->with($userId)
            ->once()
            ->andReturn([$researchId => $research]);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($userId)
            ->once()
            ->andReturn($currentResearch);
        $this->researchedRepository->shouldReceive('delete')
            ->with($currentResearch)
            ->once();
        $this->researchedRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($newResearch);
        $this->researchedRepository->shouldReceive('save')
            ->with($newResearch)
            ->once();

        $research->shouldReceive('getPoints')
            ->withNoArgs()
            ->once()
            ->andReturn($points);

        $newResearch->shouldReceive('setActive')
            ->with($points)
            ->once()
            ->andReturnSelf();
        $newResearch->shouldReceive('setUser')
            ->with($user)
            ->once()
            ->andReturnSelf();
        $newResearch->shouldReceive('setResearch')
            ->with($research)
            ->once()
            ->andReturnSelf();

        $this->response->shouldReceive('withData')
            ->with(true)
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }
}
