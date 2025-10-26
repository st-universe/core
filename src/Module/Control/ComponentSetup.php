<?php

namespace Stu\Module\Control;

use Stu\Lib\Component\ComponentLoaderInterface;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

class ComponentSetup implements ComponentSetupInterface
{
    public function __construct(
        private PrivateMessageRepositoryInterface $privateMessageRepository,
        private ComponentRegistrationInterface $componentRegistration,
        private ComponentLoaderInterface $componentLoader
    ) {}

    #[\Override]
    public function setup(GameControllerInterface $game): void
    {
        if ($game->hasUser() && $this->privateMessageRepository->hasRecentMessage($game->getUser())) {
            $this->componentRegistration->addComponentUpdate(GameComponentEnum::PM);
        }

        $this->componentLoader->loadComponentUpdates($game);
        $this->componentLoader->loadRegisteredComponents($game);
    }
}
