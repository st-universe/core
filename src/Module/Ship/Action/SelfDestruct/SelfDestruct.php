<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SelfDestruct;

use request;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class SelfDestruct implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SELFDESTRUCT';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private ShipDestructionInterface $shipDestruction,
        private ShipRepositoryInterface $shipRepository,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $user = $game->getUser();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        if ($ship->isConstruction()) {
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        $code = request::postString('destructioncode');
        if ($code === false) {
            return;
        }

        $trimmedCode = trim($code);
        if ($trimmedCode !== substr(md5($ship->getName()), 0, 6)) {
            $game->addInformation(_('Der Selbstzerstörungscode war fehlerhaft'));
            $game->setView(ShowShip::VIEW_IDENTIFIER);
            return;
        }

        $game->setView(ModuleViewEnum::SHIP);

        $tractoredShipWrapperToTriggerAlertRed = ($ship->isTractoring() && $ship->getWarpDriveState()) ? $wrapper->getTractoredShipWrapper() : null;

        $game->addInformation(_('Die Selbstzerstörung war erfolgreich'));

        $prestigeAmount = $ship->getRump()->getPrestige();
        $rumpName = $ship->getRump()->getName();

        $this->shipDestruction->destroy(
            null,
            $wrapper,
            ShipDestructionCauseEnum::SELF_DESTRUCTION,
            $game
        );

        //Alarm-Rot check for tractor ship
        if ($tractoredShipWrapperToTriggerAlertRed !== null) {
            $this->alertReactionFacade->doItAll($tractoredShipWrapperToTriggerAlertRed, $game);
        }

        if ($user->getState() == UserEnum::USER_STATE_COLONIZATION_SHIP && $this->shipRepository->getAmountByUserAndSpecialAbility($userId, ShipRumpSpecialAbilityEnum::COLONIZE) === 1) {
            $user->setState(UserEnum::USER_STATE_UNCOLONIZED);
        }

        $this->createNegativePrestigeLog($prestigeAmount, $rumpName, $user);
    }

    private function createNegativePrestigeLog(int $prestigeAmount, string $rumpName, UserInterface $user): void
    {
        $amount = -(int)abs($prestigeAmount);

        $description = sprintf(
            '[b][color=red]%d[/color][/b] Prestige erhalten für die Selbstzerstörung von: %s',
            $amount,
            $rumpName
        );

        $this->createPrestigeLog->createLog($amount, $description, $user, time());
        $this->sendSystemMessage($description, $user);
    }

    private function sendSystemMessage(string $description, UserInterface $user): void
    {
        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $user->getId(),
            $description
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
