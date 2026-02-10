<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingReactivationHandler
{
    public function __construct(private readonly PlanetFieldRepositoryInterface $planetFieldRepository) {}

    /**
     * @param callable(PlanetField):bool $activateField
     */
    public function handleAfterUpgradeFinish(
        PlanetField $field,
        bool $wasActivated,
        callable $activateField
    ): int {
        $host = $field->getHost();
        $upgradedFieldId = $field->getId();

        $field->setReactivateAfterUpgrade(null);
        $this->planetFieldRepository->save($field);

        if (!$host instanceof Colony) {
            return 0;
        }

        if (!$wasActivated) {
            $this->clearReactivationMarkersForFieldId($host, $upgradedFieldId);
            return 0;
        }

        return $this->reactivateOrbitalBuildingsAfterUpgrade($host, $upgradedFieldId, $activateField);
    }

    public function appendReactivationDetails(?string $activationDetails, int $reactivatedCount): ?string
    {
        if ($reactivatedCount <= 0) {
            return $activationDetails;
        }

        return (string) $activationDetails . sprintf(
            ' - Es wurden %d Orbitalgebäude reaktiviert',
            $reactivatedCount
        );
    }

    /**
     * @return array<PlanetField>
     */
    private function getFieldsMarkedForReactivation(Colony $host, int $upgradedFieldId): array
    {
        return array_filter(
            $host->getPlanetFields()->toArray(),
            fn(PlanetField $f) => $f->getReactivateAfterUpgrade() === $upgradedFieldId
        );
    }

    private function clearReactivationMarkersForFieldId(Colony $host, int $upgradedFieldId): void
    {
        foreach ($this->getFieldsMarkedForReactivation($host, $upgradedFieldId) as $fieldToClear) {
            $fieldToClear->setReactivateAfterUpgrade(null);
            $this->planetFieldRepository->save($fieldToClear);
        }
    }

    /**
     * @param callable(PlanetField):bool $activateField
     */
    private function reactivateOrbitalBuildingsAfterUpgrade(
        Colony $host,
        int $upgradedFieldId,
        callable $activateField
    ): int {
        $fieldsToReactivate = $this->getFieldsMarkedForReactivation($host, $upgradedFieldId);
        $reactivatedCount = 0;

        foreach ($fieldsToReactivate as $fieldToReactivate) {
            if ($activateField($fieldToReactivate)) {
                $reactivatedCount++;
            }
            $fieldToReactivate->setReactivateAfterUpgrade(null);
            $this->planetFieldRepository->save($fieldToReactivate);
        }

        return $reactivatedCount;
    }
}

