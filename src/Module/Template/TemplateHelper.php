<?php

declare(strict_types=1);

namespace Stu\Module\Template;

use Override;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;

final class TemplateHelper implements TemplateHelperInterface
{
    public function __construct(
        private PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever,
        private ColonyTerraformingRepositoryInterface $colonyTerraformingRepository
    ) {
    }

    #[Override]
    public function formatProductionValue(int $value): string
    {
        if ($value > 0) {
            return sprintf('<span class="positive">+%d</span>', $value);
        } elseif ($value < 0) {
            return sprintf('<span class="negative">%d</span>', $value);
        }
        return (string) $value;
    }

    #[Override]
    public function addPlusCharacter(string $value): string
    {
        if ($value <= 0) {
            return $value;
        }
        return sprintf('+%d', $value);
    }

    #[Override]
    public function jsquote(string $str): string
    {
        return str_replace(
            [
                "\\",
                "'",
            ],
            [
                "\\\\",
                "\\'",
            ],
            $str
        );
    }

    #[Override]
    public function formatSeconds(string $time): string
    {
        $time = (int) $time;
        $h = floor($time / 3600);
        $time -= $h * 3600;
        $m = floor($time / 60);
        $time -= $m * 60;

        $ret = '';
        if ($h > 0) {
            $ret .= $h . 'h';
        }
        if ($m > 0) {
            $ret .= ' ' . $m . 'm';
        }
        if ($time > 0) {
            $ret .= ' ' . $time . 's';
        }
        return $ret;
    }

    #[Override]
    public function getNumberWithThousandSeperator(int $number): string
    {
        return number_format((float) $number, 0, '', '.');
    }

    #[Override]
    public function getPlanetFieldTypeDescription(
        int $fieldTypeId
    ): string {
        return $this->planetFieldTypeRetriever->getDescription($fieldTypeId);
    }

    #[Override]
    public function getPlanetFieldTitle(
        PlanetFieldInterface $planetField
    ): string {
        $fieldTypeName = self::getPlanetFieldTypeDescription($planetField->getFieldType());

        $building = $planetField->getBuilding();

        if ($building === null) {
            $terraFormingState = null;
            $host = $planetField->getHost();
            if ($host instanceof ColonyInterface) {
                $terraFormingState = $this->colonyTerraformingRepository->getByColonyAndField(
                    $host->getId(),
                    $planetField->getId()
                );
            }
            if ($terraFormingState !== null) {
                return sprintf(
                    '%s läuft bis %s',
                    $terraFormingState->getTerraforming()->getDescription(),
                    date('d.m.Y H:i', $terraFormingState->getFinishDate())
                );
            }
            return $fieldTypeName;
        }

        if ($planetField->isUnderConstruction()) {
            return sprintf(
                'In Bau: %s auf %s - Fertigstellung: %s',
                $building->getName(),
                $fieldTypeName,
                date('d.m.Y H:i', $planetField->getBuildtime())
            );
        }
        if (!$planetField->isActivateable()) {
            return $building->getName() . " auf " . $fieldTypeName;
        }

        if ($planetField->isActive()) {
            if ($planetField->isDamaged()) {
                return $building->getName() . " (aktiviert, beschädigt) auf " . $fieldTypeName;
            }
            return $building->getName() . " (aktiviert) auf " . $fieldTypeName;
        }

        if ($planetField->hasHighDamage()) {
            return $building->getName() . " (stark beschädigt) auf " . $fieldTypeName;
        }

        return $building->getName() . " (deaktiviert) auf " . $fieldTypeName;
    }
}
