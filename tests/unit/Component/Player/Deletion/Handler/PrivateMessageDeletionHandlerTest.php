<?php

declare(strict_types=1);

namespace Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Player\Deletion\Handler\PrivateMessageDeletionHandler;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class PrivateMessageDeletionHandlerTest extends StuTestCase
{
    /** @var EntityManagerInterface&MockInterface */
    private $entityManager;

    private PrivateMessageDeletionHandler $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->subject = new PrivateMessageDeletionHandler(
            $this->entityManager
        );
    }

    public function testDeleteUpdatesTheSendingUser(): void
    {
        $user = $this->mock(User::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);

        $this->entityManager->shouldReceive('getConnection->executeStatement')
            ->with(
                Mockery::on(fn(string $query): bool => str_contains($query, 'UPDATE stu_pms SET send_user = :nobodyId')
                    && str_contains($query, 'WHERE send_user = :userId')),
                ['nobodyId' => 1, 'userId' => 123]
            )
            ->once()
            ->ordered();
        $this->entityManager->shouldReceive('getConnection->executeStatement')
            ->with(
                Mockery::on(
                    fn(string $query): bool => str_contains($query, 'UPDATE stu_pms outbox SET inbox_pm_id = NULL')
                        && str_contains($query, 'WHERE EXISTS (SELECT * FROM stu_pms inbox')
                        && str_contains($query, 'WHERE inbox.id = outbox.inbox_pm_id')
                        && str_contains($query, 'AND inbox.recip_user = :userId)')
                ),
                ['userId' => 123]
            )
            ->once()
            ->ordered();
        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once()
            ->ordered();

        $this->subject->delete($user);
    }
}
