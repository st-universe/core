<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\AttackSpacecraft;

use Override;
use request;
use Stu\Component\Borg\BorgSphereAttackGuard;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCoreInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;

//TODO unit tests and request class
final class AttackSpacecraft implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ATTACK_SPACECRAFT';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private FightLibInterface $fightLib,
        private SpacecraftAttackCoreInterface $spacecraftAttackCore,
        private PirateReactionInterface $pirateReaction,
        private InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory,
        private ?BorgSphereAttackGuard $borgSphereAttackGuard = null
    ) {}

    #[Override]
    public function handle(ActionControllerContext $context): void
    {
        $info = $context->getInfo();
        $userId = $context->getUser()->getId();

        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
            request::indInt('id'),
            $userId,
            request::getIntFatal('target')
        );

        $wrapper = $wrappers->getSource();
        $spacecraft = $wrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if (!$this->interactionCheckerBuilderFactory
            ->createInteractionChecker()
            ->setSource($spacecraft)
            ->setTarget($target)
            ->setCheckTypes([
                InteractionCheckType::EXPECT_SOURCE_ENABLED,
                InteractionCheckType::EXPECT_SOURCE_SUFFICIENT_CREW,
                InteractionCheckType::EXPECT_SOURCE_UNCLOAKED,
                InteractionCheckType::EXPECT_SOURCE_UNWARPED,
                InteractionCheckType::EXPECT_SOURCE_TACHYON,
                InteractionCheckType::EXPECT_TARGET_UNWARPED
            ])
            ->check($info)) {
            return;
        }

        if ($target->getCondition()->isDestroyed()) {
            $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);
            $info->addInformation(_('Das Ziel ist bereits zerstört'));
            return;
        }

        if (!$this->fightLib->canAttackTarget($spacecraft, $target)) {
            throw new SanityCheckException('Target cant be attacked', self::ACTION_IDENTIFIER);
        }

        if (!$this->canStartAttack($wrapper, $target, $context)) {
            return;
        }

        if ($spacecraft instanceof Ship && $spacecraft->getDockedTo() !== null) {
            $spacecraft->setDockedTo(null);
        }

        $isFleetFight = false;
        $fightInfos = new InformationWrapper();

        $stillAlive = $this->attack($wrapper, $targetWrapper, $isFleetFight, $fightInfos);

        if (!$stillAlive) {
            $info->addInformationWrapper($fightInfos);
            return;
        }

        $this->pirateReaction->checkForPirateReaction(
            $target,
            PirateReactionTriggerEnum::ON_ATTACK,
            $spacecraft
        );

        if ($spacecraft->getCondition()->isDestroyed()) {
            $info->addInformationWrapper($fightInfos);
            return;
        }
        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        if ($isFleetFight) {
            $context->getInfo()->addInformation(_("Angriff durchgeführt"));
            $context->setTemplateVar('FIGHT_RESULTS', $fightInfos->getInformations());
        } else {
            $context->getInfo()->addInformationWrapper($fightInfos);
        }
    }

    private function canStartAttack(SpacecraftWrapperInterface $wrapper, Spacecraft $target, ActionControllerContext $context): bool
    {
        $eps = $wrapper->getEpsSystemData();
        if ($eps === null || $eps->getEps() === 0) {
            $context->getInfo()->addInformation(_('Keine Energie vorhanden'));
            return false;
        }
        return $this->borgSphereAttackGuard === null || $this->borgSphereAttackGuard->prepare($wrapper->get(), $target, $context);
    }

    private function attack(SpacecraftWrapperInterface $source, SpacecraftWrapperInterface $target, bool &$isFleetFight, InformationWrapper $information): bool
    {
        try {
            return $this->spacecraftAttackCore->attack($source, $target, false, $isFleetFight, $information);
        } finally {
            $this->borgSphereAttackGuard?->finish();
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
