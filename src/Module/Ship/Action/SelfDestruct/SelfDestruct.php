<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SelfDestruct;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Module\Ship\View\Overview\Overview;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class SelfDestruct implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SELFDESTRUCT';

    private ShipLoaderInterface $shipLoader;

    private EntryCreatorInterface $entryCreator;

    private ShipRemoverInterface $shipRemover;

    private ShipRepositoryInterface $shipRepository;

    private AlertRedHelperInterface $alertRedHelper;

    private CreatePrestigeLogInterface $createPrestigeLog;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        EntryCreatorInterface $entryCreator,
        ShipRemoverInterface $shipRemover,
        ShipRepositoryInterface $shipRepository,
        AlertRedHelperInterface $alertRedHelper,
        CreatePrestigeLogInterface $createPrestigeLog,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipLoader = $shipLoader;
        $this->entryCreator = $entryCreator;
        $this->shipRemover = $shipRemover;
        $this->shipRepository = $shipRepository;
        $this->alertRedHelper = $alertRedHelper;
        $this->createPrestigeLog = $createPrestigeLog;
        $this->privateMessageSender = $privateMessageSender;
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

        $game->setView(Overview::VIEW_IDENTIFIER);

        $tractoredShipToTriggerAlertRed = ($ship->isTractoring() && $ship->getWarpState()) ? $ship->getTractoredShip() : null;

        $game->addInformation(_('Die Selbstzerstörung war erfolgreich'));
        $msg = sprintf(
            _('Die %s (%s) hat sich in Sektor %s selbst zerstört'),
            $ship->getName(),
            $ship->getRump()->getName(),
            $ship->getSectorString()
        );
        if ($ship->isBase()) {
            $this->entryCreator->addStationEntry(
                $msg,
                $userId
            );
        } else {
            $this->entryCreator->addShipEntry(
                $msg,
                $userId
            );
        }

        $prestigeAmount = $ship->getRump()->getPrestige();
        $rumpName = $ship->getRump()->getName();

        $destroyMsg = $this->shipRemover->destroy($wrapper);
        if ($destroyMsg !== null) {
            $game->addInformation($destroyMsg);
        }

        //Alarm-Rot check for tractor ship
        if ($tractoredShipToTriggerAlertRed !== null) {
            $this->alertRedHelper->doItAll($tractoredShipToTriggerAlertRed, $game);
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
