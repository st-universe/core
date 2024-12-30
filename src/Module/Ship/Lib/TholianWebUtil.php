<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use RuntimeException;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Data\WebEmitterSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class TholianWebUtil implements TholianWebUtilInterface
{
    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private TholianWebRepositoryInterface $tholianWebRepository,
        private SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private StuTime $stuTime,
        private PrivateMessageSenderInterface $privateMessageSender,
        private EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function releaseSpacecraftFromWeb(SpacecraftWrapperInterface $wrapper): void
    {
        $this->loggerUtil->log(sprintf('releaseSpacecraftFromWeb, shipId: %d', $wrapper->get()->getId()));

        $spacecraft = $wrapper->get();
        $web = $spacecraft->getHoldingWeb();
        if ($web === null) {
            return;
        }

        $web->getCapturedSpacecrafts()->removeElement($spacecraft);

        if ($web->getCapturedSpacecrafts()->isEmpty()) {
            $this->resetWebHelpers($web, $wrapper->getSpacecraftWrapperFactory());
            $this->removeWeb($web);
        }

        $spacecraft->setHoldingWeb(null);
        $this->spacecraftRepository->save($spacecraft);
    }

    #[Override]
    public function releaseAllShips(TholianWebInterface $web, SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory): void
    {
        foreach ($web->getCapturedSpacecrafts() as $target) {
            $this->releaseSpacecraftFromWeb($spacecraftWrapperFactory->wrapSpacecraft($target));

            //notify target owner
            $this->privateMessageSender->send(
                $web->getUser()->getId(),
                $target->getUser()->getId(),
                sprintf(
                    'Das Energienetz um die %s in Sektor %s wurde aufgelöst',
                    $target->getName(),
                    $target->getSectorString()
                ),
                $target->getType()->getMessageFolderType()
            );
        }
    }

    #[Override]
    public function removeWeb(TholianWebInterface $web): void
    {
        $this->loggerUtil->log(sprintf('removeWeb, webId: %d', $web->getId()));

        $this->tholianWebRepository->delete($web);
    }

    #[Override]
    public function releaseWebHelper(ShipWrapperInterface $wrapper): void
    {
        $this->loggerUtil->log(sprintf('releaseWebHelper, shipId: %d', $wrapper->get()->getId()));

        $emitter = $this->getMandatoryEmitter($wrapper);
        $web = $this->getMandatoryWebUnderConstruction($emitter);

        $finishedTime = $this->releaseWebHelperIntern($wrapper);
        if ($finishedTime === null) {
            return;
        }

        $currentSpinnerSystems = $this->shipSystemRepository->getWebConstructingShipSystems($web->getId());

        //remove web if lost
        if ($currentSpinnerSystems === []) {
            $this->releaseAllShips($web, $wrapper->getSpacecraftWrapperFactory());
            $this->removeWeb($web);
        } else {
            $ship = $wrapper->get();

            //notify other web spinners
            foreach ($currentSpinnerSystems as $shipSystem) {
                $this->privateMessageSender->send(
                    $ship->getUser()->getId(),
                    $shipSystem->getSpacecraft()->getUser()->getId(),
                    sprintf(
                        'Die %s hat den Netzaufbau in Sektor %s verlassen, Fertigstellung: %s',
                        $ship->getName(),
                        $ship->getSectorString(),
                        $this->stuTime->transformToStuDateTime($finishedTime)
                    ),
                    PrivateMessageFolderTypeEnum::SPECIAL_SHIP
                );
            }
        }
    }

    #[Override]
    public function resetWebHelpers(
        TholianWebInterface $web,
        SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        bool $isFinished = false
    ): void {
        $this->loggerUtil->log(sprintf('resetWebHelpers, webId: %d', $web->getId()));

        $systems = $this->shipSystemRepository->getWebConstructingShipSystems($web->getId());
        foreach ($systems as $system) {
            /** @var ShipWrapperInterface */
            $wrapper = $spacecraftWrapperFactory->wrapSpacecraft($system->getSpacecraft());
            $this->releaseWebHelperIntern($wrapper);

            //notify helpers when finished
            if ($isFinished) {
                $ship = $system->getSpacecraft();

                $this->privateMessageSender->send(
                    UserEnum::USER_NOONE,
                    $ship->getUser()->getId(),
                    sprintf(
                        'Das Energienetz in Sektor %s wurde fertiggestellt',
                        $ship->getSectorString()
                    ),
                    PrivateMessageFolderTypeEnum::SPECIAL_SHIP
                );
            }
        }
    }

    private function releaseWebHelperIntern(ShipWrapperInterface $wrapper): ?int
    {
        $emitter = $this->getMandatoryEmitter($wrapper);
        $web = $this->getMandatoryWebUnderConstruction($emitter);

        if ($emitter->ownedWebId === $emitter->webUnderConstructionId && !$web->isFinished()) {
            $emitter->setOwnedWebId(null);
        }
        $emitter->setWebUnderConstructionId(null)->update();
        $wrapper->getSpacecraftSystemManager()->deactivate($wrapper, SpacecraftSystemTypeEnum::THOLIAN_WEB, true);

        $ship = $wrapper->get();
        $ship->setState(SpacecraftStateEnum::SHIP_STATE_NONE);
        $this->spacecraftRepository->save($ship);

        //update finish time last
        return $this->updateWebFinishTime($web, -1);
    }

    #[Override]
    public function updateWebFinishTime(TholianWebInterface $web, ?int $helperModifier = null): ?int
    {
        $this->loggerUtil->log(sprintf('updateWebFinishTime, webId: %d', $web->getId()));

        //flush to read persistent webIds from system data
        $this->entityManager->flush();

        if ($web->isFinished()) {
            return null;
        }

        $currentSpinnerSystems = $this->shipSystemRepository->getWebConstructingShipSystems($web->getId());
        $time = $this->stuTime->time();

        //adjust by modified web spinner count
        if ($helperModifier !== null) {
            $secondsLeft = $web->getFinishedTime() - $time;
            $currentSpinnerCount = count($currentSpinnerSystems);
            $oldSpinnerCount =  $currentSpinnerCount - $helperModifier;

            if ($currentSpinnerCount !== 0) {
                $web->setFinishedTime($time + (int)ceil($secondsLeft * $oldSpinnerCount / $currentSpinnerCount));
            }
            $this->tholianWebRepository->save($web);

            return $web->getFinishedTime();
        }

        //initialize by weight of targets and spinners
        $targetWeightSum = array_reduce(
            $web->getCapturedSpacecrafts()->toArray(),
            fn(int $sum, SpacecraftInterface $spacecraft): int => $sum + $spacecraft->getRump()->getTractorMass(),
            0
        );
        $webSpinnerWeightSum = array_reduce(
            $this->shipSystemRepository->getWebConstructingShipSystems($web->getId()),
            fn(int $sum, SpacecraftSystemInterface $shipSystem): int => $sum + $shipSystem->getSpacecraft()->getRump()->getTractorMass(),
            0
        );

        $this->loggerUtil->log(sprintf('targetWeightSum: %d, webSpinnerWeightSum: %d', $targetWeightSum, $webSpinnerWeightSum));

        //only update if web spinners left
        if ($webSpinnerWeightSum !== 0) {
            $web->setFinishedTime($time + ((int)ceil($targetWeightSum / $webSpinnerWeightSum)) * TimeConstants::ONE_HOUR_IN_SECONDS);
            $this->tholianWebRepository->save($web);
        }

        return $web->getFinishedTime();
    }

    private function getMandatoryEmitter(ShipWrapperInterface $wrapper): WebEmitterSystemData
    {
        $emitter = $wrapper->getWebEmitterSystemData();
        if ($emitter === null) {
            throw new RuntimeException('no emitter');
        }

        return $emitter;
    }
    private function getMandatoryWebUnderConstruction(WebEmitterSystemData $emitter): TholianWebInterface
    {
        $web = $emitter->getWebUnderConstruction();
        if ($web === null) {
            throw new RuntimeException('no web under construction');
        }

        return $web;
    }

    #[Override]
    public function isTargetOutsideFinishedTholianWeb(EntityWithInteractionCheckInterface $source, EntityWithInteractionCheckInterface $target): bool
    {
        if (!$source instanceof SpacecraftInterface) {
            return false;
        }

        $web = $source->getHoldingWeb();
        if ($web === null || !$web->isFinished()) {
            return false;
        }

        return !$target instanceof SpacecraftInterface || $target->getHoldingWeb() !== $web;
    }

    #[Override]
    public function isTargetInsideFinishedTholianWeb(EntityWithInteractionCheckInterface $source, EntityWithInteractionCheckInterface $target): bool
    {
        if (!$target instanceof SpacecraftInterface) {
            return false;
        }

        $web = $target->getHoldingWeb();
        if ($web === null || !$web->isFinished()) {
            return false;
        }

        return !$source instanceof SpacecraftInterface || $source->getHoldingWeb() !== $web;
    }
}
