<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EscapeTractorBeam;

use Psr\EventDispatcher\EventDispatcherInterface;
use request;
use Stu\Component\Crew\Skill\Event\CrewExperienceEvent;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Trait\SpacecraftTractorPayloadTrait;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Spacecraft\Lib\Damage\SystemDamageInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Spacecraft;

final class EscapeTractorBeam implements ActionControllerInterface
{
    use SpacecraftTractorPayloadTrait;

    public const string ACTION_IDENTIFIER = 'B_ESCAPE_TRAKTOR';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private ApplyDamageInterface $applyDamage,
        private SystemDamageInterface $systemDamage,
        private PrivateMessageSenderInterface $privateMessageSender,
        private SpacecraftDestructionInterface $spacecraftDestruction,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $userId = $context->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );

        // is ship trapped in tractor beam?
        $ship = $wrapper->get();
        if (!$ship->isTractored()) {
            return;
        }

        //is deflector working?
        if (!$ship->isSystemHealthy(SpacecraftSystemTypeEnum::DEFLECTOR)) {
            return;
        }

        if (!$ship->hasEnoughCrew($context)) {
            return;
        }

        $tractoringShipWrapper = $wrapper->getTractoringSpacecraftWrapper();
        if ($tractoringShipWrapper === null) {
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();

        //enough energy?
        if ($epsSystem === null || $epsSystem->getEps() < 20) {
            $context->getInfo()->addInformation(sprintf(_('Nicht genug Energie für Fluchtversuch (%d benötigt)'), 20));
            $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);
            return;
        }

        //eps cost
        $epsSystem->lowerEps(20)->update();

        $tractoringShip = $tractoringShipWrapper->get();

        //parameters
        $ownMass = $ship->getRump()->getTractorMass();
        $otherPayload = $this->getTractorPayload($tractoringShip);
        $ratio = $ownMass / $otherPayload;

        // probabilities
        $chance = random_int(1, 100);
        if ($chance < (int)ceil(11 * $ratio)) {
            $this->escape($tractoringShipWrapper, $wrapper, $context);
        } elseif ($chance < 55) {
            $this->sufferDeflectorDamage($tractoringShip, $wrapper, $context);
        } else {
            $this->sufferHullDamage($tractoringShip, $wrapper, $context);
        }

        if ($ship->getCondition()->isDestroyed()) {
            return;
        }

        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);
    }

    private function escape(
        SpacecraftWrapperInterface $tractoringShipWrapper,
        ShipWrapperInterface $wrapper,
        ActionControllerContext $context
    ): void {
        $ship = $wrapper->get();
        $tractoringShip = $tractoringShipWrapper->get();
        $isTractoringShipWarped = $tractoringShip->getWarpDriveState();

        $tractoringShip->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setStatus(0);
        $this->spacecraftSystemManager->deactivate($tractoringShipWrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true); // forced active deactivation

        $this->privateMessageSender->send(
            $ship->getUser()->getId(),
            $tractoringShip->getUser()->getId(),
            sprintf(_('Bei dem Fluchtversuch der %s wurde der Traktorstrahl der %s in Sektor %s zerstört'), $ship->getName(), $tractoringShip->getName(), $ship->getSectorString()),
            $tractoringShip->getType()->getMessageFolderType(),
            $tractoringShip
        );

        $context->getInfo()->addInformation(_('Der Fluchtversuch ist gelungen'));

        $this->eventDispatcher->dispatch(new CrewExperienceEvent(
            $ship,
            SkillEnhancementEnum::ESCAPE_TRACTOR_BEAM
        ));

        //Alarm-Rot check
        if ($isTractoringShipWarped) {
            $this->alertReactionFacade->doItAll($wrapper, $context->getInfo());
        }
    }

    private function sufferDeflectorDamage(
        Spacecraft $tractoringSpacecraft,
        ShipWrapperInterface $wrapper,
        ActionControllerContext $context
    ): void {
        $informations = new InformationWrapper([_('Der Fluchtversuch ist fehlgeschlagen:')]);

        $ship = $wrapper->get();
        $system = $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::DEFLECTOR);
        $this->systemDamage->damageShipSystem($wrapper, $system, random_int(5, 25), $informations);

        $context->getInfo()->addInformationWrapper($informations);

        $this->privateMessageSender->send(
            $ship->getUser()->getId(),
            $tractoringSpacecraft->getUser()->getId(),
            sprintf(_('Der Fluchtversuch der %s ist gescheitert'), $ship->getName()),
            $tractoringSpacecraft->getType()->getMessageFolderType(),
            $tractoringSpacecraft
        );
    }

    private function sufferHullDamage(
        Spacecraft $tractoringSpacecraft,
        ShipWrapperInterface $wrapper,
        ActionControllerContext $context
    ): void {
        $ship = $wrapper->get();
        $otherUserId = $tractoringSpacecraft->getUser()->getId();
        $shipName = $ship->getName();

        $context->getInfo()->addInformation(_('Der Fluchtversuch ist fehlgeschlagen:'));

        $this->applyDamage->damage(new DamageWrapper((int) ceil($ship->getMaxHull() * random_int(10, 25) / 100)), $wrapper, $context->getInfo());

        if ($ship->getCondition()->isDestroyed()) {

            $this->spacecraftDestruction->destroy(
                $tractoringSpacecraft,
                $wrapper,
                SpacecraftDestructionCauseEnum::ESCAPE_TRACTOR,
                $context->getInfo()
            );

            $this->privateMessageSender->send(
                $ship->getUser()->getId(),
                $otherUserId,
                sprintf(_('Die %s wurde beim Fluchtversuch zerstört'), $shipName),
                $tractoringSpacecraft->getType()->getMessageFolderType()
            );
        } else {
            $this->privateMessageSender->send(
                $ship->getUser()->getId(),
                $otherUserId,
                sprintf(_('Der Fluchtversuch der %s ist gescheitert'), $shipName),
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $tractoringSpacecraft
            );
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
