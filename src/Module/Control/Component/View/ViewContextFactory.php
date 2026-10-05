<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component\View;

use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Session\SessionStringFactoryInterface;
use Stu\Module\Control\Component\ContextFactoryInterface;
use Stu\Module\Control\Component\View\ViewContextFactoryInterface as ViewViewContextFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\JavascriptExecutionInterface;
use Stu\Module\Game\Lib\GameSetupInterface;
use Stu\Module\Twig\TwigPageInterface;

final class ViewContextFactory implements ViewViewContextFactoryInterface
{
    public function __construct(
        private readonly ContextFactoryInterface $contextFactory,
        private readonly TwigPageInterface $twigPage,
        private readonly GameSetupInterface $gameSetup,
        private readonly JavascriptExecutionInterface $javascriptExecution,
        private readonly SessionStringFactoryInterface $sessionStringFactory
    ) {}

    public function createViewContext(GameControllerInterface $game, ModuleEnum $module): ViewContext
    {

        return new ViewContext(
            $this->contextFactory->createContext($game),
            $game->getGameData(),
            $module,
            $this->twigPage,
            $this->gameSetup,
            $this->javascriptExecution,
            $this->sessionStringFactory
        );
    }
}
