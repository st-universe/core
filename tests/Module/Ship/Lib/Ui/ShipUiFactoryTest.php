<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;
use VisualNavPanel;

class ShipUiFactoryTest extends StuTestCase
{
    private ShipUiFactory $subject;

    protected function setUp(): void
    {
        $this->subject = new ShipUiFactory();
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
}
