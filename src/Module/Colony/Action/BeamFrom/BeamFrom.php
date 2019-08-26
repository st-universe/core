<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BeamFrom;

use request;
use Ship;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class BeamFrom implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMFROM';

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
        if (!$colony->storagePlaceLeft()) {
            $game->addInformation(_('Der Lagerraum der %s ist voll'), $colony->getName());
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');
        if ($target->getStorage()->count() == 0) {
            $game->addInformation(_('Keine Waren zum Beamen vorhanden'));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $game->addInformation(_('Es wurde keine Waren zum Beamen ausgewählt'));
            return;
        }
        $game->addInformationf(
            _('Die Kolonie %s hat folgende Waren von der %s transferiert'),
            $colony->getName(),
            $target->getName()
        );
        foreach ($goods as $key => $value) {
            if ($colony->getEps() < 1) {
                break;
            }
            if ($colony->getStorageSum() >= $colony->getMaxStorage()) {
                break;
            }
            if (!array_key_exists($key, $gcount) || $gcount[$key] < 1) {
                continue;
            }
            if (!$target->getStorage()->offsetExists($value)) {
                continue;
            }
            $count = $gcount[$key];
            $good = $target->getStorage()->offsetGet($value);
            if (!$good->getGood()->isBeamable()) {
                $game->addInformationf(_('%s ist nicht beambar'), $good->getGood()->getName());
                continue;
            }
            if ($count == "m") {
                $count = $good->getAmount();
            } else {
                $count = intval($count);
            }
            if ($count < 1) {
                continue;
            }
            if ($count > $good->getAmount()) {
                $count = $good->getAmount();
            }
            if (ceil($count / $good->getGood()->getTransferCount()) > $colony->getEps()) {
                $count = $colony->getEps() * $good->getGood()->getTransferCount();
            }
            if ($colony->getStorageSum() + $count > $colony->getMaxStorage()) {
                $count = $colony->getMaxStorage() - $colony->getStorageSum();
            }

            $eps_usage = ceil($count / $good->getGood()->getTransferCount());
            $game->addInformationf(_('%d %s (Energieverbrauch: %d)'),
                $count,
                $good->getGood()->getName(),
                $eps_usage
            );

            $target->lowerStorage($value, $count);
            $colony->upperStorage($value, $count);
            $colony->lowerEps(ceil($count / $good->getGood()->getTransferCount()));
            $colony->setStorageSum($colony->getStorageSum() + $count);
        }
        if ($target->getUser() != $userId) {
            $game->sendInformation($target->getUserId(), $userId, PM_SPECIAL_TRADE);
        }
        $colony->save();
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
