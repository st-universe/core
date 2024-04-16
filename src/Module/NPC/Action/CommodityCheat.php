<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use request;
use Stu\Exception\ShipDoesNotExistException;
use Stu\Module\NPC\View\ShowTools\ShowTools;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;

final class CommodityCheat implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_COMMODITY_CHEAT';

    private ShipLoaderInterface $shipLoader;

    private ShipStorageManagerInterface $shipStorageManager;

    private CommodityRepositoryInterface $commodityRepository;

    private NPCLogRepositoryInterface $npcLogRepository;


    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipStorageManagerInterface $shipStorageManager,
        CommodityRepositoryInterface $commodityRepository,
        NPCLogRepositoryInterface $npcLogRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipStorageManager = $shipStorageManager;
        $this->commodityRepository = $commodityRepository;
        $this->npcLogRepository = $npcLogRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTools::VIEW_IDENTIFIER);
        $user = $game->getUser();

        // only Admins or NPC can trigger
        if (!$game->isAdmin() && !$game->isNPC()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin/NPC![/color][/b]'));
            return;
        }

        if (!request::getVarByMethod(request::postvars(), 'shipid')) {
            $game->addInformation("Kein Schiff ausgewählt");
            return;
        } else {
            $shipId = request::postInt('shipid');
            $commodityId = request::postInt('commodityid');
            $amount = request::postInt('amount');
            $reason = request::postString('reason');


            $wrapper = $this->shipLoader->find($shipId);

            if ($wrapper === null) {
                throw new ShipDoesNotExistException(_('Ship does not exist!'));
            } else {
                $ship = $wrapper->get();
            }

            if ($amount < 1) {
                $game->addInformation("Anzahl muss größer als 0 sein");
                return;
            }

            if ($reason === '') {
                $game->addInformation("Grund fehlt");
                return;
            }

            $commodity = $this->commodityRepository->find($commodityId);

            if ($commodity === null) {
                $game->addInformation("Ungültige Ware");
                return;
            }

            $this->shipStorageManager->upperStorage(
                $ship,
                $commodity,
                $amount
            );

            $text = sprintf(
                '%s hat dem Schiff %s (%d) von Spieler %s (%d) %d %s hinzugefügt. Grund: %s',
                $user->getName(),
                $ship->getName(),
                $ship->getId(),
                $ship->getUser()->getName(),
                $ship->getUser()->getId(),
                $amount,
                $commodity->getName(),
                $reason
            );

            $this->createEntry($text, $user->getId());
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

    public function performSessionCheck(): bool
    {
        return true;
    }
}
