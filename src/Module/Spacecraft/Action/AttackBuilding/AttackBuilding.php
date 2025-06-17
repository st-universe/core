<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\AttackBuilding;

use Override;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManager;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\AttackerProviderFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\EnergyWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\ProjectileWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class AttackBuilding implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ATTACK_BUILDING';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private ColonyRepositoryInterface $colonyRepository,
        private InteractionCheckerInterface $interactionChecker,
        private EnergyWeaponPhaseInterface $energyWeaponPhase,
        private ProjectileWeaponPhaseInterface $projectileWeaponPhase,
        private PrivateMessageSenderInterface $privateMessageSender,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever,
        private ColonyFunctionManagerInterface $colonyFunctionManager,
        private AttackerProviderFactoryInterface $attackerProviderFactory,
        private BattlePartyFactoryInterface $battlePartyFactory,
        private MessageFactoryInterface $messageFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $colonyId = request::getIntFatal('colid');
        $fieldId = request::getIntFatal('field');


        $field = $this->planetFieldRepository->find($fieldId);
        $colony = $this->colonyRepository->find($colonyId);
        if ($field === null || $colony === null) {
            $game->addInformation(_('Feld oder Kolonie nicht vorhanden'));
            return;
        }

        if ($field->getFieldId() >= 80) {
            $game->addInformation(_('Der Untergrund kann nicht attackiert werden'));
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

        if ($epsSystem === null || $epsSystem->getEps() == 0) {
            $game->addInformation(_('Keine Energie vorhanden'));
            return;
        }
        if ($ship->getCondition()->isDisabled()) {
            $game->addInformation(_('Das Schiff ist kampfunfähig'));
            return;
        }

        if ($colony !== $field->getHost()) {
            return;
        }
        if (!$this->interactionChecker->checkPosition($colony, $ship)) {
            return;
        }

        $isFleetAttack = $wrapper instanceof ShipWrapperInterface
            && $wrapper->getFleetWrapper() !== null
            && $wrapper->get()->isFleetLeader();

        $incomingBattleParty = $this->battlePartyFactory->createIncomingBattleParty($wrapper);

        $informations = new InformationWrapper();

        // DEFENDING FLEETS
        foreach ($colony->getDefenders() as $fleet) {
            $colonyDefendingBattleParty = $this->battlePartyFactory->createColonyDefendingBattleParty($fleet->getLeadShip());

            $this->alertReactionFacade->performAttackCycle(
                $colonyDefendingBattleParty,
                $incomingBattleParty,
                $informations
            );
        }

        // ORBITAL DEFENSE
        $count = $this->colonyFunctionManager->getBuildingWithFunctionCount(
            $colony,
            BuildingFunctionEnum::ENERGY_PHALANX,
            [ColonyFunctionManager::STATE_ENABLED]
        );
        $defendingPhalanx =  $this->attackerProviderFactory->createEnergyPhalanxAttacker($colony);

        $messageCollection = $this->messageFactory->createMessageCollection();

        for ($i = 0; $i < $count; $i++) {

            if ($incomingBattleParty->isDefeated()) {
                break;
            }
            $this->energyWeaponPhase->fire(
                $defendingPhalanx,
                $incomingBattleParty,
                SpacecraftAttackCauseEnum::COLONY_DEFENSE,
                $messageCollection
            );
        }

        $count = $this->colonyFunctionManager->getBuildingWithFunctionCount(
            $colony,
            BuildingFunctionEnum::PARTICLE_PHALANX,
            [ColonyFunctionManager::STATE_ENABLED]
        );
        $defendingPhalanx = $this->attackerProviderFactory->createProjectilePhalanxAttacker($colony);

        for ($i = 0; $i < $count; $i++) {
            if ($incomingBattleParty->isDefeated()) {
                break;
            }
            $this->projectileWeaponPhase->fire(
                $defendingPhalanx,
                $incomingBattleParty,
                SpacecraftAttackCauseEnum::COLONY_DEFENSE,
                $messageCollection
            );
        }

        $informations->addInformationWrapper($messageCollection->getInformationDump());

        // OFFENSE OF ATTACKING SHIPS
        $isOrbitField = $this->planetFieldTypeRetriever->isOrbitField($field);
        $count = $this->colonyFunctionManager->getBuildingWithFunctionCount(
            $colony,
            BuildingFunctionEnum::ANTI_PARTICLE,
            [ColonyFunctionManager::STATE_ENABLED]
        ) * 6;


        /** @var ShipWrapperInterface $attackerWrapper*/
        foreach ($incomingBattleParty->getActiveMembers(true, true) as $attackerWrapper) {
            $spacecraftAttacker = $this->attackerProviderFactory->createSpacecraftAttacker($attackerWrapper);

            if ($isOrbitField) {
                $informations->addInformationWrapper($this->energyWeaponPhase->fireAtBuilding($spacecraftAttacker, $field, $isOrbitField));

                if ($field->getIntegrity() === 0) {
                    break;
                }
            }
            $informations->addInformationWrapper($this->projectileWeaponPhase->fireAtBuilding($spacecraftAttacker, $field, $isOrbitField, $count));

            if ($field->getIntegrity() === 0) {
                break;
            }
        }

        $this->colonyRepository->save($colony);

        $pm = sprintf(
            _("Kampf in Sektor %s, Kolonie %s\n%s"),
            $ship->getSectorString(),
            $colony->getName(),
            $informations->getInformationsAsString()
        );
        $this->privateMessageSender->send(
            $userId,
            $colony->getUserId(),
            $pm,
            PrivateMessageFolderTypeEnum::SPECIAL_COLONY
        );

        if ($ship->getCondition()->isDestroyed()) {
            $game->addInformationWrapper($informations);
            return;
        }
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        if ($isFleetAttack) {
            $game->addInformation(_("Angriff durchgeführt"));
            $game->setTemplateVar('FIGHT_RESULTS', $informations->getInformations());
        } else {
            $game->addInformationWrapper($informations);
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
