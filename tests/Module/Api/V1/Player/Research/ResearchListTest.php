<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player\Research;

use Mockery\MockInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuApiV1TestCase;

class ResearchListTest extends StuApiV1TestCase
{
    /**
     * @var null|MockInterface|SessionInterface
     */
    private $session;

    /**
     * @var null|MockInterface|TechlistRetrieverInterface
     */
    private $techlistRetriever;

    public function setUp(): void
    {
        $this->session = $this->mock(SessionInterface::class);
        $this->techlistRetriever = $this->mock(TechlistRetrieverInterface::class);

        $this->setUpApiHandler(
            new ResearchList(
                $this->session,
                $this->techlistRetriever
            )
        );
    }

    public function testActionRetrievesList(): void
    {
        $availableResearchId = 42;
        $availableResearchName = 'some-available-research';
        $availableResearchPoints = 33;
        $availableResearchCommodityId = 21;
        $availableResearchCommodityName = 'elefant-dumplings';
        $availableResearchDescription = 'once upon a time...';

        $finishedResearchId = 31;
        $finishedResearchName = 'some-finished-research';
        $finishedResearchDate = 1234567;

        $availableResearch = $this->mock(ResearchInterface::class);
        $finishedState = $this->mock(ResearchedInterface::class);
        $user = $this->mock(UserInterface::class);

        $availableResearch->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($availableResearchId);
        $availableResearch->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($availableResearchName);
        $availableResearch->shouldReceive('getPoints')
            ->withNoArgs()
            ->once()
            ->andReturn($availableResearchPoints);
        $availableResearch->shouldReceive('getCommodity->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($availableResearchCommodityId);
        $availableResearch->shouldReceive('getCommodity->getName')
            ->withNoArgs()
            ->once()
            ->andReturn($availableResearchCommodityName);
        $availableResearch->shouldReceive('getDescription')
            ->withNoArgs()
            ->once()
            ->andReturn($availableResearchDescription);

        $finishedState->shouldReceive('getResearch->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($finishedResearchId);
        $finishedState->shouldReceive('getResearch->getName')
            ->withNoArgs()
            ->once()
            ->andReturn($finishedResearchName);
        $finishedState->shouldReceive('getFinished')
            ->withNoArgs()
            ->once()
            ->andReturn($finishedResearchDate);

        $this->session->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->techlistRetriever->shouldReceive('getResearchList')
            ->with($user)
            ->once()
            ->andReturn([$availableResearch]);
        $this->techlistRetriever->shouldReceive('getFinishedResearchList')
            ->with($user)
            ->once()
            ->andReturn([$finishedState]);

        $this->response->shouldReceive('withData')
            ->with([
                'available' => [
                    [
                        'researchId' => $availableResearchId,
                        'name' => $availableResearchName,
                        'description' => $availableResearchDescription,
                        'points' => $availableResearchPoints,
                        'commodity' => [
                            'commodityId' => $availableResearchCommodityId,
                            'name' => $availableResearchCommodityName
                        ]
                    ]
                ],
                'finished' => [
                    [
                        'researchId' => $finishedResearchId,
                        'name' => $finishedResearchName,
                        'finishDate' => $finishedResearchDate
                    ]
                ]
            ])
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }
}
