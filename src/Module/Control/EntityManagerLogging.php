<?php

namespace Stu\Module\Control;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Entity\GameRequestInterface;

final class EntityManagerLogging implements EntityManagerLoggingInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function beginTransaction()
    {
        $this->entityManager->beginTransaction();
    }

    public function persist(GameRequestInterface $request)
    {
        $this->entityManager->persist($request);
    }

    public function flush()
    {
        $this->entityManager->flush();
    }

    public function commit()
    {
        $this->entityManager->commit();
    }
}
