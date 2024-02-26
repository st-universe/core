<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BeamTo;

use request;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\BeamUtilInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\TargetLink;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class BeamTo implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TRANSFER';

    private ColonyLoaderInterface $colonyLoader;

    private BeamUtilInterface $beamUtil;

    private ShipLoaderInterface $shipLoader;

    private InteractionCheckerInterface $interactionChecker;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        BeamUtilInterface $beamUtil,
        ShipLoaderInterface $shipLoader,
        InteractionCheckerInterface $interactionChecker,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->beamUtil = $beamUtil;
        $this->shipLoader = $shipLoader;
        $this->interactionChecker = $interactionChecker;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        if ($colony->getEps() == 0) {
            $game->addInformation(_('Keine Energie vorhanden'));
            return;
        }
        $wrapper = $this->shipLoader->find(request::postIntFatal('target'));

        if ($wrapper === null) {
            return;
        }

        $ship = $wrapper->get();

        if (!$this->interactionChecker->checkColonyPosition($colony, $ship)) {
            return;
        }

        if ($ship->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            return;
        }

        if ($ship->getShieldState() && $ship->getUser()->getId() !== $userId) {
            $game->addInformationf(_('Die %s hat die Schilde aktiviert'), $ship->getName());
            return;
        }
        if ($ship->getMaxStorage() <= $ship->getStorageSum()) {
            $game->addInformationf(_('Der Lagerraum der %s ist voll'), $ship->getName());
            return;
        }
        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');
        $storages = $colony->getStorage();
        if ($storages->isEmpty()) {
            $game->addInformation(_('Keine Waren zum Beamen vorhanden'));
            return;
        }
        if (count($commodities) == 0 || count($gcount) == 0) {
            $game->addInformation(_('Es wurden keine Waren zum Beamen ausgewählt'));
            return;
        }

        $informations = new InformationWrapper();

        foreach ($commodities as $key => $value) {
            $commodityId = (int) $value;
            if (!array_key_exists($key, $gcount)) {
                continue;
            }

            $this->beamUtil->transferCommodity(
                $commodityId,
                $gcount[$key],
                $colony,
                $colony,
                $ship,
                $informations
            );
        }


        if (!$informations->isEmpty()) {
            $informations->addInformationArray(
                [sprintf(
                    _('Die Kolonie %s hat folgende Waren zur %s transferiert'),
                    $colony->getName(),
                    $ship->getName()
                )],
                true
            );

            if ($game->getUser() === $ship->getUser()) {

                $game->setTargetLink(new TargetLink(
                    sprintf("ship.php?%s=1&id=%d", ShowShip::VIEW_IDENTIFIER, $ship->getId()),
                    'Zum Schiff wechseln'
                ));
            }

            $this->privateMessageSender->send(
                $userId,
                $ship->getUser()->getId(),
                $informations->getInformationsAsString(),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
                sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $ship->getId())
            );
        }

        $game->addInformationWrapper($informations);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
