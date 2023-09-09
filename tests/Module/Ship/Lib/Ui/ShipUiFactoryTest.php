<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Mockery\MockInterface;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Lib\Map\VisualPanel\VisualNavPanelEntry;
use Stu\Lib\Map\VisualPanel\VisualPanelEntryData;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;
use Stu\StuTestCase;

class ShipUiFactoryTest extends StuTestCase
{
    /** @var MockInterface&UserMapRepositoryInterface */
    private MockInterface $userMapRepository;

    /** @var MockInterface&EncodedMapInterface */
    private MockInterface $encodedMap;

    private MockInterface $shipRepository;

    private ShipUiFactory $subject;

    protected function setUp(): void
    {
        $this->userMapRepository = $this->mock(UserMapRepositoryInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->encodedMap = $this->mock(EncodedMapInterface::class);

        $this->subject = new ShipUiFactory(
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
            $this->subject->createVisualNavPanelEntry(
                $this->mock(VisualPanelEntryData::class),
                null,
                $this->mock(ShipInterface::class)
            )
        );
    }
}
