<?php

namespace Stu\Module\Control;

use Doctrine\ORM\Decorator\EntityManagerDecorator;

final class ReloadOnRollbackEntityManager extends EntityManagerDecorator
{
    private EntityManagerCreatorInterface $entityManagerCreator;

    public function __construct(
        EntityManagerCreatorInterface $entityManagerCreator
    ) {
        $this->entityManagerCreator = $entityManagerCreator;
        $this->reload();
    }

    public function rollback()
    {
        $this->wrapped->rollback();
        $this->reload();
    }

    public function reload(): void
    {
        $this->wrapped = $this->entityManagerCreator->create();
    }
}
