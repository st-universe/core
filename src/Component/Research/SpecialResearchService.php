<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\SpecialResearchRepositoryInterface;

final class SpecialResearchService
{
    public function __construct(
        private SpecialResearchRepositoryInterface $repository,
        private SpecialResearchCatalog $catalog,
        private CommodityRepositoryInterface $commodities,
        private StorageManagerInterface $storageManager,
        private ColonyFunctionManagerInterface $functions,
        private StuTime $time
    ) {}

    public function start(Colony $colony, AwayMissionResearchEnum $type, InformationInterface $information): bool
    {
        $user = $colony->getUser();
        $this->repository->lockStorage($colony);
        if ($colony->getUser()->getId() !== $user->getId()) {
            throw new AccessViolationException('Der Besitzer der Kolonie hat sich geändert');
        }
        if (!$this->functions->hasActiveFunction($colony, BuildingFunctionEnum::SPECIAL_RESEARCH, false)) {
            $information->addInformation('Für die Sonderforschung wird ein aktives Forschungszentrum oder Technologiecenter benötigt');
            return false;
        }

        $researches = $this->repository->getByUser($colony->getUser());
        if (!$this->canStart($researches, $type, $information)) {
            return false;
        }
        $definition = $this->catalog->getDefinition($type, $colony->getUser()->getFactionId());
        if ($definition === null) {
            $information->addInformation('Für diese Grundlagenforschung ist kein gültiges Rezept verfügbar');
            return false;
        }
        $costs = $this->catalog->getCosts($definition);
        if (!$this->hasMaterials($colony, $costs, $information)) {
            return false;
        }
        foreach ($costs as $commodityId => $amount) {
            $commodity = $this->commodities->find($commodityId) ?? throw new \RuntimeException('Research commodity missing');
            $this->storageManager->lowerStorage($colony, $commodity, $amount);
        }

        $research = array_find($researches, static fn (SpecialResearch $entry): bool => $entry->getResearchType() === $type)
            ?? new SpecialResearch()->setUser($colony->getUser())->setResearchType($type);
        $now = $this->time->time();
        $research->setColony($colony)->setStatus(SpecialResearchStatusEnum::RUNNING)
            ->setStartTime($now)->setFinishTime($now + $definition->getDuration())
            ->setFailureChance($definition->getFailureChance());
        $this->repository->save($research);
        $information->addInformation(sprintf('Die Grundlagenforschung für %s wurde gestartet', $type->getDescription()));
        return true;
    }

    /** @param list<SpecialResearch> $researches */
    private function canStart(array $researches, AwayMissionResearchEnum $type, InformationInterface $information): bool
    {
        foreach ($researches as $research) {
            if ($research->getStatus() === SpecialResearchStatusEnum::RUNNING) {
                $information->addInformation('Es läuft bereits eine Sonderforschung');
                return false;
            }
            if ($research->getResearchType() === $type && $research->getStatus() === SpecialResearchStatusEnum::COMPLETED) {
                $information->addInformation('Diese Grundlagenforschung wurde bereits erfolgreich abgeschlossen');
                return false;
            }
        }
        return true;
    }

    /** @param array<int, int> $costs */
    private function hasMaterials(Colony $colony, array $costs, InformationInterface $information): bool
    {
        foreach ($costs as $commodityId => $amount) {
            if (($colony->getStorage()->get($commodityId)?->getAmount() ?? 0) < $amount) {
                $information->addInformation('Die benötigten Materialien sind nicht vollständig im Kolonielager vorhanden');
                return false;
            }
        }
        return true;
    }
}
