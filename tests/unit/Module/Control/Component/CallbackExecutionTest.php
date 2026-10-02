<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component;

use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\EntityLockedException;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\GameRequest;
use Stu\StuTestCase;

class CallbackExecutionTest extends StuTestCase
{
    private MockInterface&ControllerDiscoveryInterface $controllerDiscovery;
    private MockInterface&AccessCheckInterface $accessCheck;
    private MockInterface&ActionContextFactoryInterface $actionContextFactory;
    private MockInterface&StuTime $stuTime;
    private MockInterface&EntityManagerInterface $entityManager;

    private CallbackExecution $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->controllerDiscovery = $this->mock(ControllerDiscoveryInterface::class);
        $this->accessCheck = $this->mock(AccessCheckInterface::class);
        $this->actionContextFactory = $this->mock(ActionContextFactoryInterface::class);
        $this->stuTime = $this->mock(StuTime::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->subject = new CallbackExecution(
            $this->controllerDiscovery,
            $this->accessCheck,
            $this->actionContextFactory,
            $this->stuTime,
            $this->entityManager
        );
    }

    public function testExecuteExpectNoExecutionIfRequestEmpty(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ActionControllerInterface::class);
        $controller2 = $this->mock(ActionControllerInterface::class);

        request::setMockVars([]);

