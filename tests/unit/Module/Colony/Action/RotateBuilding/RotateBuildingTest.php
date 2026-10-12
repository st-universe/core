<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RotateBuilding;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\View\ShowField\ShowField;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class RotateBuildingTest extends StuTestCase
{
    private MockInterface&PlanetFieldHostProviderInterface $planetFieldHostProvider;
    private MockInterface&PlanetFieldRepositoryInterface $planetFieldRepository;
    private MockInterface&ActionControllerContext $context;
    private PlanetField $field;
    private RotateBuilding $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->planetFieldHostProvider = $this->mock(PlanetFieldHostProviderInterface::class);
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->context = $this->mock(ActionControllerContext::class);
        $this->field = new PlanetField();
        $user = $this->mock(User::class);

        $this->context->shouldReceive('getUser')->withNoArgs()->andReturn($user);
        $this->context->shouldReceive('setView')->with(ShowField::VIEW_IDENTIFIER);
        $this->planetFieldHostProvider->shouldReceive('loadFieldViaRequestParameter')
            ->with($user)->andReturn($this->field);

        $this->subject = new RotateBuilding($this->planetFieldHostProvider, $this->planetFieldRepository);
    }

    #[DataProvider('rotationProvider')]
    public function testRotatesInBothDirections(int $initial, string $direction, int $expected): void
    {
        request::setMockVars(['direction' => $direction]);
        $this->field->setBuilding(new Building()->setRotation(true))->setRotation($initial);
        $this->planetFieldRepository->shouldReceive('save')->with($this->field)->once();

        $this->subject->handle($this->context);

        self::assertSame($expected, $this->field->getRotation());
    }

    public static function rotationProvider(): iterable
    {
        yield [0, 'right', 90];
        yield [90, 'right', 180];
        yield [180, 'right', 270];
        yield [270, 'right', 0];
        yield [0, 'left', 270];
        yield [90, 'left', 0];
        yield [180, 'left', 90];
        yield [270, 'left', 180];
    }

    public function testDoesNotRotateEmptyField(): void
    {
        request::setMockVars(['direction' => 'right']);
        $this->planetFieldRepository->shouldNotReceive('save');

        $this->subject->handle($this->context);

        self::assertSame(0, $this->field->getRotation());
    }

    public function testDoesNotRotateBuildingWithoutRotationEnabled(): void
    {
        request::setMockVars(['direction' => 'right']);
        $this->field->setBuilding(new Building());
        $this->planetFieldRepository->shouldNotReceive('save');

        $this->subject->handle($this->context);

        self::assertSame(0, $this->field->getRotation());
    }

    public function testDoesNotRotateBuildingUnderConstruction(): void
    {
        request::setMockVars(['direction' => 'right']);
        $this->field->setBuilding(new Building()->setRotation(true))->setActive(time() + 3600);
        $this->planetFieldRepository->shouldNotReceive('save');

        $this->subject->handle($this->context);

        self::assertSame(0, $this->field->getRotation());
    }

    public function testRejectsInvalidDirection(): void
    {
        request::setMockVars(['direction' => '180']);
        $this->field->setBuilding(new Building()->setRotation(true))->setRotation(90);
        $this->planetFieldRepository->shouldNotReceive('save');

        $this->subject->handle($this->context);

        self::assertSame(90, $this->field->getRotation());
    }

    public function testRequiresSessionCheck(): void
    {
        self::assertTrue($this->subject->performSessionCheck());
    }
}
