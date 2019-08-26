<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\WritePm;

use PM;
use PMCategory;
use PMData;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowPmCategory\ShowPmCategory;
use User;

final class WritePm implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_WRITE_PM';

    private $writePmRequest;

    public function __construct(
        WritePmRequestInterface $writePmRequest
    ) {
        $this->writePmRequest = $writePmRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $text = $this->writePmRequest->getText();
        $recipientId = $this->writePmRequest->getRecipientId();
        $userId = $game->getUser()->getId();

        $recipient = User::getUserById($recipientId);
        if (!$recipient) {
            $this->addInformation("Dieser Siedler existiert nicht");
            return;
        }
        if ($recipient->getId() == $userId) {
            $this->addInformation("Du kannst keine Nachricht an Dich selbst schreiben");
            return;
        }
        if ($recipient->isOnIgnoreList($userId)) {
            $this->addInformation("Der Siedler ignoriert Dich");
            return;
        }

        if (strlen($text) < 5) {
            $this->addInformation("Der Text ist zu kurz");
            return;
        }
        $cat = PMCategory::getOrGenSpecialCategory(PM_SPECIAL_MAIN, $recipient->getId());

        $pm = new PMData();
        $pm->setText($text);
        $pm->setRecipientId($recipient->getId());
        $pm->setSenderId($userId);
        $pm->setDate(time());
        $pm->setCategoryId($cat->getId());
        $pm->copyPM();
        $pm->save();

        $repid = $this->writePmRequest->getReplyPmId();
        $replyPm = PM::getPMById($repid);
        if ($replyPm && $replyPm->getRecipientId() == $userId) {
            $replyPm->setReplied(1);
            $replyPm->save();
        }

        $game->addInformation(_('Die Nachricht wurde abgeschickt'));

        $game->setView(ShowPmCategory::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