        $game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);

        $gameRequest->shouldReceive('setActionMs')
            ->with(1)
            ->once();

        $this->stuTime->shouldReceive('hrtime')
            ->with()
            ->twice()
            ->andReturn(1000000, 2000000);

        $this->controllerDiscovery->shouldReceive('getControllers')
            ->with(ModuleEnum::ALLIANCE, false)
            ->once()
            ->andReturn([
                'B_DO_THIS' => $controller1,
                'B_DO_THAT' => $controller2
            ]);

        $this->subject->execute(ModuleEnum::ALLIANCE, $game);
    }

    public function testExecuteExpectErrorIfSanityException(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $context = $this->mock(ActionControllerContext::class);
        $info = $this->mock(InformationWrapper::class);
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ActionControllerInterface::class);
        $controller2 = $this->mock(ActionControllerInterface::class);
        $exception = new SanityCheckException();

        request::setMockVars(['B_DO_THIS' => 1]);

        $game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);
        $game->shouldReceive('getInfo')
            ->withNoArgs()
            ->andReturn($info);

        $gameRequest->shouldReceive('setActionMs')
            ->with(1)
            ->once();
        $gameRequest->shouldReceive('setAction')
            ->with('B_DO_THIS')
            ->once();
        $gameRequest->shouldReceive('addError')
            ->with($exception)
            ->once();

        $controller1->shouldReceive('handle')
            ->with($context)
            ->once()
            ->andThrow($exception);

        $this->stuTime->shouldReceive('hrtime')
            ->with()
            ->twice()
            ->andReturn(1000000, 2000000);

        $this->controllerDiscovery->shouldReceive('getControllers')
            ->with(ModuleEnum::ALLIANCE, false)
            ->once()
            ->andReturn([
                'B_DO_THIS' => $controller1,
                'B_DO_THAT' => $controller2
            ]);

        $this->accessCheck->shouldReceive('checkUserAccess')
            ->with($controller1, $info)
            ->once()
            ->andReturn(true);

        $this->actionContextFactory->shouldReceive('createActionContext')
            ->with($game)
            ->once()
            ->andReturn($context);

        $this->subject->execute(ModuleEnum::ALLIANCE, $game);
    }

    public function testExecuteExpectInfoIfEntityLocked(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $context = $this->mock(ActionControllerContext::class);
        $info = $this->mock(InformationWrapper::class);
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ActionControllerInterface::class);
        $controller2 = $this->mock(ActionControllerInterface::class);
        $exception = new EntityLockedException('LOCKED');

        request::setMockVars(['B_DO_THIS' => 1]);

        $game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);
        $info->shouldReceive('addInformation')
            ->with('LOCKED')
            ->once();
        $game->shouldReceive('getInfo')
            ->withNoArgs()
            ->andReturn($info);

        $gameRequest->shouldReceive('setActionMs')
            ->with(1)
            ->once();
        $gameRequest->shouldReceive('setAction')
            ->with('B_DO_THIS')
            ->once();

        $controller1->shouldReceive('handle')
            ->with($context)
            ->once()
            ->andThrow($exception);

        $this->stuTime->shouldReceive('hrtime')
            ->with()
            ->twice()
            ->andReturn(1000000, 2000000);

        $this->controllerDiscovery->shouldReceive('getControllers')
            ->with(ModuleEnum::ALLIANCE, false)
            ->once()
            ->andReturn([
                'B_DO_THIS' => $controller1,
                'B_DO_THAT' => $controller2
            ]);

        $this->accessCheck->shouldReceive('checkUserAccess')
            ->with($controller1, $info)
            ->once()
            ->andReturn(true);

        $this->actionContextFactory->shouldReceive('createActionContext')
            ->with($game)
            ->once()
            ->andReturn($context);

        $this->subject->execute(ModuleEnum::ALLIANCE, $game);
    }

    public function testExecuteExpectHandleIfAccess(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $context = $this->mock(ActionControllerContext::class);
        $info = $this->mock(InformationWrapper::class);
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ActionControllerInterface::class);
        $controller2 = $this->mock(ActionControllerInterface::class);

        request::setMockVars(['B_DO_THIS' => 1]);

        $game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);
        $game->shouldReceive('getInfo')
            ->withNoArgs()
            ->andReturn($info);

        $gameRequest->shouldReceive('setActionMs')
            ->with(1)
            ->once();
        $gameRequest->shouldReceive('setAction')
            ->with('B_DO_THIS')
            ->once();

        $controller1->shouldReceive('handle')
            ->with($context)
            ->once();

        $this->stuTime->shouldReceive('hrtime')
            ->with()
            ->twice()
            ->andReturn(1000000, 2000000);

        $this->controllerDiscovery->shouldReceive('getControllers')
            ->with(ModuleEnum::ALLIANCE, false)
            ->once()
            ->andReturn([
                'B_DO_THIS' => $controller1,
                'B_DO_THAT' => $controller2
            ]);

        $this->accessCheck->shouldReceive('checkUserAccess')
            ->with($controller1, $info)
            ->once()
            ->andReturn(true);

        $this->actionContextFactory->shouldReceive('createActionContext')
            ->with($game)
            ->once()
            ->andReturn($context);

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $this->subject->execute(ModuleEnum::ALLIANCE, $game);
    }

    public function testExecuteExpectNothingIfNoAccess(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $info = $this->mock(InformationWrapper::class);
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ActionControllerInterface::class);
        $controller2 = $this->mock(ActionControllerInterface::class);

        request::setMockVars(['B_DO_THIS' => 1]);

        $game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);
        $game->shouldReceive('getInfo')
            ->withNoArgs()
            ->andReturn($info);

        $gameRequest->shouldReceive('setActionMs')
            ->with(1)
            ->once();
        $gameRequest->shouldReceive('setAction')
            ->with('B_DO_THIS')
            ->once();

        $this->stuTime->shouldReceive('hrtime')
            ->with()
            ->twice()
            ->andReturn(1000000, 2000000);

        $this->controllerDiscovery->shouldReceive('getControllers')
            ->with(ModuleEnum::ALLIANCE, false)
            ->once()
            ->andReturn([
                'B_DO_THIS' => $controller1,
                'B_DO_THAT' => $controller2
            ]);

        $this->accessCheck->shouldReceive('checkUserAccess')
            ->with($controller1, $info)
            ->once()
            ->andReturn(false);

        $this->subject->execute(ModuleEnum::ALLIANCE, $game);
    }
}
