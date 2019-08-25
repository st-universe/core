<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\StartAirfieldShip;

use BuildplanHangar;
use request;
use Ship;
use ShipCrew;
use Shiprump;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use TorpedoType;

final class StartAirfieldShip implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_START_AIRFIELD_SHIP';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $rump_id = request::postInt('startrump');
        $available_rumps = Shiprump::getBy(
            'WHERE id IN (SELECT rump_id FROM stu_rumps_user WHERE user_id=' . $game->getUser()->getId() . ')
					AND good_id IN (SELECT goods_id FROM stu_colonies_storage WHERE colonies_id=' . $colony->getId() . ') GROUP BY id');

        if (!array_key_exists($rump_id, $available_rumps)) {
            return;
        }
        /**
         * @var Shiprump $rump
         */
        $rump = ResourceCache()->getObject('rump', $rump_id);
        if ($rump->canColonize() && Ship::countInstances('WHERE user_id=' . currentUser()->getId() . ' AND rumps_id IN (SELECT rumps_id FROM stu_rumps_specials WHERE special=' . RUMP_SPECIAL_COLONIZE . ')') > 0) {
            $game->addInformation(_("Es kann nur ein Schiff mit Kolonisierungsfunktion genutzt werden"));
            return;
        }
        $hangar = BuildplanHangar::getBy('WHERE rump_id=' . $rump_id);

        if ($hangar->getBuildplan()->getCrew() > currentUser()->getFreeCrewCount()) {
            $game->addInformation(_("Es ist für den Start des Schiffes nicht genügend Crew vorhanden"));
            return;
        }
        if (Ship::countInstances('WHERE user_id=' . currentUser()->getId()) >= 10) {
            $game->addInformation(_("Im Moment sind nur 10 Schiffe pro Siedler erlaubt"));
            return;
        }
        // XXX starting costs
        if ($colony->getEps() < 10) {
            $game->addInformation(sprintf(_('Es wird %d Energie benötigt - Vorhanden ist nur %d'), 10,
                $colony->getEps()));
            return;
        }
        $storage = &$colony->getStorage();
        if (!array_key_exists($rump->getGoodId(), $storage)) {
            $game->addInformation(sprintf(_('Es wird %d %s benötigt'), 1, getGoodName($rump->getGoodId())));
            $this->rollbackTransaction();
            return;
        }

        $ship = Ship::createBy(currentUser()->getId(), $rump_id, $hangar->getBuildplanId(), $colony);

        ShipCrew::createByRumpCategory($ship);

        if ($hangar->getDefaultTorpedoTypeId()) {
            $torp = new TorpedoType($hangar->getDefaultTorpedoTypeId());
            if ($colony->getStorage()->offsetExists($torp->getGoodId())) {
                $count = $ship->getMaxTorpedos();
                if ($count > $colony->getStorage()->offsetGet($torp->getGoodId())->getAmount()) {
                    $count = $colony->getStorage()->offsetGet($torp->getGoodId())->getAmount();
                }
                $ship->setTorpedoType($torp->getId());
                $ship->setTorpedoCount($count);
                $ship->save();
                $colony->lowerStorage($torp->getGoodId(), $count);
            }
        }
        if ($rump->canColonize()) {
            $ship->setEps($ship->getMaxEps());
            $ship->setWarpcoreLoad($ship->getWarpcoreCapacity());
            $ship->save();
        }
        $colony->lowerEps(10);
        $colony->lowerStorage($rump->getGoodId(), 1);
        $colony->save();

        if ($colony->getSystem()->getDatabaseId()) {
            $game->checkDatabaseItem($colony->getSystem()->getDatabaseId());
        }
        if ($colony->getSystem()->getSystemType()->getDatabaseId()) {
            $game->checkDatabaseItem($colony->getSystem()->getSystemType()->getDatabaseId());
        }
        if ($rump->getDatabaseId()) {
            $game->checkDatabaseItem($rump->getDatabaseId());
        }
        $game->addInformation(_('Das Schiff wurde gestartet'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
