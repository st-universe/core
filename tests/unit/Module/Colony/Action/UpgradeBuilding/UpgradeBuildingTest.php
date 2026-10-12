<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\UpgradeBuilding;

use PHPUnit\Framework\Attributes\TestWith;
use request;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\View\ShowInformation\ShowInformation;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingUpgrade;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildingFieldAlternativeRepositoryInterface;
use Stu\Orm\Repository\BuildingUpgradeRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuTestCase;

final class UpgradeBuildingTest extends StuTestCase
{
    #[TestWith([0])]
    #[TestWith([90])]
    #[TestWith([180])]
    #[TestWith([270])]
    public function testUpgradePreservesRotationAfterRemovingPreviousBuilding(int $rotation): void
    {
        request::setMockVars(['buildingid' => 23]);

        $oldBuilding = $this->mock(Building::class);
        $oldBuilding->shouldReceive('getId')->andReturn(42);
        $newBuilding = $this->mock(Building::class);
        $newBuilding->shouldReceive('getId')->andReturn(43);
        $newBuilding->shouldReceive('getName')->andReturn('Ausgebautes Gebäude');
        $field = new PlanetField()
            ->setColonySandbox(new ColonySandbox())
            ->setBuilding($oldBuilding)
            ->setRotation($rotation)
            ->setActive(1);

        $upgrade = $this->mock(BuildingUpgrade::class);
        $upgrade->shouldReceive('getUpgradeFromBuildingId')->andReturn(42);
        $upgrade->shouldReceive('getResearchId')->andReturn(0);
        $upgrade->shouldReceive('getBuilding')->andReturn($newBuilding);

        $upgradeRepository = $this->mock(BuildingUpgradeRepositoryInterface::class);
        $upgradeRepository->shouldReceive('find')->with(23)->once()->andReturn($upgrade);
        $fieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $fieldRepository->shouldReceive('save')->with($field)->once();
        $alternativeRepository = $this->mock(BuildingFieldAlternativeRepositoryInterface::class);
        $alternativeRepository->shouldReceive('getByBuildingAndFieldType')->with(43, 0)->once()->andReturnNull();

        $user = new User();
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->with(ShowInformation::VIEW_IDENTIFIER)->once();
        $context->shouldReceive('getUser')->andReturn($user);
        $context->shouldReceive('getSessionString')->andReturn('session');
        $context->shouldReceive('addExecuteJS')->with("refreshHost('session');")->once();
        $context->shouldReceive('getInfo')->andReturn(new InformationWrapper());
        $fieldProvider = $this->mock(PlanetFieldHostProviderInterface::class);
        $fieldProvider->shouldReceive('loadFieldViaRequestParameter')->with($user)->once()->andReturn($field);
        $buildingAction = $this->mock(BuildingActionInterface::class);
        $buildingAction->shouldReceive('remove')->with($field, $context, true)->once()->andReturnUsing(
            static function (PlanetField $field): void {
                $field->clearBuilding();
            }
        );
        $buildingManager = $this->mock(BuildingManagerInterface::class);
        $buildingManager->shouldReceive('finish')->with($field)->once()->andReturnNull();
        $componentRegistration = $this->mock(ComponentRegistrationInterface::class);
        $componentRegistration->shouldReceive('addComponentUpdate')->times(3)->andReturnSelf();

        $subject = new UpgradeBuilding(
            $upgradeRepository,
            $fieldRepository,
            $alternativeRepository,
            $this->mock(ResearchedRepositoryInterface::class),
            $fieldProvider,
            $this->mock(StorageManagerInterface::class),
            $this->mock(ColonyRepositoryInterface::class),
            $buildingAction,
            $buildingManager,
            $componentRegistration
        );

        $subject->handle($context);

        self::assertSame($rotation, $field->getRotation());
        self::assertSame($newBuilding, $field->getBuilding());
        self::assertTrue($field->getActivateAfterBuild());
    }
}
