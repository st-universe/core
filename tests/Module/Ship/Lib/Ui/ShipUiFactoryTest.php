<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Mockery\MockInterface;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;
use Stu\StuTestCase;

class ShipUiFactoryTest extends StuTestCase
{
    /** @var MockInterface&UserLayerRepositoryInterface */
    private MockInterface $userLayerRepository;

    /** @var MockInterface&UserMapRepositoryInterface */
    private MockInterface $userMapRepository;

    /** @var MockInterface&EncodedMapInterface */
    private MockInterface $encodedMap;

    private MockInterface $shipRepository;

    private ShipUiFactory $subject;

    protected function setUp(): void
    {
        $this->userLayerRepository = $this->mock(UserLayerRepositoryInterface::class);
        $this->userMapRepository = $this->mock(UserMapRepositoryInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->encodedMap = $this->mock(EncodedMapInterface::class);

        $this->subject = new ShipUiFactory(
            $this->userLayerRepository,
            $this->userMapRepository,
            $this->shipRepository,
            $this->encodedMap
        );
    }

    public function testCreateVisualNavPanel(): void
    {
        static::assertInstanceOf(
            VisualNavPanel::class,
            $this->subject->createVisualNavPanel(
                $this->mock(ShipInterface::class),
                $this->mock(UserInterface::class),
                $this->mock(LoggerUtilInterface::class),
                true,
                true,
            )
        );
    }

    public function testCreateVisualNavPanelEntryReturnsInstance(): void
    {
        static::assertInstanceOf(
            VisualNavPanelEntry::class,
            $this->subject->createVisualNavPanelEntry()
        );
    }

    public function testCreateVisualNavPanelRowReturnsInstance(): void
    {
        static::assertInstanceOf(
            VisualNavPanelRow::class,
            $this->subject->createVisualNavPanelRow()
        );
    }
}
