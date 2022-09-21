<?php

namespace Stu\Module\Control;

use Doctrine\ORM\EntityManagerInterface;

final class EntityManagerLogging implements EntityManagerLoggingInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
