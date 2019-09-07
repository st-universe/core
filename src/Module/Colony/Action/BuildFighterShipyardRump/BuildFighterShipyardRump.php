<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildFighterShipyardRump;

use request;
use Shiprump;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class BuildFighterShipyardRump implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_BUILD_FIGHTER_SHIPYARD_RUMP';

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

        $rump_id = request::postInt('buildrump');


        $available_rumps = Shiprump::getBuildableRumpsByBuildingFunction(
            $userId,
            BUILDING_FUNCTION_FIGHTER_SHIPYARD
        );

        if (!array_key_exists($rump_id, $available_rumps)) {
            return;
        }
        /**
         * @var Shiprump $rump
         */
        $rump = ResourceCache()->getObject('rump', $rump_id);
        if ($rump->getEpsCost() > $colony->getEps()) {
            $game->addInformationf(
                _('Es wird %d Energie benötigt - Vorhanden ist nur %d'),
                $rump->getEpsCost(),
                $colony->getEps()
            );
            return;
        }
        $storage = $colony->getStorage();
        foreach ($rump->getBuildingCosts() as $cost) {
            $stor = $storage[$cost->getCommodityId()] ?? null;

            if ($stor === null) {
                $game->addInformationf(
                    _('Es wird %d %s benötigt'),
                    $cost->getAmount(),
                    $cost->getCommodity()->getName()
                );
                return;
            }
            if ($stor->getAmount() < $cost->getAmount()) {
                $game->addInformationf(
                    _('Es wird %d %s benötigt - Vorhanden ist nur %d'),
                    $cost->getAmount(),
                    $cost->getCommodity()->getName(),
                    $stor->getAmount()
                );
                return;
            }
        }
        foreach ($rump->getBuildingCosts() as $cost) {
            $colony->lowerStorage($cost->getCommodityId(), $cost->getAmount());
        }
        $colony->lowerEps($rump->getEpsCost());
        $colony->upperStorage($rump->getGoodId(), 1);
        $colony->save();
        $game->addInformationf(_('%s-Klasse wurde gebaut'), $rump->getName());
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
