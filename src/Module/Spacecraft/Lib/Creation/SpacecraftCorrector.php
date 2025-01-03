<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Override;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

class SpacecraftCorrector implements SpacecraftCorrectorInterface
{
    private const string HEADER = "--- Vorher\n+++ Nachher\n";

    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private ConstructionProgressRepositoryInterface $constructionProgressRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[Override]
    public function correctAllSpacecrafts(): void
    {
        $count = 0;

        $differ = new Differ(new DiffOnlyOutputBuilder(self::HEADER));

        // correct all ships
        foreach ($this->spacecraftBuildplanRepository->getAllNonNpcBuildplans() as $buildplan) {

            $spacecrafts = $buildplan->getSpacecraftList();
            if ($spacecrafts->isEmpty()) {
                continue;
            }

            foreach ($spacecrafts as $spacecraft) {
                if ($this->correctSpacecraft($spacecraft, $buildplan, $differ)) {
                    $count++;
                }
            }
        }

        // correct all stations
        foreach ($this->constructionProgressRepository->findAll() as $progress) {

            $station = $progress->getStation();
            $buildplan = $station->getBuildplan();
            if (
                $buildplan === null
                || $progress->getRemainingTicks() !== 0
            ) {
                continue;
            }

            if ($this->correctSpacecraft(
                $progress->getStation(),
                $buildplan,
                $differ
            )) {
                $count++;
            }
        }

        if ($count > 0) {
            StuLogger::logf('corrected %d spacecrafts.', $count);
        }
    }

    private function correctSpacecraft(SpacecraftInterface $spacecraft, SpacecraftBuildplanInterface $buildplan, Differ $differ): bool
    {
        $rump = $buildplan->getRump();
        $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);
        $toStringBefore = $wrapper->__toString();

        foreach ($buildplan->getModules() as $buildplanModule) {
            $moduleType = $buildplanModule->getModuleType();
            $moduleRumpWrapper = $moduleType->getModuleRumpWrapperCallable()($rump, $buildplan);
            $moduleRumpWrapper->apply($wrapper);
        }

        $toStringAfter = $wrapper->__toString();
        $diff = $differ->diff($toStringBefore, $toStringAfter);

        if (strlen($diff) > strlen(self::HEADER)) {
            StuLogger::logf(
                'spacecraftId %d corrected: %s',
                $spacecraft->getId(),
                $diff
            );

            return true;
        }

        return false;
    }
}