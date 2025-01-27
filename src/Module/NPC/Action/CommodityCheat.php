<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use Override;
use request;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\NPC\View\ShowTools\ShowTools;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class CommodityCheat implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_COMMODITY_CHEAT';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(private SpacecraftLoaderInterface $spacecraftLoader, private StorageManagerInterface $storageManager, private CommodityRepositoryInterface $commodityRepository, private NPCLogRepositoryInterface $npcLogRepository, private ColonyRepositoryInterface $colonyRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTools::VIEW_IDENTIFIER);
        $user = $game->getUser();
        $text = '';
        $colony = null;

        // only Admins or NPC can trigger
        if (!$game->isAdmin() && !$game->isNpc()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin/NPC![/color][/b]'));
            return;
        }

        if (!request::getVarByMethod(request::postvars(), 'spacecraftid') && !request::getVarByMethod(request::postvars(), 'colonyid')) {
            $game->addInformation("Es wurde weder Spacecraft noch Kolonie ausgewählt");
            return;
        } else {
            $spacecraftId = request::postInt('spacecraftid');
            $colonyId = request::postInt('colonyid');
            $commodityId = request::postInt('commodityid');
            $amount = request::postInt('amount');
            $reason = request::postString('reason');

            if ($spacecraftId != null && $colonyId != null) {
                $game->addInformation("Es dürfen nicht Spacecraft und Kolonie gleichzeitig ausgewählt sein");
                return;
            }

            if ($spacecraftId != null) {
                $wrapper = $this->spacecraftLoader->find($spacecraftId);

                if ($wrapper === null) {
                    throw new SpacecraftDoesNotExistException(_('Spacecraft does not exist!'));
                }
                $spacecraft = $wrapper->get();
            }

            if ($colonyId != null) {
                $colony = $this->colonyRepository->find($colonyId);
            }

            if ($amount < 1) {
                $game->addInformation("Anzahl muss größer als 0 sein");
                return;
            }

            if ($game->getUser()->isNpc()) {
                if ($reason === '') {
                    $game->addInformation("Grund fehlt");
                    return;
                }
            }

            $commodity = $this->commodityRepository->find($commodityId);

            if ($commodity === null) {
                $game->addInformation("Ungültige Ware");
                return;
            }

            if ($spacecraftId != null) {
                $this->storageManager->upperStorage(
                    $spacecraft,
                    $commodity,
                    $amount
                );
                $text = sprintf(
                    '%s hat dem Spacecraft %s (%d) von Spieler %s (%d) %d %s hinzugefügt. Grund: %s',
                    $user->getName(),
                    $spacecraft->getName(),
                    $spacecraft->getId(),
                    $spacecraft->getUser()->getName(),
                    $spacecraft->getUser()->getId(),
                    $amount,
                    $commodity->getName(),
                    $reason
                );
            }
            if ($colony) {
                $this->storageManager->upperStorage(
                    $colony,
                    $commodity,
                    $amount
                );
                $text = sprintf(
                    '%s hat der Kolonie %s (%d) von Spieler %s (%d) %d %s hinzugefügt. Grund: %s',
                    $user->getName(),
                    $colony->getName(),
                    $colony->getId(),
                    $colony->getUser()->getName(),
                    $colony->getUser()->getId(),
                    $amount,
                    $commodity->getName(),
                    $reason
                );
            }

            if ($game->getUser()->isNpc()) {
                $this->createEntry($text, $user->getId());
            }
            $game->addInformation("Waren hinzugefügt");
        }
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
