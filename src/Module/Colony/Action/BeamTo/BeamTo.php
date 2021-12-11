<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BeamTo;

use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class BeamTo implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMTO';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ShipLoaderInterface $shipLoader
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($colony->getEps() == 0) {
            $game->addInformation(_('Keine Energie vorhanden'));
            return;
        }
        $target = $this->shipLoader->find(request::postIntFatal('target'));
        if ($target === null) {
            return;
        }

        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            return;
        }

        if ($target->getShieldState() && $target->getUserId() != $userId) {
            $game->addInformationf(_('Die %s hat die Schilde aktiviert'), $target->getName());
            return;
        }
        if ($target->getMaxStorage() <= $target->getStorageSum()) {
            $game->addInformationf(_('Der Lagerraum der %s ist voll'), $target->getName());
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');
        $storage = $colony->getStorage();
        if ($storage->isEmpty()) {
            $game->addInformation(_('Keine Waren zum Beamen vorhanden'));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $game->addInformation(_('Es wurde keine Waren zum Beamen ausgewählt'));
            return;
        }
        if ($target->isOwnedByCurrentUser()) {
            $link = "ship.php?SHOW_SHIP=1&id=" . $target->getId();

            $game->addInformationfWithLink(
                _('Die Kolonie %s hat folgende Waren zur %s transferiert'),
                $link,
                $colony->getName(),
                $target->getName()
            );
        } else {
            $game->addInformationf(
                _('Die Kolonie %s hat folgende Waren zur %s transferiert'),
                $colony->getName(),
                $target->getName()
            );
        }

        foreach ($goods as $key => $value) {
            $value = (int) $value;
            if ($colony->getEps() < 1) {
                break;
            }
            if (!array_key_exists($key, $gcount) || $gcount[$key] < 1) {
                continue;
            }
            $good = $storage[$value] ?? null;
            if ($good === null) {
                continue;
            }
            $count = $gcount[$key];
            if ($count == "m") {
                $count = $good->getAmount();
            } else {
                $count = (int)$count;
            }
            if ($count < 1) {
                continue;
            }
            if (!$good->getGood()->isBeamable()) {
                $game->addInformationf(_('%s ist nicht beambar'), $good->getGood()->getName());
                continue;
            }
            if ($target->getStorageSum() >= $target->getMaxStorage()) {
                break;
            }
            if ($count > $good->getAmount()) {
                $count = $good->getAmount();
            }

            $transferAmount = $good->getCommodity()->getTransferCount() * $colony->getBeamFactor();

            if (ceil($count / $transferAmount) > $colony->getEps()) {
                $count = $colony->getEps() * $transferAmount;
            }
            if ($target->getStorageSum() + $count > $target->getMaxStorage()) {
                $count = $target->getMaxStorage() - $target->getStorageSum();
            }

            $eps_usage = ceil($count / $transferAmount);
            $game->addInformationf(
                _('%d %s (Energieverbrauch: %d)'),
                $count,
                $good->getGood()->getName(),
                $eps_usage
            );
            $colony->lowerEps((int)ceil($count / $transferAmount));

            $this->shipStorageManager->upperStorage($target, $good->getGood(), $count);
            $this->colonyStorageManager->lowerStorage($colony, $good->getGood(), $count);
        }
        if ($target->getUserId() != $userId) {
            $game->sendInformation($target->getUserId(), $userId, PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE);
        }

        $this->colonyRepository->save($colony);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
