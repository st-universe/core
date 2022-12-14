<?php

namespace Stu\Module\Maintenance;

use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class TopFlightsReward implements MaintenanceHandlerInterface
{
    public const PRESTIGE_REWARDS = [20, 15, 10, 7, 6, 5, 4, 3, 2, 1];

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private UserRepositoryInterface $userRepository;

    private CreatePrestigeLogInterface $createPrestigeLog;

    public function __construct(
        FlightSignatureRepositoryInterface $flightSignatureRepository,
        UserRepositoryInterface $userRepository,
        CreatePrestigeLogInterface $createPrestigeLog
    ) {
        $this->flightSignatureRepository = $flightSignatureRepository;
        $this->userRepository = $userRepository;
        $this->createPrestigeLog = $createPrestigeLog;
    }

    public function handle(): void
    {
        $ranking = $this->flightSignatureRepository->getFlightsTop10();
        $index = 0;

        foreach ($ranking as $entry) {
            $this->createPrestigeLog->createLog(
                self::PRESTIGE_REWARDS[$index],
                sprintf('%d Prestige erhalten für Platz %d unter den Vielfliegern', self::PRESTIGE_REWARDS[$index++], $index),
                $this->userRepository->find($entry['user_id']),
                time()
            );
        }
    }
}
