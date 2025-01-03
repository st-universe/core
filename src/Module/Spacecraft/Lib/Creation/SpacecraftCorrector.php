<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Override;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

class SpacecraftCorrector implements SpacecraftCorrectorInterface
{
    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private ConstructionProgressRepositoryInterface $constructionProgressRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function correctAllSpacecrafts(): void
    {
        $count = 0;

        // correct all ships
        foreach ($this->spacecraftBuildplanRepository->getAllNonNpcBuildplans() as $buildplan) {

            $spacecrafts = $buildplan->getSpacecraftList();
            if ($spacecrafts->isEmpty()) {
                continue;
            }

            foreach ($spacecrafts as $spacecraft) {
                if ($this->correctSpacecraft($spacecraft, $buildplan)) {
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
                $buildplan
            )) {
                $count++;
            }
        }

        if ($count > 0) {
            StuLogger::logf('corrected %d spacecrafts.', $count);
        }
    }

    private function correctSpacecraft(SpacecraftInterface $spacecraft, SpacecraftBuildplanInterface $buildplan): bool
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
        $diff = xdiff_string_diff($toStringBefore, $toStringAfter);

        if ($diff !== '') {
            StuLogger::logf(
                'spacecraftId %d corrected: %s',
                $spacecraft->getId(),
                $diff
            );

            $this->sendPmToOwner($spacecraft, $diff);

            return true;
        }

        return false;
    }

    private function sendPmToOwner(SpacecraftInterface $spacecraft, string $diff): void
    {
        $this->privateMessageSender
            ->send(
                UserEnum::USER_NOONE,
                $spacecraft->getUser()->getId(),
                sprintf(
                    "Die Werte der %s wurden automatisch wie folgt korrigiert:\n\n%s",
                    $spacecraft->getName(),
                    $diff
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                $spacecraft->getHref()
            );
    }
}
