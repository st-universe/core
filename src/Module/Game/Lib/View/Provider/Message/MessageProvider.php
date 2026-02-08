<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider\Message;

use request;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderItem;
use Stu\Module\Message\Lib\PrivateMessageUiFactoryInterface;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class MessageProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private readonly PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        private readonly ComponentRegistrationInterface $componentRegistration,
        private readonly ClassicStyleProvider $classicStyleProvider,
        private readonly MessengerStyleProvider $messengerStyleProvider,
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly PrivateMessageUiFactoryInterface $privateMessageUiFactory
    ) {}

    #[\Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        if (!request::has('pmcat') && $this->userSettingsProvider->isInboxMessengerStyle($game->getUser())) {
            $this->messengerStyleProvider->setTemplateVariables($game);
        } else {
            $this->classicStyleProvider->setTemplateVariables($game);
        }

        $game->setTemplateVar(
            'PM_CATEGORIES',
            array_map(
                fn (PrivateMessageFolder $folder): PrivateMessageFolderItem =>
                $this->privateMessageUiFactory->createPrivateMessageFolderItem($folder),
                $this->privateMessageFolderRepository->getOrderedByUser($game->getUser())
            )
        );

        $this->componentRegistration->addComponentUpdate(GameComponentEnum::PM);
        $game->addExecuteJS("initTranslations();", JavascriptExecutionTypeEnum::AFTER_RENDER);
    }
}
