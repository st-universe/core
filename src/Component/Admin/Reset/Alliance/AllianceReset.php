<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Alliance;

use Doctrine\ORM\EntityManagerInterface;
use Override;

final class AllianceReset implements AllianceResetInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function unsetUserAlliances(): void
    {
        echo "  - removes alliance references\n";

        $this->entityManager->getConnection()->executeQuery('update stu_user set allys_id = null');

        $this->entityManager->flush();
    }
}
