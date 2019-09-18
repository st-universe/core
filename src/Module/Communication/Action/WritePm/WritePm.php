<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\WritePm;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowPmCategory\ShowPmCategory;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use User;

final class WritePm implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_WRITE_PM';

    private $writePmRequest;

    private $ignoreListRepository;

    private $privateMessageFolderRepository;

    private $privateMessageRepository;

    public function __construct(
        WritePmRequestInterface $writePmRequest,
        IgnoreListRepositoryInterface $ignoreListRepository,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository
    ) {
        $this->writePmRequest = $writePmRequest;
        $this->ignoreListRepository = $ignoreListRepository;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageRepository = $privateMessageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $text = $this->writePmRequest->getText();
        $recipientId = $this->writePmRequest->getRecipientId();
        $userId = $game->getUser()->getId();

        $recipient = User::getUserById($recipientId);
        if (!$recipient) {
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
        $cat = $this->privateMessageFolderRepository->getByUserAndSpecial((int) $recipient->getId(), PM_SPECIAL_MAIN);

        $pm = $this->privateMessageRepository->prototype();
        $pm->setText($text);
        $pm->setRecipientId($recipient->getId());
        $pm->setSenderId($userId);
        $pm->setDate(time());
        $pm->setCategory($cat);
        $pm->copyPM();

        $this->privateMessageRepository->save($pm);

        $replyPm = $this->privateMessageRepository->find($this->writePmRequest->getReplyPmId());

        if ($replyPm && $replyPm->getRecipientId() == $userId) {
            $replyPm->setReplied(true);

            $this->privateMessageRepository->save($replyPm);
        }

        $game->addInformation(_('Die Nachricht wurde abgeschickt'));

        $game->setView(ShowPmCategory::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
