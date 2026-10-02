<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SelfDestruct;

use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class SelfDestruct implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SELFDESTRUCT';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftDestructionInterface $spacecraftDestruction,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $userId = $context->getUser()->getId();
        $user = $context->getUser();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        if ($ship->isConstruction()) {
            return;
        }

        if (!$ship->hasEnoughCrew($context)) {
            return;
        }

        $code = request::postString('destructioncode');
        if ($code === false) {
            return;
        }

        $trimmedCode = trim($code);
        if ($trimmedCode !== substr(md5($ship->getName()), 0, 6)) {
            $context->getInfo()->addInformation(_('Der Selbstzerstörungscode war fehlerhaft'));
            $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);
            return;
        }

        $context->setView(ModuleEnum::SHIP);

        $tractoredShipWrapperToTriggerAlertRed = ($ship->isTractoring() && $ship->getWarpDriveState()) ? $wrapper->getTractoredShipWrapper() : null;

        $context->getInfo()->addInformation(_('Die Selbstzerstörung war erfolgreich'));

        $prestigeAmount = $ship->getRump()->getPrestige();
        $rumpName = $ship->getRump()->getName();

        $this->spacecraftDestruction->destroy(
            null,
            $wrapper,
            SpacecraftDestructionCauseEnum::SELF_DESTRUCTION,
            $context->getInfo()
        );

        //Alarm-Rot check for tractor ship
        if ($tractoredShipWrapperToTriggerAlertRed !== null) {
            $this->alertReactionFacade->doItAll($tractoredShipWrapperToTriggerAlertRed, $context->getInfo());
        }

        if (
            $user->getState() == UserStateEnum::COLONIZATION_SHIP
            && $this->spacecraftRepository->getAmountByUserAndSpecialAbility($userId, SpacecraftRump::SPECIAL_ABILITY_COLONIZE) === 1
        ) {
            $user->setState(UserStateEnum::UNCOLONIZED);
        }

        $this->createNegativePrestigeLog($prestigeAmount, $rumpName, $user);
    }

    private function createNegativePrestigeLog(int $prestigeAmount, string $rumpName, User $user): void
    {
        $amount = -abs($prestigeAmount);

        $description = sprintf(
            '[b][color=red]%d[/color][/b] Prestige erhalten für die Selbstzerstörung von: %s',
            $amount,
            $rumpName
        );

        $this->createPrestigeLog->createLog($amount, $description, $user, time());
        $this->sendSystemMessage($description, $user);
    }

    private function sendSystemMessage(string $description, User $user): void
    {
        $this->privateMessageSender->send(
            UserConstants::USER_NOONE,
            $user->getId(),
            $description
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
