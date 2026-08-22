<?php

declare(strict_types=1);

namespace Stu\Module\Database\Action\DismissCrew;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Wrapper\SpacecraftStorageCrewLogic;
use Stu\Module\Database\View\ShowCrewManagement\ShowCrewManagement;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;

final class DismissCrewTest extends ActionControllerTestCase
{
    private MockInterface&CrewAssignmentRepositoryInterface $crewAssignmentRepository;
    private MockInterface&CrewRepositoryInterface $crewRepository;
    private MockInterface&SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory;
    private MockInterface&SpacecraftStorageCrewLogic $spacecraftStorageCrewLogic;
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;
    private MockInterface&SpacecraftRemoverInterface $spacecraftRemover;
    private DismissCrew $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->crewAssignmentRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->crewRepository = $this->mock(CrewRepositoryInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->spacecraftStorageCrewLogic = $this->mock(SpacecraftStorageCrewLogic::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->spacecraftRemover = $this->mock(SpacecraftRemoverInterface::class);
        $this->subject = new DismissCrew(
            $this->crewAssignmentRepository,
            $this->crewRepository,
            $this->spacecraftWrapperFactory,
            $this->spacecraftStorageCrewLogic,
            $this->spacecraftSystemManager,
            $this->spacecraftRemover
        );
    }

    public function testDismissesCrewAwayFromSpacecraft(): void
    {
        $user = $this->mock(User::class);
        $crew = $this->mock(Crew::class);
        $assignment = $this->mock(CrewAssignment::class);

        request::setMockVars(['crew_ids' => [42]]);

        $this->game->shouldReceive('setView')->with(ShowCrewManagement::VIEW_IDENTIFIER)->once();
        $this->game->shouldReceive('getUser')->andReturn($user);
        $this->game->shouldReceive('getInfo->addInformationf')->with('%d Crewman wurde(n) entlassen', 1)->once();
        $user->shouldReceive('getId')->andReturn(5);
        $this->crewAssignmentRepository->shouldReceive('find')->with(42)->andReturn($assignment)->once();
        $assignment->shouldReceive('getCrew')->andReturn($crew)->once();
        $assignment->shouldReceive('getSpacecraft')->andReturnNull()->once();
        $assignment->shouldReceive('clearAssignment')->once();
        $this->crewAssignmentRepository->shouldReceive('delete')->with($assignment)->once();
        $this->crewRepository->shouldReceive('delete')->with($crew)->once();
        $crew->shouldReceive('getUserId')->andReturn(5)->once();

        $this->subject->handle($this->game);
    }

    public function testRemovesEmptyEscapePod(): void
    {
        $user = $this->mock(User::class);
        $crew = $this->mock(Crew::class);
        $assignment = $this->mock(CrewAssignment::class);
        $spacecraft = $this->mock(Spacecraft::class);
        $spacecraftOwner = $this->mock(User::class);
        $rump = $this->mock(SpacecraftRump::class);

        request::setMockVars(['crew_ids' => [42]]);

        $this->game->shouldReceive('setView')->with(ShowCrewManagement::VIEW_IDENTIFIER)->once();
        $this->game->shouldReceive('getUser')->andReturn($user);
        $this->game->shouldReceive('getInfo->addInformationf')->with('%d Crewman wurde(n) entlassen', 1)->once();
        $user->shouldReceive('getId')->andReturn(5);
        $this->crewAssignmentRepository->shouldReceive('find')->with(42)->andReturn($assignment)->once();
        $assignment->shouldReceive('getCrew')->andReturn($crew)->once();
        $assignment->shouldReceive('getSpacecraft')->andReturn($spacecraft)->once();
        $assignment->shouldReceive('clearAssignment')->once();
        $this->crewAssignmentRepository->shouldReceive('delete')->with($assignment)->once();
        $this->crewRepository->shouldReceive('delete')->with($crew)->once();
        $crew->shouldReceive('getUserId')->andReturn(5)->once();
        $spacecraft->shouldReceive('getId')->andReturn(23)->once();
        $spacecraft->shouldReceive('getUser')->andReturn($spacecraftOwner)->once();
        $spacecraftOwner->shouldReceive('getId')->andReturn(5)->once();
        $spacecraft->shouldReceive('getRump')->andReturn($rump)->once();
        $rump->shouldReceive('isEscapePods')->andReturnTrue()->once();
        $spacecraft->shouldReceive('getCrewAssignments')->andReturn(new ArrayCollection())->once();
        $this->spacecraftRemover->shouldReceive('remove')->with($spacecraft)->once();

        $this->subject->handle($this->game);
    }

    public function testDeactivatesSystemsOnForeignSensorStationWithoutEnoughOwnerCrew(): void
    {
        $user = $this->mock(User::class);
        $crew = $this->mock(Crew::class);
        $assignment = $this->mock(CrewAssignment::class);
        $spacecraft = $this->mock(Spacecraft::class);
        $spacecraftOwner = $this->mock(User::class);
        $rump = $this->mock(SpacecraftRump::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $information = $this->mock(InformationWrapper::class);
        $remainingCrew = $this->mock(Crew::class);
        $remainingAssignment = $this->mock(CrewAssignment::class);
        $cloakSystem = $this->mock(SpacecraftSystem::class);
        $lifeSupportSystem = $this->mock(SpacecraftSystem::class);

        request::setMockVars(['crew_ids' => [42]]);

        $this->game->shouldReceive('setView')->with(ShowCrewManagement::VIEW_IDENTIFIER)->once();
        $this->game->shouldReceive('getUser')->andReturn($user);
        $this->game->shouldReceive('getInfo')->andReturn($information);
        $user->shouldReceive('getId')->andReturn(5);
        $information->shouldReceive('addInformationf')->with('%d Crewman wurde(n) entlassen', 1)->once();
        $this->crewAssignmentRepository->shouldReceive('find')->with(42)->andReturn($assignment)->once();
        $assignment->shouldReceive('getCrew')->andReturn($crew)->once();
        $assignment->shouldReceive('getSpacecraft')->andReturn($spacecraft)->once();
        $assignment->shouldReceive('clearAssignment')->once();
        $this->crewAssignmentRepository->shouldReceive('delete')->with($assignment)->once();
        $this->crewRepository->shouldReceive('delete')->with($crew)->once();
        $crew->shouldReceive('getUserId')->andReturn(5)->once();
        $spacecraft->shouldReceive('getId')->andReturn(23)->once();
        $spacecraft->shouldReceive('getUser')->andReturn($spacecraftOwner);
        $spacecraftOwner->shouldReceive('getId')->andReturn(8);
        $spacecraft->shouldReceive('getRump')->andReturn($rump)->twice();
        $rump->shouldReceive('isEscapePods')->andReturnFalse()->once();
        $rump->shouldReceive('getRoleId')->andReturn(SpacecraftRumpRoleEnum::SENSOR)->once();
        $spacecraft->shouldReceive('getCrewAssignments')->andReturn(new ArrayCollection([$remainingAssignment]))->once();
        $spacecraft->shouldReceive('getNeededCrewCount')->andReturn(2)->once();
        $remainingAssignment->shouldReceive('getCrew')->andReturn($remainingCrew)->once();
        $remainingCrew->shouldReceive('getUserId')->andReturn(8)->once();
        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')->with($spacecraft)->andReturn($wrapper)->once();
        $this->spacecraftStorageCrewLogic->shouldReceive('postCrewTransfer')->with($wrapper, -1, $information)->once();
        $this->spacecraftSystemManager->shouldReceive('getActiveSystems')->with($spacecraft)->andReturn(new ArrayCollection([$cloakSystem, $lifeSupportSystem]))->once();
        $cloakSystem->shouldReceive('getSystemType')->andReturn(SpacecraftSystemTypeEnum::CLOAK)->twice();
        $lifeSupportSystem->shouldReceive('getSystemType')->andReturn(SpacecraftSystemTypeEnum::LIFE_SUPPORT)->once();
        $this->spacecraftSystemManager->shouldReceive('deactivate')->with($wrapper, SpacecraftSystemTypeEnum::CLOAK, true)->once();

        $this->subject->handle($this->game);
    }
}
