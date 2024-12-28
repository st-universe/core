<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider\Message;

use Override;
use request;
use Stu\Component\Game\GameEnum;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderItem;
use Stu\Module\Message\Lib\PrivateMessageUiFactoryInterface;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class MessageProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        private ComponentRegistrationInterface $componentRegistration,
        private ClassicStyleProvider $ClassicStyleProvider,
        private MessengerStyleProvider $messengerStyleProvider,
        private PrivateMessageUiFactoryInterface $privateMessageUiFactory
    ) {}

    #[Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        if (!request::has('pmcat') && $game->getUser()->isInboxMessengerStyle()) {
            $this->messengerStyleProvider->setTemplateVariables($game);
        } else {
            $this->ClassicStyleProvider->setTemplateVariables($game);
        }

        $game->setTemplateVar(
            'PM_CATEGORIES',
            array_map(
                fn(PrivateMessageFolderInterface $folder): PrivateMessageFolderItem =>
                $this->privateMessageUiFactory->createPrivateMessageFolderItem($folder),
                $this->privateMessageFolderRepository->getOrderedByUser($game->getUser())
            )
        );

        $this->componentRegistration->addComponentUpdate(GameComponentEnum::PM);
        $game->addExecuteJS("initTranslations();", GameEnum::JS_EXECUTION_AFTER_RENDER);
    }
}
