<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\InterceptShip;

use Mockery\MockInterface;
use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Interaction\Builder\CheckTypesSetup;
use Stu\Lib\Interaction\Builder\SourceSetup;
use Stu\Lib\Interaction\Builder\TargetSetup;
use Stu\Lib\Interaction\CustomizedInteractionChecker;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InterceptShipCoreInterface;
use Stu\Module\Spacecraft\Lib\SourceAndTargetWrappers;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class InterceptShipTest extends StuTestCase
{
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;
    private MockInterface&InterceptShipCoreInterface $interceptShipCore;
    private MockInterface&PirateReactionInterface $pirateReaction;
    private MockInterface&InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory;
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;

    private InterceptShip $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->interceptShipCore = $this->mock(InterceptShipCoreInterface::class);
        $this->pirateReaction = $this->mock(PirateReactionInterface::class);
        $this->interactionCheckerBuilderFactory = $this->mock(InteractionCheckerBuilderFactoryInterface::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);

        $this->subject = new InterceptShip(
            $this->spacecraftLoader,
            $this->interceptShipCore,
            $this->pirateReaction,
            $this->interactionCheckerBuilderFactory,
            $this->spacecraftSystemManager
        );
    }

    public function testHandleDeactivatesSourceCloakBeforePirateReaction(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $info = $this->mock(InformationWrapper::class);
        $user = $this->mock(User::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $targetWrapper = $this->mock(SpacecraftWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);
        $location = $this->mock(Map::class);
        $sourceSetup = $this->mock(SourceSetup::class);
        $targetSetup = $this->mock(TargetSetup::class);
        $checkTypesSetup = $this->mock(CheckTypesSetup::class);
        $interactionChecker = $this->mock(CustomizedInteractionChecker::class);

        $shipId = 42;
        $targetId = 43;
        $userId = 101;

        request::setMockVars([
            'id' => $shipId,
            'target' => $targetId,
        ]);

        $wrappers = new SourceAndTargetWrappers($wrapper);
        $wrappers->setTarget($targetWrapper);

        $game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();
        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->spacecraftLoader->shouldReceive('getWrappersBySourceAndUserAndTarget')
            ->with($shipId, $userId, $targetId)
            ->once()
            ->andReturn($wrappers);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($target);

        $this->interactionCheckerBuilderFactory->shouldReceive('createInteractionChecker')
            ->withNoArgs()
            ->once()
            ->andReturn($sourceSetup);
        $sourceSetup->shouldReceive('setSource')
            ->with($ship)
            ->once()
            ->andReturn($targetSetup);
        $targetSetup->shouldReceive('setTarget')
            ->with($target)
            ->once()
            ->andReturn($checkTypesSetup);
        $checkTypesSetup->shouldReceive('setCheckTypes')
            ->with([
                InteractionCheckType::EXPECT_SOURCE_ENABLED,
                InteractionCheckType::EXPECT_SOURCE_SUFFICIENT_CREW,
                InteractionCheckType::EXPECT_TARGET_NO_VACATION
            ])
            ->once()
            ->andReturn($interactionChecker);
        $interactionChecker->shouldReceive('check')
            ->with($info)
            ->once()
            ->andReturnTrue();

        $target->shouldReceive('isWarped')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('canIntercept')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($location);
        $target->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($location);
        $ship->shouldReceive('getDockedTo')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($wrapper, SpacecraftSystemTypeEnum::CLOAK, true)
            ->once()
            ->globally()
            ->ordered();

        $this->pirateReaction->shouldReceive('checkForPirateReaction')
            ->with($target, PirateReactionTriggerEnum::ON_INTERCEPTION_BEFORE, $ship)
            ->once()
            ->globally()
            ->ordered()
            ->andReturnFalse();

        $this->interceptShipCore->shouldReceive('intercept')
            ->with($wrapper, $targetWrapper, $info, true)
            ->once()
            ->globally()
            ->ordered();

        $this->pirateReaction->shouldReceive('checkForPirateReaction')
            ->with($target, PirateReactionTriggerEnum::ON_INTERCEPTION_AFTER, $ship)
            ->once()
            ->globally()
            ->ordered()
            ->andReturnFalse();

        $this->subject->handle($game);
    }
}
