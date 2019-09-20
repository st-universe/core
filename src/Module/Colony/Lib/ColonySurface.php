<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use ColonyData;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\PlanetGenerator\PlanetGenerator;

final class ColonySurface implements ColonySurfaceInterface
{
    private $planetFieldRepository;

    private $buildingRepository;

    private $colony;

    private $buildingId;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingRepositoryInterface $buildingRepository,
        ColonyData $colony,
        ?int $buildingId = null
    ) {
        $this->colony = $colony;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->buildingRepository = $buildingRepository;
        $this->buildingId = $buildingId;
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

        return sprintf(
            _('Energie: %d/%d (%d/Runde = %d)'),
            $this->colony->getEps(),
            $this->colony->getMaxEps(),
            $this->colony->getEpsProductionDisplay(),
            $forecast
        );
    }

    public function getPositiveEffectPrimaryDescription(): string
    {
        // XXX We need the other factions...
        switch ($this->colony->getUser()->getFaction()) {
            case FACTION_FEDERATION:
                return _('Zufriedenheit');
            case FACTION_ROMULAN:
                return _('Loyalität');
            case FACTION_KLINGON:
                return _('Ehre');
        }
    }

    public function getPositiveEffectSecondaryDescription(): string
    {
        // XXX We need the other factions...
        switch ($this->colony->getUser()->getFaction()) {
            case FACTION_FEDERATION:
                return _('Umweltkontrollen');
            case FACTION_ROMULAN:
                return _('Zerschmetterte Opposition');
            case FACTION_KLINGON:
                return _('Irgendwas mit Kahless');
        }
    }

    public function getNegativeEffectDescription(): string
    {
        // XXX We need the other factions...
        switch ($this->colony->getUser()->getFaction()) {
            case FACTION_FEDERATION:
                return _('Umweltverschmutzung');
            case FACTION_ROMULAN:
                return _('Opposition');
            case FACTION_KLINGON:
                return _('Abtrünnige');
        }
    }

    public function getStorageSumPercent(): float
    {
        return round(100 / $this->colony->getMaxStorage() * $this->colony->getStorageSum(), 2);
    }

    public function updateSurface(): array
    {
        if (!$this->colony->getMask()) {
            $generator = new PlanetGenerator();
            $surface = $generator->generateColony($this->colony->getColonyClass(),
                $this->colony->getSystem()->getBonusFieldAmount());
            $this->colony->setMask(base64_encode(serialize($surface)));
            $this->colony->save();
        }

        $fields = $this->planetFieldRepository->getByColony($this->colony->getId());

        $surface = unserialize(base64_decode($this->colony->getMask()));
        $i = 0;
        foreach ($surface as $key => $value) {
            if (!array_key_exists($key, $fields)) {
                $fields[$key] = $this->planetFieldRepository->prototype();
                $fields[$key]->setColonyId($this->colony->getId());
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

}