<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PirateWrathRepositoryInterface;

final class PirateWrathDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private PirateWrathRepositoryInterface $pirateWrathRepository) {}

    #[Override]
    public function delete(UserInterface $user): void
    {
        $entries = $this->pirateWrathRepository->getByUser($user);

        foreach ($entries as $entry) {
            $this->pirateWrathRepository->delete($entry);
            $user->setPirateWrath(null);
        }
    }
}
