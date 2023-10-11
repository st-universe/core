<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BeamTo;

use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class BeamTo implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMTO';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipLoaderInterface $shipLoader;

    private InteractionCheckerInterface $interactionChecker;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ShipLoaderInterface $shipLoader,
        InteractionCheckerInterface $interactionChecker
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipLoader = $shipLoader;
        $this->interactionChecker = $interactionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
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

        $target = $wrapper->get();

        if (!$this->interactionChecker->checkColonyPosition($colony, $target)) {
            return;
        }

        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            return;
        }

        if ($target->getShieldState() && $target->getUser()->getId() !== $userId) {
            $game->addInformationf(_('Die %s hat die Schilde aktiviert'), $target->getName());
            return;
        }
        if ($target->getMaxStorage() <= $target->getStorageSum()) {
            $game->addInformationf(_('Der Lagerraum der %s ist voll'), $target->getName());
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

        $isOwnedByCurrentUser = $game->getUser() === $target->getUser();
        if ($isOwnedByCurrentUser) {
            $link = sprintf("ship.php?%s=1&id=%d", ShowShip::VIEW_IDENTIFIER, $target->getId());

            $game->addInformationfWithLink(
                _('Die Kolonie %s hat folgende Waren zur %s transferiert'),
                $link,
                $colony->getName(),
                $target->getName()
            );
        } else {
            $game->addInformationf(
                _('Die Kolonie %s hat folgende Waren zur %s transferiert'),
                $colony->getName(),
                $target->getName()
            );
        }

        foreach ($commodities as $key => $value) {
            $value = (int) $value;
            if ($colony->getEps() < 1) {
                break;
            }
            if (!array_key_exists($key, $gcount)) {
                continue;
            }
            $storage = $storages[$value] ?? null;
            if ($storage === null) {
                continue;
            }
            $count = $gcount[$key];
            $count = $count == "max" ? $storage->getAmount() : (int)$count;
            if ($count < 1) {
                continue;
            }
            if (!$storage->getCommodity()->isBeamable($userId, $target->getUser()->getId())) {
                $game->addInformationf(_('%s ist nicht beambar'), $storage->getCommodity()->getName());
                continue;
            }
            if ($target->getStorageSum() >= $target->getMaxStorage()) {
                break;
            }
            if ($count > $storage->getAmount()) {
                $count = $storage->getAmount();
            }

            $transferAmount = $storage->getCommodity()->getTransferCount() * $colony->getBeamFactor();

            if (ceil($count / $transferAmount) > $colony->getEps()) {
                $count = $colony->getEps() * $transferAmount;
            }
            if ($target->getStorageSum() + $count > $target->getMaxStorage()) {
                $count = $target->getMaxStorage() - $target->getStorageSum();
            }

            $eps_usage = ceil($count / $transferAmount);
            $game->addInformationf(
                _('%d %s (Energieverbrauch: %d)'),
                $count,
                $storage->getCommodity()->getName(),
                $eps_usage
            );
            $colony->lowerEps((int)ceil($count / $transferAmount));

            $this->shipStorageManager->upperStorage($target, $storage->getCommodity(), $count);
            $this->colonyStorageManager->lowerStorage($colony, $storage->getCommodity(), $count);
        }
        $game->sendInformation(
            $target->getUser()->getId(),
            $userId,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $target->getId())
        );

        $this->colonyRepository->save($colony);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
