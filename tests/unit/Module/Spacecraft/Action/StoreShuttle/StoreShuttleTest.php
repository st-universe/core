<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\StoreShuttle;

use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use request;
use Stu\ActionControllerTestCase;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Interaction\Builder\CheckTypesSetup;
use Stu\Lib\Interaction\Builder\SourceSetup;
use Stu\Lib\Interaction\Builder\TargetSetup;
use Stu\Lib\Interaction\CustomizedInteractionChecker;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\SourceAndTargetWrappersInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;

class StoreShuttleTest extends ActionControllerTestCase
{
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;
    private MockInterface&StorageManagerInterface $storageManager;
    private MockInterface&EntityManagerInterface $entityManager;
    private MockInterface&TroopTransferUtilityInterface $troopTransferUtility;
    private MockInterface&SpacecraftRemoverInterface $spacecraftRemover;
    private MockInterface&InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory;

    private StoreShuttle $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->storageManager = $this->mock(StorageManagerInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->troopTransferUtility = $this->mock(TroopTransferUtilityInterface::class);
        $this->spacecraftRemover = $this->mock(SpacecraftRemoverInterface::class);
        $this->interactionCheckerBuilderFactory = $this->mock(InteractionCheckerBuilderFactoryInterface::class);

        $this->subject = new StoreShuttle(
            $this->spacecraftLoader,
            $this->storageManager,
            $this->entityManager,
            $this->troopTransferUtility,
            $this->spacecraftRemover,
            $this->interactionCheckerBuilderFactory
        );
    }

    public function testHandleRejectsTractoredShuttle(): void
    {
        $user = $this->mock(User::class);
        $info = $this->mock(InformationWrapper::class);
        $wrappers = $this->mock(SourceAndTargetWrappersInterface::class);
        $spacecraftWrapper = $this->mock(SpacecraftWrapperInterface::class);
        $shuttleWrapper = $this->mock(SpacecraftWrapperInterface::class);
        $spacecraft = $this->mock(Spacecraft::class);
        $shuttle = $this->mock(Ship::class);
        $rump = $this->mock(SpacecraftRump::class);
        $commodity = $this->mock(Commodity::class);
        $sourceSetup = $this->mock(SourceSetup::class);
        $targetSetup = $this->mock(TargetSetup::class);
        $checkTypesSetup = $this->mock(CheckTypesSetup::class);
        $interactionChecker = $this->mock(CustomizedInteractionChecker::class);

        request::setMockVars([
            'id' => 42,
            'target' => 43,
        ]);

        $this->game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->twice()
            ->andReturn($info);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->spacecraftLoader->shouldReceive('getWrappersBySourceAndUserAndTarget')
            ->with(42, 1, 43)
            ->once()
            ->andReturn($wrappers);
        $wrappers->shouldReceive('getSource')
            ->withNoArgs()
            ->once()
            ->andReturn($spacecraftWrapper);
        $wrappers->shouldReceive('getTarget')
            ->withNoArgs()
            ->once()
            ->andReturn($shuttleWrapper);
        $spacecraftWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($spacecraft);
        $shuttleWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($shuttle);

        $shuttle->shouldReceive('getRump')
            ->withNoArgs()
            ->once()
            ->andReturn($rump);
        $rump->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodity);
        $commodity->shouldReceive('isShuttle')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->interactionCheckerBuilderFactory->shouldReceive('createInteractionChecker')
            ->withNoArgs()
            ->once()
            ->andReturn($sourceSetup);
        $sourceSetup->shouldReceive('setSource')
            ->with($spacecraft)
            ->once()
            ->andReturn($targetSetup);
        $targetSetup->shouldReceive('setTarget')
            ->with($shuttle)
            ->once()
            ->andReturn($checkTypesSetup);
        $checkTypesSetup->shouldReceive('setCheckTypes')
            ->with([
                InteractionCheckType::EXPECT_SOURCE_SUFFICIENT_CREW,
                InteractionCheckType::EXPECT_SOURCE_UNCLOAKED,
                InteractionCheckType::EXPECT_SOURCE_UNSHIELDED,
                InteractionCheckType::EXPECT_SOURCE_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNSHIELDED,
                InteractionCheckType::EXPECT_TARGET_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_SAME_USER,
                InteractionCheckType::EXPECT_TARGET_ALSO_IN_FINISHED_WEB,
            ])
            ->once()
            ->andReturn($interactionChecker);
        $interactionChecker->shouldReceive('check')
            ->with($info)
            ->once()
            ->andReturnTrue();

        $shuttle->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $info->shouldReceive('addInformation')
            ->with('Das Shuttle kann nicht eingesammelt werden, solange es von einem Traktorstrahl festgehalten wird')
            ->once();

        $this->storageManager->shouldReceive('upperStorage')->never();
        $this->spacecraftRemover->shouldReceive('remove')->never();

        $this->subject->handle($this->game);
    }
}
