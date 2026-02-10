<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class BuildingReactivationHandlerTest extends StuTestCase
{
    private MockInterface&PlanetFieldRepositoryInterface $planetFieldRepository;

    private BuildingReactivationHandler $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->subject = new BuildingReactivationHandler($this->planetFieldRepository);
    }

    public function testHandleAfterUpgradeFinishClearsUpgradedFieldForNonColonyHost(): void
    {
        $field = $this->mock(PlanetField::class);
        $host = $this->mock(ColonySandbox::class);
        $activateCalls = 0;

        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->once()
            ->andReturn($host);
        $field->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $field->shouldReceive('setReactivateAfterUpgrade')
            ->with(null)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();

        $result = $this->subject->handleAfterUpgradeFinish(
            $field,
            true,
            function () use (&$activateCalls): bool {
                $activateCalls++;
                return true;
            }
        );

        $this->assertSame(0, $result);
        $this->assertSame(0, $activateCalls);
    }

    public function testHandleAfterUpgradeFinishClearsAllMarkersIfBuildingWasNotActivated(): void
    {
        $field = $this->mock(PlanetField::class);
        $host = $this->mock(Colony::class);
        $fieldA = $this->mock(PlanetField::class);
        $fieldB = $this->mock(PlanetField::class);
        $activateCalls = 0;
        $upgradedFieldId = 7;

        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->once()
            ->andReturn($host);
        $field->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($upgradedFieldId);
        $field->shouldReceive('setReactivateAfterUpgrade')
            ->with(null)
            ->once();

        $host->shouldReceive('getPlanetFields')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$fieldA, $fieldB]));

        $fieldA->shouldReceive('getReactivateAfterUpgrade')
            ->withNoArgs()
            ->once()
            ->andReturn($upgradedFieldId);
        $fieldA->shouldReceive('setReactivateAfterUpgrade')
            ->with(null)
            ->once();

        $fieldB->shouldReceive('getReactivateAfterUpgrade')
            ->withNoArgs()
            ->once()
            ->andReturn(999);

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();
        $this->planetFieldRepository->shouldReceive('save')
            ->with($fieldA)
            ->once();

        $result = $this->subject->handleAfterUpgradeFinish(
            $field,
            false,
            function () use (&$activateCalls): bool {
                $activateCalls++;
                return true;
            }
        );

        $this->assertSame(0, $result);
        $this->assertSame(0, $activateCalls);
    }

    public function testHandleAfterUpgradeFinishReactivatesMarkedFieldsWhenActivated(): void
    {
        $field = $this->mock(PlanetField::class);
        $host = $this->mock(Colony::class);
        $fieldA = $this->mock(PlanetField::class);
        $fieldB = $this->mock(PlanetField::class);
        $upgradedFieldId = 7;

        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->once()
            ->andReturn($host);
        $field->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($upgradedFieldId);
        $field->shouldReceive('setReactivateAfterUpgrade')
            ->with(null)
            ->once();

        $host->shouldReceive('getPlanetFields')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$fieldA, $fieldB]));

        foreach ([$fieldA, $fieldB] as $reactivationField) {
            $reactivationField->shouldReceive('getReactivateAfterUpgrade')
                ->withNoArgs()
                ->once()
                ->andReturn($upgradedFieldId);
            $reactivationField->shouldReceive('setReactivateAfterUpgrade')
                ->with(null)
                ->once();
        }

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();
        $this->planetFieldRepository->shouldReceive('save')
            ->with($fieldA)
            ->once();
        $this->planetFieldRepository->shouldReceive('save')
            ->with($fieldB)
            ->once();

        $activateCalls = 0;

        $result = $this->subject->handleAfterUpgradeFinish(
            $field,
            true,
            function (PlanetField $reactivationField) use (&$activateCalls, $fieldA): bool {
                $activateCalls++;
                return $reactivationField === $fieldA;
            }
        );

        $this->assertSame(1, $result);
        $this->assertSame(2, $activateCalls);
    }
}

