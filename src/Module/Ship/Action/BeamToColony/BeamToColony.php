<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BeamToColony;

use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class BeamToColony implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMTO_COLONY';

    private ShipLoaderInterface $shipLoader;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipRepositoryInterface $shipRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ShipRepositoryInterface $shipRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipRepository = $shipRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        $target = $this->colonyRepository->find(request::postIntFatal('target'));
        if ($target === null || !InteractionChecker::canInteractWith($ship, $target, $game)) {
            return;
        }

        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');
        if (count($commodities) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurden keine Waren zum Beamen ausgewählt"));
            return;
        }

        if ($target->getUserId() !== $userId && $this->colonyLibFactory->createColonyShieldingManager($target)->isShieldingEnabled() && $target->getShieldFrequency() !== 0) {
            $frequency = request::postInt('frequency');
            if ($frequency !== $target->getShieldFrequency()) {
                $game->addInformation(_("Die Schildfrequenz ist nicht korrekt"));
                return;
            }
        }

        // check for fleet option
        if (request::postInt('isfleet') && $ship->getFleet() !== null) {
            foreach ($ship->getFleet()->getShips() as $ship) {
                $this->beamToTarget(
                    $this->shipWrapperFactory->wrapShip($ship),
                    $target,
                    $game
                );
            }
        } else {
            $this->beamToTarget($wrapper, $target, $game);
        }
    }

    private function beamToTarget(ShipWrapperInterface $wrapper, ColonyInterface $target, GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $ship = $wrapper->get();
        $epsSystem = $wrapper->getEpsSystemData();

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }
        if ($epsSystem === null || $epsSystem->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getShieldState()) {
            $game->addInformation(_('Die Schilde sind aktiviert'));
            return;
        }
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }

        if (!$target->storagePlaceLeft()) {
            $game->addInformation(sprintf(_('Der Lagerraum der Kolonie %s ist voll'), $target->getName()));
            return;
        }
        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');

        $shipStorage = $ship->getStorage();

        if ($shipStorage->isEmpty()) {
            $game->addInformation(_("Keine Waren zum Beamen vorhanden"));
            return;
        }
        $game->addInformation(sprintf(
            _('Die %s hat folgende Waren zur Kolonie %s transferiert'),
            $ship->getName(),
            $target->getName()
        ));
        foreach ($commodities as $key => $value) {
            $commodityId = (int) $value;

            if ($epsSystem->getEps() < 1) {
                break;
            }
            if (!array_key_exists($key, $gcount)) {
                continue;
            }

            $storage = $shipStorage[$commodityId] ?? null;

            if ($storage === null) {
                continue;
            }
            $count = $gcount[$key];

            $commodity = $storage->getCommodity();

            if (!$commodity->isBeamable($userId, $target->getUser()->getId())) {
                $game->addInformation(sprintf(_('%s ist nicht beambar'), $commodity->getName()));
                continue;
            }
            $count = $count == "max" ? $storage->getAmount() : (int) $count;
            if ($count < 1) {
                continue;
            }
            if ($target->getStorageSum() >= $target->getMaxStorage()) {
                break;
            }
            $count = min($count, $storage->getAmount());

            $transferAmount = $commodity->getTransferCount() * $ship->getBeamFactor();

            if (ceil($count / $transferAmount) > $epsSystem->getEps()) {
                $count = $epsSystem->getEps() * $transferAmount;
            }
            if ($target->getStorageSum() + $count > $target->getMaxStorage()) {
                $count = $target->getMaxStorage() - $target->getStorageSum();
            }

            $game->addInformationf(
                _('%d %s (Energieverbrauch: %d)'),
                $count,
                $commodity->getName(),
                ceil($count / $transferAmount)
            );

            $this->shipStorageManager->lowerStorage($ship, $commodity, $count);
            $this->colonyStorageManager->upperStorage($target, $commodity, $count);

            $epsSystem->lowerEps((int) ceil($count / $transferAmount));
        }
        $game->sendInformation(
            $target->getUser()->getId(),
            $ship->getUser()->getId(),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            sprintf(_('colony.php?%s=1&id=%d'), ShowColony::VIEW_IDENTIFIER, $target->getId())
        );

        $epsSystem->update();

        $this->shipRepository->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
