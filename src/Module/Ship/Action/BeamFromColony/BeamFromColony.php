<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BeamFromColony;

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

final class BeamFromColony implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMFROM_COLONY';

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
                $this->beamFromTarget(
                    $this->shipWrapperFactory->wrapShip($ship),
                    $target,
                    $game
                );
            }
        } else {
            $this->beamFromTarget($wrapper, $target, $game);
        }
    }

    private function beamFromTarget(ShipWrapperInterface $wrapper, ColonyInterface $target, GameControllerInterface $game): void
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

        if ($ship->getMaxStorage() <= $ship->getStorageSum()) {
            $game->addInformation(sprintf(_('Der Lagerraum der %s ist voll'), $ship->getName()));
            return;
        }
        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');
        if ($target->getStorage()->isEmpty()) {
            $game->addInformation(_("Keine Waren zum Beamen vorhanden"));
            return;
        }
        if (count($commodities) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurden keine Waren zum Beamen ausgewählt"));
            return;
        }
        $game->addInformation(sprintf(
            _('Die %s hat folgende Waren von der Kolonie %s transferiert'),
            $ship->getName(),
            $target->getName()
        ));
        foreach ($commodities as $key => $value) {
            $value = (int) $value;
            if ($epsSystem->getEps() < 1) {
                break;
            }
            if (!array_key_exists($key, $gcount)) {
                continue;
            }
            $commodity = $target->getStorage()[$value] ?? null;
            if ($commodity === null) {
                continue;
            }
            $count = $gcount[$key];
            if (!$commodity->getCommodity()->isBeamable($userId, $target->getUser()->getId())) {
                $game->addInformation(sprintf(_('%s ist nicht beambar'), $commodity->getCommodity()->getName()));
                continue;
            }
            $count = $count == "max" ? $commodity->getAmount() : (int) $count;
            if ($count < 1) {
                continue;
            }
            if ($ship->getStorageSum() >= $ship->getMaxStorage()) {
                break;
            }
            if ($count > $commodity->getAmount()) {
                $count = $commodity->getAmount();
            }

            $transferAmount = $commodity->getCommodity()->getTransferCount() * $ship->getBeamFactor();

            if (ceil($count / $transferAmount) > $epsSystem->getEps()) {
                $count = $epsSystem->getEps() * $transferAmount;
            }
            if ($ship->getStorageSum() + $count > $ship->getMaxStorage()) {
                $count = $ship->getMaxStorage() - $ship->getStorageSum();
            }

            $game->addInformationf(
                _('%d %s (Energieverbrauch: %d)'),
                $count,
                $commodity->getCommodity()->getName(),
                ceil($count / $transferAmount)
            );

            $this->colonyStorageManager->lowerStorage($target, $commodity->getCommodity(), $count);
            $this->shipStorageManager->upperStorage($ship, $commodity->getCommodity(), $count);

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
