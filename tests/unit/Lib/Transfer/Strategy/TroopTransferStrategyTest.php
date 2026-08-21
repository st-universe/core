<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use request;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class TroopTransferStrategyTest extends StuTestCase
{
    private MockInterface&SpacecraftCrewCalculatorInterface $shipCrewCalculator;

    private MockInterface&TroopTransferUtilityInterface $troopTransferUtility;

    private MockInterface&CrewCreatorInterface $crewCreator;

    private MockInterface&StorageEntityWrapperInterface $source;

    private MockInterface&StorageEntityWrapperInterface $target;

    private MockInterface&InformationInterface $information;

    private TroopTransferStrategy $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->shipCrewCalculator = $this->mock(SpacecraftCrewCalculatorInterface::class);
        $this->troopTransferUtility = $this->mock(TroopTransferUtilityInterface::class);
        $this->crewCreator = $this->mock(CrewCreatorInterface::class);
        $this->source = $this->mock(StorageEntityWrapperInterface::class);
        $this->target = $this->mock(StorageEntityWrapperInterface::class);
        $this->information = $this->mock(InformationInterface::class);

        $this->subject = new TroopTransferStrategy(
            $this->shipCrewCalculator,
            $this->troopTransferUtility,
            $this->crewCreator
        );
    }

    public function testTransferAssignsCrewPositionsWhenBeamingToOwnShip(): void
    {
        request::setMockVars(['crewcount' => 1]);

        $user = $this->mock(User::class);
        $sourceEntity = $this->mock(Colony::class);
        $targetEntity = $this->mock(Ship::class);
        $crew = $this->mock(Crew::class);
        $crewAssignment = $this->mock(CrewAssignment::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(1);
        $crew->shouldReceive('getUser')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($user);
        $crewAssignment->shouldReceive('getCrew')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($crew);

        $sourceEntity->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$crewAssignment]));
        $targetEntity->shouldReceive('getUser')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($user);

        $this->source->shouldReceive('getUser')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($user);
        $this->source->shouldReceive('getMaxTransferrableCrew')
            ->with(false, $user)
            ->once()
            ->andReturn(1);
        $this->source->shouldReceive('checkCrewStorage')
            ->with(1, true, $this->information)
            ->once()
            ->andReturn(true);
        $this->source->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($sourceEntity);
        $this->source->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('Quelle');
        $this->source->shouldReceive('postCrewTransfer')
            ->with(0, $this->target, $this->information)
            ->once();

        $this->target->shouldReceive('getFreeCrewSpace')
            ->with($user)
            ->once()
            ->andReturn(1);
        $this->target->shouldReceive('getUser')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($user);
        $this->target->shouldReceive('acceptsCrewFrom')
            ->with(1, $user, $this->information)
            ->once()
            ->andReturn(true);
        $this->target->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($targetEntity);
        $this->target->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('Ziel');
        $this->target->shouldReceive('postCrewTransfer')
            ->with(0, $this->source, $this->information)
            ->once();

        $this->crewCreator->shouldReceive('createCrewAssignments')
            ->with($targetEntity, $sourceEntity, 1, $user)
            ->once();
        $this->troopTransferUtility->shouldNotReceive('assignCrew');
        $this->information->shouldReceive('addInformationf')
            ->with('Die %s hat %d Crewman %s der %s transferiert.', 'Quelle', 1, 'zu', 'Ziel')
            ->once();

        $this->subject->transfer(true, $this->source, $this->target, $this->information);
    }
}
