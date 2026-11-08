<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use LogicException;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Information\InformationInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Repository\SpecialResearchRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;

final class TechnologyService
{
    public function __construct(
        private SpecialResearchRepositoryInterface $foundations,
        private TechnologyRepositoryInterface $repository,
        private TechnologyCatalog $catalog,
        private TechnologyFacility $facility,
        private TechnologyMaterials $materials,
        private TechnologyOrderBuilder $orders,
        private TechnologyJobFactory $factory
    ) {}

    public function start(Colony $colony, PlanetField $field, TechnologyRecipe $recipe, InformationInterface $information, ?Module $module = null, int $modules = 0, int $nanites = 0): bool
    {
        $owner = $colony->getUser();
        $this->foundations->lockStorage($colony, $field);
        if ($owner->getId() !== $colony->getUser()->getId()) {
            throw new AccessViolationException('Der Besitzer der Kolonie hat sich geändert');
        }
        if (!$this->facility->canStart($colony, $field, $recipe) || !$this->catalog->canUse($owner, $recipe)) {
            $information->addInformation('Dieses Verfahren ist hier noch nicht verfügbar');
            return false;
        }
        if ($this->isBusy($colony, $field, $recipe, $information)) {
            return false;
        }
        $order = $this->orders->build($owner, $recipe, $module, $modules, $nanites, $information);
        if ($order === null || !$this->materials->consume($colony, $order->costs, $recipe->getEnergyCost(), $information)) {
            return false;
        }
        $job = $recipe->getKind()->isResearch()
            ? $this->factory->research($colony, $field, $recipe, $order->module, $order->system ?? throw new LogicException('Research system missing'), $order->chance, $order->steps)
            : $this->factory->production($colony, $field, $recipe);
        $this->repository->save($job);
        $information->addInformation(sprintf('%s wurde gestartet', $recipe->getName()));
        return true;
    }

    private function isBusy(Colony $colony, PlanetField $field, TechnologyRecipe $recipe, InformationInterface $information): bool
    {
        if ($recipe->getKind()->isResearch()) {
            $busy = $this->repository->hasRunningResearch($colony->getUser())
                || array_any($this->foundations->getByUser($colony->getUser()), static fn ($research): bool => $research->getStatus() === SpecialResearchStatusEnum::RUNNING);
            if ($busy) {
                $information->addInformation('Es läuft bereits eine Sonderforschung');
            }
            return $busy;
        }
        $jobs = $this->repository->getProductionByUser($colony->getUser());
        $matching = array_filter($jobs, static fn ($job): bool => $job->getKind() === $recipe->getKind());
        $perUser = $recipe->getSettings()['max_concurrent_per_user'] ?? PHP_INT_MAX;
        $perField = $recipe->getSettings()['max_concurrent_per_field'] ?? PHP_INT_MAX;
        $busy = count($matching) >= $perUser
            || count(array_filter($matching, static fn ($job): bool => $job->getFieldId() === $field->getId())) >= $perField;
        if ($busy) {
            $information->addInformation('Die benötigte Fertigungskapazität ist bereits belegt');
        }
        return $busy;
    }
}
