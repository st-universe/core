<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BeamTo;

use request;
use Stu\Module\Colony\Lib\ColonyStorageManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Ship\Lib\ShipStorageManagerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class BeamTo implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMTO';

    private $colonyLoader;

    private $colonyStorageManager;

    private $colonyRepository;

    private $shipStorageManager;

    private $shipRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipRepository = $shipRepository;
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
        $target = $this->shipRepository->find(request::postIntFatal('target'));
        if ($target === null) {
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
        $game->addInformationf(
            _('Die Kolonie %s hat folgende Waren zur %s transferiert'),
            $colony->getName(),
            $target->getName()
        );
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
                $count = intval($count);
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
            if (ceil($count / $good->getGood()->getTransferCount()) > $colony->getEps()) {
                $count = $colony->getEps() * $good->getGood()->getTransferCount();
            }
            if ($target->getStorageSum() + $count > $target->getMaxStorage()) {
                $count = $target->getMaxStorage() - $target->getStorageSum();
            }

            $eps_usage = ceil($count / $good->getGood()->getTransferCount());
            $game->addInformationf(
                _('%d %s (Energieverbrauch: %d)'),
                $count,
                $good->getGood()->getName(),
                $eps_usage
            );
            $colony->lowerEps((int)ceil($count / $good->getGood()->getTransferCount()));

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
