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
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyRepository = $colonyRepository;
    }

    public function byIdAndUser(int $colonyId, int $userId): ColonyInterface
    {
        $colony = $this->colonyRepository->find($colonyId);
        if ($colony === null || $colony->getUserId() !== $userId) {
            throw new AccessViolation("Colony not existent or owned by another user");
        }
        return $colony;
    }
}
