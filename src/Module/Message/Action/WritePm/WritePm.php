<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\WritePm;

use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\View\Overview\Overview;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class WritePm implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_WRITE_PM';

    private $writePmRequest;

    private $ignoreListRepository;

    private $privateMessageFolderRepository;

    private $privateMessageRepository;

    private $privateMessageSender;

    private $userRepository;

    public function __construct(
        WritePmRequestInterface $writePmRequest,
        IgnoreListRepositoryInterface $ignoreListRepository,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        UserRepositoryInterface $userRepository
    ) {
        $this->writePmRequest = $writePmRequest;
        $this->ignoreListRepository = $ignoreListRepository;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageRepository = $privateMessageRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->userRepository = $userRepository;
    }

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
        if ($recipient->getId() == $userId) {
            $game->addInformation("Du kannst keine Nachricht an Dich selbst schreiben");
            return;
        }
        if ($this->ignoreListRepository->exists((int) $recipient->getId(), $userId)) {
            $game->addInformation("Der Siedler ignoriert Dich");
            return;
        }

        if (strlen($text) < 5) {
            $game->addInformation("Der Text ist zu kurz");
            return;
        }

        $this->privateMessageSender->send($userId, $recipient->getId(), $text);

        $replyPm = $this->privateMessageRepository->find($this->writePmRequest->getReplyPmId());

        if ($replyPm && $replyPm->getRecipientId() == $userId) {
            $replyPm->setReplied(true);

            $this->privateMessageRepository->save($replyPm);
        }

        $game->addInformation(_('Die Nachricht wurde abgeschickt'));

        $game->setView(Overview::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
