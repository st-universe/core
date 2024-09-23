<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;

final class AstronomicalEntryDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private AstroEntryRepositoryInterface $astroEntryRepository) {}

    #[Override]
    public function delete(UserInterface $user): void
    {
        $entries = $this->astroEntryRepository->getByUser($user);

        foreach ($entries as $entry) {
            $this->astroEntryRepository->delete($entry);
        }
    }
}