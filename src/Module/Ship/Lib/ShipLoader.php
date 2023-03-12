<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Game\SemaphoreConstants;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Exception\AccessViolation;
use Stu\Exception\ShipDoesNotExistException;
use Stu\Exception\ShipIsDestroyedException;
use Stu\Exception\UnallowedUplinkOperation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\SemaphoreUtilInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipLoader implements ShipLoaderInterface
{
    private ShipRepositoryInterface $shipRepository;

    private SemaphoreUtilInterface $semaphoreUtil;

    private GameControllerInterface $game;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        SemaphoreUtilInterface $semaphoreUtil,
        GameControllerInterface $game,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->semaphoreUtil = $semaphoreUtil;
        $this->game = $game;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function getByIdAndUser(int $shipId, int $userId, bool $allowUplink = false): ShipInterface
    {
        return $this->getByIdAndUserAndTargetIntern($shipId, $userId, null, $allowUplink)[$shipId]->get();
    }

    public function getWrapperByIdAndUser(int $shipId, int $userId, bool $allowUplink = false): ShipWrapperInterface
    {
        return $this->getByIdAndUserAndTargetIntern(
            $shipId,
            $userId,
            null,
            $allowUplink
        )[$shipId];
    }

    public function getWrappersByIdAndUserAndTarget(int $shipId, int $userId, int $targetId, bool $allowUplink = false): array
    {
        return $this->getByIdAndUserAndTargetIntern($shipId, $userId, $targetId, $allowUplink);
    }

    /**
     * @return ShipWrapperInterface[]
     */
    private function getByIdAndUserAndTargetIntern(int $shipId, int $userId, ?int $targetId, bool $allowUplink): array
    {
        //$this->loggerUtil->init('LOAD', LoggerEnum::LEVEL_ERROR);

        $this->loggerUtil->log(sprintf(
            'userId: %d, shipId: %d, targetId: %d',
            $userId,
            $shipId,
            $targetId !== null ? $targetId : 0
        ));

        $ship = $this->shipRepository->find($shipId);
        if ($ship === null) {
            throw new ShipDoesNotExistException(_('Ship does not exist!'));
        }
        $this->checkviolations($ship, $userId, $allowUplink);

        $shipArray = $this->acquireSemaphores($ship, $targetId);

        return $shipArray;
    }

    private function checkviolations(ShipInterface $ship, int $userId, bool $allowUplink): void
    {
        if ($ship->isDestroyed()) {
            throw new ShipIsDestroyedException(_('Ship is destroyed!'));
        }

        if ($ship->getUser()->getId() !== $userId) {
            if ($ship->hasCrewmanOfUser($userId)) {
                if (!$allowUplink) {
                    throw new UnallowedUplinkOperation(_('This Operation is not allowed via uplink!'));
                }
                if (!$ship->getSystemState(ShipSystemTypeEnum::SYSTEM_UPLINK)) {
                    throw new UnallowedUplinkOperation(_('Uplink is not activated!'));
                }
                if ($ship->getUser()->isVacationRequestOldEnough()) {
                    throw new UnallowedUplinkOperation(_('Owner is on vacation!'));
                }
            } else {
                throw new AccessViolation(sprintf("Ship owned by another user (%d)! Fool: %d", $ship->getUser()->getId(), $userId));
            }
        }
    }

    public function find(int $shipId): ?ShipWrapperInterface
    {
        return $this->acquireSemaphores($this->shipRepository->find($shipId), null)[$shipId];
    }

    public function save(ShipInterface $ship): void
    {
        $this->shipRepository->save($ship);
    }

    /**
     * @return ShipWrapperInterface[]
     */
    private function acquireSemaphores(ShipInterface $ship, ?int $targetId, ?int $userId = null): array
    {
        $shipId = $ship->getId();
        $result = [];

        if ($targetId === null) {
            if ($this->game->isSemaphoreAlreadyAcquired($ship->getUser()->getId())) {
                $result[$shipId] = $this->shipWrapperFactory->wrapShip($ship);

                return $result;
            }
        }

        //main ship sema on
        $mainSema = $this->semaphoreUtil->getSemaphore(SemaphoreConstants::MAIN_SHIP_SEMAPHORE_KEY);
        if ($userId !== null) {
            $this->loggerUtil->log(sprintf('userId %d waiting for main semaphore', $userId));
        }
        $this->semaphoreUtil->acquireMainSemaphore($mainSema);
        if ($userId !== null) {
            $this->loggerUtil->log(sprintf('userId %d acquired main semaphore', $userId));
        }

        if ($userId !== null) {
            $this->loggerUtil->log(sprintf(
                'userId %d waiting for shipId %d',
                $userId,
                $shipId
            ));
        }
        $result[$shipId] = $this->acquireSemaphoresWithoutMain($ship, $shipId);
        if ($userId !== null) {
            $this->loggerUtil->log(sprintf(
                'userId %d acquired semaphore %d (shipId: %d)',
                $userId,
                $result[$shipId]->getUser()->getId(),
                $shipId
            ));
        }

        if ($targetId !== null) {
            if ($userId !== null) {
                $this->loggerUtil->log(sprintf(
                    'userId %d waiting for targetId %d',
                    $userId,
                    $targetId
                ));
            }
            $result[$targetId] = $this->acquireSemaphoresWithoutMain(null, $targetId);
            if ($userId !== null) {
                $this->loggerUtil->log(sprintf(
                    'userId %d acquired semaphore %d (targetId: %d)',
                    $userId,
                    $result[$targetId] !== null ? $result[$targetId]->getUser()->getId() : 0,
                    $targetId
                ));
            }
        }

        //main ship sema off
        $this->semaphoreUtil->releaseSemaphore($mainSema);
        if ($userId !== null) {
            $this->loggerUtil->log(sprintf('userId %d released main semaphore', $userId));
        }

        return $result;
    }

    private function acquireSemaphoresWithoutMain(?ShipInterface $ship, int $shipId): ?ShipWrapperInterface
    {
        if ($ship === null) {
            $ship = $this->shipRepository->find($shipId);
        }

        if ($ship === null) {
            return null;
        }

        $key = $ship->getUser()->getId();
        $semaphore = $this->semaphoreUtil->getSemaphore($key);
        $this->semaphoreUtil->acquireSemaphore($key, $semaphore); //prüft ob schon genommen
        return $this->shipWrapperFactory->wrapShip($ship);
    }
}
