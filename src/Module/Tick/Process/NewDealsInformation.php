<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class NewDealsInformation implements ProcessTickHandlerInterface
{
    private const TRADE_POST_ID = 2;
    private const TIME_THRESHOLD_SECONDS = 60;

    public function __construct(
        private DealsRepositoryInterface $dealsRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    #[\Override]
    public function work(): void
    {
        $currentTime = time();
        $timeThreshold = $currentTime - self::TIME_THRESHOLD_SECONDS;

        $recentDeals = $this->dealsRepository->getRecentlyStartedDeals($timeThreshold);

        if (empty($recentDeals)) {
            return;
        }

        $globalDealExists = false;
        $factionDeals = [];

        foreach ($recentDeals as $deal) {
            $faction = $deal->getFaction();

            if ($faction === null) {
                $globalDealExists = true;
                break;
            } else {
                $factionDeals[$faction->getId()] = true;
            }
        }

        if ($globalDealExists) {
            $users = $this->userRepository->getUsersWithActiveLicense(
                self::TRADE_POST_ID,
                $currentTime
            );

            foreach ($users as $user) {
                $user->setDeals(true);
                $this->userRepository->save($user);
            }
        } else {
            foreach (array_keys($factionDeals) as $factionId) {
                $users = $this->userRepository->getUsersWithActiveLicense(
                    self::TRADE_POST_ID,
                    $currentTime,
                    $factionId
                );

                foreach ($users as $user) {
                    $user->setDeals(true);
                    $this->userRepository->save($user);
                }
            }
        }
    }
}
