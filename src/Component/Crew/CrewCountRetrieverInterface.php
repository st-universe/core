<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use Stu\Orm\Entity\UserInterface;

interface CrewCountRetrieverInterface
{
    /**
     * Returns the amount of crew currently on debris fields or trade posts
     */
    public function getDebrisAndTradePostsCount(UserInterface $user): int;

    /**
     * Returns the amount of crew currently assigned to ships/stations
     */
    public function getAssignedToShipsCount(UserInterface $user): int;

    /**
     * Returns the amount of crew currently in training
     */
    public function getInTrainingCount(UserInterface $user): int;

    /**
     * Returns the remaining amount of trainable crew
     */
    public function getRemainingCount(UserInterface $user): int;

    /**
     * Returns the amount of crew assigned to ships/stations, etc...
     */
    public function getAssignedCount(UserInterface $user): int;

    /**
     * Returns the amount of maximum available crew for training
     */
    public function getTrainableCount(UserInterface $user): int;
}
