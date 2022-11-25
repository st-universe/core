<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AttackBuilding;

use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\Battle\EnergyWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\ProjectileWeaponPhaseInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class AttackBuilding implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ATTACK_BUILDING';

    private ShipLoaderInterface $shipLoader;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private PositionCheckerInterface $positionChecker;

    private FightLibInterface $fightLib;

    private EnergyWeaponPhaseInterface $energyWeaponPhase;

    private ProjectileWeaponPhaseInterface $projectileWeaponPhase;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ModuleRepositoryInterface $moduleRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private AlertRedHelperInterface $alertRedHelper;

    private array $messages = [];

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyRepositoryInterface $colonyRepository,
        PositionCheckerInterface $positionChecker,
        FightLibInterface $fightLib,
        EnergyWeaponPhaseInterface $energyWeaponPhase,
        ProjectileWeaponPhaseInterface $projectileWeaponPhase,
        PrivateMessageSenderInterface $privateMessageSender,
        ModuleRepositoryInterface $moduleRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        AlertRedHelperInterface $alertRedHelper
    ) {
        $this->shipLoader = $shipLoader;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyRepository = $colonyRepository;
        $this->positionChecker = $positionChecker;
        $this->fightLib = $fightLib;
        $this->energyWeaponPhase = $energyWeaponPhase;
        $this->projectileWeaponPhase = $projectileWeaponPhase;
        $this->privateMessageSender = $privateMessageSender;
        $this->moduleRepository = $moduleRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->alertRedHelper = $alertRedHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $colonyId = (int) request::getIntFatal('colid');
        $fieldId = (int) request::getIntFatal('field');


        $field = $this->planetFieldRepository->find($fieldId);
        if ($field->getFieldId() >= 80) {
            $game->addInformation(_('Der Untergrund kann nicht attackiert werden'));
            return;
        }

        $colony = $this->colonyRepository->find($colonyId);
        if ($field === null || $colony === null) {
            $game->addInformation(_('Feld oder Kolonie nicht vorhanden'));
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($colony->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            return;
        }

        if ($ship->getEps() == 0) {
            $game->addInformation(_('Keine Energie vorhanden'));
            return;
        }
        if ($ship->getDisabled()) {
            $game->addInformation(_('Das Schiff ist kampfunfähig'));
            return;
        }

        if ($colony->getId() != $field->getColonyId()) {
            return;
        }
        if (!$this->positionChecker->checkColonyPosition($colony, $ship)) {
            return;
        }

        $fleet = false;
        if ($ship->isFleetLeader()) {
            $attacker = $ship->getFleet()->getShips()->toArray();
            $fleet = true;
        } else {
            $attacker = [$ship->getId() => $ship];
        }

        foreach ($attacker as $attackship) {
            $this->addMessageMerge($this->fightLib->ready($attackship));
        }

        // DEFENDING FLEETS
        $informations = [];
        foreach ($colony->getDefenders() as $fleet) {
            $this->alertRedHelper->performAttackCycle($fleet->getLeadShip(), $ship, $informations, true);
        }
        $this->addMessageMerge($informations);

        // ORBITAL DEFENSE
        $count = $colony->getBuildingWithFunctionCount(BuildingEnum::BUILDING_FUNCTION_ENERGY_PHALANX);
        $defendingPhalanx = new EnergyPhalanx($colony, $this->moduleRepository);

        for ($i = 0; $i < $count; $i++) {
            $attackerPool = $this->fightLib->filterInactiveShips($attacker);

            if (count($attackerPool) === 0) {
                break;
            }
            $this->addMessageMerge($this->energyWeaponPhase->fire($defendingPhalanx, $attackerPool));
        }

        $count = $colony->getBuildingWithFunctionCount(BuildingEnum::BUILDING_FUNCTION_PARTICLE_PHALANX);
        $defendingPhalanx = new ProjectilePhalanx($colony, $this->moduleRepository->find(2), $this->colonyStorageManager);

        for ($i = 0; $i < $count; $i++) {
            $attackerPool = $this->fightLib->filterInactiveShips($attacker);

            if (count($attackerPool) === 0) {
                break;
            }
            $this->addMessageMerge($this->projectileWeaponPhase->fire($defendingPhalanx, $attackerPool));
        }

        // OFFENSE OF ATTACKING SHIPS
        $isMoon = $colony->getColonyClass()->isMoon();

        //TODO dynamic!!
        $isOrbitField = $isMoon ? $field->getFieldId() < 14 : $field->getFieldId() < 20;
        $attackerPool = $this->fightLib->filterInactiveShips($attacker);
        $count = $colony->getBuildingWithFunctionCount(BuildingEnum::BUILDING_FUNCTION_ANTI_PARTICLE) * 6;

        foreach ($attackerPool as $attacker) {
            if ($isOrbitField) {
                $this->addMessageMerge($this->energyWeaponPhase->fireAtBuilding($attacker, $field, $isOrbitField));

                if ($field->getIntegrity() === 0) {
                    break;
                }
            }
            $this->addMessageMerge($this->projectileWeaponPhase->fireAtBuilding($attacker, $field, $isOrbitField, $count));

            if ($field->getIntegrity() === 0) {
                break;
            }
        }

        $this->colonyRepository->save($colony);

        $pm = sprintf(_('Kampf in Sektor %s, Kolonie %s') . "\n", $ship->getSectorString(), $colony->getName());
        foreach ($this->messages as $value) {
            $pm .= $value . "\n";
        }
        $this->privateMessageSender->send(
            $userId,
            (int) $colony->getUserId(),
            $pm,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
        );

        if ($ship->getIsDestroyed()) {

            $game->addInformationMerge($this->messages);
            return;
        }
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        if ($fleet) {
            $game->addInformation(_("Angriff durchgeführt"));
            $game->setTemplateVar('FIGHT_RESULTS', $this->messages);
        } else {
            $game->addInformationMerge($this->messages);
            $game->setTemplateVar('FIGHT_RESULTS', null);
        }
    }

    private function addMessageMerge($msg): void
    {
        $this->messages = array_merge($this->messages, $msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
