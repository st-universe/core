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

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        SemaphoreUtilInterface $semaphoreUtil,
        GameControllerInterface $game,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->semaphoreUtil = $semaphoreUtil;
        $this->game = $game;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function getByIdAndUser(int $shipId, int $userId, bool $allowUplink = false): ShipInterface
    {
        return $this->getByIdAndUserAndTargetIntern($shipId, $userId, null, $allowUplink)[$shipId];
    }

    public function getByIdAndUserAndTarget(int $shipId, int $userId, int $targetId, bool $allowUplink = false): array
    {
        return $this->getByIdAndUserAndTargetIntern($shipId, $userId, $targetId, $allowUplink);
    }

    private function getByIdAndUserAndTargetIntern(int $shipId, int $userId, ?int $targetId, bool $allowUplink): array
    {
        if ($userId === 101 || $userId === 102) {
            $this->loggerUtil->init('LOAD', LoggerEnum::LEVEL_ERROR);
        }

        $this->loggerUtil->log(sprintf(
            'userId: %d, shipId: %d, targetId: %d',
            $userId,
            $shipId,
            $targetId !== null ? $targetId : 0
        ));

        $shipArray = $this->acquireSemaphores($shipId, $targetId, $userId);

        $this->checkviolations($shipArray[$shipId], $userId, $allowUplink);

        return $shipArray;
    }

    private function checkviolations(?ShipInterface $ship, int $userId, bool $allowUplink): void
    {
        if ($ship === null) {
            throw new ShipDoesNotExistException(_('Ship does not exist!'));
        }

        if ($ship->getIsDestroyed()) {
            throw new ShipIsDestroyedException(_('Ship is destroyed!'));
        }

        if ($ship->getUser()->getId() !== $userId) {
            if ($this->hasCrewmanOfUser($ship, $userId)) {
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

    public function find(int $shipId): ?ShipInterface
    {
        return $this->acquireSemaphores($shipId, null)[$shipId];
    }

    public function save(ShipInterface $ship): void
    {
        $this->shipRepository->save($ship);
    }

    private function acquireSemaphores(int $shipId, ?int $targetId, ?int $userId = null): array
    {
        $result = [];

        $ship = $this->shipRepository->find($shipId);
        if ($targetId === null && $ship !== null) {
            if ($this->game->isSemaphoreAlreadyAcquired($ship->getUser()->getId())) {
                $result[$shipId] = $ship;

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
        $result[$shipId] = $this->acquireSemaphoresWithoutMain($shipId);
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
            $result[$targetId] = $this->acquireSemaphoresWithoutMain($targetId);
            if ($userId !== null) {
                $this->loggerUtil->log(sprintf(
                    'userId %d acquired semaphore %d (targetId: %d)',
                    $userId,
                    $result[$targetId]->getUser()->getId(),
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

    private function acquireSemaphoresWithoutMain(int $shipId): ?ShipInterface
    {
        $ship = $this->shipRepository->find($shipId);

        if ($ship === null) {
            return null;
        }

        $key = $ship->getUser()->getId();
        $semaphore = $this->semaphoreUtil->getSemaphore($key);
        $this->semaphoreUtil->acquireSemaphore($key, $semaphore); //prüft ob schon genommen

        return $ship;
    }

    private function hasCrewmanOfUser(ShipInterface $ship, int $userId)
    {
        foreach ($ship->getCrewlist() as $shipCrew) {
            if ($shipCrew->getCrew()->getUser()->getId() === $userId) {
                return true;
            }
        }

        return false;
    }
}
