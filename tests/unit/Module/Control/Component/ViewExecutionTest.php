<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component;

use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\EntityLockedException;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\ControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Orm\Entity\GameRequest;
use Stu\StuTestCase;

class ViewExecutionTest extends StuTestCase
{
    private MockInterface&ControllerDiscoveryInterface $controllerDiscovery;
    private MockInterface&AccessCheckInterface $accessCheck;
    private MockInterface&TutorialProvider $tutorialProvider;
    private MockInterface&StuTime $stuTime;
    private MockInterface&EntityManagerInterface $entityManager;

    private ViewExecution $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->controllerDiscovery = $this->mock(ControllerDiscoveryInterface::class);
        $this->accessCheck = $this->mock(AccessCheckInterface::class);
        $this->tutorialProvider = $this->mock(TutorialProvider::class);
        $this->stuTime = $this->mock(StuTime::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->subject = new ViewExecution(
            $this->controllerDiscovery,
            $this->accessCheck,
            $this->tutorialProvider,
            $this->stuTime,
            $this->entityManager
        );
    }

    public function testExecuteExpectNoExecutionIfRequestEmpty(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ControllerInterface::class);
        $controller2 = $this->mock(ControllerInterface::class);

        request::setMockVars([]);

        $game->shouldReceive('getViewContext')
            ->with(ViewContextTypeEnum::VIEW)
            ->andReturn('');
        $game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);

        $gameRequest->shouldReceive('setViewMs')
            ->with(1)
            ->once();

        $this->stuTime->shouldReceive('hrtime')
            ->with()
            ->twice()
            ->andReturn(1000000, 2000000);

        $this->controllerDiscovery->shouldReceive('getControllers')
            ->with(ModuleEnum::ALLIANCE, true)
            ->once()
            ->andReturn([
                'SHOW_THIS' => $controller1,
                'SHOW_THAT' => $controller2
            ]);

        $this->subject->execute(ModuleEnum::ALLIANCE, $game);
    }

    public function testExecuteExpectErrorIfSanityException(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ControllerInterface::class);
        $controller2 = $this->mock(ControllerInterface::class);
        $exception = new SanityCheckException();

        request::setMockVars(['SHOW_THIS' => 1]);

        $game->shouldReceive('getViewContext')
            ->with(ViewContextTypeEnum::VIEW)
            ->andReturn('');
        $game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);

        $gameRequest->shouldReceive('setViewMs')
            ->with(1)
            ->once();
        $gameRequest->shouldReceive('setView')
            ->with('SHOW_THIS')
            ->once();
        $gameRequest->shouldReceive('addError')
            ->with($exception)
            ->once();

        $controller1->shouldReceive('handle')
            ->with($game)
            ->once()
            ->andThrow($exception);

        $this->stuTime->shouldReceive('hrtime')
            ->with()
            ->twice()
            ->andReturn(1000000, 2000000);

        $this->controllerDiscovery->shouldReceive('getControllers')
            ->with(ModuleEnum::ALLIANCE, true)
            ->once()
            ->andReturn([
                'SHOW_THIS' => $controller1,
                'SHOW_THAT' => $controller2
            ]);

        $this->accessCheck->shouldReceive('checkUserAccess')
            ->with($controller1, $game)
            ->once()
            ->andReturn(true);

        $this->subject->execute(ModuleEnum::ALLIANCE, $game);
    }

    public function testExecuteExpectInfoIfEntityLocked(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ControllerInterface::class);
        $controller2 = $this->mock(ControllerInterface::class);
        $exception = new EntityLockedException('LOCKED');

        request::setMockVars(['SHOW_THIS' => 1]);

        $game->shouldReceive('getViewContext')
            ->with(ViewContextTypeEnum::VIEW)
            ->andReturn('');
        $game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);
        $game->shouldReceive('getInfo->addInformation')
            ->with('LOCKED')
            ->once();
        $game->shouldReceive('setMacroInAjaxWindow')
            ->with('')
            ->once();

        $gameRequest->shouldReceive('setViewMs')
            ->with(1)
            ->once();
        $gameRequest->shouldReceive('setView')
            ->with('SHOW_THIS')
            ->once();

        $controller1->shouldReceive('handle')
            ->with($game)
            ->once()
            ->andThrow($exception);

        $this->stuTime->shouldReceive('hrtime')
            ->with()
            ->twice()
            ->andReturn(1000000, 2000000);

        $this->controllerDiscovery->shouldReceive('getControllers')
            ->with(ModuleEnum::ALLIANCE, true)
            ->once()
            ->andReturn([
                'SHOW_THIS' => $controller1,
                'SHOW_THAT' => $controller2
            ]);

        $this->accessCheck->shouldReceive('checkUserAccess')
            ->with($controller1, $game)
            ->once()
            ->andReturn(true);

        $this->subject->execute(ModuleEnum::ALLIANCE, $game);
    }

    public function testExecuteExpectHandleIfAccess(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ControllerInterface::class);
        $controller2 = $this->mock(ControllerInterface::class);

        request::setMockVars(['SHOW_THIS' => 1]);

        $game->shouldReceive('getViewContext')
            ->with(ViewContextTypeEnum::VIEW)
            ->andReturn('');
        $game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);

        $gameRequest->shouldReceive('setViewMs')
            ->with(1)
            ->once();
        $gameRequest->shouldReceive('setView')
            ->with('SHOW_THIS')
            ->once();

        $controller1->shouldReceive('handle')
            ->with($game)
            ->once();

        $this->stuTime->shouldReceive('hrtime')
            ->with()
            ->twice()
            ->andReturn(1000000, 2000000);

        $this->controllerDiscovery->shouldReceive('getControllers')
            ->with(ModuleEnum::ALLIANCE, true)
            ->once()
            ->andReturn([
                'SHOW_THIS' => $controller1,
                'SHOW_THAT' => $controller2
            ]);

        $this->accessCheck->shouldReceive('checkUserAccess')
            ->with($controller1, $game)
            ->once()
            ->andReturn(true);

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $this->subject->execute(ModuleEnum::ALLIANCE, $game);
    }

    public function testExecuteExpectHandleOfViewFromContextIfAccess(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ControllerInterface::class);
        $controller2 = $this->mock(ControllerInterface::class);

        $game->shouldReceive('getViewContext')
            ->with(ViewContextTypeEnum::VIEW)
            ->andReturn('SHOW_THAT');
        $game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);

        $gameRequest->shouldReceive('setViewMs')
            ->with(1)
            ->once();
        $gameRequest->shouldReceive('setView')
            ->with('SHOW_THAT')
            ->once();

        $controller2->shouldReceive('handle')
            ->with($game)
            ->once();

        $this->stuTime->shouldReceive('hrtime')
            ->with()
            ->twice()
            ->andReturn(1000000, 2000000);

        $this->controllerDiscovery->shouldReceive('getControllers')
            ->with(ModuleEnum::ALLIANCE, true)
            ->once()
            ->andReturn([
                'SHOW_THIS' => $controller1,
                'SHOW_THAT' => $controller2
            ]);

        $this->accessCheck->shouldReceive('checkUserAccess')
            ->with($controller2, $game)
            ->once()
            ->andReturn(true);

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $this->subject->execute(ModuleEnum::ALLIANCE, $game);
    }

    public function testExecuteExpectNothingIfNoAccess(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ControllerInterface::class);
        $controller2 = $this->mock(ControllerInterface::class);

        request::setMockVars(['SHOW_THIS' => 1]);

        $game->shouldReceive('getViewContext')
            ->with(ViewContextTypeEnum::VIEW)
            ->andReturn('');
        $game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);

        $gameRequest->shouldReceive('setViewMs')
            ->with(1)
            ->once();
        $gameRequest->shouldReceive('setView')
            ->with('SHOW_THIS')
            ->once();

        $this->stuTime->shouldReceive('hrtime')
            ->with()
            ->twice()
            ->andReturn(1000000, 2000000);

        $this->controllerDiscovery->shouldReceive('getControllers')
            ->with(ModuleEnum::ALLIANCE, true)
            ->once()
            ->andReturn([
                'SHOW_THIS' => $controller1,
                'SHOW_THAT' => $controller2
            ]);

        $this->accessCheck->shouldReceive('checkUserAccess')
            ->with($controller1, $game)
            ->once()
            ->andReturn(false);

        $this->subject->execute(ModuleEnum::ALLIANCE, $game);
    }
}
