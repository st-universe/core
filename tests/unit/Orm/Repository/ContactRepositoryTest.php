<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Mockery\MockInterface;
use Stu\Component\Player\Deletion\Handler\ContactDeletionHandler;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\RelationPermission;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class ContactRepositoryTest extends StuTestCase
{
    private EntityManagerInterface&MockInterface $entityManager;

    private ContactRepository $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->subject = new ContactRepository(
            $this->entityManager,
            new ClassMetadata(Contact::class)
        );
    }

    public function testPlayerDeletionRemovesIncomingAndOutgoingContactsWithTheirPermissions(): void
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->withNoArgs()->once()->andReturn(42);
        $otherUser = new User();
        $outgoing = new Contact()->setUser($user)->setRecipient($otherUser);
        $incoming = new Contact()->setUser($otherUser)->setRecipient($user);
        $outgoingPermission = new RelationPermission()->setContact($outgoing);
        $incomingPermission = new RelationPermission()->setContact($incoming);
        $outgoing->addRelationPermission($outgoingPermission);
        $incoming->addRelationPermission($incomingPermission);

        $query = $this->mock(Query::class);
        $this->entityManager->shouldReceive('createQuery')
            ->with('SELECT c FROM Stu\\Orm\\Entity\\Contact c WHERE c.user_id = :userId OR c.recipient = :userId')
            ->once()
            ->andReturn($query);
        $query->shouldReceive('setParameters')->with(['userId' => 42])->once()->andReturnSelf();
        $query->shouldReceive('getResult')->withNoArgs()->once()->andReturn([$outgoing, $incoming]);

        $this->entityManager->shouldReceive('remove')->with($outgoingPermission)->once()->ordered();
        $this->entityManager->shouldReceive('remove')->with($incomingPermission)->once()->ordered();
        $this->entityManager->shouldReceive('remove')->with($outgoing)->once()->ordered();
        $this->entityManager->shouldReceive('remove')->with($incoming)->once()->ordered();
        $this->entityManager->shouldReceive('flush')->withNoArgs()->once()->ordered();

        new ContactDeletionHandler($this->subject)->delete($user);
    }

    public function testTruncateWithoutContactsDoesNotRemoveEntities(): void
    {
        $query = $this->mock(Query::class);
        $this->entityManager->shouldReceive('createQuery')
            ->with('SELECT c FROM Stu\\Orm\\Entity\\Contact c WHERE c.user_id = :userId OR c.recipient = :userId')
            ->once()
            ->andReturn($query);
        $query->shouldReceive('setParameters')->with(['userId' => 42])->once()->andReturnSelf();
        $query->shouldReceive('getResult')->withNoArgs()->once()->andReturn([]);
        $this->entityManager->shouldReceive('flush')->withNoArgs()->once();

        $this->subject->truncateByUser(42);
    }

    public function testTruncateByUserAndOpponentRemovesContactWithoutPermissions(): void
    {
        $contact = new Contact();
        $query = $this->mock(Query::class);
        $this->entityManager->shouldReceive('createQuery')
            ->with('SELECT c FROM Stu\\Orm\\Entity\\Contact c WHERE c.user_id = :userId OR c.recipient = :opponentId')
            ->once()
            ->andReturn($query);
        $query->shouldReceive('setParameters')
            ->with(['userId' => 42, 'opponentId' => 43])
            ->once()
            ->andReturnSelf();
        $query->shouldReceive('getResult')->withNoArgs()->once()->andReturn([$contact]);
        $this->entityManager->shouldReceive('remove')->with($contact)->once()->ordered();
        $this->entityManager->shouldReceive('flush')->withNoArgs()->once()->ordered();

        $this->subject->truncateByUserAndOpponent(42, 43);
    }

    public function testDeleteRemovesAllPermissionsBeforeContact(): void
    {
        $contact = new Contact();
        $firstPermission = new RelationPermission()->setContact($contact);
        $secondPermission = new RelationPermission()->setContact($contact);
        $contact->addRelationPermission($firstPermission)->addRelationPermission($secondPermission);

        $this->entityManager->shouldReceive('remove')->with($firstPermission)->once()->ordered();
        $this->entityManager->shouldReceive('remove')->with($secondPermission)->once()->ordered();
        $this->entityManager->shouldReceive('remove')->with($contact)->once()->ordered();
        $this->entityManager->shouldReceive('flush')->withNoArgs()->once()->ordered();

        $this->subject->delete($contact);
    }
}
