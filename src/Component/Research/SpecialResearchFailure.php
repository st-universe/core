<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use LogicException;
use Stu\Component\Building\BuildingActivationHandler;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class SpecialResearchFailure
{
    public function __construct(
        private SpecialResearchFacility $facility,
        private SpecialResearchCatalog $catalog,
        private BuildingActivationHandler $activation,
        private PlanetFieldRepositoryInterface $fields,
        private StuRandom $random
    ) {}

    public function apply(SpecialResearch $research): string
    {
        $colony = $research->getColony();
        if ($colony === null || $colony->getUser()->getId() !== $research->getUser()->getId()) {
            return '';
        }
        $field = $research->getFieldId() === null ? $this->facility->find($colony) : $this->facility->getResearchField($research);
        $settings = $research->getFailureSettings()
            ?? $this->catalog->getDefinition($research->getResearchType(), $research->getUser()->getFactionId())?->getFailureSettings();
        if ($field === null || $settings === null) {
            return '';
        }
        $research->setField($field)->setFailureSettings($settings);
        return $this->applyAt($colony, $field, $settings);
    }

    /** @param array<string, int> $settings */
    public function applyAt(Colony $colony, PlanetField $field, array $settings): string
    {
        $building = $field->getBuilding() ?? throw new LogicException('Research building missing');
        $workers = $building->getWorkers();
        $damage = (int) ceil($building->getIntegrity() * $settings['damage_percent'] / 100);
        $field->setIntegrity(max(1, $field->getIntegrity() - $damage));
        $this->activation->deactivate($field, true);
        $this->fields->save($field);
        $details = sprintf('Das Gebäude %s auf Feld %d der Kolonie %s wurde beschädigt und deaktiviert', $building->getName(), $field->getFieldId(), $colony->getName());
        $consequences = $this->populationLoss($colony, $field, $workers, $settings);
        return $details . '. ' . $consequences;
    }

    /** @param array<string, int> $settings */
    private function populationLoss(Colony $colony, PlanetField $field, int $workers, array $settings): string
    {
        if ($workers > 0) {
            return $this->casualtyMessage($this->killWorkers($colony, $workers, $settings), 'Arbeiter');
        }
        if ($colony->getChangeable()->getWorkless() > 0) {
            $count = $this->removePopulation($colony, $this->random->rand($settings['population_min'], $settings['population_max']));
            return $this->casualtyMessage($count, 'Personen der freien Bevölkerung');
        }
        $neighbor = $this->findNeighbor($colony, $field);
        if ($neighbor === null) {
            return 'Es gab keine Opfer';
        }
        $building = $neighbor->getBuilding() ?? throw new LogicException('Neighbor building missing');
        $this->activation->deactivate($neighbor);
        $count = $this->killWorkers($colony, $building->getWorkers(), $settings);
        return sprintf('Naniten konnten in das benachbarte Gebäude %s auf Feld %d entweichen, das daraufhin deaktiviert wurde. %s', $building->getName(), $neighbor->getFieldId(), $this->casualtyMessage($count, 'Arbeiter'));
    }

    /** @param array<string, int> $settings */
    private function killWorkers(Colony $colony, int $workers, array $settings): int
    {
        $min = (int) ceil($workers * $settings['worker_min_percent'] / 100);
        $max = max($min, (int) floor($workers * $settings['worker_max_percent'] / 100));
        return $this->removePopulation($colony, $this->random->rand($min, $max));
    }

    private function removePopulation(Colony $colony, int $count): int
    {
        $changeable = $colony->getChangeable();
        $killed = min(max(0, $changeable->getWorkless()), $count);
        $changeable->setWorkless($changeable->getWorkless() - $killed);
        return $killed;
    }

    private function casualtyMessage(int $count, string $population): string
    {
        if ($count === 0) {
            return 'Es gab keine Opfer';
        }
        if ($count === 1) {
            $person = $population === 'Arbeiter' ? 'Arbeiter' : 'Person der freien Bevölkerung';
            return sprintf('Dabei wurde 1 %s versehentlich assimiliert und musste von Sicherheitskräften getötet werden', $person);
        }
        return sprintf('Dabei wurden %d %s versehentlich assimiliert und mussten von Sicherheitskräften getötet werden', $count, $population);
    }

    private function findNeighbor(Colony $colony, PlanetField $field): ?PlanetField
    {
        $width = $colony->getSurfaceWidth();
        if ($width < 1) {
            return null;
        }
        $index = $field->getFieldId();
        $positions = [$index - $width, $index + $width];
        $column = ($index - 1) % $width;
        if ($column > 0) {
            $positions[] = $index - 1;
        }
        if ($column < $width - 1) {
            $positions[] = $index + 1;
        }
        $neighbors = array_filter($colony->getPlanetFields()->toArray(), static fn (PlanetField $candidate): bool =>
            in_array($candidate->getFieldId(), $positions, true) && $candidate->isActive()
            && $candidate->isActivateable() && $candidate->getIntegrity() > 0
            && ($candidate->getBuilding()?->getWorkers() ?? 0) > 0);
        return $neighbors === [] ? null : $neighbors[$this->random->array_rand($neighbors)];
    }
}
