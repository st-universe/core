<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony\Component;

use Mockery\MockInterface;
use Override;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Research\ResearchStateFactoryInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuTestCase;

class AdvanceResearchTest extends StuTestCase
{
    private MockInterface&ResearchedRepositoryInterface $researchedRepository;
    private MockInterface&ResearchStateFactoryInterface $researchStateFactory;

    private MockInterface&Colony $colony;
    private MockInterface&InformationInterface $information;

    private ColonyTickComponentInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->researchedRepository = $this->mock(ResearchedRepositoryInterface::class);
        $this->researchStateFactory = $this->mock(ResearchStateFactoryInterface::class);

        $this->colony = $this->mock(Colony::class);
        $this->information = $this->mock(InformationInterface::class);

        $this->subject = new AdvanceResearch(
            $this->researchedRepository,
            $this->researchStateFactory
        );
    }

    public function testWorkExpectNothingWhenNoCurrentResearch(): void
    {
        $user = $this->mock(User::class);

        $production = [];

        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([]);

        $this->subject->work($this->colony, $production, $this->information);
    }

    public function testWorkExpectNothingWhenNeededCommodityNotProduced(): void
    {
        $user = $this->mock(User::class);
        $researched = $this->mock(Researched::class);

        $production = [];

        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $researched->shouldReceive('getResearch->getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([$researched]);

        $this->subject->work($this->colony, $production, $this->information);
    }

    public function testWorkExpectAdvanceOfOneResearch(): void
    {
        $user = $this->mock(User::class);
        $researched = $this->mock(Researched::class);
        $colonyProduction = $this->mock(ColonyProduction::class);

        $production = [42 => $colonyProduction];

        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $researched->shouldReceive('getResearch->getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $colonyProduction->shouldReceive('getProduction')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([$researched]);

        $this->researchStateFactory->shouldReceive('createResearchState->advance')
            ->with($researched, 5)
            ->once();

        $this->subject->work($this->colony, $production, $this->information);
    }

    public function testWorkExpectNoAdvanceOfSecondWhenNoRemainingPoints(): void
    {
        $user = $this->mock(User::class);
        $researched = $this->mock(Researched::class);
        $researched2 = $this->mock(Researched::class);
        $colonyProduction = $this->mock(ColonyProduction::class);

        $production = [42 => $colonyProduction];

        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $researched->shouldReceive('getResearch->getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $colonyProduction->shouldReceive('getProduction')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([$researched, $researched2]);

        $this->researchStateFactory->shouldReceive('createResearchState->advance')
            ->with($researched, 5)
            ->once()
            ->andReturn(0);

        $this->subject->work($this->colony, $production, $this->information);
    }

    public function testWorkExpectNoAdvanceOfSecondWhenNotPresent(): void
    {
        $user = $this->mock(User::class);
        $researched = $this->mock(Researched::class);
        $colonyProduction = $this->mock(ColonyProduction::class);

        $production = [42 => $colonyProduction];

        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $researched->shouldReceive('getResearch->getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $colonyProduction->shouldReceive('getProduction')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([$researched]);

        $this->researchStateFactory->shouldReceive('createResearchState->advance')
            ->with($researched, 5)
            ->once()
            ->andReturn(1);

        $this->subject->work($this->colony, $production, $this->information);
    }

    public function testWorkExpectNoAdvanceOfSecondWhenOtherCommodity(): void
    {
        $user = $this->mock(User::class);
        $researched = $this->mock(Researched::class);
        $researched2 = $this->mock(Researched::class);
        $colonyProduction = $this->mock(ColonyProduction::class);

        $production = [42 => $colonyProduction];

        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);

        $researched->shouldReceive('getResearch->getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $researched2->shouldReceive('getResearch->getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(43);

        $colonyProduction->shouldReceive('getProduction')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([$researched, $researched2]);

        $this->researchStateFactory->shouldReceive('createResearchState->advance')
            ->with($researched, 5)
            ->once()
            ->andReturn(1);

        $this->subject->work($this->colony, $production, $this->information);
    }

    public function testWorkExpectAdvanceOfSecondWhenSameCommodity(): void
    {
        $user = $this->mock(User::class);
        $researched = $this->mock(Researched::class);
        $researched2 = $this->mock(Researched::class);
        $colonyProduction = $this->mock(ColonyProduction::class);

        $production = [42 => $colonyProduction];

        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);

        $researched->shouldReceive('getResearch->getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $researched2->shouldReceive('getResearch->getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $colonyProduction->shouldReceive('getProduction')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([$researched, $researched2]);

        $this->researchStateFactory->shouldReceive('createResearchState->advance')
            ->with($researched, 5)
            ->once()
            ->andReturn(1);
        $this->researchStateFactory->shouldReceive('createResearchState->advance')
            ->with($researched2, 1)
            ->once();

        $this->subject->work($this->colony, $production, $this->information);
    }

    public function testWorkExpectAdvanceOfSameOnOtherColony(): void
    {
        $colony2 = $this->mock(Colony::class);
        $user = $this->mock(User::class);
        $researched = $this->mock(Researched::class);
        $researched2 = $this->mock(Researched::class);
        $colonyProduction = $this->mock(ColonyProduction::class);
        $colonyProduction2 = $this->mock(ColonyProduction::class);

        $production = [42 => $colonyProduction];
        $production2 = [42 => $colonyProduction2];

        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $colony2->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);

        $researched->shouldReceive('getResearch->getCommodityId')
            ->withNoArgs()
            ->twice()
            ->andReturn(42);

        $colonyProduction->shouldReceive('getProduction')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $colonyProduction2->shouldReceive('getProduction')
            ->withNoArgs()
            ->once()
            ->andReturn(3);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->twice()
            ->andReturn([$researched, $researched2]);

        $this->researchStateFactory->shouldReceive('createResearchState->advance')
            ->with($researched, 5)
            ->once()
            ->andReturn(0);
        $this->researchStateFactory->shouldReceive('createResearchState->advance')
            ->with($researched, 3)
            ->once()
            ->andReturn(0);

        $this->subject->work($this->colony, $production, $this->information);
        $this->subject->work($colony2, $production2, $this->information);
    }

    public function testWorkExpectAdvanceOfSecondOnOtherColony(): void
    {
        $colony2 = $this->mock(Colony::class);
        $user = $this->mock(User::class);
        $researched = $this->mock(Researched::class);
        $researched2 = $this->mock(Researched::class);
        $colonyProduction = $this->mock(ColonyProduction::class);
        $colonyProduction2 = $this->mock(ColonyProduction::class);

        $production = [42 => $colonyProduction];
        $production2 = [42 => $colonyProduction2];

        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $colony2->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);

        $researched->shouldReceive('getResearch->getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $researched2->shouldReceive('getResearch->getCommodityId')
            ->withNoArgs()
            ->twice()
            ->andReturn(42);

        $colonyProduction->shouldReceive('getProduction')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $colonyProduction2->shouldReceive('getProduction')
            ->withNoArgs()
            ->once()
            ->andReturn(3);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->twice()
            ->andReturn([$researched, $researched2], [$researched2]);

        $this->researchStateFactory->shouldReceive('createResearchState->advance')
            ->with($researched, 5)
            ->once()
            ->andReturn(1);
        $this->researchStateFactory->shouldReceive('createResearchState->advance')
            ->with($researched2, 1)
            ->once();
        $this->researchStateFactory->shouldReceive('createResearchState->advance')
            ->with($researched2, 3)
            ->once()
            ->andReturn(0);

        $this->subject->work($this->colony, $production, $this->information);
        $this->subject->work($colony2, $production2, $this->information);
    }

    public function testWorkExpectNoAdvanceOfSecondOnOtherColonyWhenOtherCommodity(): void
    {
        $colony2 = $this->mock(Colony::class);
        $user = $this->mock(User::class);
        $researched = $this->mock(Researched::class);
        $researched2 = $this->mock(Researched::class);
        $colonyProduction = $this->mock(ColonyProduction::class);
        $colonyProduction2 = $this->mock(ColonyProduction::class);

        $production = [42 => $colonyProduction];
        $production2 = [43 => $colonyProduction2];

        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $colony2->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);

        $researched->shouldReceive('getResearch->getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $researched2->shouldReceive('getResearch->getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(43);

        $colonyProduction->shouldReceive('getProduction')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->twice()
            ->andReturn([$researched], [$researched2]);

        $this->researchStateFactory->shouldReceive('createResearchState->advance')
            ->with($researched, 5)
            ->once()
            ->andReturn(0);

        $this->subject->work($this->colony, $production, $this->information);
        $this->subject->work($colony2, $production2, $this->information);
    }
}
