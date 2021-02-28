<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AstroMapping;

use request;

use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class PlanAstroMapping implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PLAN_ASTRO';

    private ShipLoaderInterface $shipLoader;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private AstroEntryRepositoryInterface $astroEntryRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        AstroEntryRepositoryInterface $astroEntryRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->astroEntryRepository = $astroEntryRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($ship->getSystem() == null) {
            $game->addInformation('debug1');
            return;
        }

        if ($this->astroEntryRepository->getByUserAndSystem($userId, $ship->getSystemsId()) !== null) {
            $game->addInformation('debug2');
            return;
        }

        // system needs to be active
        if (!$ship->getAstroState()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, das Astrometrische Labor muss aktiviert sein![/color][/b]'));
            return;
        }

        $astroEntry = $this->astroEntryRepository->prototype();
        $astroEntry->setUser($game->getUser());
        $astroEntry->setState(AstronomicalMappingEnum::PLANNED);
        $astroEntry->setSystemId($ship->getSystemsId());
        $this->obtainMeasurementFields($astroEntry);

        $this->astroEntryRepository->save($astroEntry);

        $game->setView(ShowShip::VIEW_IDENTIFIER);
        $game->addInformation("Kartographie-Messpunkte wurden ermittelt");
    }

    private function obtainMeasurementFields(AstronomicalEntryInterface $entry)
    {
        $fieldIds = $this->starSystemMapRepository->getRandomFieldsForAstroMeasurement($entry->getSystemId());

        $entry->setStarsystemMapId1($fieldIds[0]);
        $entry->setStarsystemMapId2($fieldIds[1]);
        $entry->setStarsystemMapId3($fieldIds[2]);
        $entry->setStarsystemMapId4($fieldIds[3]);
        $entry->setStarsystemMapId5($fieldIds[4]);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
