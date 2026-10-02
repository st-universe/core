<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\NPC\View\ShowTools\ShowTools;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PrestigeCheat implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PRESTIGE_CHEAT';

    public function __construct(
        private CreatePrestigeLogInterface $createPrestigeLog,
        private UserRepositoryInterface $userRepository,
        private NPCLogRepositoryInterface $npcLogRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowTools::VIEW_IDENTIFIER);
        $currentUser = $context->getUser();
        $userId = request::postInt('userid');
        if ($userId === 0) {
            $context->getInfo()->addInformation('Es wurde kein User ausgewählt');
            return;
        }

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            $context->getInfo()->addInformation('User existiert nicht');
            return;
        }

        $amountStr = request::postString('prestigeamount');
        if ($amountStr === '' || $amountStr === false) {
            $context->getInfo()->addInformation('Prestigewert fehlt');
            return;
        }

        $amount = (int) $amountStr;
        if ($amount === 0) {
            $context->getInfo()->addInformation('Prestigewert muss ungleich 0 sein');
            return;
        }

        $description = request::postString('prestigedescription');
        if ($description === '' || $description === false) {
            $context->getInfo()->addInformation('Beschreibung fehlt');
            return;
        }

        $reason = request::postString('reason');
        if ($context->getUser()->isNpc() && $reason === '') {
            $context->getInfo()->addInformation('Grund fehlt');
            return;
        }

        $this->createPrestigeLog->createLog(
            $amount,
            $description,
            $user,
            time()
        );

        $text = sprintf(
            '%s hat dem Spieler %s (%d) %d Prestige %s. Grund: %s',
            $currentUser->getName(),
            $user->getName(),
            $user->getId(),
            abs($amount),
            $amount > 0 ? 'hinzugefügt' : 'abgezogen',
            $reason
        );

        if ($context->getUser()->isNpc()) {
            $this->createEntry($text, $currentUser->getId());
        }

        $context->getInfo()->addInformation(sprintf(
            'Prestige wurde %s',
            $amount > 0 ? 'hinzugefügt' : 'abgezogen'
        ));
    }

    private function createEntry(
        string $text,
        int $UserId
    ): void {
        $entry = $this->npcLogRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($UserId);
        $entry->setDate(time());
        $entry->setAdminView(false);

        $this->npcLogRepository->save($entry);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
