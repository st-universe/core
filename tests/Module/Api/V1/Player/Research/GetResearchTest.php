<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player\Research;

use Mockery\MockInterface;
use Stu\Component\ErrorHandling\ErrorCodeEnum;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuApiV1TestCase;

class GetResearchTest extends StuApiV1TestCase
{
    /**
     * @var null|MockInterface|SessionInterface
     */
    private $session;

    /**
     * @var null|MockInterface|TechlistRetrieverInterface
     */
    private $techlistRetriever;

    /**
     * @var null|MockInterface|ResearchedRepositoryInterface
     */
    private $researchedRepository;

    public function setUp(): void
    {
        $this->session = $this->mock(SessionInterface::class);
        $this->techlistRetriever = $this->mock(TechlistRetrieverInterface::class);
        $this->researchedRepository = $this->mock(ResearchedRepositoryInterface::class);

        $this->setUpApiHandler(
            new GetResearch(
                $this->session,
                $this->techlistRetriever,
                $this->researchedRepository
            )
        );
    }

    public function testActionReturnsErrorOnInvalidResearchId(): void
    {
        $user = $this->mock(UserInterface::class);

        $researchId = 666;
        $userId = 42;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->session->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->args = ['researchId' => (string)$researchId];

        $this->techlistRetriever->shouldReceive('getResearchList')
            ->with($user)
            ->once()
            ->andReturn([]);

        $this->researchedRepository->shouldReceive('getFor')
            ->with($researchId, $userId)
            ->once()
            ->andReturnNull();

        $this->response->shouldReceive('withError')
            ->with(
                ErrorCodeEnum::NOT_FOUND,
                'Research not found'
            )
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }

    public function testActionReturnsDataIfAlreadyResearched(): void
    {
        $researchId = 666;
        $userId = 42;

        $researched = $this->mock(ResearchedInterface::class);
        $tech = $this->mock(ResearchInterface::class);
        $user = $this->mock(UserInterface::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->session->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->args = ['researchId' => (string)$researchId];

        $this->techlistRetriever->shouldReceive('getResearchList')
            ->with($user)
            ->once()
            ->andReturn([]);

        $this->researchedRepository->shouldReceive('getFor')
            ->with($researchId, $userId)
            ->once()
            ->andReturn($researched);

        $researched->shouldReceive('getResearch')
            ->withNoArgs()
            ->once()
            ->andReturn($tech);

        $this->buildResponse($tech);

        $this->performAssertion();
    }

    public function testActionReturnsData(): void
    {
        $researchId = 666;

        $tech = $this->mock(ResearchInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->buildResponse($tech);

        $this->session->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->args = ['researchId' => (string)$researchId];

        $this->techlistRetriever->shouldReceive('getResearchList')
            ->with($user)
            ->once()
            ->andReturn([$researchId => $tech]);


        $this->performAssertion();
    }

    private function buildResponse(MockInterface $tech): void
    {
        $techId = 33;
        $techName = 'some-name';
        $techDescription = 'some-description';
        $techPoints = 1234;
        $techCommodityId = 11;
        $techCommodityName = 'some-commodity';

        $tech->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($techId);
        $tech->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($techName);
        $tech->shouldReceive('getDescription')
            ->withNoArgs()
            ->once()
            ->andReturn($techDescription);
        $tech->shouldReceive('getPoints')
            ->withNoArgs()
            ->once()
            ->andReturn($techPoints);
        $tech->shouldReceive('getCommodity->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($techCommodityId);
        $tech->shouldReceive('getCommodity->getName')
            ->withNoArgs()
            ->once()
            ->andReturn($techCommodityName);

        $this->response->shouldReceive('withData')
            ->with([
                'researchId' => $techId,
                'name' => $techName,
                'description' => $techDescription,
                'points' => $techPoints,
                'commodity' => [
                    'commodityId' => $techCommodityId,
                    'name' => $techCommodityName
                ]
            ])
            ->once()
            ->andReturnSelf();
    }
}
