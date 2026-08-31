<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\AttackBuilding;

use Psr\EventDispatcher\EventDispatcherInterface;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManager;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Crew\Skill\Event\CrewExperienceEvent;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\AttackerProviderFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\EnergyWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\ProjectileWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Colony;
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
        private MessageFactoryInterface $messageFactory,
        private PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $colonyId = request::getIntFatal('colonyid');
        $fieldId = request::getIntFatal('field');

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUserAndTargetUser(
            request::indInt('id'),
            $userId,
            $this->colonyRepository->getUserIdOfColony($colonyId)
        );

        $colony = $this->colonyRepository->find($colonyId);
        if ($colony === null) {
            $game->getInfo()->addInformation(_('Feld oder Kolonie nicht vorhanden'));
            return;
        }

        $field = $this->planetFieldRepository->find($fieldId);
        if ($field === null) {
            $game->getInfo()->addInformation(_('Feld oder Kolonie nicht vorhanden'));
            return;
        }

        if ($field->getFieldId() >= 80) {
            $game->getInfo()->addInformation(_('Der Untergrund kann nicht attackiert werden'));
            return;
        }

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        if ($field->getBuilding() === null) {
            $game->getInfo()->addInformation(_('Gebäude nicht vorhanden'));
            return;
        }

        $ship = $wrapper->get();

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($colony->getUser()->isVacationRequestOldEnough()) {
            $game->getInfo()->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();

        if ($epsSystem === null || $epsSystem->getEps() == 0) {
            $game->getInfo()->addInformation(_('Keine Energie vorhanden'));
            return;
        }
        if ($ship->getCondition()->isDisabled()) {
            $game->getInfo()->addInformation(_('Das Schiff ist kampfunfähig'));
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
            $epsBeforeAttack = $attackerWrapper->getEpsSystemData()?->getEps();

            if ($isOrbitField) {
                $informations->addInformationWrapper($this->energyWeaponPhase->fireAtBuilding($spacecraftAttacker, $field, $isOrbitField));

                if ($field->getIntegrity() === 0) {
                    $this->awardExperienceForForeignColonyAttack($attackerWrapper, $colony, $epsBeforeAttack);
                    break;
                }
            }
            $informations->addInformationWrapper($this->projectileWeaponPhase->fireAtBuilding($spacecraftAttacker, $field, $isOrbitField, $count));

            $this->awardExperienceForForeignColonyAttack($attackerWrapper, $colony, $epsBeforeAttack);

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
            $game->setView(ModuleEnum::SHIP);
            $game->getInfo()->addInformationWrapper($informations);
            return;
        }
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        if ($isFleetAttack) {
            $game->getInfo()->addInformation(_("Angriff durchgeführt"));
            $game->setTemplateVar('FIGHT_RESULTS', $informations->getInformations());
        } else {
            $game->getInfo()->addInformationWrapper($informations);
        }
    }

    private function awardExperienceForForeignColonyAttack(
        ShipWrapperInterface $attackerWrapper,
        Colony $colony,
        ?int $epsBeforeAttack
    ): void {
        $attacker = $attackerWrapper->get();
        $epsSystem = $attackerWrapper->getEpsSystemData();

        if (
            $epsBeforeAttack === null
            || $epsSystem === null
            || $epsSystem->getEps() >= $epsBeforeAttack
            || $colony->getUser()->isNpc()
            || $this->playerRelationDeterminator->isFriend($attacker->getUser(), $colony->getUser())
        ) {
            return;
        }

        $this->eventDispatcher->dispatch(new CrewExperienceEvent(
            $attacker,
            SkillEnhancementEnum::ATTACK_FOREIGN_COLONY
        ));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
