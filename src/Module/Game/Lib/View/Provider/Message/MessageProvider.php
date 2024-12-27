<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider\Message;

use Override;
use Stu\Component\Game\GameEnum;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;

final class MessageProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private ComponentRegistrationInterface $componentRegistration,
        private ClassicStyleProvider $ClassicStyleProvider,
        private MessengerStyleProvider $messengerStyleProvider
    ) {}

    #[Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        if ($game->getUser()->isInboxMessengerStyle()) {
            $this->messengerStyleProvider->setTemplateVariables($game);
        } else {
            $this->ClassicStyleProvider->setTemplateVariables($game);
        }

        $this->componentRegistration->addComponentUpdate(GameComponentEnum::PM);
        $game->addExecuteJS("initTranslations();", GameEnum::JS_EXECUTION_AFTER_RENDER);
    }
}
