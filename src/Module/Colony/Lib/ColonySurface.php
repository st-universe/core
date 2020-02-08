<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildingEnum;
use Stu\Component\Faction\FactionEnum;
use Stu\Module\Building\BuildingFunctionTypeEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\PlanetGenerator\PlanetGenerator;

final class ColonySurface implements ColonySurfaceInterface
{
    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private BuildingRepositoryInterface $buildingRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private ColonyInterface $colony;

    private ?int $buildingId;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingRepositoryInterface $buildingRepository,
        ColonyRepositoryInterface $colonyRepository,
        ColonyInterface $colony,
        ?int $buildingId = null
    ) {
        $this->colony = $colony;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->buildingRepository = $buildingRepository;
        $this->buildingId = $buildingId;
        $this->colonyRepository = $colonyRepository;
    }

    public function getSurface(): array
    {
        $fields = $this->planetFieldRepository->getByColony($this->colony->getId());

        if ($fields === []) {
            $this->updateSurface();

            $fields = $this->planetFieldRepository->getByColony($this->colony->getId());
        }

        if ($this->buildingId !== null) {
            $building = $this->buildingRepository->find($this->buildingId);

            array_walk(
                $fields,
                function (PlanetFieldInterface $field) use ($building): void {
                    if (
                        $field->getTerraformingId() === null &&
                        $building->getBuildableFields()->containsKey((int)$field->getFieldType())
                    ) {
                        $field->setBuildMode(true);
                    }
                }
            );
        }

        return $fields;
    }

    public function getSurfaceTileCssClass(): string
    {
        if ($this->colony->getPlanetType()->getIsMoon()) {
            return 'moonSurfaceTiles';
        }
        return 'planetSurfaceTiles';
    }

    public function getEpsBoxTitleString(): string
    {
        $energyProduction = $this->colony->getEpsProduction();

        $forecast = $this->colony->getEps() + $energyProduction;

        if ($this->colony->getEps() + $energyProduction < 0) {
            $forecast = 0;
        }
        if ($this->colony->getEps() + $energyProduction > $this->colony->getMaxEps()) {
            $forecast = $this->colony->getMaxEps();
        }
        if ($energyProduction > 0) {
            $energyProduction = sprintf('+%d', $energyProduction);
        }

        return sprintf(
            _('Energie: %d/%d (%s/Runde = %d)'),
            $this->colony->getEps(),
            $this->colony->getMaxEps(),
            $energyProduction,
            $forecast
        );
    }

    public function getPositiveEffectPrimaryDescription(): string
    {
        // XXX We need the other factions...
        switch ($this->colony->getUser()->getFactionId()) {
            case FactionEnum::FACTION_FEDERATION:
                return _('Zufriedenheit');
            case FactionEnum::FACTION_ROMULAN:
                return _('Loyalität');
            case FactionEnum::FACTION_KLINGON:
                return _('Ehre');
        }
        return '';
    }

    public function getPositiveEffectSecondaryDescription(): string
    {
        // XXX We need the other factions...
        switch ($this->colony->getUser()->getFactionId()) {
            case FactionEnum::FACTION_FEDERATION:
                return _('Umweltkontrollen');
            case FactionEnum::FACTION_ROMULAN:
                return _('Zerschmetterte Opposition');
            case FactionEnum::FACTION_KLINGON:
                return _('Irgendwas mit Kahless');
        }
        return '';
    }

    public function getNegativeEffectDescription(): string
    {
        // XXX We need the other factions...
        switch ($this->colony->getUser()->getFactionId()) {
            case FactionEnum::FACTION_FEDERATION:
                return _('Umweltverschmutzung');
            case FactionEnum::FACTION_ROMULAN:
                return _('Opposition');
            case FactionEnum::FACTION_KLINGON:
                return _('Abtrünnige');
        }
        return '';
    }

    public function getStorageSumPercent(): float
    {
        $maxStorage = $this->colony->getMaxStorage();

        if ($maxStorage === 0) {
            return 0;
        }

        return round(100 / $maxStorage * $this->colony->getStorageSum(), 2);
    }

    public function updateSurface(): array
    {
        if ($this->colony->getMask() === null) {
            $generator = new PlanetGenerator();

            $surface = $generator->generateColony(
                $this->colony->getColonyClass(),
                $this->colony->getSystem()->getBonusFieldAmount()
            );
            $this->colony->setMask(base64_encode(serialize($surface)));

            $this->colonyRepository->save($this->colony);
        }

        $fields = $this->planetFieldRepository->getByColony($this->colony->getId());

        $surface = unserialize(base64_decode($this->colony->getMask()));
        $i = 0;
        foreach ($surface as $key => $value) {
            if (!array_key_exists($key, $fields)) {
                $fields[$key] = $this->planetFieldRepository->prototype();
                $fields[$key]->setColony($this->colony);
                $fields[$key]->setFieldId($i);
            }
            $fields[$key]->setBuilding(null);
            $fields[$key]->setIntegrity(0);
            $fields[$key]->setFieldType((int)$value);
            $fields[$key]->setActive(0);

            $this->planetFieldRepository->save($fields[$key]);
            $i++;
        }
        return $fields;
    }

    public function getProductionSumClass(): string
    {
        if ($this->colony->getProductionSum() < 0) {
            return 'negative';
        }
        if ($this->colony->getProductionSum() > 0) {
            return 'positive';
        }
        return '';
    }

    public function hasShipyard(): bool
    {
        return $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
                $this->colony->getId(),
                BuildingFunctionTypeEnum::getShipyardOptions(),
                [0, 1]
            ) > 0;
    }

    public function hasModuleFab(): bool
    {
        return $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
                $this->colony->getId(),
                BuildingFunctionTypeEnum::getModuleFabOptions(),
                [0, 1]
            ) > 0;
    }

    public function hasAirfield(): bool
    {
        return $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
                $this->colony->getId(),
                [BuildingEnum::BUILDING_FUNCTION_AIRFIELD],
                [0, 1]
            ) > 0;
    }

    public function getDayNightState(): string
    {
        // @todo implement
        $hour = date('G');

        if ($hour > 7 && $hour < 19) {
            return 't';
        }
        return 'n';
    }
}
