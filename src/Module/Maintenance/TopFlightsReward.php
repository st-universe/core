<?php

namespace Stu\Module\Maintenance;

use Override;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class TopFlightsReward implements MaintenanceHandlerInterface
{
    public const array PRESTIGE_REWARDS = [20, 15, 10, 7, 6, 5, 4, 3, 2, 1];

    public function __construct(private FlightSignatureRepositoryInterface $flightSignatureRepository, private UserRepositoryInterface $userRepository, private CreatePrestigeLogInterface $createPrestigeLog)
    {
    }

    #[Override]
    public function handle(): void
    {
        $ranking = $this->flightSignatureRepository->getFlightsTop10();
        $index = 0;

        foreach ($ranking as $entry) {
            $user = $this->userRepository->find($entry['user_id']);

            if ($user === null) {
                continue;
            }

            $this->createPrestigeLog->createLog(
                self::PRESTIGE_REWARDS[$index],
                sprintf('%d Prestige erhalten für Platz %d unter den Vielfliegern', self::PRESTIGE_REWARDS[$index++], $index),
                $user,
                time()
            );
        }
    }
}
