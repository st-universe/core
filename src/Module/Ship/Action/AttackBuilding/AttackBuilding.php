<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AttackBuilding;

use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManager;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Lib\InformationWrapper;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageInterface;
use Stu\Module\Ship\Lib\Battle\Provider\AttackerProviderFactoryInterface;
use Stu\Module\Ship\Lib\Battle\Weapon\EnergyWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Battle\Weapon\ProjectileWeaponPhaseInterface;
use Stu\Module\Ship\Lib\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class AttackBuilding implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ATTACK_BUILDING';

    private ShipLoaderInterface $shipLoader;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private InteractionCheckerInterface $interactionChecker;

    private FightLibInterface $fightLib;

    private EnergyWeaponPhaseInterface $energyWeaponPhase;

    private ProjectileWeaponPhaseInterface $projectileWeaponPhase;

    private PrivateMessageSenderInterface $privateMessageSender;

    private AlertRedHelperInterface $alertRedHelper;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private InformationWrapper $informations;

    private PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    private AttackerProviderFactoryInterface $attackerProviderFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyRepositoryInterface $colonyRepository,
        InteractionCheckerInterface $interactionChecker,
        FightLibInterface $fightLib,
        EnergyWeaponPhaseInterface $energyWeaponPhase,
        ProjectileWeaponPhaseInterface $projectileWeaponPhase,
        PrivateMessageSenderInterface $privateMessageSender,
        AlertRedHelperInterface $alertRedHelper,
        PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever,
        ColonyFunctionManagerInterface $colonyFunctionManager,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        AttackerProviderFactoryInterface $attackerProviderFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyRepository = $colonyRepository;
        $this->interactionChecker = $interactionChecker;
        $this->fightLib = $fightLib;
        $this->energyWeaponPhase = $energyWeaponPhase;
        $this->projectileWeaponPhase = $projectileWeaponPhase;
        $this->privateMessageSender = $privateMessageSender;
        $this->alertRedHelper = $alertRedHelper;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->planetFieldTypeRetriever = $planetFieldTypeRetriever;
        $this->colonyFunctionManager = $colonyFunctionManager;
        $this->attackerProviderFactory = $attackerProviderFactory;

        $this->informations = new InformationWrapper();
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
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

        $ship = $wrapper->get();

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($colony->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();

        if ($epsSystem->getEps() == 0) {
            $game->addInformation(_('Keine Energie vorhanden'));
            return;
        }
        if ($ship->isDisabled()) {
            $game->addInformation(_('Das Schiff ist kampfunfähig'));
            return;
        }

        if ($colony->getId() != $field->getColonyId()) {
            return;
        }
        if (!$this->interactionChecker->checkColonyPosition($colony, $ship)) {
            return;
        }

        $fleet = false;
        if ($ship->isFleetLeader()) {
            $attacker = $this->shipWrapperFactory->wrapShips($ship->getFleet()->getShips()->toArray());
            $fleet = true;
        } else {
            $attacker = $this->shipWrapperFactory->wrapShips([$ship->getId() => $ship]);
        }

        foreach ($attacker as $attackship) {
            $this->informations->addInformationMerge($this->fightLib->ready($attackship));
        }

        // DEFENDING FLEETS
        foreach ($colony->getDefenders() as $fleet) {
            $this->alertRedHelper->performAttackCycle($fleet->getLeadShip(), $ship, $this->informations, true);
        }

        // ORBITAL DEFENSE
        $count = $this->colonyFunctionManager->getBuildingWithFunctionCount(
            $colony,
            BuildingEnum::BUILDING_FUNCTION_ENERGY_PHALANX,
            [ColonyFunctionManager::STATE_ENABLED]
        );
        $defendingPhalanx =  $this->attackerProviderFactory->getEnergyPhalanxAttacker($colony);

        for ($i = 0; $i < $count; $i++) {
            $attackerPool = $this->fightLib->filterInactiveShips($attacker);

            if (count($attackerPool) === 0) {
                break;
            }
            $this->addFightMessageMerge($this->energyWeaponPhase->fire($defendingPhalanx, $attackerPool));
        }

        $count = $this->colonyFunctionManager->getBuildingWithFunctionCount(
            $colony,
            BuildingEnum::BUILDING_FUNCTION_PARTICLE_PHALANX,
            [ColonyFunctionManager::STATE_ENABLED]
        );
        $defendingPhalanx = $this->attackerProviderFactory->getProjectilePhalanxAttacker($colony);

        for ($i = 0; $i < $count; $i++) {
            $attackerPool = $this->fightLib->filterInactiveShips($attacker);

            if (count($attackerPool) === 0) {
                break;
            }
            $this->addFightMessageMerge($this->projectileWeaponPhase->fire($defendingPhalanx, $attackerPool));
        }

        // OFFENSE OF ATTACKING SHIPS
        $isOrbitField = $this->planetFieldTypeRetriever->isOrbitField($field);
        $attackerPool = $this->fightLib->filterInactiveShips($attacker);
        $count = $this->colonyFunctionManager->getBuildingWithFunctionCount(
            $colony,
            BuildingEnum::BUILDING_FUNCTION_ANTI_PARTICLE,
            [ColonyFunctionManager::STATE_ENABLED]
        ) * 6;

        foreach ($attackerPool as $attackerWrapper) {
            $shipAttacker = $this->attackerProviderFactory->getShipAttacker($attackerWrapper);

            if ($isOrbitField) {
                $this->informations->addInformationMerge($this->energyWeaponPhase->fireAtBuilding($shipAttacker, $field, $isOrbitField));

                if ($field->getIntegrity() === 0) {
                    break;
                }
            }
            $this->informations->addInformationMerge($this->projectileWeaponPhase->fireAtBuilding($shipAttacker, $field, $isOrbitField, $count));

            if ($field->getIntegrity() === 0) {
                break;
            }
        }

        $this->colonyRepository->save($colony);

        $pm = sprintf(_('Kampf in Sektor %s, Kolonie %s') . "\n", $ship->getSectorString(), $colony->getName());
        foreach ($this->informations->getInformations() as $value) {
            $pm .= $value . "\n";
        }
        $this->privateMessageSender->send(
            $userId,
            $colony->getUserId(),
            $pm,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
        );

        if ($ship->isDestroyed()) {
            $game->addInformationMerge($this->informations->getInformations());
            return;
        }
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        if ($fleet) {
            $game->addInformation(_("Angriff durchgeführt"));
            $game->setTemplateVar('FIGHT_RESULTS', $this->informations->getInformations());
        } else {
            $game->addInformationMerge($this->informations->getInformations());
            $game->setTemplateVar('FIGHT_RESULTS', null);
        }
    }

    /**
     * @param FightMessageInterface[] $fightMessages
     */
    private function addFightMessageMerge(array $fightMessages): void
    {
        foreach ($fightMessages as $message) {
            $this->informations->addInformationMerge($message->getMessage());
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
