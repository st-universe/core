<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
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

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTools::VIEW_IDENTIFIER);
        $currentUser = $game->getUser();

        // only Admins or NPC can trigger
        if (!$game->isAdmin() && !$game->isNpc()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin/NPC![/color][/b]'));
            return;
        }

        $userId = request::postInt('userid');
        if ($userId === 0) {
            $game->getInfo()->addInformation("Es wurde kein User ausgewählt");
            return;
        }

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            $game->getInfo()->addInformation("User existiert nicht");
            return;
        }

        $amountStr = request::postString('prestigeamount');
        if ($amountStr === '' || $amountStr === false) {
            $game->getInfo()->addInformation("Prestigewert fehlt");
            return;
        }

        $amount = (int)$amountStr;
        if ($amount === 0) {
            $game->getInfo()->addInformation("Prestigewert muss ungleich 0 sein");
            return;
        }

        $description = request::postString('prestigedescription');
        if ($description === '' || $description === false) {
            $game->getInfo()->addInformation("Beschreibung fehlt");
            return;
        }

        $description = (string)$description;

        $reason = request::postString('reason');
        if ($game->getUser()->isNpc() && $reason === '') {
            $game->getInfo()->addInformation("Grund fehlt");
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

        if ($game->getUser()->isNpc()) {
            $this->createEntry($text, $currentUser->getId());
        }

        $game->getInfo()->addInformation(sprintf(
            "Prestige wurde %s",
            $amount > 0 ? "hinzugefügt" : "abgezogen"
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

        $this->npcLogRepository->save($entry);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
