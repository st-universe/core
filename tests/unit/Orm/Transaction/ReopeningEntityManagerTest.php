<?php

declare(strict_types=1);

namespace Stu\Orm\Transaction;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mockery\MockInterface;
use Override;
use Stu\Orm\Transaction\EntityManagerFactoryInterface;
use Stu\StuTestCase;

class ReopeningEntityManagerTest extends StuTestCase
{
    private MockInterface&EntityManagerFactoryInterface  $entityManagerFactory;
    private MockInterface&Configuration  $configuration;

    private MockInterface&EntityManagerInterface $wrapped;

    private ReopeningEntityManager $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->entityManagerFactory = $this->mock(EntityManagerFactoryInterface::class);
        $this->configuration = $this->mock(Configuration::class);

        $this->wrapped = $this->mock(EntityManagerInterface::class);

        $this->entityManagerFactory->shouldReceive('createEntityManager')
            ->withNoArgs()
            ->once()
            ->andReturn($this->wrapped);

        $this->subject = new ReopeningEntityManager(
            $this->entityManagerFactory,
            $this->configuration,
            $this->initLoggerUtil()
        );
    }

    public function testGetRepositoryExpectRepositoryFromFactory(): void
    {
        $repository = $this->mock(EntityRepository::class);

        $this->configuration->shouldReceive('getRepositoryFactory->getRepository')
            ->with($this->subject, 'CLASSNAME')
            ->once()
            ->andReturn($repository);

        $result = $this->subject->getRepository('CLASSNAME');

        $this->assertEquals($repository, $result);
    }

    public function testBeginTransactionExpectReopenIfClosed(): void
    {
        $new = $this->mock(EntityManagerInterface::class);

        $this->wrapped->shouldReceive('isOpen')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->entityManagerFactory->shouldReceive('createEntityManager')
            ->withNoArgs()
            ->once()
            ->andReturn($new);

        $new->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once();
        $new->shouldReceive('getConnection->isTransactionActive')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->subject->beginTransaction();
    }

    public function testBeginTransactionExpectTransactionIfNotAlreadyActiveButOpen(): void
    {
        $this->wrapped->shouldReceive('isOpen')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->wrapped->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once();

        $this->wrapped->shouldReceive('getConnection->isTransactionActive')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->subject->beginTransaction();
    }

    public function testBeginTransactionExpectNoTransactionIfAlreadyActive(): void
    {
        $this->wrapped->shouldReceive('isOpen')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->wrapped->shouldReceive('getConnection->isTransactionActive')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->beginTransaction();
    }

    public function testRollbackWhenTransactionIsActive(): void
    {
        $this->wrapped->shouldReceive('clear')
            ->withNoArgs()
            ->once();
        $this->wrapped->shouldReceive('rollback')
            ->withNoArgs()
            ->once();

        $this->wrapped->shouldReceive('getConnection->isTransactionActive')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->rollback();
    }
}
