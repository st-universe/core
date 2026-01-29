<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Mockery;
use Mockery\MockInterface;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class BuildingActionTest extends StuTestCase
{
    /**
     * @var MockInterface&StorageManagerInterface
     */
    private $storageManager;
    /**
     * @var MockInterface&BuildingManagerInterface
     */
    private $buildingManager;
    /**
     * @var MockInterface&ColonyLibFactoryInterface
     */
    private $colonyLibFactory;
    /**
     * @var MockInterface&PlanetFieldRepositoryInterface
     */
    private $planetFieldRepository;

    /**
     * @var MockInterface&PlanetField
     */
    private $field;

    private BuildingActionInterface $subject;


    #[\Override]
    public function setUp(): void
    {
        $this->storageManager = Mockery::mock(StorageManagerInterface::class);
        $this->buildingManager = Mockery::mock(BuildingManagerInterface::class);
        $this->colonyLibFactory = Mockery::mock(ColonyLibFactoryInterface::class);
        $this->planetFieldRepository = Mockery::mock(PlanetFieldRepositoryInterface::class);

        $this->field = $this->mock(PlanetField::class);

        $this->subject = new BuildingAction(
            $this->storageManager,
            $this->buildingManager,
            $this->colonyLibFactory,
            $this->planetFieldRepository
        );
    }

    public function testRemoveExpectRemovalOfPreviousBuilding(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $building = $this->mock(Building::class);

        $this->field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($building);
        $this->field->shouldReceive('getFieldId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->field->shouldReceive('getHost')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(ColonySandbox::class));

        $building->shouldReceive('isRemovable')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $building->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('BUILDING');

        $this->buildingManager->shouldReceive('remove')
            ->with($this->field, false)
            ->once();

        $game->shouldReceive('getInfo->addInformationf')
            ->with(
                '%s auf Feld %d wurde demontiert',
                'BUILDING',
                42
            )
            ->once();

        $this->subject->remove($this->field, $game);
    }
}
