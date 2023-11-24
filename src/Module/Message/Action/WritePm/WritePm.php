<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\WritePm;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class WritePm implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_WRITE_PM';

    private WritePmRequestInterface $writePmRequest;

    private IgnoreListRepositoryInterface $ignoreListRepository;


    private PrivateMessageRepositoryInterface $privateMessageRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        WritePmRequestInterface $writePmRequest,
        IgnoreListRepositoryInterface $ignoreListRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        UserRepositoryInterface $userRepository
    ) {
        $this->writePmRequest = $writePmRequest;
        $this->ignoreListRepository = $ignoreListRepository;
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

        $this->privateMessageSender->send($userId, $recipient->getId(), $text, PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN);

        $replyPm = $this->privateMessageRepository->find($this->writePmRequest->getReplyPmId());

        if ($replyPm && $replyPm->getRecipientId() === $userId) {
            $replyPm->setReplied(true);

            $this->privateMessageRepository->save($replyPm);
        }

        $game->addInformation(_('Die Nachricht wurde abgeschickt'));

        $game->setView(ModuleViewEnum::PM);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
