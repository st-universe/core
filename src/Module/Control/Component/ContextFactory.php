<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component;

use Stu\Lib\Session\SessionStringFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\JavascriptExecutionInterface;
use Stu\Module\Twig\TwigPageInterface;

class ContextFactory implements ContextFactoryInterface
{
    public function __construct(
        private readonly TwigPageInterface $twigPage,
        private readonly JavascriptExecutionInterface $javascriptExecution,
        private readonly SessionStringFactoryInterface $sessionStringFactory
    ) {}

    public function createContext(GameControllerInterface $game): ControllerContext
    {
        return new Context(
            $game,
            $game->getGameData(),
            $this->twigPage,
            $this->javascriptExecution,
            $this->sessionStringFactory
        );
    }
}
