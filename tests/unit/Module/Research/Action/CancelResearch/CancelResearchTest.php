<?php

declare(strict_types=1);

namespace Stu\Module\Research\Action\CancelResearch;

use Mockery\MockInterface;
use request;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuTestCase;

class CancelResearchTest extends StuTestCase
{
    private MockInterface&ResearchedRepositoryInterface $researchedRepository;
    private MockInterface&ComponentRegistrationInterface $componentRegistration;

    private CancelResearch $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->researchedRepository = $this->mock(ResearchedRepositoryInterface::class);
        $this->componentRegistration = $this->mock(ComponentRegistrationInterface::class);

        $this->subject = new CancelResearch(
            $this->researchedRepository,
            $this->componentRegistration
        );
    }

    public function testHandleDoesNothingIfNoCurrentResearch(): void
    {
        $context = $this->mock(ActionControllerContext::class);
        $user = $this->mock(User::class);

        request::setMockVars(['id' => 42]);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([]);

        $context->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $context->shouldReceive('setView')
            ->with(GameController::DEFAULT_VIEW)
            ->once();

        $this->subject->handle($context);
    }

    public function testHandleCancelsTheUsersResearch(): void
    {
        $context = $this->mock(ActionControllerContext::class);
        $user = $this->mock(User::class);
        $researchReference = $this->mock(Researched::class);

        request::setMockVars(['id' => 42]);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([$researchReference]);
        $this->researchedRepository->shouldReceive('delete')
            ->with($researchReference)
            ->once();

        $researchReference->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $context->shouldReceive('getInfo->addInformation')
            ->with('Die laufende Forschung wurde abgebrochen')
            ->once();
        $context->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $context->shouldReceive('setView')
            ->with(GameController::DEFAULT_VIEW)
            ->once();

        $this->componentRegistration->shouldReceive('addComponentUpdate')
            ->with(GameComponentEnum::RESEARCH)
            ->once();

        $this->subject->handle($context);
    }

    public function testPerformSessionCheckReturnsTrue(): void
    {
        $this->assertTrue(
            $this->subject->performSessionCheck()
        );
    }
}
