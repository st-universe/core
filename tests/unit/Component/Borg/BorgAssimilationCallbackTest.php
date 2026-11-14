<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\Component\CallbackExecution;
use Stu\Module\Control\Component\ControllerDiscoveryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

final class BorgAssimilationCallbackTest extends StuTestCase
{
    public function testForgedActionIsStoppedBeforeTheHandlerAndFlushAndReturnsANormalGameMessage(): void
    {
        request::setMockVars(['B_SELFDESTRUCT' => 1, 'id' => 77]);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(261);
        $ship = $this->mock(Spacecraft::class);
        $ship->shouldReceive('getUser')->andReturn($user);
        $ship->shouldReceive('isBeingAssimilated')->once()->andReturn(true);
        $repository = $this->mock(SpacecraftRepositoryInterface::class);
        $repository->shouldReceive('find')->once()->with(77)->andReturn($ship);
        $action = $this->mock(ActionControllerInterface::class);
        $action->shouldNotReceive('handle');
        $discovery = $this->mock(ControllerDiscoveryInterface::class);
        $discovery->shouldReceive('getControllers')->once()->with(ModuleEnum::SHIP, false)->andReturn(['B_SELFDESTRUCT' => $action]);
        $information = new InformationWrapper();
        $access = $this->mock(AccessCheckInterface::class);
        $access->shouldReceive('checkUserAccess')->once()->with($action, $information)->andReturn(true);
        $gameRequest = $this->mock(GameRequest::class);
        $gameRequest->shouldReceive('setAction')->once()->with('B_SELFDESTRUCT');
        $gameRequest->shouldReceive('setActionMs')->once()->with(1);
        $game = $this->mock(GameControllerInterface::class);
        $game->shouldReceive('getGameRequest')->andReturn($gameRequest);
        $game->shouldReceive('hasUser')->once()->andReturn(true);
        $game->shouldReceive('getUser')->once()->andReturn($user);
        $game->shouldReceive('getInfo')->andReturn($information);
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('hrtime')->twice()->andReturn(0, 1000000);
        $em = $this->mock(EntityManagerInterface::class);
        $em->shouldNotReceive('flush');
        $factory = $this->mock(ActionContextFactoryInterface::class);
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->once()->with(ShowSpacecraft::VIEW_IDENTIFIER);
        $factory->shouldReceive('createActionContext')->once()->with($game)->andReturn($context);
        $guard = new BorgAssimilationActionGuard($repository, $this->mock(CrewAssignmentRepositoryInterface::class));

        new CallbackExecution($discovery, $access, $factory, $clock, $em, $guard)->execute(ModuleEnum::SHIP, $game);

        self::assertSame(['Das Schiff wird assimiliert und kann bis zu seiner Befreiung nicht gesteuert oder verändert werden'], $information->getInformations());
    }

    /** @return iterable<string, array{string}> */
    public static function publicActions(): iterable
    {
        foreach (['B_LOGIN', 'B_SEND_REGISTRATION', 'B_CHECK_REGVAR', 'B_SEND_PASSWORD', 'B_RESET_PASSWORD', 'CONFIRM_ACCOUNT_DELETION'] as $action) {
            yield $action => [$action];
        }
    }

    #[DataProvider('publicActions')]
    public function testPublicActionsRunBeforeAUserHasBeenSet(string $identifier): void
    {
        request::setMockVars([$identifier => 1]);
        $context = $this->mock(ActionControllerContext::class);
        $action = $this->mock(ActionControllerInterface::class);
        $action->shouldReceive('handle')->once()->with($context);
        $discovery = $this->mock(ControllerDiscoveryInterface::class);
        $discovery->shouldReceive('getControllers')->once()->with(ModuleEnum::INDEX, false)->andReturn([$identifier => $action]);
        $information = new InformationWrapper();
        $access = $this->mock(AccessCheckInterface::class);
        $access->shouldReceive('checkUserAccess')->once()->with($action, $information)->andReturn(true);
        $gameRequest = $this->mock(GameRequest::class);
        $gameRequest->shouldReceive('setAction')->once()->with($identifier);
        $gameRequest->shouldReceive('setActionMs')->once()->with(1);
        $game = $this->mock(GameControllerInterface::class);
        $game->shouldReceive('getGameRequest')->andReturn($gameRequest);
        $game->shouldReceive('hasUser')->once()->andReturn(false);
        $game->shouldNotReceive('getUser');
        $game->shouldReceive('getInfo')->andReturn($information);
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('hrtime')->twice()->andReturn(0, 1000000);
        $em = $this->mock(EntityManagerInterface::class);
        $em->shouldReceive('flush')->once();
        $factory = $this->mock(ActionContextFactoryInterface::class);
        $factory->shouldReceive('createActionContext')->once()->with($game)->andReturn($context);
        $repository = $this->mock(SpacecraftRepositoryInterface::class);
        $repository->shouldNotReceive('find');
        $guard = new BorgAssimilationActionGuard($repository, $this->mock(CrewAssignmentRepositoryInterface::class));

        new CallbackExecution($discovery, $access, $factory, $clock, $em, $guard)->execute(ModuleEnum::INDEX, $game);

        self::assertSame([], $information->getInformations());
    }
}
