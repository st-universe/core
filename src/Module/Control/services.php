<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Component\Game\RedirectionException;
use Stu\Exception\MaintenanceGameStateException;
use Stu\Exception\RelocationGameStateException;
use Stu\Exception\ResetGameStateException;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Exception\UnallowedUplinkOperationException;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Lib\LoginException;
use Stu\Lib\UserLockedException;
use Stu\Module\Control\Component\ControllerDiscovery;
use Stu\Module\Control\Component\ControllerDiscoveryInterface;
use Stu\Module\Control\Component\TutorialProvider;
use Stu\Module\Control\Exception\ItemNotFoundException;
use Stu\Module\Control\Render\GameTwigRenderer;
use Stu\Module\Control\Render\GameTwigRendererInterface;
use Stu\Module\Control\Router\FallbackRouter;
use Stu\Module\Control\Router\FallbackRouterInterface;
use Stu\Module\Control\Router\Handler\AccountNotVerifiedFallbackHandler;
use Stu\Module\Control\Router\Handler\ItemNotFoundFallbackHandler;
use Stu\Module\Control\Router\Handler\LoginErrorFallbackHandler;
use Stu\Module\Control\Router\Handler\MaintenanceFallbackHandler;
use Stu\Module\Control\Router\Handler\RedirectionFallbackHandler;
use Stu\Module\Control\Router\Handler\RelocationFallbackHandler;
use Stu\Module\Control\Router\Handler\ResetFallbackHandler;
use Stu\Module\Control\Router\Handler\SpacecraftNotFoundFallbackHandler;
use Stu\Module\Control\Router\Handler\UplinkFallbackHandler;
use Stu\Module\Control\Router\Handler\UserLockedFallbackHandler;

use function DI\autowire;

return [
    AccessCheckInterface::class => autowire(AccessCheck::class),
    BenchmarkResultInterface::class => autowire(BenchmarkResult::class),
    ComponentSetupInterface::class => autowire(ComponentSetup::class),
    GameStateInterface::class => autowire(GameState::class),
    ControllerDiscoveryInterface::class => autowire(ControllerDiscovery::class),
    GameTwigRendererInterface::class => autowire(GameTwigRenderer::class),
    JavascriptExecutionInterface::class => autowire(JavascriptExecution::class),
    SemaphoreUtilInterface::class => autowire(SemaphoreUtil::class),
    StuTime::class => autowire(StuTime::class),
    StuHashInterface::class => autowire(StuHash::class),
    StuRandom::class => autowire(StuRandom::class),
    TutorialProvider::class => autowire(TutorialProvider::class),
    FallbackRouterInterface::class => autowire(FallbackRouter::class)
        ->constructorParameter(
            'handlers',
            [
                AccountNotVerifiedException::class => autowire(AccountNotVerifiedFallbackHandler::class),
                LoginException::class => autowire(LoginErrorFallbackHandler::class),
                UserLockedException::class => autowire(UserLockedFallbackHandler::class),
                MaintenanceGameStateException::class => autowire(MaintenanceFallbackHandler::class),
                ResetGameStateException::class => autowire(ResetFallbackHandler::class),
                RelocationGameStateException::class => autowire(RelocationFallbackHandler::class),
                SpacecraftDoesNotExistException::class => autowire(SpacecraftNotFoundFallbackHandler::class),
                ItemNotFoundException::class => autowire(ItemNotFoundFallbackHandler::class),
                UnallowedUplinkOperationException::class => autowire(UplinkFallbackHandler::class),
                RedirectionException::class => autowire(RedirectionFallbackHandler::class),
            ]
        )
];
