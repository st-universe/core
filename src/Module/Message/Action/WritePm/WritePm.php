<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\WritePm;

use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class WritePm implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_WRITE_PM';

    public function __construct(
        private WritePmRequestInterface $writePmRequest,
        private IgnoreListRepositoryInterface $ignoreListRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private UserRepositoryInterface $userRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $text = $this->writePmRequest->getText();
        $recipientId = $this->writePmRequest->getRecipientId();
        $userId = $game->getUser()->getId();

        $recipient = $this->userRepository->find($recipientId);
        if ($recipient === null) {
            $game->addInformation("Dieser Siedler existiert nicht");
            return;
        }
        if ($recipient->getId() === $userId) {
            $game->addInformation("Du kannst keine Nachricht an Dich selbst schreiben");
            return;
        }
        if ($this->ignoreListRepository->exists($recipient->getId(), $userId)) {
            $game->addInformation("Der Siedler ignoriert Dich");
            return;
        }

        if (strlen($text) < 5) {
            $game->addInformation("Der Text ist zu kurz");
            return;
        }

        $this->privateMessageSender->send($userId, $recipient->getId(), $text, PrivateMessageFolderTypeEnum::SPECIAL_MAIN);

        $game->addInformation(_('Die Nachricht wurde abgeschickt'));
        $game->setView(ModuleEnum::PM);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
