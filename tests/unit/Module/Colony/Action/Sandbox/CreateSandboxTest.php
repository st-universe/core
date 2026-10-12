<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\Sandbox;

use request;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Colony\View\Sandbox\ShowColonySandbox;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

final class CreateSandboxTest extends StuTestCase
{
    public function testSandboxCopiesBuildingRotation(): void
    {
        request::setMockVars(['colonyid' => 42, 'name' => 'Sandbox']);

        $user = new User();
        $colony = new Colony();
        $colony->setChangeable(new ColonyChangeable($colony));
        $user->getColonies()->set(42, $colony);
        $building = new Building()->setIntegrity(100);
        $field = new PlanetField()->setBuilding($building)->setRotation(180)->setActive(1);
        $colony->getPlanetFields()->set(3, $field);
        $sandbox = new ColonySandbox();
        $sandboxField = new PlanetField();

        $sandboxRepository = $this->mock(ColonySandboxRepositoryInterface::class);
        $sandboxRepository->shouldReceive('prototype')->once()->andReturn($sandbox);
        $sandboxRepository->shouldReceive('save')->with($sandbox)->once();
        $fieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $fieldRepository->shouldReceive('prototype')->once()->andReturn($sandboxField);
        $fieldRepository->shouldReceive('save')->with($sandboxField)->once();
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->with(ShowColonySandbox::VIEW_IDENTIFIER)->once();
        $context->shouldReceive('getUser')->andReturn($user);
        $context->shouldReceive('getInfo')->andReturn(new InformationWrapper());
        $context->shouldReceive('setViewContext')->with(ViewContextMetadataTypeEnum::HOST, $sandbox)->once();

        new CreateSandbox($sandboxRepository, $fieldRepository)->handle($context);

        self::assertSame(180, $sandboxField->getRotation());
        self::assertSame($building, $sandboxField->getBuilding());
        self::assertSame($sandboxField, $sandbox->getPlanetFields()->get(3));
    }
}
