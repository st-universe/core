<?php

namespace Stu\Module\Tick\Colony\Component;

use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;

class ProceedMigration implements ColonyTickComponentInterface
{
    public function __construct(
        private readonly ColonyLibFactoryInterface $colonyLibFactory
    ) {}

    public function work(ColonyInterface $colony, array &$production, InformationInterface $information): void
    {
        if ($colony->getPopulation() > $colony->getMaxBev()) {
            if ($colony->getWorkless() !== 0) {
                $bev = random_int(1, $colony->getWorkless());
                $colony->setWorkless($colony->getWorkless() - $bev);
                $information->addInformationf("%d Einwohner sind ausgewandert", $bev);
            }
            return;
        }

        if ($colony->getPopulationLimit() > 0 && $colony->getPopulation() > $colony->getPopulationLimit() && $colony->getWorkless()) {
            if (($free = ($colony->getPopulationLimit() - $colony->getWorkers())) > 0) {
                $information->addInformationf(
                    _('Es sind %d Arbeitslose ausgewandert'),
                    ($colony->getWorkless() - $free)
                );
                $colony->setWorkless($free);
            } else {
                $information->addInformation('Es sind alle Arbeitslosen ausgewandert');
                $colony->setWorkless(0);
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
        ColonyInterface $colony,
        array $production
    ): void {
        // @todo
        $colony->setWorkless(
            $colony->getWorkless() +
                $this->colonyLibFactory->createColonyPopulationCalculator($colony, $production)->getGrowth()
        );
    }
}
