<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BeamTo;

use request;
use Ship;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class BeamTo implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMTO';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
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
        $target = new Ship(request::postIntFatal('target'));
        if ($target->shieldIsActive() && $target->getUserId() != $userId) {
            $game->addInformationf(_('Die %s hat die Schilde aktiviert'), $target->getName());
            return;
        }
        if (!$target->storagePlaceLeft()) {
            $game->addInformationf(_('Der Lagerraum der %s ist voll'), $target->getName());
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');
        $storage = $colony->getStorage();
        if ($storage->count() == 0) {
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
            if ($colony->getEps() < 1) {
                break;
            }
            if (!array_key_exists($key, $gcount) || $gcount[$key] < 1) {
                continue;
            }
            if (!$storage->offsetExists($value)) {
                continue;
            }
            $good = $storage->offsetGet($value);
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
            $colony->lowerEps(ceil($count / $good->getGood()->getTransferCount()));
            $target->upperStorage($value, $count);
            $colony->lowerStorage($value, $count);
            $target->setStorageSum($target->getStorageSum() + $count);
        }
        if ($target->getUserId() != $userId) {
            $game->sendInformation($target->getUserId(), $userId, PM_SPECIAL_TRADE);
        }
        $colony->save();
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
