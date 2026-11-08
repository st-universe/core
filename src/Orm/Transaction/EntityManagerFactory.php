<?php

declare(strict_types=1);

namespace Stu\Orm\Transaction;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Override;
use Stu\Component\Borg\BorgSphereDeletionListener;

class EntityManagerFactory implements EntityManagerFactoryInterface
{
    public function __construct(
        private ConnectionFactoryInterface $connectionFactory,
        private Configuration $configuration,
        private ?BorgSphereDeletionListener $borgSphereDeletionListener = null
    ) {}

    #[Override]
    public function createEntityManager(?Connection $connection = null): EntityManagerInterface
    {
        $em = new EntityManager(
            $connection ?? $this->connectionFactory->createConnection(),
            $this->configuration
        );
        if ($this->borgSphereDeletionListener !== null) {
            $em->getEventManager()->addEventListener([Events::preRemove], $this->borgSphereDeletionListener);
        }
        return $em;
    }
}
