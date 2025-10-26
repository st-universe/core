<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use request;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;

final class SaveWelcomeMessage implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SAVE_WELCOME_MESSAGE';

    public function __construct(
        private FactionRepositoryInterface $factionRepository,
        private NPCLogRepositoryInterface $npcLogRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        if (!$game->isAdmin() && !$game->isNpc()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin/NPC![/color][/b]'));
            return;
        }

        $user = $game->getUser();
        $factionId = $user->getFactionId();
        $welcomeMessage = request::postString('welcomemessage');


        if ($welcomeMessage === false) {
            $welcomeMessage = '';
        }

        $faction = $this->factionRepository->find($factionId);
        if ($faction === null) {
            throw new AccessViolationException();
        }

        $oldMessage = $faction->getWelcomeMessage() ?? '';
        $faction->setWelcomeMessage($welcomeMessage);

        $this->factionRepository->save($faction);

        if ($game->getUser()->isNpc()) {
            $this->createLogEntry($oldMessage, $welcomeMessage, $userId, $game->getUser()->getName(), $faction->getName());
        }

        $game->getInfo()->addInformation(_('Die Willkommensnachricht wurde gespeichert'));
    }

    private function createLogEntry(string $oldMessage, string $newMessage, int $userId, string $userName, string $factionName): void
    {
        $logText = sprintf(
            '%s hat die Willkommensnachricht der Fraktion %s geändert.',
            $userName,
            $factionName
        );

        $entry = $this->npcLogRepository->prototype();
        $entry->setText($logText);
        $entry->setSourceUserId($userId);
        $entry->setDate(time());

        $this->npcLogRepository->save($entry);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
