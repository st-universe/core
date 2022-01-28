<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Module\Colony\Lib\ColonyResetterInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ColonyDeletionHandler implements PlayerDeletionHandlerInterface
{
    private ColonyResetterInterface $colonyResetter;

    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        ColonyResetterInterface $colonyResetter,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyResetter = $colonyResetter;
        $this->colonyRepository = $colonyRepository;
    }

    public function delete(UserInterface $user): void
    {
        foreach ($this->colonyRepository->getOrderedListByUser($user) as $colony) {
            $this->colonyResetter->reset($colony, false);
        }
    }
}
