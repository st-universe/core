<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TorpedoTransfer;

use request;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class TorpedoTransfer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TORPEDO_TRANSFER';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        if (!$ship->hasEnoughCrew()) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
            return;
        }

        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)) {
            $game->addInformation(_("Das Torpedolager ist zerstört"));
            return;
        }
        if ($ship->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        if ($ship->getShieldState()) {
            $game->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }

        $isUnload = request::has('isUnload');

        $target = $this->shipRepository->find((int) request::postIntFatal('target'));

        if ($target === null) {
            return;
        }
        if (!$ship->canInteractWith($target, false, true)) {
            return;
        }
        if ($target->getWarpState()) {
            $game->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return;
        }
        if ($ship->getTorpedoCount() > 0 && $target->getTorpedoCount() > 0 && $ship->getTorpedo() !== $target->getTorpedo()) {
            $game->addInformation(_("Die Schiffe haben unterschiedliche Torpedos geladen"));
            return;
        }

        $requestedTransferCount = request::postInt('tcount');

        if ($isUnload) {
            $amount = min(
                $requestedTransferCount,
                $ship->getTorpedoCount(),
                $target->getMaxTorpedos() - $target->getTorpedoCount()
            );

            if ($amount > 0) {
                if ($target->getRump()->getTorpedoLevel() !== $ship->getTorpedo()->getLevel()) {
                    $game->addInformation(sprintf(_('Die %s kann den Torpedotyp nicht ausrüsten'), $target->getName()));
                    return;
                }

                $ship->setTorpedoCount($ship->getTorpedoCount() - $amount);
                $target->setTorpedoCount($target->getTorpedoCount() + $amount);
                $target->setTorpedo($ship->getTorpedo());
            }
        } else {
            $amount = min(
                $requestedTransferCount,
                $target->getTorpedoCount(),
                $ship->getMaxTorpedos() - $ship->getTorpedoCount()
            );

            if ($amount > 0) {
                $ship->setTorpedoCount($ship->getTorpedoCount() + $amount);
                $ship->setTorpedo($target->getTorpedo());
                $target->setTorpedoCount($target->getTorpedoCount() - $amount);
            }
        }

        if ($ship->getTorpedoCount() === 0) {
            $ship->setTorpedo(null);
        }
        if ($target->getTorpedoCount() === 0) {
            $target->setTorpedo(null);
        }

        $this->shipRepository->save($ship);
        $this->shipRepository->save($target);

        $game->addInformation(
            sprintf(
                _('Die %s hat %d Torpedos %s der %s transferiert'),
                $ship->getName(),
                $amount,
                $isUnload ? 'zu' : 'von',
                $target->getName()
            )
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
