<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use Mockery\MockInterface;
use request;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\StuTestCase;

final class RenameBuildplanTest extends StuTestCase
{
    private MockInterface&SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository;

    private MockInterface&NPCLogRepositoryInterface $npcLogRepository;

    private RenameBuildplan $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->spacecraftBuildplanRepository = $this->mock(SpacecraftBuildplanRepositoryInterface::class);
        $this->npcLogRepository = $this->mock(NPCLogRepositoryInterface::class);
        $this->subject = new RenameBuildplan($this->spacecraftBuildplanRepository, $this->npcLogRepository);
    }

    public function testAdminCanRenameAnotherUsersBuildplan(): void
    {
        $buildplanId = 42;
        $newName = 'Neuer Bauplanname';
        $oldName = 'Alter Bauplanname';
        $context = $this->mock(ActionControllerContext::class);
        $admin = $this->mock(User::class);
        $buildplan = $this->mock(SpacecraftBuildplan::class);

        request::setMockVars([
            'planid' => $buildplanId,
            'newName' => $newName
        ]);

        $context->shouldReceive('getUser')
            ->once()
            ->andReturn($admin);
        $context->shouldReceive('getInfo->addInformation')
            ->with('Der Name des Bauplans wurde geändert')
            ->once();

        $admin->shouldReceive('getId')
            ->once()
            ->andReturn(101);
        $admin->shouldReceive('isNpc')
            ->once()
            ->andReturn(false);

        $this->spacecraftBuildplanRepository->shouldReceive('find')
            ->with($buildplanId)
            ->once()
            ->andReturn($buildplan);
        $buildplan->shouldReceive('getName')
            ->once()
            ->andReturn($oldName);
        $buildplan->shouldReceive('setName')
            ->with($newName)
            ->once()
            ->andReturnSelf();
        $this->spacecraftBuildplanRepository->shouldReceive('save')
            ->with($buildplan)
            ->once();

        $this->subject->handle($context);
    }
}
