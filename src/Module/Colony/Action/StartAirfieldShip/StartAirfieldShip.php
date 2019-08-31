<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\StartAirfieldShip;

use BuildplanHangar;
use request;
use Ship;
use ShipCrew;
use Shiprump;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class StartAirfieldShip implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_START_AIRFIELD_SHIP';

    private $colonyLoader;

    private $torpedoTypeRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $rump_id = request::postInt('startrump');
        $available_rumps = Shiprump::getBy(
            sprintf(
                'WHERE id IN (SELECT rump_id FROM stu_rumps_user WHERE user_id = %d) AND good_id IN (SELECT goods_id FROM stu_colonies_storage WHERE colonies_id=%d) GROUP BY id',
                $userId,
                $colony->getId()
            )
        );

        if (!array_key_exists($rump_id, $available_rumps)) {
            return;
        }
        /**
         * @var Shiprump $rump
         */
        $rump = ResourceCache()->getObject('rump', $rump_id);
        if ($rump->canColonize() && Ship::countInstances(
                sprintf(
                    'WHERE user_id = %d AND rumps_id IN (SELECT rumps_id FROM stu_rumps_specials WHERE special = %d)',
                    $userId,
                    RUMP_SPECIAL_COLONIZE
                )
            ) > 0) {
            $game->addInformation(_('Es kann nur ein Schiff mit Kolonisierungsfunktion genutzt werden'));
            return;
        }
        $hangar = BuildplanHangar::getBy(sprintf('WHERE rump_id = %d', $rump_id));

        if ($hangar->getBuildplan()->getCrew() > $user->getFreeCrewCount()) {
            $game->addInformation(_('Es ist für den Start des Schiffes nicht genügend Crew vorhanden'));
            return;
        }
        if (Ship::countInstances('WHERE user_id=' . $userId) >= 10) {
            $game->addInformation(_('Im Moment sind nur 10 Schiffe pro Spieler erlaubt'));
            return;
        }
        // XXX starting costs
        if ($colony->getEps() < 10) {
            $game->addInformationf(
                _('Es wird %d Energie benötigt - Vorhanden ist nur %d'),
                10,
                $colony->getEps()
            );
            return;
        }
        $storage = &$colony->getStorage();
        if (!array_key_exists($rump->getGoodId(), $storage)) {
            $game->addInformationf(
                _('Es wird %d %s benötigt'),
                1,
                getGoodName($rump->getGoodId())
            );
            return;
        }

        $ship = Ship::createBy($userId, $rump_id, $hangar->getBuildplanId(), $colony);

        ShipCrew::createByRumpCategory($ship);

        if ($hangar->getDefaultTorpedoTypeId()) {
            $torp = $this->torpedoTypeRepository->find((int) $hangar->getDefaultTorpedoTypeId());
            if ($colony->getStorage()->offsetExists($torp->getGoodId())) {
                $count = $ship->getMaxTorpedos();
                if ($count > $storage[$torp->getGoodId()]->getAmount()) {
                    $count = $storage[$torp->getGoodId()]->getAmount();
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
