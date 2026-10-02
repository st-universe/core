<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component\Action;

use Stu\Module\Control\Component\ContextFactoryInterface;
use Stu\Module\Control\GameControllerInterface;

class ActionContextFactory implements ActionContextFactoryInterface {

    public function __construct(
        private readonly ContextFactoryInterface $contextFactory
    ) {}

    public function createActionContext(GameControllerInterface $game): ActionControllerContext {
        return new ActionContext($this->contextFactory->createContext($game));
    }
}
