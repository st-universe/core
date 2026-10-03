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
use Stu\Module\Control\Component\View\ViewContext;
use Stu\Module\Control\Component\View\ViewContextFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\GameRequest;
use Stu\StuTestCase;

class ViewExecutionTest extends StuTestCase
{
    private MockInterface&ControllerDiscoveryInterface $controllerDiscovery;
    private MockInterface&AccessCheckInterface $accessCheck;
    private MockInterface&ViewContextFactoryInterface $viewContextFactory;
    private MockInterface&TutorialProvider $tutorialProvider;
    private MockInterface&StuTime $stuTime;
    private MockInterface&EntityManagerInterface $entityManager;

    private MockInterface&GameControllerInterface $game;
    private MockInterface&ViewContext $context;

    private ViewExecution $subject;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->controllerDiscovery = $this->mock(ControllerDiscoveryInterface::class);
        $this->accessCheck = $this->mock(AccessCheckInterface::class);
        $this->viewContextFactory = $this->mock(ViewContextFactoryInterface::class);
        $this->tutorialProvider = $this->mock(TutorialProvider::class);
        $this->stuTime = $this->mock(StuTime::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->game = $this->mock(GameControllerInterface::class);
        $this->context = $this->mock(ViewContext::class);

        $this->viewContextFactory->shouldReceive('createViewContext')
            ->with($this->game, ModuleEnum::ALLIANCE)
            ->andReturn($this->context);

        $this->subject = new ViewExecution(
            $this->controllerDiscovery,
            $this->accessCheck,
            $this->viewContextFactory,
            $this->tutorialProvider,
            $this->stuTime,
            $this->entityManager
        );
    }

    public function testExecuteExpectNoExecutionIfRequestEmpty(): void
    {
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ViewControllerInterface::class);
        $controller2 = $this->mock(ViewControllerInterface::class);

        request::setMockVars([]);

        $this->context->shouldReceive('getViewContextMetadata')
            ->with(ViewContextMetadataTypeEnum::VIEW)
            ->andReturn('');
        $this->game->shouldReceive('getGameRequest')
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

        $this->subject->execute(ModuleEnum::ALLIANCE, $this->game);
    }

    public function testExecuteExpectErrorIfSanityException(): void
    {
        $info = $this->mock(InformationWrapper::class);
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ViewControllerInterface::class);
        $controller2 = $this->mock(ViewControllerInterface::class);
        $exception = new SanityCheckException();

        request::setMockVars(['SHOW_THIS' => 1]);

        $this->context->shouldReceive('getViewContextMetadata')
            ->with(ViewContextMetadataTypeEnum::VIEW)
            ->andReturn('');
        $this->game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->andReturn($info);

        $gameRequest->shouldReceive('setViewMs')
            ->with(1)
            ->once();
        $gameRequest->shouldReceive('setView')
            ->with('SHOW_THIS')
            ->once();
        $gameRequest->shouldReceive('addError')
            ->with($exception)
            ->once();

        $this->context->shouldReceive('setViewIdentifier')
            ->with('SHOW_THIS')
            ->once();

        $controller1->shouldReceive('handle')
            ->with($this->context)
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
            ->with($controller1, $info)
            ->once()
            ->andReturn(true);

        $this->subject->execute(ModuleEnum::ALLIANCE, $this->game);
    }

    public function testExecuteExpectInfoIfEntityLocked(): void
    {
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ViewControllerInterface::class);
        $controller2 = $this->mock(ViewControllerInterface::class);
        $exception = new EntityLockedException('LOCKED');
        $info = $this->mock(InformationWrapper::class);

        request::setMockVars(['SHOW_THIS' => 1]);

        $this->context->shouldReceive('getViewContextMetadata')
            ->with(ViewContextMetadataTypeEnum::VIEW)
            ->andReturn('');
        $this->game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);
        $info->shouldReceive('addInformation')
            ->with('LOCKED')
            ->once();
        $this->context->shouldReceive('setMacroInAjaxWindow')
            ->with('')
            ->once();
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->andReturn($info);

        $gameRequest->shouldReceive('setViewMs')
            ->with(1)
            ->once();
        $gameRequest->shouldReceive('setView')
            ->with('SHOW_THIS')
            ->once();

        $this->context->shouldReceive('setViewIdentifier')
            ->with('SHOW_THIS')
            ->once();

        $controller1->shouldReceive('handle')
            ->with($this->context)
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
            ->with($controller1, $info)
            ->once()
            ->andReturn(true);

        $this->subject->execute(ModuleEnum::ALLIANCE, $this->game);
    }

    public function testExecuteExpectHandleIfAccess(): void
    {
        $info = $this->mock(InformationWrapper::class);
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ViewControllerInterface::class);
        $controller2 = $this->mock(ViewControllerInterface::class);

        request::setMockVars(['SHOW_THIS' => 1]);

        $this->context->shouldReceive('getViewContextMetadata')
            ->with(ViewContextMetadataTypeEnum::VIEW)
            ->andReturn('');
        $this->game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);
        $this->game->shouldReceive('getInfo')
            ->once()
            ->withNoArgs()
            ->andReturn($info);

        $gameRequest->shouldReceive('setViewMs')
            ->with(1)
            ->once();
        $gameRequest->shouldReceive('setView')
            ->with('SHOW_THIS')
            ->once();

        $this->context->shouldReceive('setViewIdentifier')
            ->with('SHOW_THIS')
            ->once();

        $controller1->shouldReceive('handle')
            ->with($this->context)
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
            ->with($controller1, $info)
            ->once()
            ->andReturn(true);

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $this->subject->execute(ModuleEnum::ALLIANCE, $this->game);
    }

    public function testExecuteExpectHandleOfViewFromContextIfAccess(): void
    {
        $info = $this->mock(InformationWrapper::class);
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ViewControllerInterface::class);
        $controller2 = $this->mock(ViewControllerInterface::class);

        request::setMockVars(['SHOW_THIS' => 1]);

        $this->context->shouldReceive('getViewContextMetadata')
            ->with(ViewContextMetadataTypeEnum::VIEW)
            ->andReturn('SHOW_THAT');
        $this->game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->andReturn($info);

        $this->context->shouldReceive('setViewIdentifier')
            ->with('SHOW_THAT')
            ->once();

        $gameRequest->shouldReceive('setViewMs')
            ->with(1)
            ->once();
        $gameRequest->shouldReceive('setView')
            ->with('SHOW_THAT')
            ->once();

        $controller2->shouldReceive('handle')
            ->with($this->context)
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
            ->with($controller2, $info)
            ->once()
            ->andReturn(true);

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $this->subject->execute(ModuleEnum::ALLIANCE, $this->game);
    }

    public function testExecuteExpectNothingIfNoAccess(): void
    {
        $gameRequest = $this->mock(GameRequest::class);
        $controller1 = $this->mock(ViewControllerInterface::class);
        $controller2 = $this->mock(ViewControllerInterface::class);
        $info = $this->mock(InformationWrapper::class);

        request::setMockVars(['SHOW_THIS' => 1]);

        $this->context->shouldReceive('getViewContextMetadata')
            ->with(ViewContextMetadataTypeEnum::VIEW)
            ->andReturn('');
        $this->game->shouldReceive('getGameRequest')
            ->withNoArgs()
            ->andReturn($gameRequest);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->andReturn($info);

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
            ->with($controller1, $info)
            ->once()
            ->andReturn(false);

        $this->subject->execute(ModuleEnum::ALLIANCE, $this->game);
    }
}
