<?php

declare(strict_types=1);

namespace Stu\Module\Index\Lib;

use Override;
use Stu\Orm\Entity\FactionInterface;
use Stu\StuTestCase;

class UiItemFactoryTest extends StuTestCase
{
    private UiItemFactory $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->subject = new UiItemFactory();
    }

    public function testCreateFactionItemReturnsValue(): void
    {
        static::assertInstanceOf(
            FactionItem::class,
            $this->subject->createFactionItem(
                $this->mock(FactionInterface::class),
                666
            )
        );
    }
}
