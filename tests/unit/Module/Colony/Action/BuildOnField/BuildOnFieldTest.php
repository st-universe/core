<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildOnField;

use Doctrine\Common\Collections\ArrayCollection;
use request;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Module\Colony\View\ShowInformation\ShowInformation;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\PlanetFieldTypeBuilding;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildingFieldAlternativeRepositoryInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuTestCase;

final class BuildOnFieldTest extends StuTestCase
{
    public function testNewBuildingStartsWithZeroRotation(): void
    {
        request::setMockVars(['buildingid' => 42]);

        $field = new PlanetField()
            ->setColonySandbox(new ColonySandbox())
            ->setFieldType(101)
            ->setRotation(270);
        $building = $this->mock(Building::class);
        $building->shouldReceive('getId')->andReturn(42);
        $building->shouldReceive('getResearchId')->andReturn(0);
        $building->shouldReceive('getBuildableFields')->andReturn(new ArrayCollection([101 => new PlanetFieldTypeBuilding()]));
        $building->shouldReceive('hasLimitColony')->andReturnFalse();
        $building->shouldReceive('getName')->andReturn('Gebäude');

        $buildingRepository = $this->mock(BuildingRepositoryInterface::class);
        $buildingRepository->shouldReceive('find')->with(42)->once()->andReturn($building);
        $alternativeRepository = $this->mock(BuildingFieldAlternativeRepositoryInterface::class);
        $alternativeRepository->shouldReceive('getByBuildingAndFieldType')->with(42, 101)->once()->andReturnNull();
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(UserConstants::USER_NOONE);
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->with(ShowInformation::VIEW_IDENTIFIER)->once();
        $context->shouldReceive('getUser')->andReturn($user);
        $context->shouldReceive('getSessionString')->andReturn('session');
        $context->shouldReceive('addExecuteJS')->with("refreshHost('session');")->once();
        $context->shouldReceive('getInfo')->andReturn(new InformationWrapper());
        $fieldProvider = $this->mock(PlanetFieldHostProviderInterface::class);
        $fieldProvider->shouldReceive('loadFieldViaRequestParameter')->with($user)->once()->andReturn($field);
        $buildingManager = $this->mock(BuildingManagerInterface::class);
        $buildingManager->shouldReceive('finish')->with($field)->once()->andReturnNull();
        $componentRegistration = $this->mock(ComponentRegistrationInterface::class);
        $componentRegistration->shouldReceive('addComponentUpdate')->times(3)->andReturnSelf();

        $subject = new BuildOnField(
            $fieldProvider,
            $alternativeRepository,
            $this->mock(ResearchedRepositoryInterface::class),
            $buildingRepository,
            $this->mock(PlanetFieldRepositoryInterface::class),
            $this->mock(StorageManagerInterface::class),
            $this->mock(ColonyRepositoryInterface::class),
            $this->mock(BuildingActionInterface::class),
            $this->mock(PlanetFieldTypeRetrieverInterface::class),
            $buildingManager,
            $componentRegistration
        );

        $subject->handle($context);

        self::assertSame(0, $field->getRotation());
        self::assertSame($building, $field->getBuilding());
    }
}
