<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use Override;
use request;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\NPC\View\ShowTools\ShowTools;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;

final class CreateHistoryEntry implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_HISTORY_ENTRY';

    public function __construct(private NPCLogRepositoryInterface $npcLogRepository, private EntryCreatorInterface $entryCreator, private LocationRepositoryInterface $locationRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTools::VIEW_IDENTIFIER);
        $user = $game->getUser();

        // only Admins or NPC can trigger
        if (!$game->isAdmin() && !$game->isNpc()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin/NPC![/color][/b]'));
            return;
        }

        $reason = request::postString('reason');
        $historyText = request::postString('historytext');
        $x = request::postIntFatal('x');
        $y = request::postIntFatal('y');
        $layer = request::postIntFatal('layer');

        if (($x !== 0 && $y === 0) || ($x === 0 && $y !== 0)) {
            $game->addInformation("X und Y Koordinaten müssen beide gesetzt oder beide leer sein");
            return;
        }

        $historyTypeId = request::postIntFatal('historytype');
        $historyType = null;

        foreach (HistoryTypeEnum::cases() as $type) {
            if ($type->value === $historyTypeId) {
                $historyType = $type;
                break;
            }
        }

        if ($historyType === null) {
            $game->addInformation("Ungültiger Kategorie-Typ ausgewählt");
            return;
        }

        if ($game->getUser()->isNpc() && $reason === '') {
            $game->addInformation("Grund fehlt");
            return;
        }

        if ($historyText === '' || !$historyText) {
            $game->addInformation("Es wurde kein Text ausgewählt");
            return;
        }

        $location = $x || $y ? $this->locationRepository->getByCoordinates($x, $y, $layer) : null;

        $this->entryCreator->createEntry($historyType, $historyText, $user->getId(), $user->getId(), $location);

        if ($reason !== '') {

            $text = sprintf(
                '%s hat einen History Eintrag in der Kategorie %s erstellt. Grund: %s',
                $user->getName(),
                $historyType->getName(),
                $reason
            );
            $this->createReasonEntry($text, $user->getId());
        }

        $game->addInformation("History Eintrag wurde hinzugefügt");
    }

    private function createReasonEntry(
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
