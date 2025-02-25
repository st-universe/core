<?php

namespace Stu\Orm\Transaction;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Transaction\EntityManagerFactoryInterface;

class ReopeningEntityManager extends EntityManagerDecorator
{
    private LoggerUtilInterface $logger;

    public function __construct(
        private EntityManagerFactoryInterface $entityManagerFactory,
        private Configuration $configuration,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        parent::__construct($entityManagerFactory->createEntityManager());
        $this->logger = $loggerUtilFactory->getLoggerUtil(true);
    }

    private function reset(): void
    {
        $this->wrapped = $this->entityManagerFactory->createEntityManager();
    }

    #[Override]
    public function getRepository(string $className): EntityRepository
    {
        return $this
            ->configuration
            ->getRepositoryFactory()
            ->getRepository($this, $className);
    }

    #[Override]
    public function beginTransaction(): void
    {
        if (!$this->wrapped->isOpen()) {
            $this->logger->log('!!! TRANSACTION_CLOSED: now resetting!');
            $this->reset();
            $this->logger->log('!!! TRANSACTION_RESETTED');
        }

        if (!$this->wrapped->getConnection()->isTransactionActive()) {
            //$this->logger->log('BEGIN_TRANSACTION');
            $this->wrapped->beginTransaction();
        }
    }

    #[Override]
    public function flush(): void
    {
        if ($this->wrapped->isOpen()) {
            $this->wrapped->flush();
        } else {
            throw new TransactionException('entity manager is closed.');
        }
    }

    #[Override]
    public function commit(): void
    {
        if ($this->wrapped->getConnection()->isTransactionActive()) {
            //$this->logger->log('COMMIT_TRANSACTION');
            $this->wrapped->commit();
        }
    }

    #[Override]
    public function rollback(): void
    {
        if ($this->wrapped->getConnection()->isTransactionActive()) {
            $this->logger->log('CLEAR_UNIT_OF_WORK');
            $this->wrapped->clear();
            $this->logger->log('ROLLBACK_TRANSACTION');
            $this->wrapped->rollback();
        }
    }
}
