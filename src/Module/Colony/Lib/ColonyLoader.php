<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Exception\AccessViolation;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ColonyLoader implements ColonyLoaderInterface
{
    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        ColonyRepositoryInterface $colonyRepository,
    ) {
        $this->colonyRepository = $colonyRepository;
    }

    public function byIdAndUser(int $colonyId, int $userId): ColonyInterface
    {
        $colony = $this->colonyRepository->find($colonyId);
        if ($colony === null) {
            throw new AccessViolation(sprintf("Colony not existent! Fool: %d", $userId));
        }
        if ($colony->getUserId() !== $userId) {
            throw new AccessViolation(sprintf("Colony owned by another user (%d)! Fool: %d", $colony->getUserId(), $userId));
        }
        return $colony;
    }
}
