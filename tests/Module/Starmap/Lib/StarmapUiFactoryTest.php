<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Mockery\MockInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\StuTestCase;

class StarmapUiFactoryTest extends StuTestCase
{
    /** @var MapRepositoryInterface&MockInterface */
    private MockInterface $mapRepository;

    /** @var StarSystemMapRepositoryInterface&MockInterface */
    private MockInterface $starSystemMapRepository;

    private StarmapUiFactory $subject;

    protected function setUp(): void
    {
        $this->mapRepository = $this->mock(MapRepositoryInterface::class);
        $this->starSystemMapRepository = $this->mock(StarSystemMapRepositoryInterface::class);

        $this->subject = new StarmapUiFactory(
            $this->mapRepository,
            $this->starSystemMapRepository
        );
    }

    public function testCreateMapSectionHelperCreates(): void
    {
        static::assertInstanceOf(
            MapSectionHelper::class,
            $this->subject->createMapSectionHelper()
        );
    }

    public function testCreateYRowCreates(): void
    {
        static::assertInstanceOf(
            YRow::class,
            $this->subject->createYRow(
                111,
                222,
                333,
                444,
                555
            )
        );
    }

    public function testCreateUserYRowCreates(): void
    {
        static::assertInstanceOf(
            UserYRow::class,
            $this->subject->createUserYRow(
                $this->mock(UserInterface::class),
                111,
                222,
                333,
                444,
                555
            )
        );
    }
}
