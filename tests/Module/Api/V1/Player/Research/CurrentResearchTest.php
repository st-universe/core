<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player\Research;

use Mockery\MockInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuApiV1TestCase;

class CurrentResearchTest extends StuApiV1TestCase
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
            new CurrentResearch(
                $this->session,
                $this->researchedRepository
            )
        );
    }

    public function testActionReturnsEmptyResultifNoResearchIsActive(): void
    {
        $userId = 666;

        $user = $this->mock(UserInterface::class);

        $this->session->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($userId)
            ->once()
            ->andReturnNull();

        $this->response->shouldReceive('withData')
            ->with(null)
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }

    public function testActionReturnsResult(): void
    {
        $userId = 666;
        $techId = 42;
        $techName = 'some-tech';
        $techPoints = 555;
        $pointsLeft = 444;

        $user = $this->mock(UserInterface::class);
        $researchState = $this->mock(ResearchedInterface::class);

        $this->session->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($userId)
            ->once()
            ->andReturn($researchState);

        $researchState->shouldReceive('getResearch->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($techId);
        $researchState->shouldReceive('getResearch->getName')
            ->withNoArgs()
            ->once()
            ->andReturn($techName);
        $researchState->shouldReceive('getResearch->getPoints')
            ->withNoArgs()
            ->once()
            ->andReturn($techPoints);
        $researchState->shouldReceive('getActive')
            ->withNoArgs()
            ->once()
            ->andReturn($pointsLeft);

        $this->response->shouldReceive('withData')
            ->with([
                'tech' => [
                    'id' => $techId,
                    'name' => $techName,
                    'points' => $techPoints
                ],
                'pointsLeft' => $pointsLeft
            ])
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }
}
