<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AstroMapping;

use request;
use RuntimeException;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Type\AstroLaboratoryShipSystem;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class StartAstroMapping implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_START_ASTRO';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private AstroEntryRepositoryInterface $astroEntryRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        AstroEntryRepositoryInterface $astroEntryRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->astroEntryRepository = $astroEntryRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $message = '';
        $ship = $wrapper->get();
        $entry = $this->astroEntryRepository->getByShipLocation($ship, false);
        if ($entry === null || $entry->getState() !== AstronomicalMappingEnum::MEASURED) {
            return;
        }

        if ($ship->getSystem() != null) {
            $message = 'des Systems';
        }

        if ($ship->getMap() != null) {
            $message = 'der Region';
        }


        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($ship->isUnderRepair()) {
            $game->addInformation(_('Kartographieren nicht möglich. Das Schiff wird derzeit repariert.'));
            return;
        }

        // system needs to be active
        if (!$ship->getAstroState()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, das Astrometrische Labor muss aktiviert sein![/color][/b]'));
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();

        // check for energy
        if ($epsSystem === null || $epsSystem->getEps() < AstroLaboratoryShipSystem::FINALIZING_ENERGY_COST) {
            $game->addInformation(sprintf(_('[b][color=#ff2626]Aktion nicht möglich, ungenügend Energie vorhanden. Bedarf: %dE[/color][/b]'), AstroLaboratoryShipSystem::FINALIZING_ENERGY_COST));
            return;
        }

        $entry->setState(AstronomicalMappingEnum::FINISHING);
        $entry->setAstroStartTurn($game->getCurrentRound()->getTurn());
        $this->astroEntryRepository->save($entry);

        $epsSystem->lowerEps(AstroLaboratoryShipSystem::FINALIZING_ENERGY_COST)->update();
        $ship->setState(ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING);

        $astroLab = $wrapper->getAstroLaboratorySystemData();
        if ($astroLab === null) {
            throw new RuntimeException('this should not happen');
        }
        $astroLab->setAstroStartTurn($game->getCurrentRound()->getTurn())->update();
        $this->shipRepository->save($ship);

        $game->setView(ShowShip::VIEW_IDENTIFIER);
        $game->addInformation(sprintf(_("Die Kartographierung %s wird finalisiert"), $message));
    }


    public function performSessionCheck(): bool
    {
        return true;
    }
}
