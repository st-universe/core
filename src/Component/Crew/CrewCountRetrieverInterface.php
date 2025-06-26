<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use Stu\Orm\Entity\User;

interface CrewCountRetrieverInterface
{
    /**
     * Returns the amount of crew currently on debris fields or trade posts
     */
    public function getDebrisAndTradePostsCount(User $user): int;

    /**
     * Returns the amount of crew currently assigned to ships/stations
     */
    public function getAssignedToShipsCount(User $user): int;

    /**
     * Returns the amount of crew currently in training
     */
    public function getInTrainingCount(User $user): int;

    /**
     * Returns the remaining amount of trainable crew
     */
    public function getRemainingCount(User $user): int;

    /**
     * Returns the amount of crew assigned to ships/stations, etc...
     */
    public function getAssignedCount(User $user): int;

    /**
     * Returns the amount of maximum available crew for training
     */
    public function getTrainableCount(User $user): int;
}
