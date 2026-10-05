<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Lib\Component\ComponentLoaderInterface;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Lib\Session\SessionInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

class ComponentSetup implements ComponentSetupInterface
{
    public function __construct(
        private readonly PrivateMessageRepositoryInterface $privateMessageRepository,
        private readonly ComponentRegistrationInterface $componentRegistration,
        private readonly ComponentLoaderInterface $componentLoader,
        private readonly SessionInterface $session,
        private readonly TemplateInterface $template
    ) {}

    #[\Override]
    public function setup(): void
    {
        $user = $this->session->getUser();
        if ($user === null) {
            return;
        }

        if ($this->privateMessageRepository->hasRecentMessage($user)) {
            $this->componentRegistration->addComponentUpdate(GameComponentEnum::PM);
        }

        $this->componentLoader->loadComponentUpdates();
        $this->componentLoader->loadRegisteredComponents($user, $this->template);
    }
}
