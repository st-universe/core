<?php

declare(strict_types=1);

namespace Stu\Component\Database;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\StuTestCase;

class AchievementManagerTest extends StuTestCase
{
    private MockInterface&DatabaseUserRepositoryInterface $databaseUserRepository;
    private MockInterface&CreateDatabaseEntryInterface $createDatabaseEntry;

    private AchievementManagerInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->databaseUserRepository = $this->mock(DatabaseUserRepositoryInterface::class);
        $this->createDatabaseEntry = $this->mock(CreateDatabaseEntryInterface::class);

        $this->subject = new AchievementManager(
            $this->databaseUserRepository,
            $this->createDatabaseEntry
        );
    }

    #[Override]
    public function tearDown(): void
    {
        AchievementManager::reset();
    }

    public static function provideData(): array
    {
        return [
            [null],
            [-1],
            [0]
        ];
    }

    #[DataProvider('provideData')]
    public function testCheckDatabaseItemExpectNothingWhenEntryIdUnset(?int $databaseEntryId): void
    {
        $user = $this->mock(User::class);

        $this->databaseUserRepository->shouldNotHaveBeenCalled();
        $this->createDatabaseEntry->shouldNotHaveBeenCalled();

        $this->subject->checkDatabaseItem($databaseEntryId, $user);
    }

    public function testCheckDatabaseItemExpectNoCreationWhenAlreadyExists(): void
    {
        $user = $this->mock(User::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->databaseUserRepository->shouldReceive('exists')
            ->with(42, 1)
            ->once()
            ->andReturn(true);

        $this->subject->checkDatabaseItem(1, $user);
    }

    public function testCheckDatabaseItemExpectCreationWhenNew(): void
    {
        $user = $this->mock(User::class);
        $entry = $this->mock(DatabaseEntry::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $entry->shouldReceive('getDescription')
            ->withNoArgs()
            ->once()
            ->andReturn('DESC');
        $entry->shouldReceive('getCategory->getPoints')
            ->withNoArgs()
            ->once()
            ->andReturn(7);

        $this->databaseUserRepository->shouldReceive('exists')
            ->with(42, 111)
            ->once()
            ->andReturn(false);

        $this->createDatabaseEntry->shouldReceive('createDatabaseEntryForUser')
            ->with($user, 111)
            ->once()
            ->andReturn($entry);

        $this->subject->checkDatabaseItem(111, $user);
        $this->assertEquals(['Neuer Datenbankeintrag: DESC (+7 Punkte)'], $this->subject->getAchievements());
    }
}
