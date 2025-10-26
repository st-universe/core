<?php

namespace Stu\Module\Tick\Colony\Component;

use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\Colony;

class ProceedMigration implements ColonyTickComponentInterface
{
    public function __construct(
        private readonly ColonyLibFactoryInterface $colonyLibFactory
    ) {}

    #[\Override]
    public function work(Colony $colony, array &$production, InformationInterface $information): void
    {
        $changeable = $colony->getChangeable();

        if ($colony->getPopulation() > $changeable->getMaxBev()) {
            if ($changeable->getWorkless() !== 0) {
                $bev = random_int(1, $changeable->getWorkless());
                $changeable->setWorkless($changeable->getWorkless() - $bev);
                $information->addInformationf('%d Einwohner sind ausgewandert', $bev);
            }
            return;
        }

        if (
            $changeable->getPopulationLimit() > 0
            && $colony->getPopulation() > $changeable->getPopulationLimit()
            && $changeable->getWorkless()
        ) {
            if (($free = $changeable->getPopulationLimit() - $colony->getWorkers()) > 0) {
                $information->addInformationf(
                    _('Es sind %d Arbeitslose ausgewandert'),
                    $changeable->getWorkless() - $free
                );
                $changeable->setWorkless($free);
            } else {
                $information->addInformation('Es sind alle Arbeitslosen ausgewandert');
                $changeable->setWorkless(0);
            }
        }

        $this->proceedImmigration(
            $colony,
            $production
        );
    }

    /**
     * @param array<int, ColonyProduction> $production
     */
    private function proceedImmigration(
        Colony $colony,
        array $production
    ): void {
        $changeable = $colony->getChangeable();

        $changeable->setWorkless(
            $changeable->getWorkless()
            + $this->colonyLibFactory->createColonyPopulationCalculator($colony, $production)->getGrowth()
        );
    }
}
